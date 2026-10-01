<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\ChapterComment;
use App\Models\Prayer;
use App\Models\Translation;
use App\Models\UserVersePreference;
use App\Models\Verse;
use App\Models\VerseComment;
use Illuminate\Support\Facades\Auth;

class OfflineBundleController extends Controller
{
    public function show()
    {
        $userId = Auth::id();

        return response()->json([
            'translations' => Translation::all(['id', 'name']),
            'verses' => Verse::select('id', 'chapter_id', 'translation_id', 'number', 'text')->get(),
            'chapters' => Chapter::with('book:id,name,sort_order')->get(['id', 'book_id', 'number']),
            'verseComments' => VerseComment::withoutGlobalScopes()
                ->where('user_id', $userId)
                ->get(['id', 'client_uuid', 'chapter_id', 'verse_number', 'comment', 'created_at']),
            'chapterComments' => ChapterComment::withoutGlobalScopes()
                ->where('user_id', $userId)
                ->get(['id', 'client_uuid', 'chapter_id', 'comment', 'created_at']),
            'highlights' => UserVersePreference::where('user_id', $userId)
                ->whereNotNull('highlight_color')
                ->get(['chapter_id', 'verse_number', 'highlight_color']),
            'prayers' => Prayer::withoutGlobalScopes()
                ->where('user_id', $userId)
                ->get(['id', 'client_uuid', 'date', 'content', 'prayer_type_id']),
            'prayerTypes' => \App\Models\PrayerType::all(['id', 'name']),
        ]);
    }
}
