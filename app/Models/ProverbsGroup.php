<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProverbsGroup extends Model
{
    protected $guarded = [];

    public function verseAssignments()
    {
        return $this->hasMany(ProverbsVerseGroup::class);
    }
}
