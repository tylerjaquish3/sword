<?php

namespace App\Http\Controllers;

use App\Models\VerseFavorite;
use App\Models\Verse;
use Illuminate\Http\Request;

class VerseFavoriteController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate([
            'verse_id' => 'required|exists:verses,id',
        ]);

        $verse = Verse::find($request->verse_id);

        $existing = VerseFavorite::where('chapter_id', $verse->chapter_id)
            ->where('verse_number', $verse->number)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['favorite' => false]);
        }

        VerseFavorite::create([
            'chapter_id'   => $verse->chapter_id,
            'verse_number' => $verse->number,
        ]);

        return response()->json(['favorite' => true]);
    }
}
