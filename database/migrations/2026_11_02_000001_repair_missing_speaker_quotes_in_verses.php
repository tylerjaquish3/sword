<?php

use App\Http\Controllers\OfflineBundleController;
use App\Support\SpeakerQuoteRepair;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('verses')
            ->where('text', 'like', '%"%')
            ->orderBy('id')
            ->select('id', 'text')
            ->chunkById(500, function ($verses) {
                foreach ($verses as $verse) {
                    $repaired = SpeakerQuoteRepair::repair($verse->text);

                    if ($repaired !== $verse->text) {
                        DB::table('verses')->where('id', $verse->id)->update(['text' => $repaired]);
                    }
                }
            });

        // The offline bundle caches verse text forever, so drop it to pick up the repaired text.
        Cache::forget(OfflineBundleController::STATIC_BUNDLE_CACHE_KEY);
    }

    public function down(): void
    {
        // Not reversible: the repaired text is the correct text.
    }
};
