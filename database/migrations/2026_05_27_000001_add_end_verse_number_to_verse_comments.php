<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verse_comments', function (Blueprint $table) {
            $table->unsignedInteger('end_verse_number')->nullable()->after('verse_number');
        });
    }

    public function down(): void
    {
        Schema::table('verse_comments', function (Blueprint $table) {
            $table->dropColumn('end_verse_number');
        });
    }
};
