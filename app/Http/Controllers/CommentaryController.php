<?php

namespace App\Http\Controllers;

use App\Models\ChapterComment;
use App\Models\VerseComment;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\UserVersePreference;
use App\Models\Verse;
use Illuminate\Support\Facades\Auth;

class CommentaryController extends Controller
{
    const HIGHLIGHT_TYPES = [
        'yellow' => 'Important',
        'blue'   => 'Prophecy',
        'green'  => 'Promise',
        'red'    => 'Command',
    ];

    public function index()
    {
        $chapterComments = ChapterComment::with(['chapter.book'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get verse comments with chapter relationship, grouped by chapter_id and verse_number
        $verseComments = VerseComment::with(['chapter.book'])
            ->whereNotNull('chapter_id')
            ->whereNotNull('verse_number')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique(function ($item) {
                return $item->chapter_id . '-' . $item->verse_number . '-' . $item->comment;
            });

        // Get highlighted verses with their book/chapter reference and text
        $highlightedVerses = UserVersePreference::with(['chapter.book'])
            ->where('user_id', Auth::id())
            ->whereNotNull('highlight_color')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($pref) {
                $verse = Verse::where('chapter_id', $pref->chapter_id)
                    ->where('number', $pref->verse_number)
                    ->first();

                return (object) [
                    'chapter_id'      => $pref->chapter_id,
                    'book_id'         => $pref->chapter->book_id ?? '',
                    'verse_number'    => $pref->verse_number,
                    'highlight_color' => $pref->highlight_color,
                    'type_label'      => self::HIGHLIGHT_TYPES[$pref->highlight_color] ?? 'Other',
                    'book_name'       => $pref->chapter->book->name ?? 'N/A',
                    'chapter_number'  => $pref->chapter->number ?? '',
                    'text'            => $verse->text ?? '',
                    'updated_at'      => $pref->updated_at,
                ];
            });

        $highlightTypes = self::HIGHLIGHT_TYPES;

        return view('commentary.index', compact('chapterComments', 'verseComments', 'highlightedVerses', 'highlightTypes'));
    }

    public function create()
    {
        $books = Book::with('chapters.verses')->get();
        
        return view('commentary.create', compact('books'));
    }

    public function store()
    {
        $type = request('type');
        
        if ($type === 'chapter') {
            $data = request()->validate([
                'chapter_id' => 'required|exists:chapters,id',
                'comment' => 'required',
            ]);
            ChapterComment::create($data);
        } else {
            $data = request()->validate([
                'verse_id' => 'required|exists:verses,id',
                'comment' => 'required',
            ]);
            
            // Get the verse to extract chapter_id and verse_number
            $verse = Verse::find($data['verse_id']);
            
            $data['chapter_id'] = $verse->chapter_id;
            $data['verse_number'] = $verse->number;
            VerseComment::create($data);
        }

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('commentary.index');
    }

    public function editChapter(ChapterComment $chapterComment)
    {
        $books = Book::with('chapters')->get();
        
        return view('commentary.edit-chapter', compact('chapterComment', 'books'));
    }

    public function updateChapter(ChapterComment $chapterComment)
    {
        $data = request()->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'comment' => 'required',
        ]);

        $chapterComment->update($data);

        return redirect()->route('commentary.index');
    }

    public function destroyChapter(ChapterComment $chapterComment)
    {
        $chapterComment->delete();

        return response()->json(['success' => true]);
    }

    public function editVerse(VerseComment $verseComment)
    {
        $books = Book::with('chapters.verses')->get();
        
        return view('commentary.edit-verse', compact('verseComment', 'books'));
    }

    public function updateVerse(VerseComment $verseComment)
    {
        $data = request()->validate([
            'verse_id' => 'required|exists:verses,id',
            'comment' => 'required',
        ]);
        
        // Get the verse to extract chapter_id and verse_number
        $verse = Verse::find($data['verse_id']);
        $data['chapter_id'] = $verse->chapter_id;
        $data['verse_number'] = $verse->number;

        $verseComment->update($data);

        return redirect()->route('commentary.index');
    }

    public function destroyVerse(VerseComment $verseComment)
    {
        $verseComment->delete();

        return response()->json(['success' => true]);
    }
}