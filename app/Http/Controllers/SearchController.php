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
                ->where(function ($query) use ($q) {
                    $query->where('text', 'LIKE', '%' . $q . '%')
                          ->orWhere('reference', 'LIKE', '%' . $q . '%');
                })
                ->orderBy('id')
                ->limit(500)
                ->get();
        }

        return view('search.index', compact('verses', 'q'));
    }
}
