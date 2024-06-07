<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Verse;

class HomeController extends Controller
{
    public function index()
    {
        $books = Book::all();

        return view('home.index', compact('books'));
    }
    
}