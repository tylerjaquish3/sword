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
        $chapterComments = ChapterComment::with(['chapter.book'])->get();
        $verseComments = VerseComment::with(['verse.chapter.book'])->get();

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

        return redirect()->route('commentary.index');
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

        $verseComment->update($data);

        return redirect()->route('commentary.index');
    }

    public function destroyVerse(VerseComment $verseComment)
    {
        $verseComment->delete();

        return redirect()->route('commentary.index');
    }
}