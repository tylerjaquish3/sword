<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verse_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('verse_number');
            $table->timestamps();
            $table->unique(['chapter_id', 'verse_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verse_favorites');
    }
};
