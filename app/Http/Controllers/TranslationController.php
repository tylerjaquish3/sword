<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Translation;
use App\Models\Verse;
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
     * Get verses by translation and chapter
     */
    public function verses(Request $request)
    {
        $verses = Verse::where('translation_id', $request->translation_id)
            ->where('chapter_id', $request->chapter_id)
            ->get();

        return response()->json($verses);
    }
}