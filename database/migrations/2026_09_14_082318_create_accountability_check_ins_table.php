<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accountability_check_ins', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sharer_name')->nullable();
            $table->unsignedTinyInteger('word_days');
            $table->unsignedTinyInteger('word_quality');
            $table->unsignedTinyInteger('prayer_days');
            $table->unsignedTinyInteger('prayer_quality');
            $table->string('fellowship_level');
            $table->boolean('corner_man_prayer_request')->default(false);
            $table->boolean('corner_man_asked_how_to_pray')->default(false);
            $table->boolean('corner_man_encouragement')->default(false);
            $table->boolean('corner_man_multiple_touchpoints')->default(false);
            $table->unsignedTinyInteger('overall_number');
            $table->text('overall_why')->nullable();
            $table->text('one_praise')->nullable();
            $table->text('one_prayer')->nullable();
            $table->boolean('is_shared')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accountability_check_ins');
    }
};
