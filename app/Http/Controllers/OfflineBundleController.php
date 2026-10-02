<?php

namespace App\Http\Controllers;

use App\Models\ChapterComment;
use App\Models\Prayer;
use App\Models\PrayerType;
use App\Models\UserVersePreference;
use App\Models\VerseComment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OfflineBundleController extends Controller
{
    /**
     * Cache key for the Bible reference data (translations/verses/chapters). This data only
     * changes when KeplinVerses imports new verses, which invalidates this key, so it's safe
     * to cache forever rather than re-querying and re-hydrating ~137k verse rows (which was
     * blowing past PHP's memory_limit via Eloquent and causing 502s) on every bundle sync.
     */
    public const STATIC_BUNDLE_CACHE_KEY = 'offline_bundle_static_v1';

    public function show()
    {
        $user = Auth::user();

        abort_unless($user->offline_enabled, 403);

        $userId = $user->id;

        return response()->json(array_merge($this->staticBundle(), [
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
            'prayerTypes' => PrayerType::all(['id', 'name']),
        ]));
    }

    private function staticBundle(): array
    {
        return Cache::rememberForever(self::STATIC_BUNDLE_CACHE_KEY, function () {
            $books = DB::table('books')->select('id', 'name', 'sort_order')->get()->keyBy('id');

            $chapters = DB::table('chapters')
                ->select('id', 'book_id', 'number')
                ->get()
                ->map(function ($chapter) use ($books) {
                    $book = $books->get($chapter->book_id);

                    return [
                        'id' => $chapter->id,
                        'book_id' => $chapter->book_id,
                        'number' => $chapter->number,
                        'book' => $book ? [
                            'id' => $book->id,
                            'name' => $book->name,
                            'sort_order' => $book->sort_order,
                        ] : null,
                    ];
                })
                ->values();

            return [
                'translations' => DB::table('translations')->select('id', 'name')->get()->values(),
                'verses' => DB::table('verses')
                    ->select('id', 'chapter_id', 'translation_id', 'number', 'text')
                    ->get()
                    ->values(),
                'chapters' => $chapters,
            ];
        });
    }
}
