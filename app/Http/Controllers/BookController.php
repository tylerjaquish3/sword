<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

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
}