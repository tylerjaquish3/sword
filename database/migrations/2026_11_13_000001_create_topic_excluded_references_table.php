<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topic_excluded_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->string('reference');
            $table->timestamps();
            $table->unique(['topic_id', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topic_excluded_references');
    }
};
