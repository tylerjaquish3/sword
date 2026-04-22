<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ChapterComment extends Model
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

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
