<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProverbsVerseGroup extends Model
{
    protected $guarded = [];

    public function group()
    {
        return $this->belongsTo(ProverbsGroup::class, 'proverbs_group_id');
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
