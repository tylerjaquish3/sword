<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topic_note_verses', function (Blueprint $table) {
            $table->foreignId('topic_note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('verse_id')->constrained()->cascadeOnDelete();
            $table->primary(['topic_note_id', 'verse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topic_note_verses');
    }
};
