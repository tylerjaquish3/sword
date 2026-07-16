<?php

namespace App\Http\Controllers;

use App\Models\UserVersePreference;
use App\Models\Verse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerseHighlightController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate([
            'verse_id'         => 'required|exists:verses,id',
            'color'            => 'required|in:yellow,blue,green,red',
            'end_verse_number' => 'nullable|integer',
        ]);

        $verse = Verse::find($request->verse_id);

        $pref = UserVersePreference::where('user_id', Auth::id())
            ->where('chapter_id', $verse->chapter_id)
            ->where('verse_number', $verse->number)
            ->first();

        $endVerseNumber = $request->end_verse_number ? (int) $request->end_verse_number : null;
        if (! $endVerseNumber || $endVerseNumber < $verse->number) {
            $endVerseNumber = $verse->number;
        }

        // Same color as the range's starting verse → remove highlight from the whole range (toggle off)
        $newColor = ($pref && $pref->highlight_color === $request->color) ? null : $request->color;

        for ($number = $verse->number; $number <= $endVerseNumber; $number++) {
            UserVersePreference::updateOrCreate(
                ['user_id' => Auth::id(), 'chapter_id' => $verse->chapter_id, 'verse_number' => $number],
                ['highlight_color' => $newColor]
            );
        }

        return response()->json(['color' => $newColor]);
    }
}
