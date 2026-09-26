<?php

namespace Tests\Feature;

use App\Models\ProverbsGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProverbsGroupCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_is_redirected_away_from_every_proverbs_groups_route(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $group = ProverbsGroup::create(['user_id' => $user->id, 'name' => 'Wisdom']);

        $this->actingAs($user)->post('/proverbs-groups', ['name' => 'New'])
            ->assertRedirect(route('home.index'));
        $this->actingAs($user)->put("/proverbs-groups/{$group->id}", ['name' => 'Renamed'])
            ->assertRedirect(route('home.index'));
        $this->actingAs($user)->delete("/proverbs-groups/{$group->id}")
            ->assertRedirect(route('home.index'));
    }

    public function test_admin_can_create_a_group(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('proverbs-groups.store'), ['name' => 'Wisdom']);

        $this->assertDatabaseHas('proverbs_groups', ['user_id' => $admin->id, 'name' => 'Wisdom']);
    }

    public function test_admin_can_rename_their_own_group(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $group = ProverbsGroup::create(['user_id' => $admin->id, 'name' => 'Wisdom']);

        $this->actingAs($admin)->put(route('proverbs-groups.update', $group), ['name' => 'Renamed']);

        $this->assertDatabaseHas('proverbs_groups', ['id' => $group->id, 'name' => 'Renamed']);
    }

    public function test_admin_cannot_rename_another_users_group(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $otherAdmin = User::factory()->create(['is_admin' => true]);
        $group = ProverbsGroup::create(['user_id' => $otherAdmin->id, 'name' => 'Wisdom']);

        $this->actingAs($admin)->put(route('proverbs-groups.update', $group), ['name' => 'Hijacked'])
            ->assertForbidden();

        $this->assertDatabaseHas('proverbs_groups', ['id' => $group->id, 'name' => 'Wisdom']);
    }

    public function test_admin_can_delete_their_own_group(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $group = ProverbsGroup::create(['user_id' => $admin->id, 'name' => 'Wisdom']);

        $this->actingAs($admin)->delete(route('proverbs-groups.destroy', $group));

        $this->assertDatabaseMissing('proverbs_groups', ['id' => $group->id]);
    }

    public function test_admin_cannot_delete_another_users_group(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $otherAdmin = User::factory()->create(['is_admin' => true]);
        $group = ProverbsGroup::create(['user_id' => $otherAdmin->id, 'name' => 'Wisdom']);

        $this->actingAs($admin)->delete(route('proverbs-groups.destroy', $group))
            ->assertForbidden();

        $this->assertDatabaseHas('proverbs_groups', ['id' => $group->id]);
    }
}
