<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigestGuestComment extends Model
{
    protected $guarded = [];

    public function sharedDigest()
    {
        return $this->belongsTo(SharedDigest::class);
    }

    public function displayName(): string
    {
        return $this->name ?: 'Anonymous';
    }
}
