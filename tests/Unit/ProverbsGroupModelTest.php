<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\ProverbsGroup;
use App\Models\ProverbsVerseGroup;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProverbsGroupModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeProverbsChapter(int $number = 1): Chapter
    {
        $book = Book::create(['name' => 'Proverbs', 'abbr' => 'Prov', 'new_testament' => 0]);

        return Chapter::create(['book_id' => $book->id, 'number' => $number]);
    }

    public function test_a_group_has_many_verse_assignments(): void
    {
        $user = User::factory()->create();
        $chapter = $this->makeProverbsChapter();
        $group = ProverbsGroup::create(['user_id' => $user->id, 'name' => 'Wisdom']);

        ProverbsVerseGroup::create([
            'user_id' => $user->id,
            'proverbs_group_id' => $group->id,
            'chapter_id' => $chapter->id,
            'verse_number' => 1,
        ]);

        $this->assertCount(1, $group->fresh()->verseAssignments);
    }

    public function test_a_verse_can_only_be_assigned_to_one_group_per_user(): void
    {
        $user = User::factory()->create();
        $chapter = $this->makeProverbsChapter();
        $groupOne = ProverbsGroup::create(['user_id' => $user->id, 'name' => 'Wisdom']);
        $groupTwo = ProverbsGroup::create(['user_id' => $user->id, 'name' => 'Folly']);

        ProverbsVerseGroup::create([
            'user_id' => $user->id,
            'proverbs_group_id' => $groupOne->id,
            'chapter_id' => $chapter->id,
            'verse_number' => 1,
        ]);

        $this->expectException(QueryException::class);

        ProverbsVerseGroup::create([
            'user_id' => $user->id,
            'proverbs_group_id' => $groupTwo->id,
            'chapter_id' => $chapter->id,
            'verse_number' => 1,
        ]);
    }

    public function test_deleting_a_group_removes_its_verse_assignments(): void
    {
        $user = User::factory()->create();
        $chapter = $this->makeProverbsChapter();
        $group = ProverbsGroup::create(['user_id' => $user->id, 'name' => 'Wisdom']);
        ProverbsVerseGroup::create([
            'user_id' => $user->id,
            'proverbs_group_id' => $group->id,
            'chapter_id' => $chapter->id,
            'verse_number' => 1,
        ]);

        $group->delete();

        $this->assertDatabaseCount('proverbs_verse_groups', 0);
    }
}
