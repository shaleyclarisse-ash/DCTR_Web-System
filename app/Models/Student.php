<?php

namespace App\Models;

use App\Models\Concerns\HasAccount;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasAccount;

    protected $guarded = [];
    protected $casts = [
        'birthdate' => 'date',
        'learner_classifications' => 'array',
        'documents_submitted' => 'array',
        'permanent_address_same_as_current' => 'boolean',
    ];

    public function user()        { return $this->belongsTo(User::class); }
    public function enrollments() { return $this->hasMany(Enrollment::class); }

    public function currentEnrollment()
    {
        return $this->hasOne(Enrollment::class)->latestOfMany();
    }
}
