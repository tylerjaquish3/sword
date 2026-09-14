<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accountability_guest_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accountability_check_in_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->text('comment');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accountability_guest_comments');
    }
};
