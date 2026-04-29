<?php

use App\Enum\CommonWords;
use App\Models\Verse;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $commonWords = CommonWords::COMMON_WORDS;

        Verse::whereNull('key_words')
            ->orWhere('key_words', '')
            ->chunk(500, function ($verses) use ($commonWords) {
                foreach ($verses as $verse) {
                    $words = preg_replace('/[^a-zA-Z0-9\s]/', '', $verse->text);
                    $words = strtolower($words);
                    $words = array_diff(explode(' ', $words), $commonWords);
                    $verse->key_words = implode(' ', $words);
                    $verse->save();
                }
            });
    }

    public function down(): void
    {
        //
    }
};
