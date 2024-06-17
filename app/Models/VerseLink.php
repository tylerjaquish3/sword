<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerseLink extends Model
{
    protected $guarded = [];

    public function verseOne()
    {
        return $this->belongsTo(Verse::class, 'verse_id');
    }

    public function verseTwo()
    {
        return $this->belongsTo(Verse::class, 'linked_verse_id');
    }
}