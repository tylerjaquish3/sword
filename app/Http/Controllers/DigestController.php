<?php

namespace App\Http\Controllers;

use App\Models\ChapterComment;
use App\Models\Memory;
use App\Models\Prayer;
use App\Models\UserRead;
use App\Models\VerseComment;
use Illuminate\Support\Facades\Auth;

class DigestController extends Controller
{
    public function weekly()
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        $chaptersRead = UserRead::where('user_id', Auth::id())
            ->whereBetween('read_at', [$weekStart, $weekEnd])
            ->with('book')
            ->get()
            ->groupBy('book_id')
            ->sortBy(fn($reads) => $reads->first()->book->sort_order ?? 999);

        $prayers = Prayer::whereBetween('created_at', [$weekStart, $weekEnd])
            ->with('type')
            ->orderByDesc('created_at')
            ->get();

        $chapterComments = ChapterComment::whereBetween('created_at', [$weekStart, $weekEnd])
            ->with('chapter.book')
            ->orderByDesc('created_at')
            ->get();

        $verseComments = VerseComment::whereBetween('created_at', [$weekStart, $weekEnd])
            ->with('chapter.book')
            ->orderByDesc('created_at')
            ->get();

        $activeMemories = Memory::active()->withCount('verses')->get();

        $completedThisWeek = Memory::completed()
            ->whereBetween('completed_at', [$weekStart, $weekEnd])
            ->count();

        $daysStudied = UserRead::where('user_id', Auth::id())
            ->whereBetween('read_at', [$weekStart, $weekEnd])
            ->selectRaw('DATE(read_at) as date')
            ->groupBy('date')
            ->count();

        $yearAgoStart = now()->subYear()->startOfWeek();
        $yearAgoEnd = now()->subYear()->endOfWeek();

        $pastNote = VerseComment::whereBetween('created_at', [$yearAgoStart, $yearAgoEnd])
            ->with('chapter.book')
            ->inRandomOrder()
            ->first();

        $pastNoteType = 'verse';
        if (!$pastNote) {
            $pastNote = ChapterComment::whereBetween('created_at', [$yearAgoStart, $yearAgoEnd])
                ->with('chapter.book')
                ->inRandomOrder()
                ->first();
            $pastNoteType = 'chapter';
        }

        return view('digest.weekly', compact(
            'weekStart',
            'weekEnd',
            'chaptersRead',
            'prayers',
            'chapterComments',
            'verseComments',
            'activeMemories',
            'completedThisWeek',
            'daysStudied',
            'pastNote',
            'pastNoteType'
        ));
    }
}
