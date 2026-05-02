<?php

namespace App\Http\Controllers;

use App\Models\ChapterComment;
use App\Models\SharedDigest;
use App\Models\Translation;
use App\Models\UserRead;
use App\Models\UserVersePreference;
use App\Models\Verse;
use App\Models\VerseComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $reads = UserRead::with(['book', 'translation'])
            ->where('user_id', Auth::id())
            ->orderByDesc('read_at')
            ->get();

        $digests = SharedDigest::where('user_id', Auth::id())->orderByDesc('week_start')->get();

        $verseComments = VerseComment::with(['chapter.book'])
            ->get()
            ->map(fn($c) => [
                'type'       => 'Verse',
                'book'       => $c->chapter?->book?->name ?? '—',
                'book_id'    => $c->chapter?->book?->id,
                'reference'  => ($c->chapter?->book?->name ?? '—') . ' ' . ($c->chapter?->number ?? '') . ':' . $c->verse_number,
                'comment'    => $c->comment,
                'created_at' => $c->created_at,
            ]);

        $chapterComments = ChapterComment::with(['chapter.book'])
            ->get()
            ->map(fn($c) => [
                'type'       => 'Chapter',
                'book'       => $c->chapter?->book?->name ?? '—',
                'book_id'    => $c->chapter?->book?->id,
                'reference'  => ($c->chapter?->book?->name ?? '—') . ' ' . ($c->chapter?->number ?? ''),
                'comment'    => $c->comment,
                'created_at' => $c->created_at,
            ]);

        $commentary = $verseComments->concat($chapterComments)->sortByDesc('created_at')->values();

        $translations = Translation::orderBy('name')->get();

        // Favorite verses
        $favorites = UserVersePreference::where('user_id', Auth::id())
            ->where('is_favorite', true)
            ->with('chapter.book')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($pref) {
                $verse = Verse::where('chapter_id', $pref->chapter_id)
                    ->where('number', $pref->verse_number)
                    ->first();
                return [
                    'reference' => ($pref->chapter->book->name ?? '—') . ' ' . ($pref->chapter->number ?? '') . ':' . $pref->verse_number,
                    'book'      => $pref->chapter->book->name ?? '—',
                    'chapter'   => $pref->chapter->number ?? '',
                    'text'      => $verse?->text ?? '—',
                    'book_id'   => $pref->chapter->book_id ?? null,
                    'favorited' => $pref->created_at,
                ];
            });

        return view('profile.index', compact('reads', 'digests', 'commentary', 'translations', 'favorites'));
    }

    public function updateDefaultTranslation(Request $request)
    {
        $request->validate(['translation_id' => 'nullable|exists:translations,id']);
        Auth::user()->update(['default_translation_id' => $request->translation_id ?: null]);
        return back()->with('success', 'Default translation saved.');
    }
}
