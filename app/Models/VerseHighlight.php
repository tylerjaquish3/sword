<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerseHighlight extends Model
{
    protected $guarded = [];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
