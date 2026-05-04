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
        Schema::table('shared_digests', function (Blueprint $table) {
            $table->text('fruits_description')->nullable()->after('fruits_needing_prayer');
            $table->text('idols_description')->nullable()->after('idols');
        });
    }

    public function down(): void
    {
        Schema::table('shared_digests', function (Blueprint $table) {
            $table->dropColumn(['fruits_description', 'idols_description']);
        });
    }
};
