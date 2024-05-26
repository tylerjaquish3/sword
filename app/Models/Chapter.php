<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{

    protected $guarded = [];

    public $timestamps = false;

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function verses()
    {
        return $this->hasMany(Verse::class);
    }

}