<?php

namespace Tests\Feature;

use App\Models\SharedDigest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SharedDigestWeekStartTest extends TestCase
{
    use RefreshDatabase;

    public function test_explicit_week_start_pins_the_snapshot_to_that_week(): void
    {
        $user = User::factory()->create();
        $pastMonday = Carbon::parse('2026-09-07')->startOfWeek();

        $response = $this->actingAs($user)->post(route('digest.complete.store'), [
            'submit_action' => 'save',
            'week_start' => $pastMonday->toDateString(),
        ]);

        $response->assertRedirect();
        $digest = SharedDigest::where('user_id', $user->id)->first();
        $this->assertNotNull($digest);
        $this->assertTrue($digest->week_start->isSameDay($pastMonday));
    }

    public function test_omitting_week_start_defaults_to_the_current_week(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('digest.complete.store'), ['submit_action' => 'save']);

        $digest = SharedDigest::where('user_id', $user->id)->first();
        $this->assertTrue($digest->week_start->isSameDay(now()->startOfWeek()));
    }

    public function test_malformed_week_start_returns_a_validation_error_not_a_crash(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('digest.complete.store'), [
            'submit_action' => 'save',
            'week_start' => 'not-a-date',
        ]);

        $response->assertStatus(422);
    }

    public function test_digest_with_same_client_uuid_is_not_duplicated(): void
    {
        $user = User::factory()->create();
        $payload = ['submit_action' => 'save', 'client_uuid' => '66666666-6666-6666-6666-666666666666'];

        $this->actingAs($user)->post(route('digest.complete.store'), $payload);
        $this->actingAs($user)->post(route('digest.complete.store'), $payload);

        $this->assertSame(1, SharedDigest::where('user_id', $user->id)->count());
    }
}
