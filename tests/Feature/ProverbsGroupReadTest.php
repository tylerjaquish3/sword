<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\ProverbsGroup;
use App\Models\ProverbsVerseGroup;
use App\Models\Translation;
use App\Models\User;
use App\Models\Verse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProverbsGroupReadTest extends TestCase
{
    use RefreshDatabase;

    private function seedProverbs(User $admin): array
    {
        $book = Book::create(['name' => 'Proverbs', 'abbr' => 'Prov', 'new_testament' => 0]);
        $translation = Translation::create(['name' => 'KJV']);
        $admin->forceFill(['default_translation_id' => $translation->id])->save();

        $chapterOne = Chapter::create(['book_id' => $book->id, 'number' => 1]);
        $chapterTwo = Chapter::create(['book_id' => $book->id, 'number' => 2]);

        $verses = [
            'c1v2' => Verse::create(['chapter_id' => $chapterOne->id, 'translation_id' => $translation->id, 'number' => 2, 'reference' => 'Proverbs 1:2', 'text' => 'Verse one two']),
            'c1v1' => Verse::create(['chapter_id' => $chapterOne->id, 'translation_id' => $translation->id, 'number' => 1, 'reference' => 'Proverbs 1:1', 'text' => 'Verse one one']),
            'c2v1' => Verse::create(['chapter_id' => $chapterTwo->id, 'translation_id' => $translation->id, 'number' => 1, 'reference' => 'Proverbs 2:1', 'text' => 'Verse two one']),
        ];

        return [$chapterOne, $chapterTwo, $verses];
    }

    public function test_reading_a_group_shows_its_verses_in_canonical_order_regardless_of_assignment_order(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        [$chapterOne, $chapterTwo, $verses] = $this->seedProverbs($admin);
        $group = ProverbsGroup::create(['user_id' => $admin->id, 'name' => 'Wisdom']);

        // Assign out of canonical order: 1:2 first, then 1:1, then 2:1.
        ProverbsVerseGroup::create(['user_id' => $admin->id, 'proverbs_group_id' => $group->id, 'chapter_id' => $chapterOne->id, 'verse_number' => 2]);
        ProverbsVerseGroup::create(['user_id' => $admin->id, 'proverbs_group_id' => $group->id, 'chapter_id' => $chapterOne->id, 'verse_number' => 1]);
        ProverbsVerseGroup::create(['user_id' => $admin->id, 'proverbs_group_id' => $group->id, 'chapter_id' => $chapterTwo->id, 'verse_number' => 1]);

        $response = $this->actingAs($admin)->get(route('proverbs-groups.read', $group));

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Verse one one', 'Verse one two', 'Verse two one']);
    }

    public function test_reading_unassigned_shows_only_verses_with_no_group_across_all_chapters(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        [$chapterOne, $chapterTwo, $verses] = $this->seedProverbs($admin);
        $group = ProverbsGroup::create(['user_id' => $admin->id, 'name' => 'Wisdom']);
        ProverbsVerseGroup::create(['user_id' => $admin->id, 'proverbs_group_id' => $group->id, 'chapter_id' => $chapterOne->id, 'verse_number' => 1]);

        $response = $this->actingAs($admin)->get(route('proverbs-groups.read-unassigned'));

        $response->assertStatus(200);
        $response->assertDontSee('Verse one one');
        $response->assertSeeInOrder(['Verse one two', 'Verse two one']);
    }

    public function test_first_group_has_no_previous_and_unassigned_has_no_next(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->seedProverbs($admin);
        $onlyGroup = ProverbsGroup::create(['user_id' => $admin->id, 'name' => 'Only Group']);

        $groupResponse = $this->actingAs($admin)->get(route('proverbs-groups.read', $onlyGroup));
        $groupResponse->assertSee('disabled', false);
        $groupResponse->assertSee(route('proverbs-groups.read-unassigned'));

        $unassignedResponse = $this->actingAs($admin)->get(route('proverbs-groups.read-unassigned'));
        $unassignedResponse->assertSee(route('proverbs-groups.read', $onlyGroup));
    }

    public function test_non_admin_is_redirected_away_from_read_screens(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $group = ProverbsGroup::create(['user_id' => $user->id, 'name' => 'Wisdom']);

        $this->actingAs($user)->get(route('proverbs-groups.read', $group))
            ->assertRedirect(route('home.index'));
        $this->actingAs($user)->get(route('proverbs-groups.read-unassigned'))
            ->assertRedirect(route('home.index'));
    }

    public function test_admin_cannot_read_another_users_group(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $otherAdmin = User::factory()->create(['is_admin' => true]);
        $group = ProverbsGroup::create(['user_id' => $otherAdmin->id, 'name' => 'Not Yours']);

        $this->actingAs($admin)->get(route('proverbs-groups.read', $group))
            ->assertForbidden();
    }

    public function test_selected_translation_is_preserved_in_prev_and_next_navigation_links(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        [$chapterOne, $chapterTwo, $verses] = $this->seedProverbs($admin);
        $niv = Translation::create(['name' => 'NIV']);
        Verse::create(['chapter_id' => $chapterOne->id, 'translation_id' => $niv->id, 'number' => 1, 'reference' => 'Proverbs 1:1', 'text' => 'NIV verse one one']);
        Verse::create(['chapter_id' => $chapterOne->id, 'translation_id' => $niv->id, 'number' => 2, 'reference' => 'Proverbs 1:2', 'text' => 'NIV verse one two']);
        Verse::create(['chapter_id' => $chapterTwo->id, 'translation_id' => $niv->id, 'number' => 1, 'reference' => 'Proverbs 2:1', 'text' => 'NIV verse two one']);

        $groupOne = ProverbsGroup::create(['user_id' => $admin->id, 'name' => 'First']);
        $groupTwo = ProverbsGroup::create(['user_id' => $admin->id, 'name' => 'Second']);
        ProverbsVerseGroup::create(['user_id' => $admin->id, 'proverbs_group_id' => $groupOne->id, 'chapter_id' => $chapterOne->id, 'verse_number' => 1]);
        ProverbsVerseGroup::create(['user_id' => $admin->id, 'proverbs_group_id' => $groupTwo->id, 'chapter_id' => $chapterOne->id, 'verse_number' => 2]);

        // groupTwo sits between groupOne and the unassigned stop, so both the
        // prev and next nav links are rendered — proving translation_id is
        // carried through in both directions, not just one.
        $response = $this->actingAs($admin)->get(route('proverbs-groups.read', $groupTwo).'?translation_id='.$niv->id);

        $response->assertStatus(200);
        $occurrences = substr_count($response->getContent(), 'translation_id='.$niv->id);
        $this->assertGreaterThanOrEqual(2, $occurrences, 'Expected translation_id to appear in both the prev and next nav links.');
    }
}
