<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Section;
use App\Models\Student;
use App\Support\Options;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Senior High School enrollment. */
class EnrollmentController extends CrudController
{
    protected function model(): string { return Enrollment::class; }
    protected function title(): string { return 'Enrollments'; }
    protected function route(): string { return 'enrollments'; }
    protected function with(): array { return ['student', 'section']; }
    protected function searchable(): bool { return true; }

    protected function applySearch(Builder $q, string $term): void
    {
        $q->where(function ($w) use ($term) {
            $w->where('school_year', 'like', "%{$term}%")
              ->orWhereHas('student', fn ($s) => $s->where('first_name', 'like', "%{$term}%")
                  ->orWhere('last_name', 'like', "%{$term}%")->orWhere('lrn', 'like', "%{$term}%"))
              ->orWhereHas('section', fn ($s) => $s->where('name', 'like', "%{$term}%")->orWhere('strand', 'like', "%{$term}%"));
        });
    }

    protected function columns(): array
    {
        return [
            'Student'     => fn ($r) => $r->student->full_name,
            'Section'     => fn ($r) => $r->section->label,
            'School year' => 'school_year',
            'Semester'    => 'semester',
            'Fee'         => fn ($r) => '₱'.number_format($r->fee, 2),
            'Status'      => fn ($r) => $this->badge($r->status),
        ];
    }

    protected function fields(?Model $record = null): array
    {
        return [
            ['name' => 'student_id', 'label' => 'Student', 'type' => 'select', 'rules' => 'required',
                'options' => fn () => Student::where('status', 'active')->orWhere('id', $record?->student_id)->orderBy('last_name')->get()
                    ->mapWithKeys(fn ($s) => [$s->id => $s->full_name.($s->lrn ? " ({$s->lrn})" : '')])->all()],
            ['name' => 'section_id', 'label' => 'Section', 'type' => 'select', 'rules' => 'required',
                'options' => fn () => Section::orderBy('grade_level')->orderBy('name')->get()->mapWithKeys(fn ($s) => [$s->id => $s->label])->all()],
            ['name' => 'school_year', 'label' => 'School year', 'rules' => 'required|string|max:9', 'default' => Options::schoolYear()],
            ['name' => 'semester', 'label' => 'Semester', 'type' => 'select', 'rules' => 'required', 'options' => Options::semesters()],
            ['name' => 'fee', 'label' => 'Tuition / fees (₱) - added to the account on enrollment', 'type' => 'number', 'step' => '0.01', 'rules' => 'required|numeric|min:0', 'default' => 0],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'rules' => 'required', 'default' => 'enrolled',
                'options' => Options::make(['enrolled', 'dropped', 'completed'])],
        ];
    }

    protected function validateExtra(array $data, ?Model $record): void
    {
        $errors = [];

        $duplicate = Enrollment::where('student_id', $data['student_id'])
            ->where('school_year', $data['school_year'])->where('semester', $data['semester'])
            ->when($record, fn ($q) => $q->where('id', '!=', $record->id))->exists();
        if ($duplicate) {
            $errors['student_id'] = 'This student is already enrolled for that school year and semester.';
        }

        if ($data['status'] === 'enrolled') {
            $section  = Section::findOrFail($data['section_id']);
            $enrolled = Enrollment::where('section_id', $section->id)->where('status', 'enrolled')
                ->when($record, fn ($q) => $q->where('id', '!=', $record->id))->count();
            if ($enrolled >= $section->capacity) {
                $errors['section_id'] = "This section is already full ({$section->capacity} students).";
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    protected function saved(Model $record, array $data, bool $created, Request $request): ?string
    {
        if ($created && $record->fee > 0) {
            $record->student->charges()->create([
                'description' => "Tuition/fees SY {$record->school_year} - {$record->semester} Sem",
                'amount'      => $record->fee,
            ]);

            return 'A charge of ₱'.number_format($record->fee, 2).' was added to the student account.';
        }

        return null;
    }
}
