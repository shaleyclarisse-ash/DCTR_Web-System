<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Charge extends Model
{
    protected $guarded = [];
    protected $casts = ['amount' => 'decimal:2', 'due_date' => 'date'];

    public function payable() { return $this->morphTo(); }
}
