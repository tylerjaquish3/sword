<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $guarded = [];

    public function notes()
    {
        return $this->hasMany(TopicNote::class)->latest();
    }

    public function excludedReferences()
    {
        return $this->hasMany(TopicExcludedReference::class);
    }
}
