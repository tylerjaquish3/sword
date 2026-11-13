<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopicExcludedReference extends Model
{
    protected $guarded = [];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }
}
