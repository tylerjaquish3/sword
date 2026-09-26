<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\ProverbsGroup;
use App\Models\ProverbsVerseGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProverbsGroupAssignTest extends TestCase
{
    use RefreshDatabase;

    private function makeProverbsChapter(int $number = 1): Chapter
    {
        $book = Book::query()->where('name', 'Proverbs')->first()
            ?? Book::create(['name' => 'Proverbs', 'abbr' => 'Prov', 'new_testament' => 0]);

        return Chapter::create(['book_id' => $book->id, 'number' => $number]);
    }

    public function test_assigning_a_verse_creates_an_assignment_row(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $chapter = $this->makeProverbsChapter();
        $group = ProverbsGroup::create(['user_id' => $admin->id, 'name' => 'Wisdom']);

        $this->actingAs($admin)->post(route('proverbs-groups.assign'), [
            'assignments' => [
                $chapter->id => [1 => (string) $group->id],
            ],
        ]);

        $this->assertDatabaseHas('proverbs_verse_groups', [
            'user_id' => $admin->id,
            'chapter_id' => $chapter->id,
            'verse_number' => 1,
            'proverbs_group_id' => $group->id,
        ]);
    }

    public function test_reassigning_a_verse_moves_it_instead_of_duplicating(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $chapter = $this->makeProverbsChapter();
        $groupOne = ProverbsGroup::create(['user_id' => $admin->id, 'name' => 'Wisdom']);
        $groupTwo = ProverbsGroup::create(['user_id' => $admin->id, 'name' => 'Folly']);

        $this->actingAs($admin)->post(route('proverbs-groups.assign'), [
            'assignments' => [$chapter->id => [1 => (string) $groupOne->id]],
        ]);
        $this->actingAs($admin)->post(route('proverbs-groups.assign'), [
            'assignments' => [$chapter->id => [1 => (string) $groupTwo->id]],
        ]);

        $this->assertDatabaseCount('proverbs_verse_groups', 1);
        $this->assertDatabaseHas('proverbs_verse_groups', [
            'chapter_id' => $chapter->id,
            'verse_number' => 1,
            'proverbs_group_id' => $groupTwo->id,
        ]);
    }

    public function test_setting_a_verse_to_unassigned_deletes_its_row(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $chapter = $this->makeProverbsChapter();
        $group = ProverbsGroup::create(['user_id' => $admin->id, 'name' => 'Wisdom']);
        ProverbsVerseGroup::create([
            'user_id' => $admin->id,
            'proverbs_group_id' => $group->id,
            'chapter_id' => $chapter->id,
            'verse_number' => 1,
        ]);

        $this->actingAs($admin)->post(route('proverbs-groups.assign'), [
            'assignments' => [$chapter->id => [1 => 'unassigned']],
        ]);

        $this->assertDatabaseCount('proverbs_verse_groups', 0);
    }

    public function test_assign_rejects_a_group_id_belonging_to_another_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $otherAdmin = User::factory()->create(['is_admin' => true]);
        $chapter = $this->makeProverbsChapter();
        $otherGroup = ProverbsGroup::create(['user_id' => $otherAdmin->id, 'name' => 'Not Yours']);

        $this->actingAs($admin)->post(route('proverbs-groups.assign'), [
            'assignments' => [$chapter->id => [1 => (string) $otherGroup->id]],
        ])->assertStatus(422);

        $this->assertDatabaseCount('proverbs_verse_groups', 0);
    }
}
