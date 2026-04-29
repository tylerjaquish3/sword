<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shared_digests', function (Blueprint $table) {
            $table->string('sharer_name')->nullable()->after('uuid');
        });
    }

    public function down(): void
    {
        Schema::table('shared_digests', function (Blueprint $table) {
            $table->dropColumn('sharer_name');
        });
    }
};
