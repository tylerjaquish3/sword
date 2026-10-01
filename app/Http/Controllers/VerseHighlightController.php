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
        $explicit = $request->boolean('explicit');

        $request->validate([
            'verse_id'         => 'required_without_all:chapter_id,verse_number|exists:verses,id',
            'chapter_id'       => 'required_without:verse_id|exists:chapters,id',
            'verse_number'     => 'required_without:verse_id|integer',
            'color'            => $explicit ? 'nullable|in:yellow,blue,green,red' : 'required|in:yellow,blue,green,red',
            'end_verse_number' => 'nullable|integer',
        ]);

        if ($request->filled('verse_id')) {
            $verse = Verse::find($request->verse_id);
            $chapterId = $verse->chapter_id;
            $verseNumber = $verse->number;
        } else {
            $chapterId = (int) $request->chapter_id;
            $verseNumber = (int) $request->verse_number;
        }

        $endVerseNumber = $request->end_verse_number ? (int) $request->end_verse_number : null;
        if (! $endVerseNumber || $endVerseNumber < $verseNumber) {
            $endVerseNumber = $verseNumber;
        }

        if ($explicit) {
            // Offline sync path: the client already computed the desired end state locally,
            // so apply it as-is rather than toggling against whatever the server currently has.
            $newColor = $request->input('color') ?: null;
        } else {
            $pref = UserVersePreference::where('user_id', Auth::id())
                ->where('chapter_id', $chapterId)
                ->where('verse_number', $verseNumber)
                ->first();

            // Same color as the range's starting verse → remove highlight from the whole range (toggle off)
            $newColor = ($pref && $pref->highlight_color === $request->color) ? null : $request->color;
        }

        for ($number = $verseNumber; $number <= $endVerseNumber; $number++) {
            UserVersePreference::updateOrCreate(
                ['user_id' => Auth::id(), 'chapter_id' => $chapterId, 'verse_number' => $number],
                ['highlight_color' => $newColor]
            );
        }

        return response()->json(['color' => $newColor]);
    }
}
