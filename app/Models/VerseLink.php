<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerseLink extends Model
{
    protected $guarded = [];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function linkedChapter()
    {
        return $this->belongsTo(Chapter::class, 'linked_chapter_id');
    }
}