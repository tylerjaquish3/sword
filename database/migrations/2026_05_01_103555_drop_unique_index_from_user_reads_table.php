<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_reads', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'book_id', 'chapter_number', 'translation_id']);
        });
    }

    public function down(): void
    {
        Schema::table('user_reads', function (Blueprint $table) {
            $table->unique(['user_id', 'book_id', 'chapter_number', 'translation_id']);
        });
    }
};
