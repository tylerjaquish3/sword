<?php

namespace App\Http\Controllers;

use App\Models\Verse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $wholeWord = $request->boolean('whole_word');

        $verses = collect();

        if (strlen($q) >= 2) {
            $query = Verse::with(['chapter.book', 'translation'])
                ->where(function ($query) use ($q) {
                    $query->where('text', 'LIKE', '%' . $q . '%')
                          ->orWhere('reference', 'LIKE', '%' . $q . '%');
                })
                ->orderBy('id');

            if ($wholeWord) {
                $pattern = '/\b' . preg_quote($q, '/') . '\b/i';

                $verses = $query->get()
                    ->filter(fn ($verse) => preg_match($pattern, $verse->text) || preg_match($pattern, $verse->reference))
                    ->take(500)
                    ->values();
            } else {
                $verses = $query->limit(500)->get();
            }
        }

        return view('search.index', compact('verses', 'q', 'wholeWord'));
    }
}
