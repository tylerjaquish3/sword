<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\ChapterComment;
use App\Models\Prayer;
use App\Models\PrayerType;
use App\Models\Topic;
use App\Models\Translation;
use App\Models\UserLogin;
use App\Models\UserRead;
use App\Models\Verse;
use App\Models\VerseComment;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $books = Book::all();
        
        // Dashboard metrics
        $prayerCount = Prayer::distinct('date')->count('date');
        $topicCount = Topic::count();
        $chapterCommentCount = ChapterComment::count();
        $verseCommentCount = VerseComment::count();
        $commentaryCount = $chapterCommentCount + $verseCommentCount;
        
        // Bible stats
        $bookCount = Book::count();
        $chapterCount = Chapter::count();
        $verseCount = Verse::count();
        $translationCount = Translation::count();
        
        // Prayer breakdown by type
        $prayersByType = Prayer::selectRaw('prayer_type_id, count(*) as count')
            ->groupBy('prayer_type_id')
            ->with('type')
            ->get();
        
        // Recent activity counts (last 7 days)
        $recentPrayers = Prayer::where('created_at', '>=', now()->subDays(7))->distinct('date')->count('date');
        $recentComments = ChapterComment::where('created_at', '>=', now()->subDays(7))->count()
            + VerseComment::where('created_at', '>=', now()->subDays(7))->count();

        $lastLogin = UserLogin::where('user_id', Auth::id())
            ->orderByDesc('logged_in_at')
            ->skip(1)
            ->first();

        // Heatmap: reads per date for the last year
        $readsByDate = UserRead::where('user_id', Auth::id())
            ->where('read_at', '>=', now()->subYear())
            ->selectRaw('DATE(read_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        // All distinct read dates for streak calculation
        $allReadDates = UserRead::where('user_id', Auth::id())
            ->selectRaw('DATE(read_at) as date')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('date')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->startOfDay());

        // Current streak: consecutive days backwards from today
        $currentStreak = 0;
        $checkDate = now()->startOfDay();
        while ($allReadDates->contains(fn($d) => $d->eq($checkDate))) {
            $currentStreak++;
            $checkDate->subDay();
        }

        // Longest streak: scan all read dates
        $longestStreak = 0;
        $runLength = 0;
        $prevDate = null;
        foreach ($allReadDates as $date) {
            if ($prevDate && $date->diffInDays($prevDate) === 1) {
                $runLength++;
            } else {
                $runLength = 1;
            }
            $longestStreak = max($longestStreak, $runLength);
            $prevDate = $date;
        }

        $todayReadCount = (int) ($readsByDate->get(now()->toDateString(), 0));

        return view('home.index', compact(
            'books',
            'prayerCount',
            'topicCount',
            'commentaryCount',
            'chapterCommentCount',
            'verseCommentCount',
            'bookCount',
            'chapterCount',
            'verseCount',
            'translationCount',
            'prayersByType',
            'recentPrayers',
            'recentComments',
            'lastLogin',
            'readsByDate',
            'currentStreak',
            'longestStreak',
            'todayReadCount'
        ));
    }
    
}