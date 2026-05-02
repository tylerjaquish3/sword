<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_verse_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('verse_number');
            $table->string('highlight_color')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->text('prefix')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'chapter_id', 'verse_number']);
        });

        $uid = (int) DB::table('users')->orderBy('id')->value('id');

        DB::statement("
            INSERT INTO user_verse_preferences (user_id, chapter_id, verse_number, highlight_color, is_favorite, created_at, updated_at)
            SELECT {$uid}, chapter_id, verse_number, color, 0, created_at, updated_at
            FROM verse_highlights
        ");

        DB::statement("
            INSERT OR IGNORE INTO user_verse_preferences (user_id, chapter_id, verse_number, is_favorite, created_at, updated_at)
            SELECT {$uid}, chapter_id, verse_number, 1, created_at, updated_at
            FROM verse_favorites
        ");
        DB::statement("
            UPDATE user_verse_preferences SET is_favorite = 1
            WHERE user_id = {$uid} AND EXISTS (
                SELECT 1 FROM verse_favorites
                WHERE verse_favorites.chapter_id = user_verse_preferences.chapter_id
                  AND verse_favorites.verse_number = user_verse_preferences.verse_number
            )
        ");

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

        Schema::drop('verse_highlights');
        Schema::drop('verse_favorites');
        Schema::table('verses', function (Blueprint $table) {
            $table->dropColumn('prefix');
        });
    }

    public function down(): void
    {
        Schema::table('verses', function (Blueprint $table) {
            $table->string('prefix')->nullable()->after('number');
        });

        Schema::create('verse_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('verse_number');
            $table->timestamps();
            $table->unique(['chapter_id', 'verse_number']);
        });

        Schema::create('verse_highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('verse_number');
            $table->string('color');
            $table->timestamps();
            $table->unique(['chapter_id', 'verse_number']);
        });

        Schema::drop('user_verse_preferences');
    }
};
