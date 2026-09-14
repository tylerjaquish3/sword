<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountabilityCheckIn extends Model
{
    protected $guarded = [];

    protected $casts = [
        'corner_man_prayer_request' => 'boolean',
        'corner_man_asked_how_to_pray' => 'boolean',
        'corner_man_encouragement' => 'boolean',
        'corner_man_multiple_touchpoints' => 'boolean',
        'is_shared' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function guestComments()
    {
        return $this->hasMany(AccountabilityGuestComment::class);
    }
}
