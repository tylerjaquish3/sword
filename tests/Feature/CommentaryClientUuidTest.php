<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\ChapterComment;
use App\Models\Translation;
use App\Models\User;
use App\Models\Verse;
use App\Models\VerseComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentaryClientUuidTest extends TestCase
{
    use RefreshDatabase;

    private function makeVerse(): Verse
    {
        $book = Book::create(['name' => 'John', 'abbr' => 'John', 'new_testament' => 1]);
        $chapter = Chapter::create(['book_id' => $book->id, 'number' => 1]);
        $translation = Translation::create(['name' => 'KJV']);

        return Verse::create([
            'chapter_id' => $chapter->id,
            'translation_id' => $translation->id,
            'number' => 1,
            'reference' => 'John 1:1',
            'text' => 'In the beginning was the Word.',
        ]);
    }

    public function test_verse_comment_with_same_client_uuid_is_not_duplicated(): void
    {
        $user = User::factory()->create();
        $verse = $this->makeVerse();
        $payload = ['verse_id' => $verse->id, 'comment' => 'Great verse', 'client_uuid' => '11111111-1111-1111-1111-111111111111'];

        $this->actingAs($user)->postJson(route('commentary.store'), $payload);
        $this->actingAs($user)->postJson(route('commentary.store'), $payload);

        $this->assertSame(1, VerseComment::withoutGlobalScopes()->where('user_id', $user->id)->count());
    }

    public function test_chapter_comment_with_same_client_uuid_is_not_duplicated(): void
    {
        $user = User::factory()->create();
        $verse = $this->makeVerse();
        $payload = [
            'type' => 'chapter',
            'chapter_id' => $verse->chapter_id,
            'comment' => 'Good chapter',
            'client_uuid' => '22222222-2222-2222-2222-222222222222',
        ];

        $this->actingAs($user)->postJson(route('commentary.store'), $payload);
        $this->actingAs($user)->postJson(route('commentary.store'), $payload);

        $this->assertSame(1, ChapterComment::withoutGlobalScopes()->where('user_id', $user->id)->count());
    }

    public function test_comment_without_client_uuid_still_works_as_before(): void
    {
        $user = User::factory()->create();
        $verse = $this->makeVerse();

        $response = $this->actingAs($user)->postJson(
            route('commentary.store'),
            [
                'verse_id' => $verse->id,
                'comment' => 'No uuid here',
            ],
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();
        $this->assertSame(1, VerseComment::withoutGlobalScopes()->where('user_id', $user->id)->count());
    }
}
