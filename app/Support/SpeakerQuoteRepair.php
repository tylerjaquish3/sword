<?php

namespace App\Support;

/**
 * The Keplin API drops the closing quote, the space and the opening quote when the speaker
 * changes mid-verse in the NIV and NLT, so `...among us." "That's enough,"` arrives as
 * `...among us.That's enough,"`. A sentence ender butted directly against a capital letter
 * inside a quoted verse is that signature; this puts the lost `" "` back.
 */
class SpeakerQuoteRepair
{
    public static function repair(string $text): string
    {
        if (! str_contains($text, '"')) {
            return $text;
        }

        // Skip a preceding dot so ellipses ("...Waheb") and dotted leaders are left alone.
        return preg_replace('/(?<!\.)([.!?])(?=[A-Z])/', '$1" "', $text);
    }
}
