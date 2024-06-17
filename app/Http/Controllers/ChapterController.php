<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    /**
     * Get chapters by book
     */
    public function lookup(Request $request)
    {
        $chapters = Chapter::where('book_id', $request->book_id)
            ->get();

        return response()->json($chapters);
    }
}