<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{

    protected $guarded = [];

    public $timestamps = false;

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

}