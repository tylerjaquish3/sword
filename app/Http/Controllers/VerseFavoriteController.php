<?php

namespace App\Http\Controllers;

use App\Models\UserVersePreference;
use App\Models\Verse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerseFavoriteController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate([
            'verse_id' => 'required|exists:verses,id',
        ]);

        $verse = Verse::find($request->verse_id);

        $pref = UserVersePreference::where('user_id', Auth::id())
            ->where('chapter_id', $verse->chapter_id)
            ->where('verse_number', $verse->number)
            ->first();

        if ($pref?->is_favorite) {
            $pref->update(['is_favorite' => false]);
            return response()->json(['favorite' => false]);
        }

        UserVersePreference::updateOrCreate(
            ['user_id' => Auth::id(), 'chapter_id' => $verse->chapter_id, 'verse_number' => $verse->number],
            ['is_favorite' => true]
        );

        return response()->json(['favorite' => true]);
    }
}
