<?php

namespace App\Http\Controllers;

use App\Models\ChapterComment;
use App\Models\VerseComment;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Verse;

class CommentaryController extends Controller
{
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

        return view('commentary.index', compact('chapterComments', 'verseComments'));
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
            $data['user_id'] = 1; // Default user for now
            
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
            $data['user_id'] = 1; // Default user for now
            
            VerseComment::create($data);
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