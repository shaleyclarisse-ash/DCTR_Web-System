<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $guarded = [];
    protected $casts = ['fee' => 'decimal:2'];

    public function student()     { return $this->belongsTo(Student::class); }
    public function section()     { return $this->belongsTo(Section::class); }
    public function grades()      { return $this->hasMany(Grade::class); }
    public function attendances() { return $this->hasMany(StudentAttendance::class); }
}
