<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\ChapterComment;
use App\Models\UserBookMetadata;
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
        $meta = UserBookMetadata::where('user_id', Auth::id())
            ->where('book_id', $book->id)
            ->first();

        return response()->json([
            'id'          => $book->id,
            'name'        => $book->name,
            'author'      => $meta?->author,
            'description' => $meta?->description,
            'timeframe'   => $meta?->timeframe,
        ]);
    }

    public function update(Request $request, Book $book)
    {
        $request->validate([
            'author'      => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'timeframe'   => 'nullable|string|max:255',
        ]);

        UserBookMetadata::updateOrCreate(
            ['user_id' => Auth::id(), 'book_id' => $book->id],
            $request->only(['author', 'description', 'timeframe'])
        );

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

        // Merge user-specific metadata onto the book object for the view
        $meta = UserBookMetadata::where('user_id', Auth::id())
            ->where('book_id', $book->id)
            ->first();
        foreach (['author', 'timeframe', 'description', 'history', 'themes', 'notes'] as $field) {
            $book->$field = $meta?->$field;
        }

        return view('books.study', compact('book', 'chapterCount', 'chaptersRead', 'commentaryCount', 'wordCloud', 'bookNotes'));
    }

    public function updateStudy(Request $request, Book $book)
    {
        $request->validate([
            'author'      => 'nullable|string|max:255',
            'timeframe'   => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'history'     => 'nullable|string',
            'themes'      => 'nullable|string',
            'notes'       => 'nullable|string',
        ]);

        UserBookMetadata::updateOrCreate(
            ['user_id' => Auth::id(), 'book_id' => $book->id],
            $request->only(['author', 'timeframe', 'description', 'history', 'themes', 'notes'])
        );

        return redirect()->route('books.study', $book)->with('success', 'Study notes saved.');
    }
}