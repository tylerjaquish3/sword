<?php

namespace App\Http\Controllers;

use App\Models\ChapterComment;
use App\Models\SharedDigest;
use App\Models\Translation;
use App\Models\UserRead;
use App\Models\Verse;
use App\Models\VerseFavorite;
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

        // Favorite verses — pick one verse per (chapter_id, verse_number) for display text
        $favorites = VerseFavorite::with('chapter.book')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($fav) {
                $verse = Verse::where('chapter_id', $fav->chapter_id)
                    ->where('number', $fav->verse_number)
                    ->first();
                return [
                    'reference' => ($fav->chapter->book->name ?? '—') . ' ' . ($fav->chapter->number ?? '') . ':' . $fav->verse_number,
                    'book'      => $fav->chapter->book->name ?? '—',
                    'chapter'   => $fav->chapter->number ?? '',
                    'text'      => $verse?->text ?? '—',
                    'book_id'   => $fav->chapter->book_id ?? null,
                    'favorited' => $fav->created_at,
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
