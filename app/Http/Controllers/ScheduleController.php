<?php

namespace App\Http\Controllers;

use App\Models\ClassSchedule;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use App\Support\Options;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/** Class schedules of the Senior High School (with conflict detection). */
class ScheduleController extends CrudController
{
    protected function model(): string { return ClassSchedule::class; }
    protected function title(): string { return 'Class Schedules'; }
    protected function route(): string { return 'class-schedules'; }
    protected function with(): array { return ['section', 'subject', 'teacher']; }
    protected function searchable(): bool { return true; }

    protected function applySearch(Builder $q, string $term): void
    {
        $q->where(function ($w) use ($term) {
            $w->where('room', 'like', "%{$term}%")->orWhere('day', 'like', "%{$term}%")
              ->orWhereHas('subject', fn ($s) => $s->where('name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"))
              ->orWhereHas('section', fn ($s) => $s->where('name', 'like', "%{$term}%"))
              ->orWhereHas('teacher', fn ($s) => $s->where('name', 'like', "%{$term}%"));
        });
    }

    protected function columns(): array
    {
        return [
            'Section' => fn ($r) => $r->section->label,
            'Subject' => fn ($r) => $r->subject->code.' - '.$r->subject->name,
            'Teacher' => fn ($r) => $r->teacher->name ?? '-',
            'Day'     => 'day',
            'Time'    => fn ($r) => $r->time_range,
            'Room'    => 'room',
        ];
    }

    protected function fields(?Model $record = null): array
    {
        return [
            ['name' => 'section_id', 'label' => 'Section', 'type' => 'select', 'rules' => 'required',
                'options' => fn () => Section::orderBy('grade_level')->orderBy('name')->get()->mapWithKeys(fn ($s) => [$s->id => $s->label])->all()],
            ['name' => 'subject_id', 'label' => 'Subject', 'type' => 'select', 'rules' => 'required',
                'options' => fn () => Subject::orderBy('code')->get()->mapWithKeys(fn ($s) => [$s->id => $s->code.' - '.$s->name])->all()],
            ['name' => 'teacher_id', 'label' => 'Teacher', 'type' => 'select',
                'options' => fn () => User::where('role', 'teacher')->orderBy('name')->pluck('name', 'id')->all()],
            ['name' => 'room', 'label' => 'Room', 'rules' => 'nullable|string|max:50'],
            ['name' => 'day', 'label' => 'Day', 'type' => 'select', 'rules' => 'required', 'options' => Options::days()],
            ['name' => 'start_time', 'label' => 'Start time', 'type' => 'time', 'rules' => 'required|date_format:H:i'],
            ['name' => 'end_time', 'label' => 'End time', 'type' => 'time', 'rules' => 'required|date_format:H:i|after:start_time'],
        ];
    }

    protected function validateExtra(array $data, ?Model $record): void
    {
        $base = ClassSchedule::where('day', $data['day'])
            ->where('start_time', '<', $this->hms($data['end_time']))
            ->where('end_time', '>', $this->hms($data['start_time']))
            ->when($record, fn ($q) => $q->where('id', '!=', $record->id));

        $errors = [];
        if ((clone $base)->where('section_id', $data['section_id'])->exists()) {
            $errors['section_id'] = 'This section already has a class at that time.';
        }
        if (! empty($data['teacher_id']) && (clone $base)->where('teacher_id', $data['teacher_id'])->exists()) {
            $errors['teacher_id'] = 'This teacher is already scheduled at that time.';
        }
        if (! empty($data['room']) && (clone $base)->where('room', $data['room'])->exists()) {
            $errors['room'] = 'This room is already in use at that time.';
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    protected function saving(array $data, ?Model $record): array
    {
        $data['start_time'] = $this->hms($data['start_time']);
        $data['end_time']   = $this->hms($data['end_time']);

        return $data;
    }
}
