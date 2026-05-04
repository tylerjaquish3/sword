<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class VerseQuizAttempt extends Model
{
    protected $guarded = [];

    protected $casts = ['correct' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function memory()
    {
        return $this->belongsTo(Memory::class);
    }

    public function verse()
    {
        return $this->belongsTo(Verse::class);
    }

    public static function masteryForMemory(int $memoryId): array
    {
        $rows = static::where('memory_id', $memoryId)
            ->selectRaw('verse_id, SUM(correct) as correct_count, COUNT(*) as total_count')
            ->groupBy('verse_id')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[$row->verse_id] = [
                'correct' => (int) $row->correct_count,
                'total'   => (int) $row->total_count,
                'pct'     => $row->total_count > 0
                    ? (int) round(($row->correct_count / $row->total_count) * 100)
                    : null,
            ];
        }
        return $result;
    }
}
