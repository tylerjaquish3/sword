<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopicNote extends Model
{
    protected $guarded = [];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function verses()
    {
        return $this->belongsToMany(Verse::class, 'topic_note_verses')
                    ->with('chapter.book');
    }
}
