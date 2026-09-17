<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateGroups = DB::table('verses')
            ->select('chapter_id', 'translation_id', 'number')
            ->groupBy('chapter_id', 'translation_id', 'number')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            $ids = DB::table('verses')
                ->where('chapter_id', $group->chapter_id)
                ->where('translation_id', $group->translation_id)
                ->where('number', $group->number)
                ->orderBy('id')
                ->pluck('id');

            $keepId = $ids->first();
            $dupIds = $ids->slice(1);

            foreach ($dupIds as $dupId) {
                // Remap references, skipping any that would collide with a row the keeper already has
                DB::table('memory_verse')->where('verse_id', $dupId)
                    ->whereNotIn('memory_id', function ($q) use ($keepId) {
                        $q->select('memory_id')->from('memory_verse')->where('verse_id', $keepId);
                    })
                    ->update(['verse_id' => $keepId]);
                DB::table('memory_verse')->where('verse_id', $dupId)->delete();

                DB::table('topic_note_verses')->where('verse_id', $dupId)
                    ->whereNotIn('topic_note_id', function ($q) use ($keepId) {
                        $q->select('topic_note_id')->from('topic_note_verses')->where('verse_id', $keepId);
                    })
                    ->update(['verse_id' => $keepId]);
                DB::table('topic_note_verses')->where('verse_id', $dupId)->delete();

                DB::table('verse_quiz_attempts')->where('verse_id', $dupId)->update(['verse_id' => $keepId]);
                DB::table('verse_comments')->where('verse_id', $dupId)->update(['verse_id' => $keepId]);

                DB::table('verses')->where('id', $dupId)->delete();
            }
        }

        Schema::table('verses', function (Blueprint $table) {
            $table->unique(['chapter_id', 'translation_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::table('verses', function (Blueprint $table) {
            $table->dropUnique(['chapter_id', 'translation_id', 'number']);
        });
    }
};
