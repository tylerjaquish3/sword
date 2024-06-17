<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerseComment extends Model
{
    protected $guarded = [];

    public function verse()
    {
        return $this->belongsTo(Verse::class);
    }

}