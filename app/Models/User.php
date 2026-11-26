<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public const ROLES = [
        'admin'         => 'Administrator',
        'shs_staff'     => 'SHS Staff',
        'teacher'       => 'Teacher',
        'driving_staff' => 'Driving School Staff',
        'instructor'    => 'Driving Instructor',
        'student'       => 'Student',
        'trainee'       => 'Trainee',
    ];

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    public function student()    { return $this->hasOne(Student::class); }
    public function trainee()    { return $this->hasOne(Trainee::class); }
    public function instructor() { return $this->hasOne(Instructor::class); }
}
