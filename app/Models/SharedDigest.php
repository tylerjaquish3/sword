<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SharedDigest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'snapshot' => 'array',
        'fruits_needing_prayer' => 'array',
        'idols' => 'array',
        'show_chapters' => 'boolean',
        'show_prayers' => 'boolean',
        'show_commentary' => 'boolean',
        'show_memory' => 'boolean',
        'show_past_note' => 'boolean',
        'week_start' => 'date',
        'week_end' => 'date',
    ];
}
