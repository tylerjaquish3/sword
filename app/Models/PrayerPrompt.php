<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PrayerPrompt extends Model
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

    public static function forToday(): ?self
    {
        $dayOfWeek = now()->dayOfWeek; // 0=Sun, 6=Sat

        return static::where('day_of_week', $dayOfWeek)->first()
            ?? static::whereNull('day_of_week')->first();
    }
}
