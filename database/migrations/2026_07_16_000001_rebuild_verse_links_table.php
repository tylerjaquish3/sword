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
        // verse_links has never shipped a UI and is empty — rebuild rather than alter.
        // Switches from translation-specific verse_id columns to chapter_id + verse_number
        // (the same pattern used by verse_highlights/verse_comments) so a link shows up
        // no matter which translation the user is currently reading.
        Schema::dropIfExists('verse_links');

        Schema::create('verse_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('verse_number');
            $table->foreignId('linked_chapter_id')->constrained('chapters')->cascadeOnDelete();
            $table->unsignedSmallInteger('linked_verse_number');
            $table->timestamps();
            $table->unique(['chapter_id', 'verse_number', 'linked_chapter_id', 'linked_verse_number'], 'verse_links_pair_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verse_links');

        Schema::create('verse_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verse_id');
            $table->foreignId('linked_verse_id');
            $table->timestamps();
        });
    }
};
