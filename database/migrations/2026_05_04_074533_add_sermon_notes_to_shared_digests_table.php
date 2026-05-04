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
            $table->text('sermon_notes')->nullable()->after('additional_content');
        });
    }

    public function down(): void
    {
        Schema::table('shared_digests', function (Blueprint $table) {
            $table->dropColumn('sermon_notes');
        });
    }
};
