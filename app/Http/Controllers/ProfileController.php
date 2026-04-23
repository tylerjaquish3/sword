<?php

namespace App\Http\Controllers;

use App\Models\ChapterComment;
use App\Models\Translation;
use App\Models\UserLogin;
use App\Models\UserRead;
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

        $logins = UserLogin::where('user_id', Auth::id())
            ->orderByDesc('logged_in_at')
            ->limit(100)
            ->get();

        $verseComments = VerseComment::with(['chapter.book'])
            ->get()
            ->map(fn($c) => [
                'type'       => 'Verse',
                'book'       => $c->chapter?->book?->name ?? '—',
                'reference'  => ($c->chapter?->book?->name ?? '—') . ' ' . ($c->chapter?->number ?? '') . ':' . $c->verse_number,
                'comment'    => $c->comment,
                'created_at' => $c->created_at,
            ]);

        $chapterComments = ChapterComment::with(['chapter.book'])
            ->get()
            ->map(fn($c) => [
                'type'       => 'Chapter',
                'book'       => $c->chapter?->book?->name ?? '—',
                'reference'  => ($c->chapter?->book?->name ?? '—') . ' ' . ($c->chapter?->number ?? ''),
                'comment'    => $c->comment,
                'created_at' => $c->created_at,
            ]);

        $commentary = $verseComments->concat($chapterComments)->sortByDesc('created_at')->values();

        $translations = Translation::orderBy('name')->get();

        return view('profile.index', compact('reads', 'logins', 'commentary', 'translations'));
    }

    public function updateDefaultTranslation(Request $request)
    {
        $request->validate(['translation_id' => 'nullable|exists:translations,id']);
        Auth::user()->update(['default_translation_id' => $request->translation_id ?: null]);
        return back()->with('success', 'Default translation saved.');
    }
}
