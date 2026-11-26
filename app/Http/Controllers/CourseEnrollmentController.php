<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CreatesLoginAccount;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Payment;
use App\Models\Trainee;
use App\Models\User;
use App\Support\Options;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Driving-course enrollment (TDC / PDC). */
class CourseEnrollmentController extends CrudController
{
    use CreatesLoginAccount;

    protected function model(): string { return CourseEnrollment::class; }
    protected function title(): string { return 'Course Enrollments'; }
    protected function route(): string { return 'course-enrollments'; }
    protected function with(): array { return ['trainee', 'course']; }
    protected function counts(): array { return ['completedSessions']; }
    protected function searchable(): bool { return true; }

    protected function applySearch(Builder $q, string $term): void
    {
        $q->where(function ($w) use ($term) {
            $w->whereHas('trainee', fn ($s) => $s->where('first_name', 'like', "%{$term}%")->orWhere('last_name', 'like', "%{$term}%"))
              ->orWhereHas('course', fn ($s) => $s->where('code', 'like', "%{$term}%")->orWhere('name', 'like', "%{$term}%"));
        });
    }

    protected function columns(): array
    {
        return [
            'Trainee'  => fn ($r) => $r->trainee->full_name,
            'Program'  => fn ($r) => $r->program_type ?: $r->course->code.' - '.$r->course->name,
            'Enrolled' => fn ($r) => $r->enrolled_at->format('M d, Y'),
            'Progress' => fn ($r) => $r->completed_sessions_count.' / '.$r->course->required_sessions.' sessions ('.$r->progress.'%)',
            'Status'   => fn ($r) => $this->badge($r->status),
        ];
    }

