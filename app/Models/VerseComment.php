<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class VerseComment extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        static::addGlobalScope('user', function (Builder $builder) {
            $builder->where('user_id', auth()->id());
        });

        static::creating(function (self $model) {
            $model->user_id ??= auth()->id();
        });
    }

    public function verse()
    {
        return $this->belongsTo(Verse::class);
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function scopeForVerse($query, $chapterId, $verseNumber)
    {
        return $query->where('chapter_id', $chapterId)
                     ->where('verse_number', $verseNumber);
    }
}
