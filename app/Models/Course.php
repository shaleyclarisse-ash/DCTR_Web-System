<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $guarded = [];
    protected $casts = ['fee' => 'decimal:2'];

    public function courseEnrollments() { return $this->hasMany(CourseEnrollment::class); }
}
