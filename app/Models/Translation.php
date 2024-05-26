<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    public function verses()
    {
        return $this->hasMany(Verse::class);
    }
}