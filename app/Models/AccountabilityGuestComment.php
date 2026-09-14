<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountabilityGuestComment extends Model
{
    protected $guarded = [];

    public function checkIn()
    {
        return $this->belongsTo(AccountabilityCheckIn::class, 'accountability_check_in_id');
    }

    public function displayName(): string
    {
        return $this->name ?: 'Anonymous';
    }
}
