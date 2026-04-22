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
        Schema::table('prayers', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id');
        });

        // Backfill existing prayers to the first user
        DB::table('prayers')->whereNull('user_id')->update(['user_id' => 1]);
    }

    public function down(): void
    {
        Schema::table('prayers', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
