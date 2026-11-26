<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseEnrollment extends Model
{
    protected $guarded = [];
    protected $casts = ['enrolled_at' => 'date'];

    public function trainee()  { return $this->belongsTo(Trainee::class); }
    public function course()   { return $this->belongsTo(Course::class); }
    public function sessions() { return $this->hasMany(TrainingSession::class); }

    public function completedSessions()
    {
        return $this->hasMany(TrainingSession::class)->where('status', 'completed');
    }

    /** Training progress in percent (completed sessions / required sessions). */
    public function getProgressAttribute(): int
    {
        $done     = $this->completed_sessions_count ?? $this->completedSessions()->count();
        $required = max(1, (int) $this->course->required_sessions);

        return min(100, (int) round($done / $required * 100));
    }

    public function getAvgScoreAttribute()
    {
        return $this->completedSessions()->whereNotNull('score')->avg('score');
    }
}
