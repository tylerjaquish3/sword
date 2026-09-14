<?php

namespace Tests\Feature;

use App\Models\AccountabilityCheckIn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DigestHistoryAccountabilityTabTest extends TestCase
{
    use RefreshDatabase;

    public function test_digest_history_page_lists_the_users_own_check_ins_only(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $mine = AccountabilityCheckIn::create($this->payload(['uuid' => 'mine', 'user_id' => $user->id]));
        AccountabilityCheckIn::create($this->payload(['uuid' => 'theirs', 'user_id' => $otherUser->id]));

        $response = $this->actingAs($user)->get(route('digest.history'));

        $response->assertStatus(200);
        $response->assertViewHas('checkIns', function ($checkIns) use ($mine) {
            return $checkIns->count() === 1 && $checkIns->first()->id === $mine->id;
        });
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'word_days' => 5,
            'word_quality' => 4,
            'prayer_days' => 6,
            'prayer_quality' => 3,
            'fellowship_level' => 'Decent',
            'overall_number' => 8,
        ], $overrides);
    }
}
