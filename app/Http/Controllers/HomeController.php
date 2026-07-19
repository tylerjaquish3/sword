<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\ChapterComment;
use App\Models\Memory;
use App\Models\Prayer;
use App\Models\PrayerType;
use App\Models\Topic;
use App\Models\Translation;
use App\Models\UserLogin;
use App\Models\UserRead;
use App\Models\UserVersePreference;
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
        $totalPrayerCount = Prayer::count();
        $topicCount = Topic::where('user_id', Auth::id())->count();
        $chapterCommentCount = ChapterComment::count();
        $verseCommentCount = VerseComment::count();
        $commentaryCount = $chapterCommentCount + $verseCommentCount;
        
        // Bible stats
        $bookCount = Book::count();
        $chapterCount = Chapter::count();
        $verseCount = \DB::table(\DB::raw('(SELECT DISTINCT chapter_id, number FROM verses) as sub'))->count();
        $translationCount = Translation::count();

        // Bible reading progress
        $totalBibleChapters = $chapterCount;
        $chaptersRead = \DB::table(function ($q) {
            $q->from('user_reads')
              ->where('user_id', Auth::id())
              ->select('book_id', 'chapter_number')
              ->distinct();
        }, 'sub')->count();

        // Verse highlights by color/type
        $highlightsByColor = UserVersePreference::where('user_id', Auth::id())
            ->whereNotNull('highlight_color')
            ->selectRaw('highlight_color, count(*) as total')
            ->groupBy('highlight_color')
            ->pluck('total', 'highlight_color');
        
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

        // All distinct read dates as plain strings (YYYY-MM-DD), ascending
        $allReadDates = UserRead::where('user_id', Auth::id())
            ->selectRaw('DATE(read_at) as date')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('date')
            ->toArray();

        $readDateSet = array_flip($allReadDates); // O(1) lookup

        // Current streak: count consecutive days backwards from today.
        // If today hasn't been read yet, start from yesterday so a streak
        // ending yesterday still shows — it resets only if yesterday was also missed.
        $startFrom = isset($readDateSet[now()->toDateString()])
            ? now()
            : now()->subDay();

        $currentStreak = 0;
        $checkDate = $startFrom->copy()->startOfDay();
        while (isset($readDateSet[$checkDate->toDateString()])) {
            $currentStreak++;
            $checkDate->subDay();
        }

        // Longest streak: scan all read dates with plain date arithmetic
        $longestStreak = 0;
        $runLength = 0;
        $prevDate = null;
        foreach ($allReadDates as $dateStr) {
            if ($prevDate !== null) {
                $diff = (new \DateTime($dateStr))->diff(new \DateTime($prevDate))->days;
                $runLength = ($diff === 1) ? $runLength + 1 : 1;
            } else {
                $runLength = 1;
            }
            $longestStreak = max($longestStreak, $runLength);
            $prevDate = $dateStr;
        }

        $todayReadCount = (int) ($readsByDate->get(now()->toDateString(), 0));

        // Weekly digest preview stats
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $digestStats = [
            'days' => UserRead::where('user_id', Auth::id())
                ->whereBetween('read_at', [$weekStart, $weekEnd])
                ->selectRaw('DATE(read_at) as date')
                ->groupBy('date')
                ->get()
                ->count(),
            'chapters' => \DB::table('user_reads')
                ->where('user_id', Auth::id())
                ->whereBetween('read_at', [$weekStart, $weekEnd])
                ->count(),
            'prayers' => Prayer::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
            'notes' => ChapterComment::whereBetween('created_at', [$weekStart, $weekEnd])->count()
                + VerseComment::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
        ];

        $yearAgoStart = now()->subYear()->startOfWeek();
        $yearAgoEnd = now()->subYear()->endOfWeek();
        $digestPastNote = VerseComment::whereBetween('created_at', [$yearAgoStart, $yearAgoEnd])
            ->inRandomOrder()
            ->first();
        if (!$digestPastNote) {
            $digestPastNote = ChapterComment::whereBetween('created_at', [$yearAgoStart, $yearAgoEnd])
                ->inRandomOrder()
                ->first();
        }

        $activeMemory = Memory::active()
            ->with(['verses' => fn($q) => $q->with('chapter.book')->orderBy('id')])
            ->latest()
            ->first();

        $memorizedCount = Memory::completed()->count();

        return view('home.index', compact(
            'books',
            'prayerCount',
            'totalPrayerCount',
            'topicCount',
            'commentaryCount',
            'chapterCommentCount',
            'verseCommentCount',
            'bookCount',
            'chapterCount',
            'verseCount',
            'translationCount',
            'totalBibleChapters',
            'chaptersRead',
            'highlightsByColor',
            'prayersByType',
            'recentPrayers',
            'recentComments',
            'lastLogin',
            'readsByDate',
            'currentStreak',
            'longestStreak',
            'todayReadCount',
            'digestStats',
            'digestPastNote',
            'activeMemory',
            'memorizedCount'
        ));
    }
    
}