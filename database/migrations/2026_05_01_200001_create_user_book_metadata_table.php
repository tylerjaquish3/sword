<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_book_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('author')->nullable();
            $table->string('timeframe')->nullable();
            $table->text('description')->nullable();
            $table->text('history')->nullable();
            $table->text('themes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'book_id']);
        });

        $uid = (int) DB::table('users')->orderBy('id')->value('id');

        DB::statement("
            INSERT INTO user_book_metadata (user_id, book_id, author, timeframe, description, history, themes, notes, created_at, updated_at)
            SELECT {$uid}, id, author, timeframe, description, history, themes, notes, datetime('now'), datetime('now')
            FROM books
            WHERE author IS NOT NULL
               OR timeframe IS NOT NULL
               OR description IS NOT NULL
               OR history IS NOT NULL
               OR themes IS NOT NULL
               OR notes IS NOT NULL
        ");

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['author', 'timeframe', 'description', 'history', 'themes', 'notes']);
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('author')->nullable();
            $table->string('timeframe')->nullable();
            $table->text('description')->nullable();
            $table->text('history')->nullable();
            $table->text('themes')->nullable();
            $table->text('notes')->nullable();
        });

        Schema::drop('user_book_metadata');
    }
};
