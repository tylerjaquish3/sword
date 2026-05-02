<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete()->after('id');
        });

        $uid = (int) DB::table('users')->orderBy('id')->value('id');

        DB::statement("UPDATE topics SET user_id = {$uid}");

        Schema::table('topic_notes', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete()->after('id');
        });

        DB::statement("UPDATE topic_notes SET user_id = {$uid}");
    }

    public function down(): void
    {
        Schema::table('topic_notes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('topics', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
