<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    protected $guarded = [];
    protected $casts = ['date' => 'date'];

    public function enrollment() { return $this->belongsTo(Enrollment::class); }
    public function schedule()   { return $this->belongsTo(ClassSchedule::class, 'class_schedule_id'); }
}
