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
        // Make table for verse comments
        Schema::create('verse_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verse_id');
            $table->foreignId('user_id');
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        // Make table for chapter comments
        Schema::create('chapter_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id');
            $table->foreignId('user_id');
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        // Make table for verse linking to other verses
        Schema::create('verse_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verse_id');
            $table->foreignId('linked_verse_id');
            $table->timestamps();
        });

        // Make table for topics
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('keywords')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verse_comments');
        Schema::dropIfExists('chapter_comments');
        Schema::dropIfExists('verse_links');
        Schema::dropIfExists('topics');
    }
};
