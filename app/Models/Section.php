<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $guarded = [];

    public function enrollments() { return $this->hasMany(Enrollment::class); }
    public function schedules()   { return $this->hasMany(ClassSchedule::class); }

    public function getLabelAttribute(): string
    {
        return "Gr {$this->grade_level} {$this->strand} - {$this->name}";
    }
}
