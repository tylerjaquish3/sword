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

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * Scope to get comments for a specific verse (by chapter and verse number)
     */
    public function scopeForVerse($query, $chapterId, $verseNumber)
    {
        return $query->where('chapter_id', $chapterId)
                     ->where('verse_number', $verseNumber);
    }
}