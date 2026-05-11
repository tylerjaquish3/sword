<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digest_guest_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shared_digest_id')->constrained('shared_digests')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->text('comment');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digest_guest_comments');
    }
};
