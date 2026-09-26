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

class StudyHubProverbsTabTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_does_not_see_the_proverbs_tab(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get(route('topics.index'));

        $response->assertStatus(200);
        $response->assertDontSee('tab-proverbs', false);
    }

    public function test_admin_sees_the_proverbs_tab_with_group_and_unassigned_counts(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $book = Book::create(['name' => 'Proverbs', 'abbr' => 'Prov', 'new_testament' => 0]);
        $chapter = Chapter::create(['book_id' => $book->id, 'number' => 1]);
        $translation = Translation::create(['name' => 'KJV']);
        $admin->forceFill(['default_translation_id' => $translation->id])->save();
        Verse::create(['chapter_id' => $chapter->id, 'translation_id' => $translation->id, 'number' => 1, 'reference' => 'Proverbs 1:1', 'text' => 'Verse text']);
        Verse::create(['chapter_id' => $chapter->id, 'translation_id' => $translation->id, 'number' => 2, 'reference' => 'Proverbs 1:2', 'text' => 'Verse text two']);

        $group = ProverbsGroup::create(['user_id' => $admin->id, 'name' => 'Wisdom']);
        ProverbsVerseGroup::create(['user_id' => $admin->id, 'proverbs_group_id' => $group->id, 'chapter_id' => $chapter->id, 'verse_number' => 1]);

        $response = $this->actingAs($admin)->get(route('topics.index'));

        $response->assertStatus(200);
        $response->assertSee('tab-proverbs', false);
        $response->assertSee('Wisdom');
        $response->assertSee(route('proverbs-groups.index'));
    }
}
