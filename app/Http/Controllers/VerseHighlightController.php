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
            'verse_id' => 'required|exists:verses,id',
            'color'    => 'required|in:yellow,blue,green,red',
        ]);

        $verse = Verse::find($request->verse_id);

        $pref = UserVersePreference::where('user_id', Auth::id())
            ->where('chapter_id', $verse->chapter_id)
            ->where('verse_number', $verse->number)
            ->first();

        // Same color → remove highlight (toggle off)
        if ($pref && $pref->highlight_color === $request->color) {
            $pref->update(['highlight_color' => null]);
            return response()->json(['color' => null]);
        }

        UserVersePreference::updateOrCreate(
            ['user_id' => Auth::id(), 'chapter_id' => $verse->chapter_id, 'verse_number' => $verse->number],
            ['highlight_color' => $request->color]
        );

        return response()->json(['color' => $request->color]);
    }
}
