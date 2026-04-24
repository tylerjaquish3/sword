<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{

    protected $guarded = [];

    public $timestamps = false;

    protected static function booted(): void
    {
        static::addGlobalScope('canonical_order', fn (Builder $q) => $q->orderBy('sort_order'));
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

}