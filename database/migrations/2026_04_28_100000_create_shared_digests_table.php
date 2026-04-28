<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_digests', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->date('week_start');
            $table->date('week_end');
            $table->json('snapshot');
            $table->boolean('show_chapters')->default(true);
            $table->boolean('show_prayers')->default(true);
            $table->boolean('show_commentary')->default(true);
            $table->boolean('show_memory')->default(true);
            $table->boolean('show_past_note')->default(true);
            $table->json('fruits_needing_prayer')->nullable();
            $table->text('impactful_scripture')->nullable();
            $table->json('idols')->nullable();
            $table->text('additional_content')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_digests');
    }
};
