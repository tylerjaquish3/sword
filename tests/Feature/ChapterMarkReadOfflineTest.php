<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Translation;
use App\Models\User;
use App\Models\UserRead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChapterMarkReadOfflineTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $extra = []): array
    {
        $book = Book::create(['name' => 'John', 'abbr' => 'JHN', 'new_testament' => 1, 'sort_order' => 43]);
        $translation = Translation::create(['name' => 'KJV']);

        return array_merge([
            'book_id' => $book->id,
            'chapter_number' => 1,
            'translation_id' => $translation->id,
        ], $extra);
    }

    public function test_queued_read_keeps_the_client_read_at_timestamp(): void
    {
        $user = User::factory()->create();
        $readAt = now()->subDays(2)->startOfHour();

        $this->actingAs($user)
            ->postJson('/chapters/mark-read', $this->payload(['read_at' => $readAt->toIso8601String(), 'client_uuid' => (string) \Illuminate\Support\Str::uuid()]))
            ->assertOk();

        $this->assertTrue(UserRead::where('user_id', $user->id)->first()->read_at->equalTo($readAt));
    }

    public function test_replaying_the_same_offline_read_does_not_duplicate(): void
    {
        $user = User::factory()->create();
        $payload = $this->payload(['read_at' => now()->subHour()->toIso8601String()]);

        $this->actingAs($user)->postJson('/chapters/mark-read', $payload)->assertOk();
        $this->actingAs($user)->postJson('/chapters/mark-read', $payload)->assertOk()->assertJson(['already_read_today' => true]);

        $this->assertSame(1, UserRead::count());
    }

    public function test_read_at_without_value_defaults_to_now(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/chapters/mark-read', $this->payload())->assertOk();

        $this->assertSame(1, UserRead::count());
    }
}
