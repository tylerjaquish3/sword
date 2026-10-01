<?php

namespace Tests\Feature;

use App\Models\Prayer;
use App\Models\PrayerType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrayerClientUuidTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_type_prayer_with_same_client_uuid_is_not_duplicated(): void
    {
        $user = User::factory()->create();
        $type = PrayerType::create(['name' => 'Requests']);
        $payload = [
            'date' => '2026-10-01',
            "type{$type->id}" => 'Pray for wisdom',
            'client_uuid' => '33333333-3333-3333-3333-333333333333',
        ];

        $this->actingAs($user)->postJson(route('prayers.store'), $payload);
        $this->actingAs($user)->postJson(route('prayers.store'), $payload);

        $this->assertSame(1, Prayer::withoutGlobalScopes()->where('user_id', $user->id)->count());
    }

    public function test_multi_type_prayer_request_ignores_client_uuid_and_still_creates_every_type(): void
    {
        $user = User::factory()->create();
        $typeA = PrayerType::create(['name' => 'Requests']);
        $typeB = PrayerType::create(['name' => 'Praises']);

        $response = $this->actingAs($user)->postJson(route('prayers.store'), [
            'date' => '2026-10-01',
            "type{$typeA->id}" => 'Pray for wisdom',
            "type{$typeB->id}" => 'Thankful for family',
            'client_uuid' => '44444444-4444-4444-4444-444444444444',
        ]);

        $response->assertOk();
        $this->assertSame(2, Prayer::withoutGlobalScopes()->where('user_id', $user->id)->count());
    }

    public function test_prayer_without_client_uuid_still_works_as_before(): void
    {
        $user = User::factory()->create();
        $type = PrayerType::create(['name' => 'Requests']);

        $response = $this->actingAs($user)->postJson(route('prayers.store'), [
            'date' => '2026-10-01',
            "type{$type->id}" => 'Pray for wisdom',
        ]);

        $response->assertOk();
        $this->assertSame(1, Prayer::withoutGlobalScopes()->where('user_id', $user->id)->count());
    }
}
