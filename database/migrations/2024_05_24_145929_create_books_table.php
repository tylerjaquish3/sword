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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('abbr');
            $table->unsignedTinyInteger('new_testament');
        });
        
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('book_id');
            $table->unsignedInteger('number')->index();
            $table->text('description')->nullable();
        });
        
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
        });
       
        Schema::create('verses', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('chapter_id');
            $table->unsignedInteger('translation_id');
            $table->unsignedInteger('number');
            $table->string('reference')->index();
            $table->text('text')->index();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
        Schema::dropIfExists('chapters');
        Schema::dropIfExists('translations');
        Schema::dropIfExists('verses');
    }
};
