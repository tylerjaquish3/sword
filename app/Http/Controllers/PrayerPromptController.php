<?php

namespace App\Http\Controllers;

use App\Models\PrayerPrompt;
use Illuminate\Http\Request;

class PrayerPromptController extends Controller
{
    // Bulk-save all prompts for the current user: replace existing set with new values.
    // Expects: { prompts: { "default": "...", "0": "...", "1": "...", ... } }
    public function sync(Request $request)
    {
        $userId = auth()->id();
        $incoming = $request->input('prompts', []);

        PrayerPrompt::where('user_id', $userId)->delete();

        foreach ($incoming as $key => $text) {
            $text = trim($text);
            if ($text === '') {
                continue;
            }

            PrayerPrompt::create([
                'user_id'     => $userId,
                'day_of_week' => $key === 'default' ? null : (int) $key,
                'prompt'      => $text,
            ]);
        }

        return response()->json(['success' => true]);
    }
}
