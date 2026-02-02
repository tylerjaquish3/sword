<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\VerseComment;
use App\Models\Verse;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new columns to verse_comments if they don't exist
        if (!Schema::hasColumn('verse_comments', 'chapter_id')) {
            Schema::table('verse_comments', function (Blueprint $table) {
                $table->unsignedBigInteger('chapter_id')->nullable()->after('id');
            });
        }
        
        if (!Schema::hasColumn('verse_comments', 'verse_number')) {
            Schema::table('verse_comments', function (Blueprint $table) {
                $table->unsignedInteger('verse_number')->nullable()->after('chapter_id');
            });
        }

        // Migrate existing data using Eloquent (works with SQLite)
        $verseComments = DB::table('verse_comments')->whereNull('chapter_id')->get();
        foreach ($verseComments as $comment) {
            $verse = DB::table('verses')->where('id', $comment->verse_id)->first();
            if ($verse) {
                DB::table('verse_comments')
                    ->where('id', $comment->id)
                    ->update([
                        'chapter_id' => $verse->chapter_id,
                        'verse_number' => $verse->number
                    ]);
            }
        }

        // Remove duplicate comments (keep only the first one per chapter/verse/user/comment)
        $duplicates = DB::table('verse_comments as vc1')
            ->join('verse_comments as vc2', function($join) {
                $join->on('vc1.chapter_id', '=', 'vc2.chapter_id')
                    ->on('vc1.verse_number', '=', 'vc2.verse_number')
                    ->on('vc1.user_id', '=', 'vc2.user_id')
                    ->on('vc1.comment', '=', 'vc2.comment')
                    ->whereColumn('vc1.id', '>', 'vc2.id');
            })
            ->pluck('vc1.id');
        
        if ($duplicates->count() > 0) {
            DB::table('verse_comments')->whereIn('id', $duplicates)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('verse_comments', function (Blueprint $table) {
            if (Schema::hasColumn('verse_comments', 'chapter_id')) {
                $table->dropColumn('chapter_id');
            }
            if (Schema::hasColumn('verse_comments', 'verse_number')) {
                $table->dropColumn('verse_number');
            }
        });
    }
};
