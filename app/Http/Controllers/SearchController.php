<?php

namespace App\Http\Controllers;

use App\Models\Verse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $verses = collect();

        if (strlen($q) >= 2) {
            $verses = Verse::with(['chapter.book', 'translation'])
                ->where('text', 'LIKE', '%' . $q . '%')
                ->orderBy('id')
                ->limit(500)
                ->get();
        }

        return view('search.index', compact('verses', 'q'));
    }
}
