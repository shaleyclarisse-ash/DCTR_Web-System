<?php

namespace App\Http\Controllers;

use App\Models\CourseEnrollment;
use App\Models\Instructor;
use App\Models\TrainingSession;
use App\Models\Vehicle;
use App\Support\Options;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Driving-training schedule, attendance and progress (one row = one session). */
class TrainingSessionController extends CrudController
{
    protected function model(): string { return TrainingSession::class; }
    protected function title(): string { return 'Training Sessions'; }
    protected function route(): string { return 'training-sessions'; }
    protected function with(): array { return ['courseEnrollment.trainee', 'courseEnrollment.course', 'instructor', 'vehicle']; }
    protected function searchable(): bool { return true; }
    protected function order(Builder $q): Builder { return $q->orderByDesc('session_date')->orderByDesc('start_time'); }

    protected function applySearch(Builder $q, string $term): void
    {
        $q->where(function ($w) use ($term) {
            $w->whereHas('courseEnrollment.trainee', fn ($s) => $s->where('first_name', 'like', "%{$term}%")->orWhere('last_name', 'like', "%{$term}%"))
              ->orWhereHas('instructor', fn ($s) => $s->where('name', 'like', "%{$term}%"))
              ->orWhereHas('vehicle', fn ($s) => $s->where('plate_no', 'like', "%{$term}%"))
              ->orWhere('status', 'like', "%{$term}%");
        });
    }

    protected function columns(): array
    {
        return [
            'Date'       => fn ($r) => $r->session_date->format('M d, Y'),
            'Time'       => fn ($r) => $r->time_range,
            'Trainee'    => fn ($r) => $r->courseEnrollment->trainee->full_name,
            'Course'     => fn ($r) => $r->courseEnrollment->course->code,
            'Instructor' => fn ($r) => $r->instructor->name,
            'Vehicle'    => fn ($r) => $r->vehicle->plate_no ?? '-',
            'Status'     => fn ($r) => $this->badge($r->status),
            'Attendance' => fn ($r) => $r->attendance ? $this->badge($r->attendance) : '-',
            'Score'      => fn ($r) => $r->score ?? '-',
        ];
    }

    protected function fields(?Model $record = null): array
    {
        return [
            ['name' => 'course_enrollment_id', 'label' => 'Trainee / course', 'type' => 'select', 'rules' => 'required', 'col' => 12,
                'options' => fn () => CourseEnrollment::with('trainee', 'course')
                    ->where(fn ($q) => $q->where('status', 'ongoing')->orWhere('id', $record?->course_enrollment_id))->get()
                    ->sortBy(fn ($e) => $e->trainee->last_name)
                    ->mapWithKeys(fn ($e) => [$e->id => $e->trainee->full_name.' - '.$e->course->code])->all()],
            ['name' => 'instructor_id', 'label' => 'Instructor', 'type' => 'select', 'rules' => 'required',
                'options' => fn () => Instructor::where('status', 'active')->orWhere('id', $record?->instructor_id)->orderBy('name')->pluck('name', 'id')->all()],
            ['name' => 'vehicle_id', 'label' => 'Vehicle (leave blank for classroom/TDC)', 'type' => 'select',
                'options' => fn () => Vehicle::where('status', 'available')->orWhere('id', $record?->vehicle_id)->orderBy('plate_no')->get()
                    ->mapWithKeys(fn ($v) => [$v->id => $v->plate_no.' - '.$v->model])->all()],
            ['name' => 'session_date', 'label' => 'Date', 'type' => 'date', 'rules' => 'required|date'],
            ['name' => 'start_time', 'label' => 'Start time', 'type' => 'time', 'rules' => 'required|date_format:H:i'],
            ['name' => 'end_time', 'label' => 'End time', 'type' => 'time', 'rules' => 'required|date_format:H:i|after:start_time'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'rules' => 'required', 'default' => 'scheduled',
                'options' => Options::make(['scheduled', 'completed', 'cancelled', 'no_show'])],
            ['name' => 'attendance', 'label' => 'Attendance', 'type' => 'select', 'options' => Options::make(['present', 'absent'])],
            ['name' => 'score', 'label' => 'Assessment score (0-100)', 'type' => 'number', 'rules' => 'nullable|integer|between:0,100'],
            ['name' => 'remarks', 'label' => 'Remarks', 'rules' => 'nullable|string|max:255'],
        ];
    }

    /** Detect instructor / vehicle / trainee double-booking. */
    protected function validateExtra(array $data, ?Model $record): void
    {
        if ($data['status'] === 'cancelled') {
            return;
        }

        $base = TrainingSession::where('session_date', $data['session_date'])
            ->where('status', '!=', 'cancelled')
            ->where('start_time', '<', $this->hms($data['end_time']))
            ->where('end_time', '>', $this->hms($data['start_time']))
            ->when($record, fn ($q) => $q->where('id', '!=', $record->id));

        $errors = [];

        if ((clone $base)->where('instructor_id', $data['instructor_id'])->exists()) {
            $errors['instructor_id'] = 'This instructor already has a session at that time.';
        }
        if (! empty($data['vehicle_id']) && (clone $base)->where('vehicle_id', $data['vehicle_id'])->exists()) {
            $errors['vehicle_id'] = 'This vehicle is already booked at that time.';
        }

        $traineeId = CourseEnrollment::findOrFail($data['course_enrollment_id'])->trainee_id;
        if ((clone $base)->whereHas('courseEnrollment', fn ($q) => $q->where('trainee_id', $traineeId))->exists()) {
            $errors['course_enrollment_id'] = 'This trainee already has a session at that time.';
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

    protected function saved(Model $record, array $data, bool $created, Request $request): ?string
    {
        return $record->refreshEnrollmentStatus()
            ? 'All required sessions are done - the course enrollment is now marked completed.'
            : null;
    }
}
