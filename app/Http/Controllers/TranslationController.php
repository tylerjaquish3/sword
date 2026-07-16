<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\Translation;
use App\Models\UserLogin;
use App\Models\Verse;
use App\Models\UserVersePreference;
use App\Models\VerseComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TranslationController extends Controller
{
    public function index()
    {
        $translations = Translation::all();
        $books = Book::all();

        $lastLogin = UserLogin::where('user_id', Auth::id())
            ->orderByDesc('logged_in_at')
            ->skip(1)
            ->first();

        $defaultTranslationId = Auth::user()->default_translation_id;

        return view('translations.index', compact('translations', 'books', 'lastLogin', 'defaultTranslationId'));
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

        // Get verse numbers that have commentary, expanding any range comments
        $commentRanges = VerseComment::where('chapter_id', $chapter->id)
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->get(['verse_number', 'end_verse_number']);

        $verseNumbersWithCommentary = [];
        foreach ($commentRanges as $c) {
            $end = $c->end_verse_number ?? $c->verse_number;
            for ($v = $c->verse_number; $v <= $end; $v++) {
                $verseNumbersWithCommentary[] = $v;
            }
        }
        $verseNumbersWithCommentary = array_unique($verseNumbersWithCommentary);

        // Get user preferences (highlights, favorites) keyed by verse_number
        $prefs = UserVersePreference::where('user_id', Auth::id())
            ->where('chapter_id', $chapter->id)
            ->get()
            ->keyBy('verse_number');

        $verses = $verses->map(function ($verse) use ($verseNumbersWithCommentary, $prefs) {
            $pref = $prefs[$verse->number] ?? null;
            $verse->has_commentary  = in_array($verse->number, $verseNumbersWithCommentary);
            $verse->highlight_color = $pref?->highlight_color;
            $verse->is_favorite     = (bool) ($pref?->is_favorite);
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

        // Get comments anchored to this verse OR whose range covers this verse
        $comments = VerseComment::where('chapter_id', $verse->chapter_id)
            ->where(function ($q) use ($verse) {
                $q->where('verse_number', $verse->number)
                  ->orWhere(function ($q2) use ($verse) {
                      $q2->where('verse_number', '<', $verse->number)
                         ->where('end_verse_number', '>=', $verse->number);
                  });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $pref = UserVersePreference::where('user_id', Auth::id())
            ->where('chapter_id', $verse->chapter_id)
            ->where('verse_number', $verse->number)
            ->first();

        // Return all verses in the same chapter/translation for range expansion
        $chapterVerses = Verse::where('chapter_id', $verse->chapter_id)
            ->where('translation_id', $verse->translation_id)
            ->orderBy('number')
            ->get(['id', 'number', 'text']);

        $maxEndVerse = VerseComment::where('chapter_id', $verse->chapter_id)
            ->where('verse_number', $verse->number)
            ->whereNotNull('end_verse_number')
            ->max('end_verse_number');

        return response()->json([
            'verse'            => $verse,
            'reference'        => $verse->chapter->book->name . ' ' . $verse->chapter->number . ':' . $verse->number,
            'comments'         => $comments,
            'highlight_color'  => $pref?->highlight_color,
            'is_favorite'      => (bool) ($pref?->is_favorite),
            'chapter_verses'   => $chapterVerses,
            'book_name'        => $verse->chapter->book->name,
            'chapter_number'   => $verse->chapter->number,
            'end_verse_number' => $maxEndVerse,
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
        if ($request->line_break == '1' || $request->section_title) {
            $prefix = '</p>';
            if ($request->section_title) {
                $prefix .= '<h5 class="mt-3 mb-2 fw-bold">' . e($this->titleCaseWords($request->section_title)) . '</h5>';
            }
            $prefix .= '<p>';
        }

        // Update prefix on all translations of this verse (shared across users)
        Verse::where('chapter_id', $verse->chapter_id)
            ->where('number', $verse->number)
            ->update(['prefix' => $prefix ?: null]);

        // Create a single comment (linked by chapter_id and verse_number, not verse_id)
        if ($request->commentary) {
            $endVerseNumber = $request->end_verse_number ? (int) $request->end_verse_number : null;
            if ($endVerseNumber && $endVerseNumber <= $verse->number) {
                $endVerseNumber = null;
            }

            VerseComment::create([
                'chapter_id'      => $verse->chapter_id,
                'verse_number'    => $verse->number,
                'end_verse_number' => $endVerseNumber,
                'verse_id'        => $verse->id,
                'comment'         => $request->commentary,
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get all verses in a chapter for the bulk section/paragraph editor (admin only)
     */
    public function sectionEditorVerses(Request $request)
    {
        $chapter = Chapter::where('book_id', $request->book_id)
            ->where('number', $request->chapter_number)
            ->first();

        if (!$chapter) {
            return response()->json(['verses' => []]);
        }

        $verses = Verse::where('translation_id', $request->translation_id)
            ->where('chapter_id', $chapter->id)
            ->orderBy('number')
            ->get(['number', 'text', 'prefix']);

        $verses = $verses->map(function ($verse) {
            $sectionTitle = '';
            $lineBreak = (bool) $verse->prefix;

            if ($verse->prefix && preg_match('/<h5[^>]*>(.*?)<\/h5>/s', $verse->prefix, $matches)) {
                $sectionTitle = html_entity_decode(strip_tags($matches[1]));
            }

            return [
                'number'        => $verse->number,
                'text'          => $verse->text,
                'section_title' => $sectionTitle,
                'line_break'    => $lineBreak,
            ];
        });

        return response()->json(['verses' => $verses]);
    }

    /**
     * Bulk update section titles / paragraph breaks for every verse in a chapter (admin only)
     */
    public function updateSectionEditor(Request $request)
    {
        $chapter = Chapter::where('book_id', $request->book_id)
            ->where('number', $request->chapter_number)
            ->first();

        if (!$chapter) {
            return response()->json(['success' => false], 404);
        }

        foreach ($request->input('verses', []) as $row) {
            $prefix = '';
            if (!empty($row['line_break']) || !empty($row['section_title'])) {
                $prefix = '</p>';
                if (!empty($row['section_title'])) {
                    $prefix .= '<h5 class="mt-3 mb-2 fw-bold">' . e($this->titleCaseWords($row['section_title'])) . '</h5>';
                }
                $prefix .= '<p>';
            }

            Verse::where('chapter_id', $chapter->id)
                ->where('number', $row['number'] ?? null)
                ->update(['prefix' => $prefix ?: null]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Capitalize the first letter of each word, leaving the rest of each word untouched
     */
    private function titleCaseWords(string $text): string
    {
        return preg_replace_callback('/\b\w/u', fn($match) => mb_strtoupper($match[0]), $text);
    }
}