    protected function fields(?Model $record = null): array
    {
        $trainee = $record?->trainee;
        $traineeOptions = fn () => ($record
            ? Trainee::whereKey($record->trainee_id)->get()
            : Trainee::where('status', 'active')->orderBy('last_name')->get())
            ->mapWithKeys(fn ($t) => [$t->id => $t->full_name])->all();

        return [
            ['name' => 'applicant_section', 'label' => 'Applicant information', 'type' => 'section'],
            ['name' => 'trainee_id', 'label' => 'Existing trainee (leave blank to register a new applicant)', 'type' => 'select',
                'rules' => 'nullable|integer|exists:trainees,id', 'options' => $traineeOptions],
            ['name' => 'last_name', 'label' => 'Surname', 'rules' => 'nullable|string|max:100', 'virtual' => true, 'default' => $trainee?->last_name],
            ['name' => 'first_name', 'label' => 'First name', 'rules' => 'nullable|string|max:100', 'virtual' => true, 'default' => $trainee?->first_name],
            ['name' => 'middle_name', 'label' => 'Middle name', 'rules' => 'nullable|string|max:100', 'virtual' => true, 'default' => $trainee?->middle_name],
            ['name' => 'middle_initial', 'label' => 'Middle initial', 'rules' => 'nullable|string|max:10', 'virtual' => true, 'default' => $trainee?->middle_initial],
            ['name' => 'birthdate', 'label' => 'Birthdate', 'type' => 'date', 'rules' => 'nullable|date|before:today', 'virtual' => true, 'default' => $trainee?->birthdate?->format('Y-m-d')],
            ['name' => 'applicant_age', 'label' => 'Age (calculated from birthdate)', 'type' => 'age', 'birthdate' => 'birthdate'],
            ['name' => 'place_of_birth', 'label' => 'Place of birth', 'rules' => 'nullable|string|max:150', 'virtual' => true, 'default' => $trainee?->place_of_birth],
            ['name' => 'sex', 'label' => 'Gender', 'type' => 'select', 'rules' => 'nullable|string|max:10', 'virtual' => true,
                'options' => Options::make(['Male', 'Female']), 'default' => $trainee?->sex],
            ['name' => 'marital_status', 'label' => 'Marital status', 'type' => 'select', 'rules' => 'nullable|string|max:30', 'virtual' => true,
                'options' => Options::make(['Single', 'Married', 'Widowed', 'Separated']), 'default' => $trainee?->marital_status],
            ['name' => 'contact_no', 'label' => 'Mobile / contact number', 'rules' => 'nullable|string|max:30', 'virtual' => true, 'default' => $trainee?->contact_no],
            ['name' => 'email', 'label' => 'Email address', 'type' => 'email', 'rules' => 'nullable|email|max:255', 'virtual' => true, 'default' => $trainee?->email],

            ['name' => 'address_section', 'label' => 'Address', 'type' => 'section'],
            ['name' => 'house_no', 'label' => 'House number', 'rules' => 'nullable|string|max:100', 'virtual' => true, 'default' => $trainee?->house_no],
            ['name' => 'street_name', 'label' => 'Purok / street name', 'rules' => 'nullable|string|max:150', 'virtual' => true, 'default' => $trainee?->street_name],
            ['name' => 'barangay', 'label' => 'Barangay', 'rules' => 'nullable|string|max:100', 'virtual' => true, 'default' => $trainee?->barangay],
            ['name' => 'city', 'label' => 'City / municipality', 'rules' => 'nullable|string|max:100', 'virtual' => true, 'default' => $trainee?->city],
            ['name' => 'province', 'label' => 'Province', 'rules' => 'nullable|string|max:100', 'virtual' => true, 'default' => $trainee?->province],
            ['name' => 'region', 'label' => 'Region', 'rules' => 'nullable|string|max:100', 'virtual' => true, 'default' => $trainee?->region],
            ['name' => 'zip_code', 'label' => 'ZIP / area code', 'rules' => 'nullable|string|max:20', 'virtual' => true, 'default' => $trainee?->zip_code],
            ['name' => 'highest_education', 'label' => 'Highest educational attainment', 'rules' => 'nullable|string|max:100', 'virtual' => true, 'default' => $trainee?->highest_education],
            ['name' => 'facebook_account', 'label' => 'Facebook account name', 'rules' => 'nullable|string|max:255', 'virtual' => true, 'default' => $trainee?->facebook_account],
            ['name' => 'contact_person', 'label' => 'Contact person', 'rules' => 'nullable|string|max:255', 'virtual' => true, 'default' => $trainee?->contact_person],
            ['name' => 'contact_person_no', 'label' => 'Contact person number', 'rules' => 'nullable|string|max:30', 'virtual' => true, 'default' => $trainee?->contact_person_no],
            ['name' => 'lto_client_id', 'label' => 'LTO client ID number', 'rules' => 'nullable|string|max:50', 'virtual' => true, 'default' => $trainee?->lto_client_id],

            ['name' => 'program_section', 'label' => 'Program enrollment', 'type' => 'section'],
            ['name' => 'program_type', 'label' => 'Program type', 'type' => 'select',
                'rules' => $record ? 'nullable|string|max:30' : 'required|string|max:30',
                'options' => fn () => Options::make(['TDC', 'PDC', 'Student Permit', 'Additional Code', 'Renewal'])
                    + Course::orderBy('code')->pluck('code', 'code')->all(),
                'default' => $record?->course?->code],
            ['name' => 'course_id', 'label' => 'Related course', 'type' => 'select', 'rules' => 'required',
                'options' => fn () => Course::orderBy('code')->get()->mapWithKeys(fn ($c) => [$c->id => $c->code.' - '.$c->name.' (₱'.number_format($c->fee, 2).')'])->all()],
            ['name' => 'schedule_preferences', 'label' => 'Training preferences', 'type' => 'section'],
            ['name' => 'training_purpose', 'label' => 'Training purpose', 'type' => 'textarea', 'rules' => 'nullable|string|max:1000', 'col' => 12],
            ['name' => 'license_no', 'label' => 'Student permit / license number', 'rules' => 'nullable|string|max:50', 'virtual' => true, 'default' => $trainee?->license_no],
            ['name' => 'preferred_schedule', 'label' => 'Preferred schedule', 'type' => 'select', 'rules' => 'nullable|string|max:20',
                'options' => ['Weekdays' => 'Weekdays (Monday–Friday)', 'Weekends' => 'Weekends (Saturday–Sunday)']],
            ['name' => 'preferred_transmission', 'label' => 'Preferred vehicle transmission', 'type' => 'select', 'rules' => 'nullable|string|max:20',
                'options' => Options::make(['Manual', 'Automatic', 'Mix'])],

            ['name' => 'payment_section', 'label' => 'Initial payment (balance is calculated from course fee and payments)', 'type' => 'section'],
            ['name' => 'or_number', 'label' => 'OR number', 'rules' => 'nullable|string|max:30', 'virtual' => true],
            ['name' => 'amount_paid', 'label' => 'Total amount paid', 'type' => 'number', 'step' => '0.01', 'rules' => 'nullable|numeric|min:0', 'virtual' => true, 'default' => 0],
            ['name' => 'payment_method', 'label' => 'Payment method', 'type' => 'select', 'rules' => 'nullable|string|max:20', 'virtual' => true,
                'options' => ['cash' => 'Cash', 'gcash' => 'GCash', 'bank' => 'Bank', 'other' => 'Other'], 'default' => 'cash'],
            ['name' => 'paid_at', 'label' => 'Payment date', 'type' => 'date', 'rules' => 'nullable|date', 'virtual' => true, 'default' => now()->toDateString()],

            ['name' => 'schedule_section', 'label' => 'Requested training schedule', 'type' => 'section'],
            ['name' => 'preferred_day', 'label' => 'Preferred day', 'type' => 'select', 'rules' => 'nullable|string|max:15',
                'options' => Options::make(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            ['name' => 'preferred_date', 'label' => 'Preferred training date', 'type' => 'date', 'rules' => 'nullable|date'],
            ['name' => 'preferred_time', 'label' => 'Preferred training time', 'type' => 'time', 'rules' => 'nullable|date_format:H:i'],

            ['name' => 'enrollment_details', 'label' => 'Enrollment details', 'type' => 'section'],
            ['name' => 'enrolled_at', 'label' => 'Date of application / enrollment', 'type' => 'date', 'rules' => 'required|date', 'default' => now()->toDateString()],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'rules' => 'required', 'default' => 'ongoing',
                'options' => Options::make(['ongoing', 'completed', 'dropped'])],
            ...(! $record ? [[
                'name' => 'create_account', 'label' => 'Create a trainee portal login using the email above',
                'type' => 'checkbox', 'rules' => 'nullable|boolean', 'virtual' => true, 'col' => 12,
            ]] : []),
        ];
    }

    protected function validateExtra(array $data, ?Model $record): void
    {
        $errors = [];
        $traineeId = $data['trainee_id'] ?? $record?->trainee_id;

        if (! $traineeId) {
            foreach ([
                'first_name' => 'First name',
                'last_name' => 'Surname',
                'birthdate' => 'Birthdate',
                'sex' => 'Gender',
                'marital_status' => 'Marital status',
                'contact_no' => 'Mobile / contact number',
                'email' => 'Email address',
            ] as $field => $label) {
                if (blank($data[$field] ?? null)) {
                    $errors[$field] = "{$label} is required for a new applicant.";
                }
            }
        }

        $duplicate = $traineeId && $data['status'] === 'ongoing' && CourseEnrollment::where('trainee_id', $traineeId)
            ->where('course_id', $data['course_id'])->where('status', 'ongoing')
            ->when($record, fn ($q) => $q->where('id', '!=', $record->id))->exists();

        if ($duplicate) {
            $errors['course_id'] = 'This trainee already has an ongoing enrollment in this course.';
        }

        $amountPaid = (float) ($data['amount_paid'] ?? 0);
        if ($amountPaid > 0) {
            if (blank($data['or_number'] ?? null)) {
                $errors['or_number'] = 'An OR number is required when recording a payment.';
            } elseif (Payment::where('or_number', $data['or_number'])->exists()) {
                $errors['or_number'] = 'This OR number has already been used.';
            }

            $fee = (float) Course::findOrFail($data['course_id'])->fee;
            if ($amountPaid > $fee) {
                $errors['amount_paid'] = 'The initial payment cannot be more than the selected course fee.';
            }
        }

        $trainee = $traineeId ? Trainee::find($traineeId) : null;
        if (! empty($data['create_account']) && ! $trainee?->user_id) {
            $email = $data['email'] ?? $trainee?->email;
            if (blank($email)) {
                $errors['email'] = 'Email is required to create a trainee login.';
            } elseif (User::where('email', $email)->exists()) {
                $errors['email'] = 'This email already has an account.';
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    protected function saving(array $data, ?Model $record): array
    {
        $traineeId = $data['trainee_id'] ?? $record?->trainee_id;
        $trainee = $traineeId ? Trainee::findOrFail($traineeId) : null;
        $profileFields = [
            'first_name', 'middle_initial', 'middle_name', 'last_name', 'birthdate', 'place_of_birth',
            'sex', 'marital_status', 'contact_no', 'email', 'house_no', 'street_name', 'barangay',
            'city', 'province', 'region', 'zip_code', 'highest_education', 'facebook_account',
            'contact_person', 'contact_person_no', 'lto_client_id', 'license_no',
        ];
        $profile = Arr::only($data, $profileFields);
        $addressParts = ['house_no', 'street_name', 'barangay', 'city', 'province', 'region', 'zip_code'];
        $address = array_filter(array_map(fn ($field) => trim((string) ($data[$field] ?? '')), $addressParts));

        if ($address) {
            $profile['address'] = implode(', ', $address);
        }

        if ($trainee) {
            $updates = array_filter($profile, fn ($value) => $value !== null && $value !== '');
            $trainee->fill($updates)->save();
        } else {
            $profile['address'] = $profile['address'] ?? null;
            $profile['status'] = 'active';
            $trainee = Trainee::create($profile);
        }

        $data['trainee_id'] = $trainee->id;

        return $data;
    }

    public function store(Request $request)
    {
        return DB::transaction(fn () => parent::store($request));
    }

    public function update(Request $request, $id)
    {
        return DB::transaction(fn () => parent::update($request, $id));
    }

    protected function saved(Model $record, array $data, bool $created, Request $request): ?string
    {
        $messages = [];

        if ($created && $record->course->fee > 0) {
            $record->trainee->charges()->create([
                'description' => 'Course fee - '.$record->course->code,
                'amount'      => $record->course->fee,
            ]);

            $messages[] = 'A course charge was added to the trainee account.';
        }

        if ($created && (float) ($data['amount_paid'] ?? 0) > 0) {
            $payment = $record->trainee->payments()->create([
                'or_number' => $data['or_number'],
                'amount' => $data['amount_paid'],
                'method' => $data['payment_method'] ?? 'cash',
                'paid_at' => $data['paid_at'] ?? now()->toDateString(),
                'received_by' => auth()->id(),
                'remarks' => 'Initial registration payment - '.($data['program_type'] ?? $record->course->code),
            ]);
            $messages[] = 'Initial payment recorded with OR '.$payment->or_number.'.';
        }

        $accountMessage = $this->createAccount($record->trainee, $data, 'trainee');
        if ($accountMessage) {
            $messages[] = $accountMessage;
        }

        return $messages ? implode(' ', $messages) : null;
    }
}
