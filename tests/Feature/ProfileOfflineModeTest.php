<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileOfflineModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_turn_on_offline_mode(): void
    {
        $user = User::factory()->create(['offline_enabled' => false]);

        $response = $this->actingAs($user)
            ->patchJson(route('profile.offline-mode'), ['offline_enabled' => true]);

        $response->assertOk()->assertJson(['offline_enabled' => true]);
        $this->assertTrue($user->fresh()->offline_enabled);
    }

    public function test_user_can_turn_off_offline_mode(): void
    {
        $user = User::factory()->create(['offline_enabled' => true]);

        $response = $this->actingAs($user)
            ->patchJson(route('profile.offline-mode'), ['offline_enabled' => false]);

        $response->assertOk()->assertJson(['offline_enabled' => false]);
        $this->assertFalse($user->fresh()->offline_enabled);
    }

    public function test_offline_enabled_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patchJson(route('profile.offline-mode'), []);

        $response->assertStatus(422);
    }
}
