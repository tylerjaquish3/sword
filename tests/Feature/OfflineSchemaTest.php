<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OfflineSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_has_offline_enabled_column(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'offline_enabled'));
    }

    public function test_offline_enabled_defaults_to_false_and_casts_to_boolean(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->fresh()->offline_enabled);
    }

    public function test_offline_syncable_tables_have_a_unique_client_uuid_column(): void
    {
        foreach (['verse_comments', 'chapter_comments', 'prayers', 'accountability_check_ins', 'shared_digests'] as $table) {
            $this->assertTrue(Schema::hasColumn($table, 'client_uuid'), "{$table} is missing client_uuid");
        }
    }
}
