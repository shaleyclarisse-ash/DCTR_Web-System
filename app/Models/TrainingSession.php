<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingSession extends Model
{
    protected $guarded = [];
    protected $casts = ['session_date' => 'date'];

    public function courseEnrollment() { return $this->belongsTo(CourseEnrollment::class); }
    public function instructor()       { return $this->belongsTo(Instructor::class); }
    public function vehicle()          { return $this->belongsTo(Vehicle::class); }

    public function getTimeRangeAttribute(): string
    {
        return substr($this->start_time, 0, 5).' - '.substr($this->end_time, 0, 5);
    }

    /** Mark the course enrollment "completed" once every required session is done. */
    public function refreshEnrollmentStatus(): bool
    {
        $enrollment = $this->courseEnrollment()->with('course')->first();

        if ($enrollment->status === 'ongoing'
            && $enrollment->completedSessions()->count() >= $enrollment->course->required_sessions) {
            $enrollment->update(['status' => 'completed']);

            return true;
        }

        return false;
    }
}
