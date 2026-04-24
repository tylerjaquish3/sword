<?php

use App\Jobs\ImportEsvVerses;
use App\Models\Translation;
use App\Models\Verse;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        ImportEsvVerses::dispatchSync();
    }

    public function down(): void
    {
        $translation = Translation::where('name', 'ESV')->first();

        if ($translation) {
            Verse::where('translation_id', $translation->id)->delete();
            $translation->delete();
        }
    }
};
