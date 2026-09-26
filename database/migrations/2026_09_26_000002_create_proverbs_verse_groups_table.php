<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proverbs_verse_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('proverbs_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('verse_number');
            $table->timestamps();
            $table->unique(['user_id', 'chapter_id', 'verse_number'], 'proverbs_verse_groups_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proverbs_verse_groups');
    }
};
