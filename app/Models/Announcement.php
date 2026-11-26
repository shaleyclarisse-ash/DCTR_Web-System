<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $guarded = [];

    public function author() { return $this->belongsTo(User::class, 'created_by'); }

    /** Announcements visible to a given user (by department). Usage: Announcement::visibleTo($user). */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->role === 'admin') {
            return $query;
        }

        $dept = in_array($user->role, ['shs_staff', 'teacher', 'student']) ? 'shs' : 'driving';

        return $query->whereIn('audience', ['all', $dept]);
    }
}
