<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\ChapterComment;
use App\Models\Prayer;
use App\Models\PrayerType;
use App\Models\Topic;
use App\Models\Translation;
use App\Models\Verse;
use App\Models\VerseComment;

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
            'recentComments'
        ));
    }
    
}