<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->text('history')->nullable()->after('description');
            $table->text('themes')->nullable()->after('history');
            $table->text('notes')->nullable()->after('themes');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['history', 'themes', 'notes']);
        });
    }
};
