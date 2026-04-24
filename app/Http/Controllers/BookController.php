<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\ChapterComment;
use App\Models\UserRead;
use App\Models\Verse;
use App\Models\VerseComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::all();

        return view('books.index', compact('books'));
    }

    public function show(Book $book)
    {
        return response()->json([
            'id'          => $book->id,
            'name'        => $book->name,
            'author'      => $book->author,
            'description' => $book->description,
        ]);
    }

    public function update(Request $request, Book $book)
    {
        $request->validate([
            'author'      => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $book->update([
            'author'      => $request->author,
            'description' => $request->description,
        ]);

        return response()->json(['success' => true]);
    }

    public function study(Book $book)
    {
        $chapterIds = $book->chapters()->pluck('id');
        $chapterCount = $chapterIds->count();
        $chaptersRead = UserRead::where('user_id', Auth::id())
            ->where('book_id', $book->id)
            ->distinct('chapter_number')
            ->count('chapter_number');
        $chapterCommentRecords = ChapterComment::whereIn('chapter_id', $chapterIds)
            ->with('chapter')
            ->orderByDesc('created_at')
            ->get();

        $verseCommentRecords = VerseComment::whereIn('chapter_id', $chapterIds)
            ->with('chapter')
            ->orderByDesc('created_at')
            ->get();

        $commentaryCount = $chapterCommentRecords->count() + $verseCommentRecords->count();

        $bookNotes = $chapterCommentRecords->map(fn($c) => [
                'type' => 'chapter',
                'ref'  => 'Ch. ' . $c->chapter->number,
                'text' => $c->comment,
                'date' => $c->created_at,
            ])
            ->concat($verseCommentRecords->map(fn($c) => [
                'type' => 'verse',
                'ref'  => $c->chapter->number . ':' . $c->verse_number,
                'text' => $c->comment,
                'date' => $c->created_at,
            ]))
            ->sortByDesc('date')
            ->take(6)
            ->values();

        $rawKeywords = Verse::whereIn('chapter_id', $chapterIds)
            ->whereNotNull('key_words')
            ->where('key_words', '!=', '')
            ->pluck('key_words');

        $wordFreq = [];
        foreach ($rawKeywords as $kw) {
            foreach (array_filter(explode(' ', $kw)) as $word) {
                if (strlen($word) > 2) {
                    $wordFreq[$word] = ($wordFreq[$word] ?? 0) + 1;
                }
            }
        }
        arsort($wordFreq);
        $wordCloud = collect(array_slice($wordFreq, 0, 80, true))
            ->map(fn($count, $text) => ['text' => $text, 'size' => $count])
            ->values();

        return view('books.study', compact('book', 'chapterCount', 'chaptersRead', 'commentaryCount', 'wordCloud', 'bookNotes'));
    }

    public function updateStudy(Request $request, Book $book)
    {
        $request->validate([
            'author'      => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'history'     => 'nullable|string',
            'themes'      => 'nullable|string',
            'notes'       => 'nullable|string',
        ]);

        $book->update($request->only(['author', 'description', 'history', 'themes', 'notes']));

        return redirect()->route('books.study', $book)->with('success', 'Study notes saved.');
    }
}