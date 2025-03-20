<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prayer extends Model
{

    protected $guarded = [];

    public function type()
    {
        return $this->belongsTo(PrayerType::class, 'prayer_type_id');
    }
}