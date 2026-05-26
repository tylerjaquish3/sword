<?php

namespace App\Http\Controllers;

use App\Models\ChapterComment;
use App\Models\Memory;
use App\Models\Prayer;
use App\Models\SharedDigest;
use App\Models\UserRead;
use App\Models\VerseComment;
use Illuminate\Http\Request;
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
            ->with(['chapter.book', 'verse'])
            ->orderByDesc('created_at')
            ->get();

        $activeMemories = Memory::active()->withCount('verses')->get();

        $completedMemories = Memory::completed()
            ->whereBetween('completed_at', [$weekStart, $weekEnd])
            ->with(['verses.chapter.book'])
            ->get();

        $completedThisWeek = $completedMemories->count();

        $startedThisWeek = Memory::whereBetween('start_date', [$weekStart, $weekEnd])
            ->with(['verses.chapter.book'])
            ->get();

        $daysStudied = UserRead::where('user_id', Auth::id())
            ->whereBetween('read_at', [$weekStart, $weekEnd])
            ->selectRaw('DATE(read_at) as date')
            ->groupBy('date')
            ->get()
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

        $savedThisWeek = SharedDigest::where('user_id', Auth::id())
            ->where('week_start', now()->startOfWeek()->toDateString())
            ->exists();

        return view('digest.weekly', compact(
            'weekStart',
            'weekEnd',
            'chaptersRead',
            'prayers',
            'chapterComments',
            'verseComments',
            'activeMemories',
            'completedMemories',
            'completedThisWeek',
            'startedThisWeek',
            'daysStudied',
            'pastNote',
            'pastNoteType',
            'savedThisWeek'
        ));
    }

    public function history()
    {
        $digests = SharedDigest::where('user_id', Auth::id())
            ->orderByDesc('week_start')
            ->get();

        return view('digest.history', compact('digests'));
    }

    public function show(SharedDigest $shared)
    {
        abort_if($shared->user_id !== Auth::id(), 403);

        $comments = $shared->guestComments()->orderBy('created_at')->get();

        return view('digest.show', compact('shared', 'comments'));
    }

    public function destroy(SharedDigest $shared)
    {
        abort_if($shared->user_id !== Auth::id(), 403);

        $shared->delete();

        return response()->json(['success' => true]);
    }

    public function markShared(SharedDigest $shared)
    {
        abort_if($shared->user_id !== Auth::id(), 403);

        $shared->update(['is_shared' => true]);

        return redirect()->route('digest.share.link', $shared->uuid);
    }
}
