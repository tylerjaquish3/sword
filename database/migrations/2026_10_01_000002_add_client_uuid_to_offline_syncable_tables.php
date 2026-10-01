<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'verse_comments',
        'chapter_comments',
        'prayers',
        'accountability_check_ins',
        'shared_digests',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->uuid('client_uuid')->nullable()->after('id');
                $blueprint->unique(['user_id', 'client_uuid']);
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropUnique("{$table}_user_id_client_uuid_unique");
                $blueprint->dropColumn('client_uuid');
            });
        }
    }
};
