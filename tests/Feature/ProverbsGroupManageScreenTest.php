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

class ProverbsGroupManageScreenTest extends TestCase
{
    use RefreshDatabase;

    private function seedProverbsChapterOneVerseOne(User $admin): array
    {
        $book = Book::create(['name' => 'Proverbs', 'abbr' => 'Prov', 'new_testament' => 0]);
        $chapter = Chapter::create(['book_id' => $book->id, 'number' => 1]);
        $translation = Translation::create(['name' => 'KJV']);
        $admin->forceFill(['default_translation_id' => $translation->id])->save();
        $verse = Verse::create([
            'chapter_id' => $chapter->id,
            'translation_id' => $translation->id,
            'number' => 1,
            'reference' => 'Proverbs 1:1',
            'text' => 'The proverbs of Solomon the son of David, king of Israel;',
        ]);

        return [$chapter, $verse];
    }

    public function test_non_admin_is_redirected_away_from_the_manage_screen(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('proverbs-groups.index'))
            ->assertRedirect(route('home.index'));
    }

    public function test_manage_screen_lists_groups_and_verse_text_in_the_default_translation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        [$chapter, $verse] = $this->seedProverbsChapterOneVerseOne($admin);
        ProverbsGroup::create(['user_id' => $admin->id, 'name' => 'Wisdom']);

        $response = $this->actingAs($admin)->get(route('proverbs-groups.index'));

        $response->assertStatus(200);
        $response->assertSee('Wisdom');
        $response->assertSee($verse->text);
    }

    public function test_an_unassigned_verse_shows_the_unassigned_option_checked(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        [$chapter] = $this->seedProverbsChapterOneVerseOne($admin);

        $response = $this->actingAs($admin)->get(route('proverbs-groups.index'));

        $response->assertSee(
            'id="verse-'.$chapter->id.'-1-unassigned" value="unassigned" autocomplete="off" checked',
            false
        );
    }

    public function test_an_assigned_verse_shows_its_group_option_checked(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        [$chapter] = $this->seedProverbsChapterOneVerseOne($admin);
        $group = ProverbsGroup::create(['user_id' => $admin->id, 'name' => 'Wisdom']);
        ProverbsVerseGroup::create([
            'user_id' => $admin->id,
            'proverbs_group_id' => $group->id,
            'chapter_id' => $chapter->id,
            'verse_number' => 1,
        ]);

        $response = $this->actingAs($admin)->get(route('proverbs-groups.index'));

        $response->assertSee(
            'id="verse-'.$chapter->id.'-1-'.$group->id.'" value="'.$group->id.'" autocomplete="off" checked',
            false
        );
    }
}
