<?php

namespace Tests\Feature;

use App\Models\AccountabilityCheckIn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountabilityClientUuidTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'word_days' => 5,
            'word_quality' => 4,
            'prayer_days' => 6,
            'prayer_quality' => 3,
            'fellowship_level' => 'Decent',
            'overall_number' => 8,
            'submit_action' => 'save',
        ], $overrides);
    }

    public function test_logged_in_check_in_with_same_client_uuid_is_not_duplicated(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload(['client_uuid' => '55555555-5555-5555-5555-555555555555']);

        $this->actingAs($user)->post(route('accountability.store'), $payload);
        $this->actingAs($user)->post(route('accountability.store'), $payload);

        $this->assertSame(1, AccountabilityCheckIn::where('user_id', $user->id)->count());
    }

    public function test_check_in_without_client_uuid_still_works_as_before(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('accountability.store'), $this->validPayload());

        $response->assertRedirect(route('digest.history') . '#accountability');
        $this->assertSame(1, AccountabilityCheckIn::where('user_id', $user->id)->count());
    }
}
