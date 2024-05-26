<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Verse extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    public function translation()
    {
        return $this->belongsTo(Translation::class);
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}