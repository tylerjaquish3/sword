<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verses', function (Blueprint $table) {
            $table->text('prefix')->nullable()->after('number');
        });

        // Copy prefix from user_verse_preferences to all matching verses
        DB::statement("
            UPDATE verses
            SET prefix = (
                SELECT prefix FROM user_verse_preferences
                WHERE user_verse_preferences.chapter_id = verses.chapter_id
                  AND user_verse_preferences.verse_number = verses.number
                  AND user_verse_preferences.prefix IS NOT NULL
                  AND user_verse_preferences.prefix != ''
                LIMIT 1
            )
            WHERE EXISTS (
                SELECT 1 FROM user_verse_preferences
                WHERE user_verse_preferences.chapter_id = verses.chapter_id
                  AND user_verse_preferences.verse_number = verses.number
                  AND user_verse_preferences.prefix IS NOT NULL
                  AND user_verse_preferences.prefix != ''
            )
        ");

        Schema::table('user_verse_preferences', function (Blueprint $table) {
            $table->dropColumn('prefix');
        });
    }

    public function down(): void
    {
        Schema::table('user_verse_preferences', function (Blueprint $table) {
            $table->text('prefix')->nullable();
        });

        $uid = (int) DB::table('users')->orderBy('id')->value('id');

        DB::statement("
            INSERT OR IGNORE INTO user_verse_preferences (user_id, chapter_id, verse_number, prefix, created_at, updated_at)
            SELECT {$uid}, chapter_id, number, prefix, datetime('now'), datetime('now')
            FROM verses
            WHERE prefix IS NOT NULL AND prefix != ''
        ");
        DB::statement("
            UPDATE user_verse_preferences SET prefix = (
                SELECT prefix FROM verses
                WHERE verses.chapter_id = user_verse_preferences.chapter_id
                  AND verses.number = user_verse_preferences.verse_number
            )
            WHERE user_id = {$uid} AND EXISTS (
                SELECT 1 FROM verses
                WHERE verses.chapter_id = user_verse_preferences.chapter_id
                  AND verses.number = user_verse_preferences.verse_number
                  AND verses.prefix IS NOT NULL AND verses.prefix != ''
            )
        ");

        Schema::table('verses', function (Blueprint $table) {
            $table->dropColumn('prefix');
        });
    }
};
