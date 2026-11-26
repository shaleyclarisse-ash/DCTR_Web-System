<?php

namespace App\Models;

use App\Models\Concerns\HasAccount;
use Illuminate\Database\Eloquent\Model;

class Trainee extends Model
{
    use HasAccount;

    protected $guarded = [];
    protected $casts = ['birthdate' => 'date'];

    public function user()             { return $this->belongsTo(User::class); }
    public function courseEnrollments() { return $this->hasMany(CourseEnrollment::class); }
}
