<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CreatesLoginAccount;
use App\Models\Student;
use App\Support\Options;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StudentController extends CrudController
{
    use CreatesLoginAccount;

    protected function model(): string { return Student::class; }
    protected function title(): string { return 'Students'; }
    protected function route(): string { return 'students'; }
    protected function hasShow(): bool { return true; }
    protected function search(): array { return ['lrn', 'first_name', 'last_name', 'strand', 'email', 'contact_no']; }

    protected function columns(): array
    {
        return [
            'LRN'     => 'lrn',
            'Name'    => fn ($r) => $r->full_name,
            'Grade'   => fn ($r) => 'Grade '.$r->grade_level,
            'Strand'  => 'strand',
            'Contact' => 'contact_no',
            'Status'  => fn ($r) => $this->badge($r->status),
        ];
    }

    protected function fields(?Model $record = null): array
    {
        $fields = [
            ['name' => 'learner_information', 'label' => 'Learner information', 'type' => 'section'],
            ['name' => 'lrn', 'label' => 'LRN (12 digits)', 'rules' => 'nullable|digits:12', 'unique' => true],
            ['name' => 'learner_entry_type', 'label' => 'Learner type', 'type' => 'select', 'rules' => 'nullable|string|max:40',
                'options' => Options::make(['Grade 10 completer', 'ALS graduate', 'Returning (Balik-Aral)', 'Transfer / Move-in'])],
            ['name' => 'first_name', 'label' => 'First name', 'rules' => 'required|string|max:100'],
            ['name' => 'middle_name', 'label' => 'Middle name', 'rules' => 'nullable|string|max:100'],
            ['name' => 'last_name', 'label' => 'Last name', 'rules' => 'required|string|max:100'],
            ['name' => 'birthdate', 'label' => 'Birthdate', 'type' => 'date', 'rules' => 'nullable|date|before:today'],
            ['name' => 'age_display', 'label' => 'Age (calculated from birthdate)', 'type' => 'age', 'birthdate' => 'birthdate'],
            ['name' => 'place_of_birth', 'label' => 'Place of birth', 'rules' => 'nullable|string|max:150'],
            ['name' => 'sex', 'label' => 'Gender', 'type' => 'select', 'options' => Options::make(['Male', 'Female'])],
            ['name' => 'religion', 'label' => 'Religion', 'rules' => 'nullable|string|max:100'],
            ['name' => 'grade_level', 'label' => 'Grade level', 'type' => 'select', 'rules' => 'required', 'options' => ['11' => 'Grade 11', '12' => 'Grade 12']],
            ['name' => 'program_track', 'label' => 'Program track', 'type' => 'select', 'rules' => 'nullable|string|max:20',
                'options' => ['Academic' => 'Academic Track', 'Tech-Pro' => 'Technical-Professional (Tech-Pro) Track']],
            ['name' => 'strand', 'label' => 'Strand / specialization', 'type' => 'select', 'options' => Options::strands()],
            ['name' => 'learner_classifications', 'label' => 'Learner classification (select all that apply)', 'type' => 'checkboxes', 'rules' => 'nullable|array', 'col' => 12,
                'options' => [
                    '4Ps beneficiary' => '4Ps beneficiary', 'IP community' => 'Indigenous Peoples (IP) community',
                    'PWD' => 'Person with disability (PWD)', 'GIDCA resident' => 'GIDCA resident',
                    'Special needs' => 'Learner with special needs',
                ]],
            ['name' => 'classification_details', 'label' => 'Classification details (if applicable)', 'rules' => 'nullable|string|max:255', 'col' => 12],

            ['name' => 'current_address_section', 'label' => 'Current address', 'type' => 'section'],
            ['name' => 'house_no', 'label' => 'House number', 'rules' => 'nullable|string|max:100'],
            ['name' => 'street_name', 'label' => 'Sitio / street name', 'rules' => 'nullable|string|max:150'],
            ['name' => 'barangay', 'label' => 'Barangay', 'rules' => 'nullable|string|max:100'],
            ['name' => 'city', 'label' => 'Municipality / city', 'rules' => 'nullable|string|max:100'],
            ['name' => 'province', 'label' => 'Province', 'rules' => 'nullable|string|max:100'],
            ['name' => 'country', 'label' => 'Country', 'rules' => 'nullable|string|max:100'],
            ['name' => 'zip_code', 'label' => 'ZIP code', 'rules' => 'nullable|string|max:20'],
            ['name' => 'permanent_address_same_as_current', 'label' => 'Permanent address is the same as current address',
                'type' => 'checkbox', 'rules' => 'nullable|boolean', 'default' => false, 'col' => 12],
            ['name' => 'permanent_house_no', 'label' => 'Permanent house number', 'rules' => 'nullable|string|max:100'],
            ['name' => 'permanent_street_name', 'label' => 'Permanent sitio / street name', 'rules' => 'nullable|string|max:150'],
            ['name' => 'permanent_barangay', 'label' => 'Permanent barangay', 'rules' => 'nullable|string|max:100'],
            ['name' => 'permanent_city', 'label' => 'Permanent municipality / city', 'rules' => 'nullable|string|max:100'],
            ['name' => 'permanent_province', 'label' => 'Permanent province', 'rules' => 'nullable|string|max:100'],
            ['name' => 'permanent_country', 'label' => 'Permanent country', 'rules' => 'nullable|string|max:100'],
            ['name' => 'permanent_zip_code', 'label' => 'Permanent ZIP code', 'rules' => 'nullable|string|max:20'],
            ['name' => 'contact_no', 'label' => 'Contact no.', 'rules' => 'nullable|string|max:30'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'nullable|email|max:255'],

            ['name' => 'parent_guardian_section', 'label' => 'Parent / guardian information', 'type' => 'section'],
            ['name' => 'father_name', 'label' => 'Father’s full name', 'rules' => 'nullable|string|max:255'],
            ['name' => 'mother_maiden_name', 'label' => 'Mother’s maiden name', 'rules' => 'nullable|string|max:255'],
            ['name' => 'guardian_name', 'label' => 'Guardian name', 'rules' => 'nullable|string|max:150'],
            ['name' => 'guardian_contact', 'label' => 'Guardian contact', 'rules' => 'nullable|string|max:30'],
            ['name' => 'guardian_address', 'label' => 'Guardian address', 'type' => 'textarea', 'rules' => 'nullable|string|max:500', 'col' => 12],

            ['name' => 'previous_school_section', 'label' => 'Previous school information (returning / transfer learners)', 'type' => 'section'],
            ['name' => 'last_grade_level_completed', 'label' => 'Last grade level completed', 'rules' => 'nullable|string|max:30'],
            ['name' => 'last_school_attended', 'label' => 'Last school attended', 'rules' => 'nullable|string|max:255'],
            ['name' => 'last_school_year_completed', 'label' => 'Last school year completed', 'rules' => 'nullable|string|max:9'],

            ['name' => 'document_section', 'label' => 'Documentary requirements submitted', 'type' => 'section'],
            ['name' => 'documents_submitted', 'label' => 'Select documents received', 'type' => 'checkboxes', 'rules' => 'nullable|array', 'col' => 12,
                'options' => [
                    'SF 9' => 'SF 9 - Progress Report Card', 'PSA birth certificate' => 'Original PSA Birth Certificate',
                    'ALS / PEPT rating' => 'Certificate of Rating (ALS / PEPT passer)', '4Ps ID' => 'Copy of 4Ps ID',
                    'IP certificate' => 'IP membership / tribal certification', 'PWD ID' => 'Copy of PWD ID',
                    'Indigency proof' => 'Barangay indigency / income proof',
                ]],

            ['name' => 'account_section', 'label' => 'Account status', 'type' => 'section'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'rules' => 'required', 'options' => Options::make(['active', 'inactive', 'graduated'])],
        ];

        if (! $record?->user_id) {
            $fields[] = ['name' => 'create_account', 'label' => 'Create a student portal login (uses the email above)',
                'type' => 'checkbox', 'rules' => 'nullable|boolean', 'virtual' => true, 'col' => 12];
        }

        return $fields;
    }

    protected function saving(array $data, ?Model $record): array
    {
        $currentParts = ['house_no', 'street_name', 'barangay', 'city', 'province', 'country', 'zip_code'];
        $data['address'] = $this->joinedAddress($data, $currentParts, $record?->address);

        if (filter_var($data['permanent_address_same_as_current'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            foreach ($currentParts as $part) {
                $data['permanent_'.$part] = $data[$part] ?? null;
            }
            $data['permanent_address'] = $data['address'];
        } else {
            $permanentParts = array_map(fn ($part) => 'permanent_'.$part, $currentParts);
            $data['permanent_address'] = $this->joinedAddress($data, $permanentParts, $record?->permanent_address);
        }

        return $data;
    }

    private function joinedAddress(array $data, array $parts, ?string $fallback): ?string
    {
        $values = array_filter(array_map(fn ($part) => trim((string) ($data[$part] ?? '')), $parts));

        return $values ? implode(', ', $values) : ($fallback ?: null);
    }

    protected function validateExtra(array $data, ?Model $record): void
    {
        $allowedStrands = match ($data['program_track'] ?? null) {
            'Academic' => ['ASSH', 'STEM', 'B&E', 'SHW'],
            'Tech-Pro' => ['ASET', 'CBT'],
            default => [],
        };

        if (! empty($data['strand']) && $allowedStrands && ! in_array($data['strand'], $allowedStrands, true)) {
            throw ValidationException::withMessages([
                'strand' => 'Choose a strand or specialization that belongs to the selected program track.',
            ]);
        }

        $this->checkAccount($data, $record);
    }

    protected function saved(Model $record, array $data, bool $created, Request $request): ?string
    {
        return $this->createAccount($record, $data, 'student');
    }

    public function show($id)
    {
        $student = Student::with(['enrollments.section', 'documents'])->findOrFail($id);

        return view('students.show', [
            'student'  => $student,
            'charges'  => $student->charges()->latest()->get(),
            'payments' => $student->payments()->latest('paid_at')->get(),
        ]);
    }
}
