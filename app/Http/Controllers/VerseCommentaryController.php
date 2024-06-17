<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Verse;

class VerseCommentaryController extends Controller
{
    public function index()
    {
        $books = Book::all();

        return view('verses.index', compact('books'));
    }

    public function show(Book $book)
    {
        return view('verses.show', compact('book'));
    }

    public function create()
    {
        return view('verses.create');
    }

    public function store()
    {
        $data = request()->validate([
            'chapter_id' => 'required',
            'translation_id' => 'required',
            'number' => 'required',
            'reference' => 'required',
            'text' => 'required',
        ]);

        Verse::create($data);

        return redirect()->route('verses.index');
    }

    public function edit(Verse $verse)
    {
        return view('verses.edit', compact('verse'));
    }

    public function update(Verse $verse)
    {
        $data = request()->validate([
            'chapter_id' => 'required',
            'translation_id' => 'required',
            'number' => 'required',
            'reference' => 'required',
            'text' => 'required',
        ]);

        $verse->update($data);

        return redirect()->route('verses.index');
    }

    public function destroy(Verse $verse)
    {
        $verse->delete();

        return redirect()->route('verses.index');
    }
}