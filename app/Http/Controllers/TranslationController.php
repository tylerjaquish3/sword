<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\Translation;
use App\Models\Verse;
use App\Models\VerseComment;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function index()
    {
        $translations = Translation::all();
        $books = Book::all();

        return view('translations.index', compact('translations', 'books'));
    }

    public function show(Translation $translation)
    {
        return view('translations.show', compact('translation'));
    }

    /**
     * Get verses by translation, book, and chapter number
     */
    public function verses(Request $request)
    {
        // First find the chapter by book_id and chapter number
        $chapter = Chapter::where('book_id', $request->book_id)
            ->where('number', $request->chapter_id)
            ->first();

        if (!$chapter) {
            return response()->json([]);
        }

        $verses = Verse::where('translation_id', $request->translation_id)
            ->where('chapter_id', $chapter->id)
            ->get();

        // Get verse numbers that have commentary for this chapter
        $verseNumbersWithCommentary = VerseComment::where('chapter_id', $chapter->id)
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->pluck('verse_number')
            ->toArray();

        // Add has_commentary flag to each verse
        $verses = $verses->map(function ($verse) use ($verseNumbersWithCommentary) {
            $verse->has_commentary = in_array($verse->number, $verseNumbersWithCommentary);
            return $verse;
        });

        return response()->json($verses);
    }

    /**
     * Get a single verse with its commentary
     */
    public function getVerse(Verse $verse)
    {
        $verse->load('chapter.book');
        
        // Get comments by chapter_id and verse_number (translation-independent)
        $comments = VerseComment::where('chapter_id', $verse->chapter_id)
            ->where('verse_number', $verse->number)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'verse' => $verse,
            'reference' => $verse->chapter->book->name . ' ' . $verse->chapter->number . ':' . $verse->number,
            'comments' => $comments
        ]);
    }

    /**
     * Get verse data by chapter_id and verse_number (translation-independent)
     */
    public function getVerseByLocation(Request $request)
    {
        $chapterId = $request->chapter_id;
        $verseNumber = $request->verse_number;
        
        // Get any verse with this chapter_id and verse_number (we just need one for the text)
        $verse = Verse::where('chapter_id', $chapterId)
            ->where('number', $verseNumber)
            ->with('chapter.book')
            ->first();
        
        if (!$verse) {
            return response()->json(['error' => 'Verse not found'], 404);
        }
        
        // Get comments by chapter_id and verse_number (translation-independent)
        $comments = VerseComment::where('chapter_id', $chapterId)
            ->where('verse_number', $verseNumber)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'verse' => $verse,
            'reference' => $verse->chapter->book->name . ' ' . $verse->chapter->number . ':' . $verse->number,
            'comments' => $comments
        ]);
    }

    /**
     * Update verse prefix and commentary for all translations
     */
    public function updateVerse(Request $request, Verse $verse)
    {
        // Build the prefix HTML from checkbox and section title
        $prefix = '';
        if ($request->line_break || $request->section_title) {
            $prefix = '</p>';
            if ($request->section_title) {
                $prefix .= '<h5 class="mt-3 mb-2 fw-bold">' . e($request->section_title) . '</h5>';
            }
            $prefix .= '<p>';
        }

        // Get all verses with the same chapter and verse number across all translations
        $matchingVerses = Verse::where('chapter_id', $verse->chapter_id)
            ->where('number', $verse->number)
            ->get();

        // Update the prefix on all matching verses
        foreach ($matchingVerses as $matchingVerse) {
            $matchingVerse->update([
                'prefix' => $prefix ?: null
            ]);
        }

        // Create a single comment (linked by chapter_id and verse_number, not verse_id)
        if ($request->commentary) {
            VerseComment::create([
                'chapter_id' => $verse->chapter_id,
                'verse_number' => $verse->number,
                'verse_id' => $verse->id, // Keep for backwards compatibility
                'comment' => $request->commentary,
                'user_id' => 1 // TODO: use auth user
            ]);
        }

        return response()->json(['success' => true]);
    }
}