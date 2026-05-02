<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVersePreference extends Model
{
    protected $guarded = [];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
