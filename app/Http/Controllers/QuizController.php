<?php

namespace App\Http\Controllers;

use App\Models\Memory;
use App\Models\VerseQuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    public function attempt(Request $request)
    {
        $request->validate([
            'memory_id' => 'required|exists:memories,id',
            'verse_id'  => 'required|exists:verses,id',
            'correct'   => 'required|boolean',
        ]);

        Memory::findOrFail($request->memory_id);

        VerseQuizAttempt::create([
            'user_id'   => Auth::id(),
            'memory_id' => $request->memory_id,
            'verse_id'  => $request->verse_id,
            'correct'   => $request->boolean('correct'),
        ]);

        $stats = VerseQuizAttempt::where('memory_id', $request->memory_id)
            ->where('verse_id', $request->verse_id)
            ->selectRaw('SUM(correct) as correct_count, COUNT(*) as total_count')
            ->first();

        $pct = $stats->total_count > 0
            ? (int) round(($stats->correct_count / $stats->total_count) * 100)
            : null;

        return response()->json([
            'verse_id' => (int) $request->verse_id,
            'correct'  => (int) $stats->correct_count,
            'total'    => (int) $stats->total_count,
            'pct'      => $pct,
        ]);
    }
}
