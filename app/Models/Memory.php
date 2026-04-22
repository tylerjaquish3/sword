<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Memory extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('user', function (Builder $builder) {
            $builder->where('user_id', auth()->id());
        });

        static::creating(function (self $model) {
            $model->user_id ??= auth()->id();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verses()
    {
        return $this->belongsToMany(Verse::class)->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->whereNull('completed_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    public function isCompleted()
    {
        return !is_null($this->completed_at);
    }
}
