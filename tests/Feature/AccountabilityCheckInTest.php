<?php

namespace Tests\Feature;

use App\Models\AccountabilityCheckIn;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountabilityCheckInTest extends TestCase
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
            'corner_man_prayer_request' => '1',
            'corner_man_asked_how_to_pray' => '1',
            'corner_man_encouragement' => '0',
            'corner_man_multiple_touchpoints' => '0',
            'overall_number' => 8,
            'overall_why' => 'Solid week overall.',
            'one_praise' => 'Answered prayer for a friend.',
            'one_prayer' => 'Wisdom for a decision at work.',
        ], $overrides);
    }

    public function test_guest_can_view_the_create_form(): void
    {
        $response = $this->get(route('accountability.create'));

        $response->assertStatus(200);
    }

    public function test_guest_submitting_with_share_creates_an_unowned_shared_check_in(): void
    {
        $response = $this->post(route('accountability.store'), $this->validPayload([
            'submit_action' => 'share',
            'sharer_name' => 'A Guest',
        ]));

        $checkIn = AccountabilityCheckIn::first();

        $this->assertNotNull($checkIn);
        $this->assertNull($checkIn->user_id);
        $this->assertTrue($checkIn->is_shared);
        $response->assertRedirect(route('accountability.share.link', $checkIn->uuid));
    }

    public function test_logged_in_user_can_save_without_sharing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('accountability.store'), $this->validPayload([
            'submit_action' => 'save',
        ]));

        $checkIn = AccountabilityCheckIn::first();

        $this->assertNotNull($checkIn);
        $this->assertSame($user->id, $checkIn->user_id);
        $this->assertFalse($checkIn->is_shared);
        $response->assertRedirect(route('digest.history') . '#accountability');
    }

    public function test_logged_in_user_can_save_and_share(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('accountability.store'), $this->validPayload([
            'submit_action' => 'share',
        ]));

        $checkIn = AccountabilityCheckIn::first();

        $this->assertTrue($checkIn->is_shared);
        $response->assertRedirect(route('accountability.share.link', $checkIn->uuid));
    }

    public function test_anyone_can_view_the_public_shared_page(): void
    {
        $checkIn = AccountabilityCheckIn::create($this->validPayload([
            'uuid' => 'test-uuid-1',
            'is_shared' => true,
        ]));

        $response = $this->get(route('accountability.shared.show', $checkIn->uuid));

        $response->assertStatus(200);
        $response->assertSee('8', false);
    }

    public function test_guest_comment_notifies_the_owner_when_the_check_in_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $checkIn = AccountabilityCheckIn::create($this->validPayload([
            'uuid' => 'test-uuid-2',
            'user_id' => $user->id,
            'is_shared' => true,
        ]));

        $this->post(route('accountability.shared.comment', $checkIn->uuid), [
            'name' => 'Corner Man',
            'comment' => 'Praying for you this week!',
        ]);

        $this->assertDatabaseHas('accountability_guest_comments', [
            'accountability_check_in_id' => $checkIn->id,
            'comment' => 'Praying for you this week!',
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'type' => 'accountability_comment',
        ]);
    }

    public function test_guest_comment_on_an_unowned_check_in_creates_no_notification(): void
    {
        $checkIn = AccountabilityCheckIn::create($this->validPayload([
            'uuid' => 'test-uuid-3',
            'is_shared' => true,
        ]));

        $this->post(route('accountability.shared.comment', $checkIn->uuid), [
            'comment' => 'Praying for you!',
        ]);

        $this->assertDatabaseHas('accountability_guest_comments', [
            'accountability_check_in_id' => $checkIn->id,
        ]);
        $this->assertSame(0, UserNotification::count());
    }

    public function test_owner_can_view_their_own_check_in(): void
    {
        $user = User::factory()->create();
        $checkIn = AccountabilityCheckIn::create($this->validPayload([
            'uuid' => 'test-uuid-owner-view',
            'user_id' => $user->id,
        ]));

        $response = $this->actingAs($user)->get(route('accountability.show', $checkIn));

        $response->assertStatus(200);
        $response->assertSee('8', false);
    }

    public function test_owner_cannot_view_another_users_check_in(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $checkIn = AccountabilityCheckIn::create($this->validPayload([
            'uuid' => 'test-uuid-4',
            'user_id' => $owner->id,
        ]));

        $response = $this->actingAs($other)->get(route('accountability.show', $checkIn));

        $response->assertStatus(403);
    }

    public function test_owner_can_view_the_edit_form_for_an_unshared_check_in(): void
    {
        $user = User::factory()->create();
        $checkIn = AccountabilityCheckIn::create($this->validPayload([
            'uuid' => 'test-uuid-edit',
            'user_id' => $user->id,
            'is_shared' => false,
        ]));

        $response = $this->actingAs($user)->get(route('accountability.edit', $checkIn));

        $response->assertStatus(200);
    }

    public function test_owner_cannot_edit_a_check_in_already_shared(): void
    {
        $user = User::factory()->create();
        $checkIn = AccountabilityCheckIn::create($this->validPayload([
            'uuid' => 'test-uuid-edit-shared',
            'user_id' => $user->id,
            'is_shared' => true,
        ]));

        $response = $this->actingAs($user)->get(route('accountability.edit', $checkIn));

        $response->assertStatus(403);
    }

    public function test_owner_can_update_an_unshared_check_in(): void
    {
        $user = User::factory()->create();
        $checkIn = AccountabilityCheckIn::create($this->validPayload([
            'uuid' => 'test-uuid-update',
            'user_id' => $user->id,
            'is_shared' => false,
        ]));

        $response = $this->actingAs($user)->put(route('accountability.update', $checkIn), $this->validPayload([
            'overall_number' => 3,
            'submit_action' => 'save',
        ]));

        $response->assertRedirect(route('digest.history') . '#accountability');
        $this->assertSame(3, $checkIn->refresh()->overall_number);
        $this->assertFalse($checkIn->is_shared);
    }

    public function test_owner_can_mark_a_saved_check_in_as_shared(): void
    {
        $user = User::factory()->create();
        $checkIn = AccountabilityCheckIn::create($this->validPayload([
            'uuid' => 'test-uuid-mark-shared',
            'user_id' => $user->id,
            'is_shared' => false,
        ]));

        $response = $this->actingAs($user)->post(route('accountability.mark-shared', $checkIn));

        $response->assertRedirect(route('accountability.share.link', $checkIn->uuid));
        $this->assertTrue($checkIn->refresh()->is_shared);
    }

    public function test_owner_can_delete_their_own_check_in(): void
    {
        $user = User::factory()->create();
        $checkIn = AccountabilityCheckIn::create($this->validPayload([
            'uuid' => 'test-uuid-5',
            'user_id' => $user->id,
        ]));

        $response = $this->actingAs($user)->delete(route('accountability.destroy', $checkIn));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('accountability_check_ins', ['id' => $checkIn->id]);
    }
}
