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

    /**
     * Order a week's completed and newly-started memory sets chronologically,
     * so "finished A, started B, finished B" reads in the order it happened.
     *
     * @return \Illuminate\Support\Collection<int, array{type: string, memory: self, at: \Carbon\Carbon}>
     */
    public static function weeklyTimeline($completed, $started)
    {
        $startedAt = [];
        $events = collect();

        foreach ($started as $m) {
            $at = $m->start_date->copy()->startOfDay();
            // start_date has no time; when the set was created that same day, use the real time
            if ($m->created_at && $m->created_at->isSameDay($m->start_date)) {
                $at = $m->created_at->copy();
            }
            $startedAt[$m->id] = $at;
            $events->push(['type' => 'started', 'memory' => $m, 'at' => $at]);
        }

        foreach ($completed as $m) {
            $at = $m->completed_at->copy();
            if (isset($startedAt[$m->id]) && $at->lte($startedAt[$m->id])) {
                $at = $startedAt[$m->id]->copy()->addSecond();
            }
            $events->push(['type' => 'completed', 'memory' => $m, 'at' => $at]);
        }

        return $events->sortBy(fn($e) => $e['at']->getTimestamp())->values();
    }
}
