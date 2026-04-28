<?php

namespace App\Http\Controllers;

use App\Models\VerseHighlight;
use App\Models\Verse;
use Illuminate\Http\Request;

class VerseHighlightController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate([
            'verse_id' => 'required|exists:verses,id',
            'color'    => 'required|in:yellow,blue,green,red',
        ]);

        $verse = Verse::find($request->verse_id);

        $existing = VerseHighlight::where('chapter_id', $verse->chapter_id)
            ->where('verse_number', $verse->number)
            ->first();

        // Same color → remove (toggle off)
        if ($existing && $existing->color === $request->color) {
            $existing->delete();
            return response()->json(['color' => null]);
        }

        // Different color or no highlight → set
        if ($existing) {
            $existing->update(['color' => $request->color]);
        } else {
            VerseHighlight::create([
                'chapter_id'   => $verse->chapter_id,
                'verse_number' => $verse->number,
                'color'        => $request->color,
            ]);
        }

        return response()->json(['color' => $request->color]);
    }
}
