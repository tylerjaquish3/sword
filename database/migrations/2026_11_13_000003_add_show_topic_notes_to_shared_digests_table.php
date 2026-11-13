<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shared_digests', function (Blueprint $table) {
            $table->boolean('show_topic_notes')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('shared_digests', function (Blueprint $table) {
            $table->dropColumn('show_topic_notes');
        });
    }
};
