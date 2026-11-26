<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CreatesLoginAccount;
use App\Models\Trainee;
use App\Support\Options;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TraineeController extends CrudController
{
    use CreatesLoginAccount;

    protected function model(): string { return Trainee::class; }
    protected function title(): string { return 'Trainees'; }
    protected function route(): string { return 'trainees'; }
    protected function hasShow(): bool { return true; }
    protected function search(): array { return ['first_name', 'last_name', 'license_no', 'email', 'contact_no']; }

    protected function columns(): array
    {
        return [
            'Name'       => fn ($r) => $r->full_name,
            'Contact'    => 'contact_no',
            'Permit/License no.' => 'license_no',
            'Status'     => fn ($r) => $this->badge($r->status),
        ];
    }

    protected function fields(?Model $record = null): array
    {
        $fields = [
            ['name' => 'personal_information', 'label' => 'Applicant information', 'type' => 'section'],
            ['name' => 'first_name', 'label' => 'First name', 'rules' => 'required|string|max:100'],
            ['name' => 'middle_initial', 'label' => 'Middle initial', 'rules' => 'nullable|string|max:10'],
            ['name' => 'middle_name', 'label' => 'Middle name', 'rules' => 'nullable|string|max:100'],
            ['name' => 'last_name', 'label' => 'Last name', 'rules' => 'required|string|max:100'],
            ['name' => 'birthdate', 'label' => 'Birthdate', 'type' => 'date', 'rules' => 'nullable|date|before:today'],
            ['name' => 'age_display', 'label' => 'Age (calculated from birthdate)', 'type' => 'age', 'birthdate' => 'birthdate'],
            ['name' => 'place_of_birth', 'label' => 'Place of birth', 'rules' => 'nullable|string|max:150'],
            ['name' => 'sex', 'label' => 'Gender', 'type' => 'select', 'options' => Options::make(['Male', 'Female'])],
            ['name' => 'marital_status', 'label' => 'Marital status', 'type' => 'select', 'rules' => 'nullable|string|max:30',
                'options' => Options::make(['Single', 'Married', 'Widowed', 'Separated'])],
            ['name' => 'license_no', 'label' => 'Student permit / license number', 'rules' => 'nullable|string|max:50'],

            ['name' => 'address_section', 'label' => 'Address', 'type' => 'section'],
            ['name' => 'house_no', 'label' => 'House number', 'rules' => 'nullable|string|max:100'],
            ['name' => 'street_name', 'label' => 'Purok / street name', 'rules' => 'nullable|string|max:150'],
            ['name' => 'barangay', 'label' => 'Barangay', 'rules' => 'nullable|string|max:100'],
            ['name' => 'city', 'label' => 'City / municipality', 'rules' => 'nullable|string|max:100'],
            ['name' => 'province', 'label' => 'Province', 'rules' => 'nullable|string|max:100'],
            ['name' => 'region', 'label' => 'Region', 'rules' => 'nullable|string|max:100'],
            ['name' => 'zip_code', 'label' => 'ZIP / area code', 'rules' => 'nullable|string|max:20'],
            ['name' => 'contact_no', 'label' => 'Contact no.', 'rules' => 'nullable|string|max:30'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'nullable|email|max:255'],

            ['name' => 'other_information', 'label' => 'Additional registration details', 'type' => 'section'],
            ['name' => 'highest_education', 'label' => 'Highest educational attainment', 'rules' => 'nullable|string|max:100'],
            ['name' => 'facebook_account', 'label' => 'Facebook account name', 'rules' => 'nullable|string|max:255'],
            ['name' => 'lto_client_id', 'label' => 'LTO client ID number', 'rules' => 'nullable|string|max:50'],
            ['name' => 'contact_person', 'label' => 'Contact person / emergency contact', 'rules' => 'nullable|string|max:255'],
            ['name' => 'contact_person_no', 'label' => 'Contact person number', 'rules' => 'nullable|string|max:30'],

            ['name' => 'account_status', 'label' => 'Account status', 'type' => 'section'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'rules' => 'required', 'options' => Options::make(['active', 'inactive'])],
        ];

        if (! $record?->user_id) {
            $fields[] = ['name' => 'create_account', 'label' => 'Create a trainee portal login (uses the email above)',
                'type' => 'checkbox', 'rules' => 'nullable|boolean', 'virtual' => true, 'col' => 12];
        }

        return $fields;
    }

    protected function saving(array $data, ?Model $record): array
    {
        $parts = ['house_no', 'street_name', 'barangay', 'city', 'province', 'region', 'zip_code'];
        $values = array_filter(array_map(fn ($part) => trim((string) ($data[$part] ?? '')), $parts));
        $data['address'] = $values ? implode(', ', $values) : ($record?->address ?: null);

        return $data;
    }

    protected function validateExtra(array $data, ?Model $record): void
    {
        $this->checkAccount($data, $record);
    }

    protected function saved(Model $record, array $data, bool $created, Request $request): ?string
    {
        return $this->createAccount($record, $data, 'trainee');
    }

    public function show($id)
    {
        $trainee = Trainee::with(['courseEnrollments.course', 'documents'])->findOrFail($id);

        foreach ($trainee->courseEnrollments as $ce) {
            $ce->loadCount('completedSessions');
        }

        return view('trainees.show', [
            'trainee'  => $trainee,
            'charges'  => $trainee->charges()->latest()->get(),
            'payments' => $trainee->payments()->latest('paid_at')->get(),
        ]);
    }
}
