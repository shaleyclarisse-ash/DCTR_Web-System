<?php

namespace App\Models\Concerns;

use App\Models\Charge;
use App\Models\Document;
use App\Models\Payment;

/** Shared by Student and Trainee: financial account + documents + display name. */
trait HasAccount
{
    public function charges()
    {
        return $this->morphMany(Charge::class, 'payable');
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function getTotalChargesAttribute(): float
    {
        return (float) $this->charges()->sum('amount');
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getBalanceAttribute(): float
    {
        return $this->total_charges - $this->total_paid;
    }

    public function getFullNameAttribute(): string
    {
        $middle = $this->middle_name ?: $this->middle_initial;

        return trim("{$this->last_name}, {$this->first_name} {$middle}");
    }
}
