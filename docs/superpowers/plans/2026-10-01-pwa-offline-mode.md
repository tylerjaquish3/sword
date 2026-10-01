# PWA Install + Offline Mode Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make Sword installable as a PWA from Chrome, and let a user opt in to reading the Bible + their own comments/prayers/highlights offline, with new content (comments, prayers, highlights, accountability check-ins, digest reflections) queued and synced once back online.

**Architecture:** Three independent layers, in dependency order: (1) small server-side additions — a per-user `offline_enabled` flag, `client_uuid` dedupe columns on five tables, and a read-only bundle endpoint; (2) a PWA shell — manifest, icons, and a service worker that caches the app shell and falls back to a new Offline Reader page on failed navigation; (3) client-side JS — an IndexedDB store populated from the bundle endpoint, an outbox/sync-manager that queues writes made while offline and drains them on reconnect, and the Offline Reader page itself, which is the only page that talks to IndexedDB/the outbox (every other existing page is untouched and keeps working exactly as it does today).

**Tech Stack:** Laravel 10 / PHP 8.1, SQLite, Blade + jQuery 4 (no JS test runner in this repo — client-side tasks are verified manually via Chrome DevTools, per the spec's own Testing Plan), vanilla service worker + IndexedDB (no Workbox/Dexie — the cached dataset is small enough not to need a library).

**Spec:** `docs/superpowers/specs/2026-10-01-pwa-offline-mode-design.md`

## Global Constraints

- Offline Mode is opt-in per user (`users.offline_enabled`, default `false`). Nothing changes for a user who never turns it on.
- Only **creating** new comments/prayers/check-ins/digests is queueable offline — no offline edit or delete. Highlights are the one exception (an upsert, not a strict create) because they're a single-owner scalar value with no real conflict to avoid.
- The service worker never caches API/data responses — only the static app shell. All Bible text and personal-content caching goes through the explicit IndexedDB bundle sync, never opportunistic HTTP caching.
- No Background Sync API. The outbox drains only when the app is open and `navigator.onLine` is true (on the `online` event and once on load).
- The existing full-featured reading page (`resources/views/translations/index.blade.php`) is untouched — offline reading is a separate, simpler Offline Reader page. Cross-references are explicitly out of scope for offline.
- `client_uuid` columns are nullable `uuid`, unique per `(user_id, client_uuid)`. Online-created rows never set it; only the offline sync path does.

## Review Focus

- A retried/duplicate POST from the outbox (flaky connection resends the same item) must not create a duplicate row for verse comments, chapter comments, prayers, accountability check-ins, or digests — pinned by `client_uuid` dedupe tests in Tasks 3–6.
- `PrayerController::store` already loops over every non-null form field to create one `Prayer` per prayer-type key in a single request; naively applying `client_uuid` dedupe to that loop would silently drop every prayer type after the first if more than one field were ever present alongside a `client_uuid` — pinned by a dedicated test in Task 4.
- `VerseHighlightController::toggle` must still 422 (not 500) when neither `verse_id` nor `chapter_id`+`verse_number` is supplied — pinned in Task 7.
- `SharedDigestController::store` must reject a malformed `week_start` (422), not crash `Carbon::parse` with a 500 — pinned in Task 6.
- `OfflineBundleController` must never include another user's comments, prayers, or highlights in the response — pinned in Task 8.
- Opening the Offline Reader before ever enabling Offline Mode (no bundle synced yet) must show a clear "enable Offline Mode first" state, not a blank/broken page — pinned in Task 13.
- The outbox sync manager must distinguish a validation failure (stays queued, surfaced as "failed," keeps draining the rest of the queue) from a network failure (stops draining entirely, retried next time) — pinned via manual verification steps in Task 12.

---

### Task 1: Schema — `offline_enabled` and `client_uuid` columns

**Files:**
- Create: `database/migrations/2026_10_01_000001_add_offline_enabled_to_users_table.php`
- Create: `database/migrations/2026_10_01_000002_add_client_uuid_to_offline_syncable_tables.php`
- Modify: `app/Models/User.php`
- Test: `tests/Feature/OfflineSchemaTest.php`

**Interfaces:**
- Produces: `users.offline_enabled` (boolean, default false), `User::$fillable` includes `offline_enabled`, `User::$casts['offline_enabled'] = 'boolean'`. `client_uuid` (nullable uuid, unique per `user_id`) on `verse_comments`, `chapter_comments`, `prayers`, `accountability_check_ins`, `shared_digests`.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OfflineSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_has_offline_enabled_column(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'offline_enabled'));
    }

    public function test_offline_enabled_defaults_to_false_and_casts_to_boolean(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->fresh()->offline_enabled);
    }

    public function test_offline_syncable_tables_have_a_unique_client_uuid_column(): void
    {
        foreach (['verse_comments', 'chapter_comments', 'prayers', 'accountability_check_ins', 'shared_digests'] as $table) {
            $this->assertTrue(Schema::hasColumn($table, 'client_uuid'), "{$table} is missing client_uuid");
        }
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=OfflineSchemaTest`
Expected: FAIL — `offline_enabled`/`client_uuid` columns don't exist yet.

- [ ] **Step 3: Write the migrations**

`database/migrations/2026_10_01_000001_add_offline_enabled_to_users_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('offline_enabled')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('offline_enabled');
        });
    }
};
```

`database/migrations/2026_10_01_000002_add_client_uuid_to_offline_syncable_tables.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'verse_comments',
        'chapter_comments',
        'prayers',
        'accountability_check_ins',
        'shared_digests',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->uuid('client_uuid')->nullable()->after('id');
                $blueprint->unique(['user_id', 'client_uuid']);
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropUnique("{$table}_user_id_client_uuid_unique");
                $blueprint->dropColumn('client_uuid');
            });
        }
    }
};
```

- [ ] **Step 4: Update the User model**

Modify `app/Models/User.php`: add `'offline_enabled'` to `$fillable`, and `'offline_enabled' => 'boolean'` to `$casts`.

- [ ] **Step 5: Run migrations and the test to verify it passes**

Run: `php artisan migrate && php artisan test --filter=OfflineSchemaTest`
Expected: PASS (3 tests)

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_10_01_000001_add_offline_enabled_to_users_table.php \
        database/migrations/2026_10_01_000002_add_client_uuid_to_offline_syncable_tables.php \
        app/Models/User.php tests/Feature/OfflineSchemaTest.php
git commit -m "Add offline_enabled flag and client_uuid dedupe columns"
```

---

### Task 2: Profile "Offline Mode" toggle

**Files:**
- Modify: `app/Http/Controllers/ProfileController.php`
- Modify: `routes/web.php`
- Modify: `resources/views/profile/index.blade.php:211-241`
- Test: `tests/Feature/ProfileOfflineModeTest.php`

**Interfaces:**
- Consumes: `User::$fillable`/`$casts` from Task 1.
- Produces: `PATCH /profile/offline-mode` (route name `profile.offline-mode`), returns `{"offline_enabled": bool}`.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileOfflineModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_turn_on_offline_mode(): void
    {
        $user = User::factory()->create(['offline_enabled' => false]);

        $response = $this->actingAs($user)
            ->patchJson(route('profile.offline-mode'), ['offline_enabled' => true]);

        $response->assertOk()->assertJson(['offline_enabled' => true]);
        $this->assertTrue($user->fresh()->offline_enabled);
    }

    public function test_user_can_turn_off_offline_mode(): void
    {
        $user = User::factory()->create(['offline_enabled' => true]);

        $response = $this->actingAs($user)
            ->patchJson(route('profile.offline-mode'), ['offline_enabled' => false]);

        $response->assertOk()->assertJson(['offline_enabled' => false]);
        $this->assertFalse($user->fresh()->offline_enabled);
    }

    public function test_offline_enabled_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patchJson(route('profile.offline-mode'), []);

        $response->assertStatus(422);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ProfileOfflineModeTest`
Expected: FAIL — route `profile.offline-mode` doesn't exist.

- [ ] **Step 3: Add the route**

In `routes/web.php`, inside the existing `auth` middleware group, next to the other `/profile/*` route:

```php
Route::patch('/profile/offline-mode', [ProfileController::class, 'updateOfflineMode'])->name('profile.offline-mode');
```

- [ ] **Step 4: Add the controller method**

In `app/Http/Controllers/ProfileController.php`, add:

```php
public function updateOfflineMode(Request $request)
{
    $request->validate(['offline_enabled' => 'required|boolean']);

    $user = Auth::user();
    $user->update(['offline_enabled' => $request->boolean('offline_enabled')]);

    return response()->json(['offline_enabled' => $user->offline_enabled]);
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=ProfileOfflineModeTest`
Expected: PASS (3 tests)

- [ ] **Step 6: Add the toggle UI**

In `resources/views/profile/index.blade.php`, add a new card after the Preferences card (after line 241, before `@include('commentary.modals.verse')`):

```blade
<div class="row mb-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0"><i class="mdi mdi-cloud-off-outline me-2"></i>Offline Mode</h4>
            </div>
            <div class="card-body">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="offline_enabled"
                           {{ auth()->user()->offline_enabled ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="offline_enabled">
                        Enable offline access
                    </label>
                </div>
                <div class="form-text">
                    Downloads Bible text (all translations) plus your comments, prayers,
                    and highlights for offline reading. New comments, prayers, highlights,
                    accountability check-ins, and digests created while offline will sync
                    once you're back online.
                </div>
                <div id="offline-mode-status" class="small mt-2"></div>
            </div>
        </div>
    </div>
</div>
```

And in the existing `@push('js')` block, add:

```javascript
$('#offline_enabled').on('change', function () {
    var enabled = $(this).is(':checked');
    $.ajax({
        url: '{{ route("profile.offline-mode") }}',
        type: 'PATCH',
        data: { _token: '{{ csrf_token() }}', offline_enabled: enabled },
        success: function () {
            $('#offline-mode-status').text(enabled ? 'Offline Mode enabled.' : 'Offline Mode disabled.');
            if (enabled && window.swordOffline) {
                window.swordOffline.bundleSync.sync();
            }
            if (!enabled && window.swordOffline) {
                window.swordOffline.db.clearAll();
            }
        }
    });
});
```

(`window.swordOffline` is wired up in Task 11; this call is a no-op guard until that file loads.)

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/ProfileController.php routes/web.php \
        resources/views/profile/index.blade.php tests/Feature/ProfileOfflineModeTest.php
git commit -m "Add per-user Offline Mode toggle"
```

---

### Task 3: `client_uuid` dedupe for verse & chapter comments

**Files:**
- Modify: `app/Http/Controllers/CommentaryController.php:74-102`
- Test: `tests/Feature/CommentaryClientUuidTest.php`

**Interfaces:**
- Consumes: `client_uuid` column from Task 1.
- Produces: `commentary.store` accepts optional `client_uuid` (nullable uuid) on both the `type=chapter` and verse-comment branches; replays with the same `client_uuid` are no-ops.

- [ ] **Step 1: Write the failing test**

```php
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
        $book = Book::create(['name' => 'John', 'abbr' => 'JHN', 'new_testament' => 1, 'sort_order' => 43]);
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

        $response = $this->actingAs($user)->postJson(route('commentary.store'), [
            'verse_id' => $verse->id,
            'comment' => 'No uuid here',
        ]);

        $response->assertOk();
        $this->assertSame(1, VerseComment::withoutGlobalScopes()->where('user_id', $user->id)->count());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=CommentaryClientUuidTest`
Expected: FAIL — second request creates a duplicate row (no dedupe yet).

- [ ] **Step 3: Implement the dedupe**

Replace `app/Http/Controllers/CommentaryController.php:74-102` (`store()`) with:

```php
public function store()
{
    $type = request('type');
    $clientUuid = request('client_uuid');

    request()->validate(['client_uuid' => 'nullable|uuid']);

    if ($type === 'chapter') {
        $data = request()->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'comment' => 'required',
        ]);

        if ($clientUuid) {
            ChapterComment::firstOrCreate(['client_uuid' => $clientUuid], $data + ['client_uuid' => $clientUuid]);
        } else {
            ChapterComment::create($data);
        }
    } else {
        $data = request()->validate([
            'verse_id' => 'required|exists:verses,id',
            'comment' => 'required',
        ]);

        // Get the verse to extract chapter_id and verse_number
        $verse = Verse::find($data['verse_id']);

        $data['chapter_id'] = $verse->chapter_id;
        $data['verse_number'] = $verse->number;

        if ($clientUuid) {
            VerseComment::firstOrCreate(['client_uuid' => $clientUuid], $data + ['client_uuid' => $clientUuid]);
        } else {
            VerseComment::create($data);
        }
    }

    if (request()->ajax()) {
        return response()->json(['success' => true]);
    }
    return redirect()->route('commentary.index');
}
```

(`firstOrCreate`'s lookup goes through `VerseComment`/`ChapterComment`'s existing global scope, which already filters by the current `user_id` — so the dedupe lookup is automatically scoped to this user, and the `creating()` hook on both models still sets `user_id` on the created row.)

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=CommentaryClientUuidTest`
Expected: PASS (3 tests)

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/CommentaryController.php tests/Feature/CommentaryClientUuidTest.php
git commit -m "Dedupe verse/chapter comment creation on client_uuid"
```

---

### Task 4: `client_uuid` dedupe for prayers

**Files:**
- Modify: `app/Http/Controllers/PrayerController.php:32-55`
- Test: `tests/Feature/PrayerClientUuidTest.php`

**Interfaces:**
- Consumes: `client_uuid` column from Task 1.
- Produces: `prayers.store` accepts optional `client_uuid`; dedupe only applies when exactly one prayer-type field is present in the request (the Offline Reader's constraint — see Review Focus).

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Prayer;
use App\Models\PrayerType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrayerClientUuidTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_type_prayer_with_same_client_uuid_is_not_duplicated(): void
    {
        $user = User::factory()->create();
        $type = PrayerType::create(['name' => 'Requests']);
        $payload = [
            'date' => '2026-10-01',
            "type{$type->id}" => 'Pray for wisdom',
            'client_uuid' => '33333333-3333-3333-3333-333333333333',
        ];

        $this->actingAs($user)->postJson(route('prayers.store'), $payload);
        $this->actingAs($user)->postJson(route('prayers.store'), $payload);

        $this->assertSame(1, Prayer::withoutGlobalScopes()->where('user_id', $user->id)->count());
    }

    public function test_multi_type_prayer_request_ignores_client_uuid_and_still_creates_every_type(): void
    {
        $user = User::factory()->create();
        $typeA = PrayerType::create(['name' => 'Requests']);
        $typeB = PrayerType::create(['name' => 'Praises']);

        $response = $this->actingAs($user)->postJson(route('prayers.store'), [
            'date' => '2026-10-01',
            "type{$typeA->id}" => 'Pray for wisdom',
            "type{$typeB->id}" => 'Thankful for family',
            'client_uuid' => '44444444-4444-4444-4444-444444444444',
        ]);

        $response->assertOk();
        $this->assertSame(2, Prayer::withoutGlobalScopes()->where('user_id', $user->id)->count());
    }

    public function test_prayer_without_client_uuid_still_works_as_before(): void
    {
        $user = User::factory()->create();
        $type = PrayerType::create(['name' => 'Requests']);

        $response = $this->actingAs($user)->postJson(route('prayers.store'), [
            'date' => '2026-10-01',
            "type{$type->id}" => 'Pray for wisdom',
        ]);

        $response->assertOk();
        $this->assertSame(1, Prayer::withoutGlobalScopes()->where('user_id', $user->id)->count());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PrayerClientUuidTest`
Expected: FAIL — first test creates a duplicate row.

- [ ] **Step 3: Implement the dedupe**

Replace `app/Http/Controllers/PrayerController.php:32-55` (`store()`) with:

```php
public function store(Request $request)
{
    $data = $request->all();
    $clientUuid = $data['client_uuid'] ?? null;
    unset($data['client_uuid']);

    $typeFields = array_filter(
        $data,
        fn ($value, $key) => $key !== '_token' && $key !== 'date' && $value !== null,
        ARRAY_FILTER_USE_BOTH
    );

    // Dedupe only makes sense for a single-prayer request (the Offline Reader always
    // sends exactly one type field at a time). A multi-type request falls back to
    // plain creates so a stray client_uuid can never cause a type to be silently dropped.
    $useDedupe = $clientUuid && count($typeFields) === 1;

    foreach ($typeFields as $key => $value) {
        $prayerTypeId = str_replace('type', '', $key);

        if ($useDedupe) {
            Prayer::firstOrCreate(
                ['client_uuid' => $clientUuid],
                ['date' => $data['date'], 'content' => $value, 'prayer_type_id' => $prayerTypeId]
            );
        } else {
            Prayer::create([
                'date'           => $data['date'],
                'content'        => $value,
                'prayer_type_id' => $prayerTypeId,
            ]);
        }
    }

    if ($request->wantsJson() || $request->ajax()) {
        return response()->json(['success' => true]);
    }

    return redirect()->route('prayers.index');
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=PrayerClientUuidTest`
Expected: PASS (3 tests)

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/PrayerController.php tests/Feature/PrayerClientUuidTest.php
git commit -m "Dedupe single-type prayer creation on client_uuid"
```

---

### Task 5: `client_uuid` dedupe for accountability check-ins

**Files:**
- Modify: `app/Http/Controllers/AccountabilityCheckInController.php:22-40,124-140`
- Test: `tests/Feature/AccountabilityClientUuidTest.php`

**Interfaces:**
- Consumes: `client_uuid` column from Task 1.
- Produces: `accountability.store` accepts optional `client_uuid`; replays with the same value are no-ops (only for logged-in users — the public/guest path is unaffected, matching the spec's non-goal of leaving guest flows alone).

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\AccountabilityCheckIn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountabilityClientUuidTest extends TestCase
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
            'overall_number' => 8,
            'submit_action' => 'save',
        ], $overrides);
    }

    public function test_logged_in_check_in_with_same_client_uuid_is_not_duplicated(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload(['client_uuid' => '55555555-5555-5555-5555-555555555555']);

        $this->actingAs($user)->post(route('accountability.store'), $payload);
        $this->actingAs($user)->post(route('accountability.store'), $payload);

        $this->assertSame(1, AccountabilityCheckIn::where('user_id', $user->id)->count());
    }

    public function test_check_in_without_client_uuid_still_works_as_before(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('accountability.store'), $this->validPayload());

        $response->assertRedirect(route('digest.history') . '#accountability');
        $this->assertSame(1, AccountabilityCheckIn::where('user_id', $user->id)->count());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AccountabilityClientUuidTest`
Expected: FAIL — second request creates a duplicate row.

- [ ] **Step 3: Implement the dedupe**

In `app/Http/Controllers/AccountabilityCheckInController.php`, add `'client_uuid' => 'nullable|uuid',` to the `validated()` method's rule array (line ~140, alongside `overall_why`/`one_praise`/`one_prayer`).

Replace the `store()` method (lines 22-40) with:

```php
public function store(Request $request)
{
    $data = $this->validated($request);
    $clientUuid = $data['client_uuid'] ?? null;
    unset($data['client_uuid']);

    $isSharing = Auth::check() ? $request->input('submit_action') === 'share' : true;

    $attributes = array_merge($data, [
        'uuid' => Str::uuid()->toString(),
        'user_id' => Auth::id(),
        'sharer_name' => Auth::check() ? Auth::user()->name : $request->input('sharer_name'),
        'is_shared' => $isSharing,
    ]);

    if ($clientUuid && Auth::check()) {
        $checkIn = AccountabilityCheckIn::firstOrCreate(
            ['user_id' => Auth::id(), 'client_uuid' => $clientUuid],
            $attributes + ['client_uuid' => $clientUuid]
        );
    } else {
        $checkIn = AccountabilityCheckIn::create($attributes);
    }

    if ($isSharing) {
        return redirect()->route('accountability.share.link', $checkIn->uuid);
    }

    return redirect()
        ->to(route('digest.history') . '#accountability')
        ->with('success', 'Accountability check-in saved.');
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=AccountabilityClientUuidTest`
Expected: PASS (2 tests)

- [ ] **Step 5: Run the full existing accountability suite to confirm no regression**

Run: `php artisan test --filter=AccountabilityCheckInTest`
Expected: PASS (all pre-existing tests still pass)

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/AccountabilityCheckInController.php tests/Feature/AccountabilityClientUuidTest.php
git commit -m "Dedupe accountability check-in creation on client_uuid"
```

---

### Task 6: Digest `week_start` pinning + `client_uuid` dedupe

**Files:**
- Modify: `app/Http/Controllers/SharedDigestController.php:29-91`
- Test: `tests/Feature/SharedDigestWeekStartTest.php`

**Interfaces:**
- Consumes: `client_uuid` column from Task 1.
- Produces: `digest.complete.store` accepts optional `week_start` (date string) to pin which week's data `fetchWeeklyData()` builds the snapshot from, and optional `client_uuid` for dedupe.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\SharedDigest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SharedDigestWeekStartTest extends TestCase
{
    use RefreshDatabase;

    public function test_explicit_week_start_pins_the_snapshot_to_that_week(): void
    {
        $user = User::factory()->create();
        $pastMonday = Carbon::parse('2026-09-07')->startOfWeek();

        $response = $this->actingAs($user)->post(route('digest.complete.store'), [
            'submit_action' => 'save',
            'week_start' => $pastMonday->toDateString(),
        ]);

        $response->assertRedirect();
        $digest = SharedDigest::where('user_id', $user->id)->first();
        $this->assertNotNull($digest);
        $this->assertTrue($digest->week_start->isSameDay($pastMonday));
    }

    public function test_omitting_week_start_defaults_to_the_current_week(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('digest.complete.store'), ['submit_action' => 'save']);

        $digest = SharedDigest::where('user_id', $user->id)->first();
        $this->assertTrue($digest->week_start->isSameDay(now()->startOfWeek()));
    }

    public function test_malformed_week_start_returns_a_validation_error_not_a_crash(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('digest.complete.store'), [
            'submit_action' => 'save',
            'week_start' => 'not-a-date',
        ]);

        $response->assertStatus(422);
    }

    public function test_digest_with_same_client_uuid_is_not_duplicated(): void
    {
        $user = User::factory()->create();
        $payload = ['submit_action' => 'save', 'client_uuid' => '66666666-6666-6666-6666-666666666666'];

        $this->actingAs($user)->post(route('digest.complete.store'), $payload);
        $this->actingAs($user)->post(route('digest.complete.store'), $payload);

        $this->assertSame(1, SharedDigest::where('user_id', $user->id)->count());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SharedDigestWeekStartTest`
Expected: FAIL — `week_start` input is ignored, malformed input 500s, `client_uuid` duplicates.

- [ ] **Step 3: Implement `week_start` + `client_uuid`**

In `app/Http/Controllers/SharedDigestController.php`, add `'week_start' => 'nullable|date', 'client_uuid' => 'nullable|uuid',` to the `store()` validation array (lines 32-48).

Replace the body of `store()` from the `fetchWeeklyData` call onward (lines 50-89) with:

```php
$forWeekOf = $request->filled('week_start') ? \Carbon\Carbon::parse($request->input('week_start')) : null;
[$weekStart, $weekEnd, $data] = $this->fetchWeeklyData($forWeekOf);

$snapshot = $this->buildSnapshot($data);

$idols = $request->input('idols', []);
if ($request->filled('idols_other')) {
    foreach (explode(',', $request->input('idols_other')) as $extra) {
        $trimmed = trim($extra);
        if ($trimmed) {
            $idols[] = $trimmed;
        }
    }
}

$isSharing = $request->input('submit_action') === 'share';
$clientUuid = $request->input('client_uuid');

$attributes = [
    'uuid' => Str::uuid()->toString(),
    'user_id' => Auth::id(),
    'sharer_name' => Auth::user()->name,
    'week_start' => $weekStart->toDateString(),
    'week_end' => $weekEnd->toDateString(),
    'snapshot' => $snapshot,
    'is_shared' => $isSharing,
    'show_chapters' => $request->boolean('show_chapters'),
    'show_prayers' => $request->boolean('show_prayers'),
    'show_commentary' => $request->boolean('show_commentary'),
    'show_memory' => $request->boolean('show_memory'),
    'show_past_note' => $request->boolean('show_past_note'),
    'fruits_needing_prayer' => $request->input('fruits_needing_prayer', []),
    'fruits_description' => $request->input('fruits_description'),
    'impactful_scripture' => $request->input('impactful_scripture'),
    'idols' => $idols,
    'idols_description' => $request->input('idols_description'),
    'additional_content' => $request->input('additional_content'),
    'sermon_notes' => $request->input('sermon_notes'),
];

$shared = $clientUuid
    ? SharedDigest::firstOrCreate(['user_id' => Auth::id(), 'client_uuid' => $clientUuid], $attributes + ['client_uuid' => $clientUuid])
    : SharedDigest::create($attributes);

if ($isSharing) {
    return redirect()->route('digest.share.link', $shared->uuid);
}

return redirect()->route('digest.history')->with('success', 'Digest saved for ' . $weekStart->format('M j') . '–' . $weekEnd->format('M j, Y') . '.');
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=SharedDigestWeekStartTest`
Expected: PASS (4 tests)

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/SharedDigestController.php tests/Feature/SharedDigestWeekStartTest.php
git commit -m "Pin digest snapshot to an explicit week_start and dedupe on client_uuid"
```

---

### Task 7: Verse highlight — explicit color + chapter/verse-number input

**Files:**
- Modify: `app/Http/Controllers/VerseHighlightController.php`
- Test: `tests/Feature/VerseHighlightOfflineTest.php`

**Interfaces:**
- Produces: `verse-highlights.toggle` accepts `chapter_id`+`verse_number` as an alternative to `verse_id`, and a new `explicit` boolean flag — when `true`, `color` (nullable) is applied as-is (no toggle-against-current-state logic), which is what the offline sync path needs for idempotent replay.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\Translation;
use App\Models\User;
use App\Models\UserVersePreference;
use App\Models\Verse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerseHighlightOfflineTest extends TestCase
{
    use RefreshDatabase;

    private function makeChapter(): Chapter
    {
        $book = Book::create(['name' => 'John', 'abbr' => 'JHN', 'new_testament' => 1, 'sort_order' => 43]);

        return Chapter::create(['book_id' => $book->id, 'number' => 1]);
    }

    public function test_chapter_id_and_verse_number_can_be_used_instead_of_verse_id(): void
    {
        $user = User::factory()->create();
        $chapter = $this->makeChapter();

        $response = $this->actingAs($user)->postJson(route('verse-highlights.toggle'), [
            'chapter_id' => $chapter->id,
            'verse_number' => 3,
            'color' => 'yellow',
        ]);

        $response->assertOk()->assertJson(['color' => 'yellow']);
        $this->assertDatabaseHas('user_verse_preferences', [
            'user_id' => $user->id,
            'chapter_id' => $chapter->id,
            'verse_number' => 3,
            'highlight_color' => 'yellow',
        ]);
    }

    public function test_explicit_mode_sets_the_given_color_regardless_of_current_state(): void
    {
        $user = User::factory()->create();
        $chapter = $this->makeChapter();
        UserVersePreference::create([
            'user_id' => $user->id, 'chapter_id' => $chapter->id, 'verse_number' => 5, 'highlight_color' => 'yellow',
        ]);

        // Same color, explicit mode — must NOT toggle off (unlike the normal click-to-toggle behavior).
        $response = $this->actingAs($user)->postJson(route('verse-highlights.toggle'), [
            'chapter_id' => $chapter->id,
            'verse_number' => 5,
            'color' => 'yellow',
            'explicit' => true,
        ]);

        $response->assertOk()->assertJson(['color' => 'yellow']);
        $this->assertDatabaseHas('user_verse_preferences', [
            'user_id' => $user->id, 'chapter_id' => $chapter->id, 'verse_number' => 5, 'highlight_color' => 'yellow',
        ]);
    }

    public function test_explicit_mode_can_clear_a_highlight_with_a_null_color(): void
    {
        $user = User::factory()->create();
        $chapter = $this->makeChapter();
        UserVersePreference::create([
            'user_id' => $user->id, 'chapter_id' => $chapter->id, 'verse_number' => 5, 'highlight_color' => 'yellow',
        ]);

        $response = $this->actingAs($user)->postJson(route('verse-highlights.toggle'), [
            'chapter_id' => $chapter->id,
            'verse_number' => 5,
            'explicit' => true,
        ]);

        $response->assertOk()->assertJson(['color' => null]);
        $this->assertDatabaseHas('user_verse_preferences', [
            'user_id' => $user->id, 'chapter_id' => $chapter->id, 'verse_number' => 5, 'highlight_color' => null,
        ]);
    }

    public function test_missing_both_verse_id_and_chapter_verse_number_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('verse-highlights.toggle'), ['color' => 'yellow']);

        $response->assertStatus(422);
    }

    public function test_existing_verse_id_toggle_behavior_is_unchanged(): void
    {
        $user = User::factory()->create();
        $book = Book::create(['name' => 'John', 'abbr' => 'JHN', 'new_testament' => 1, 'sort_order' => 43]);
        $chapter = Chapter::create(['book_id' => $book->id, 'number' => 1]);
        $translation = Translation::create(['name' => 'KJV']);
        $verse = Verse::create(['chapter_id' => $chapter->id, 'translation_id' => $translation->id, 'number' => 1, 'reference' => 'John 1:1', 'text' => 'Text']);

        $first = $this->actingAs($user)->postJson(route('verse-highlights.toggle'), [
            'verse_id' => $verse->id,
            'color' => 'blue',
        ]);
        $first->assertJson(['color' => 'blue']);

        // Clicking the same color again toggles it off, same as before this change.
        $second = $this->actingAs($user)->postJson(route('verse-highlights.toggle'), [
            'verse_id' => $verse->id,
            'color' => 'blue',
        ]);
        $second->assertJson(['color' => null]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=VerseHighlightOfflineTest`
Expected: FAIL — `chapter_id`/`verse_number`/`explicit` aren't supported yet.

- [ ] **Step 3: Implement the changes**

Replace `app/Http/Controllers/VerseHighlightController.php` with:

```php
<?php

namespace App\Http\Controllers;

use App\Models\UserVersePreference;
use App\Models\Verse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerseHighlightController extends Controller
{
    public function toggle(Request $request)
    {
        $explicit = $request->boolean('explicit');

        $request->validate([
            'verse_id'         => 'required_without_all:chapter_id,verse_number|exists:verses,id',
            'chapter_id'       => 'required_without:verse_id|exists:chapters,id',
            'verse_number'     => 'required_without:verse_id|integer',
            'color'            => $explicit ? 'nullable|in:yellow,blue,green,red' : 'required|in:yellow,blue,green,red',
            'end_verse_number' => 'nullable|integer',
        ]);

        if ($request->filled('verse_id')) {
            $verse = Verse::find($request->verse_id);
            $chapterId = $verse->chapter_id;
            $verseNumber = $verse->number;
        } else {
            $chapterId = (int) $request->chapter_id;
            $verseNumber = (int) $request->verse_number;
        }

        $endVerseNumber = $request->end_verse_number ? (int) $request->end_verse_number : null;
        if (! $endVerseNumber || $endVerseNumber < $verseNumber) {
            $endVerseNumber = $verseNumber;
        }

        if ($explicit) {
            // Offline sync path: the client already computed the desired end state locally,
            // so apply it as-is rather than toggling against whatever the server currently has.
            $newColor = $request->input('color') ?: null;
        } else {
            $pref = UserVersePreference::where('user_id', Auth::id())
                ->where('chapter_id', $chapterId)
                ->where('verse_number', $verseNumber)
                ->first();

            // Same color as the range's starting verse → remove highlight from the whole range (toggle off)
            $newColor = ($pref && $pref->highlight_color === $request->color) ? null : $request->color;
        }

        for ($number = $verseNumber; $number <= $endVerseNumber; $number++) {
            UserVersePreference::updateOrCreate(
                ['user_id' => Auth::id(), 'chapter_id' => $chapterId, 'verse_number' => $number],
                ['highlight_color' => $newColor]
            );
        }

        return response()->json(['color' => $newColor]);
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=VerseHighlightOfflineTest`
Expected: PASS (5 tests)

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/VerseHighlightController.php tests/Feature/VerseHighlightOfflineTest.php
git commit -m "Support chapter_id/verse_number and explicit color on verse-highlight toggle"
```

---

### Task 8: Offline bundle endpoint

**Files:**
- Create: `app/Http/Controllers/OfflineBundleController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/OfflineBundleControllerTest.php`

**Interfaces:**
- Produces: `GET /api/offline/bundle` (route name `offline.bundle`), JSON shape `{translations, verses, chapters, verseComments, chapterComments, highlights, prayers}`, scoped entirely to the authenticated user for the personal-content keys.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\ChapterComment;
use App\Models\Prayer;
use App\Models\PrayerType;
use App\Models\Translation;
use App\Models\User;
use App\Models\UserVersePreference;
use App\Models\Verse;
use App\Models\VerseComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineBundleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_bundle_includes_all_translations_and_verse_text(): void
    {
        $user = User::factory()->create();
        $book = Book::create(['name' => 'John', 'abbr' => 'JHN', 'new_testament' => 1, 'sort_order' => 43]);
        $chapter = Chapter::create(['book_id' => $book->id, 'number' => 1]);
        $translation = Translation::create(['name' => 'KJV']);
        Verse::create(['chapter_id' => $chapter->id, 'translation_id' => $translation->id, 'number' => 1, 'reference' => 'John 1:1', 'text' => 'In the beginning.']);

        $response = $this->actingAs($user)->getJson(route('offline.bundle'));

        $response->assertOk();
        $response->assertJsonCount(1, 'translations');
        $response->assertJsonCount(1, 'verses');
        $response->assertJsonFragment(['text' => 'In the beginning.']);
    }

    public function test_bundle_only_includes_the_authenticated_users_own_content(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::create(['name' => 'John', 'abbr' => 'JHN', 'new_testament' => 1, 'sort_order' => 43]);
        $chapter = Chapter::create(['book_id' => $book->id, 'number' => 1]);

        $this->actingAs($me)->postJson(route('commentary.store'), [
            'chapter_id' => $chapter->id, 'type' => 'chapter', 'comment' => 'Mine',
        ]);
        $this->actingAs($other)->postJson(route('commentary.store'), [
            'chapter_id' => $chapter->id, 'type' => 'chapter', 'comment' => 'Not mine',
        ]);
        Prayer::create(['user_id' => $other->id, 'date' => '2026-10-01', 'content' => 'Not mine', 'prayer_type_id' => PrayerType::create(['name' => 'X'])->id]);
        UserVersePreference::create(['user_id' => $other->id, 'chapter_id' => $chapter->id, 'verse_number' => 1, 'highlight_color' => 'red']);

        $response = $this->actingAs($me)->getJson(route('offline.bundle'));

        $response->assertOk();
        $response->assertJsonCount(1, 'chapterComments');
        $response->assertJsonFragment(['comment' => 'Mine']);
        $response->assertJsonMissing(['comment' => 'Not mine']);
        $response->assertJsonCount(0, 'prayers');
        $response->assertJsonCount(0, 'highlights');
    }

    public function test_bundle_requires_authentication(): void
    {
        $response = $this->getJson(route('offline.bundle'));

        $response->assertStatus(401);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=OfflineBundleControllerTest`
Expected: FAIL — route/controller don't exist.

- [ ] **Step 3: Add the route**

In `routes/web.php`, inside the `auth` middleware group:

```php
Route::get('/api/offline/bundle', [OfflineBundleController::class, 'show'])->name('offline.bundle');
```

Add `use App\Http\Controllers\OfflineBundleController;` to the top of the file with the other controller imports.

- [ ] **Step 4: Create the controller**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\ChapterComment;
use App\Models\Prayer;
use App\Models\Translation;
use App\Models\UserVersePreference;
use App\Models\Verse;
use App\Models\VerseComment;
use Illuminate\Support\Facades\Auth;

class OfflineBundleController extends Controller
{
    public function show()
    {
        $userId = Auth::id();

        return response()->json([
            'translations' => Translation::all(['id', 'name']),
            'verses' => Verse::select('id', 'chapter_id', 'translation_id', 'number', 'text')->get(),
            'chapters' => Chapter::with('book:id,name,sort_order')->get(['id', 'book_id', 'number']),
            'verseComments' => VerseComment::withoutGlobalScopes()
                ->where('user_id', $userId)
                ->get(['id', 'client_uuid', 'chapter_id', 'verse_number', 'comment', 'created_at']),
            'chapterComments' => ChapterComment::withoutGlobalScopes()
                ->where('user_id', $userId)
                ->get(['id', 'client_uuid', 'chapter_id', 'comment', 'created_at']),
            'highlights' => UserVersePreference::where('user_id', $userId)
                ->whereNotNull('highlight_color')
                ->get(['chapter_id', 'verse_number', 'highlight_color']),
            'prayers' => Prayer::withoutGlobalScopes()
                ->where('user_id', $userId)
                ->get(['id', 'client_uuid', 'date', 'content', 'prayer_type_id']),
            'prayerTypes' => \App\Models\PrayerType::all(['id', 'name']),
        ]);
    }
}
```

(`prayerTypes` aren't user-specific, but the Offline Reader's "Add Prayer" form needs the list to populate its type dropdown, so it rides along in the same bundle.)

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=OfflineBundleControllerTest`
Expected: PASS (3 tests)

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/OfflineBundleController.php routes/web.php tests/Feature/OfflineBundleControllerTest.php
git commit -m "Add offline bundle endpoint for Bible text and own personal content"
```

---

### Task 9: PWA manifest + icons

**Files:**
- Create: `scripts/generate-pwa-icons.php`
- Create: `public/images/icon-192.png`, `public/images/icon-512.png` (generated, then committed)
- Create: `public/manifest.json`
- Modify: `resources/views/base/layout.blade.php:1-12`
- Test: `tests/Feature/ManifestTest.php`

**Interfaces:**
- Produces: `public/manifest.json` served at `/manifest.json`; `<link rel="manifest">` in the shared layout.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ManifestTest extends TestCase
{
    public function test_manifest_is_served_as_valid_json_with_required_fields(): void
    {
        $response = $this->get('/manifest.json');

        $response->assertOk();
        $manifest = json_decode($response->getContent(), true);

        $this->assertSame('Sword', $manifest['name']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertCount(2, $manifest['icons']);
        $this->assertSame('192x192', $manifest['icons'][0]['sizes']);
        $this->assertSame('512x512', $manifest['icons'][1]['sizes']);
    }

    public function test_login_page_links_the_manifest(): void
    {
        $response = $this->get(route('login'));

        $response->assertSee('<link rel="manifest" href="/manifest.json">', false);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ManifestTest`
Expected: FAIL — `/manifest.json` doesn't exist, layout doesn't link it.

- [ ] **Step 3: Write the icon generation script**

```php
<?php
// One-off script: generates public/images/icon-192.png and icon-512.png from the
// existing (non-square) logo.png by padding it onto a square navy canvas, rather
// than stretching it. Run once with: php scripts/generate-pwa-icons.php
$sizes = [192, 512];
$source = __DIR__ . '/../public/images/logo.png';
$bg = [0x0e, 0x16, 0x28]; // --sword-navy from resources/css/sword.css

$src = imagecreatefrompng($source);
imagesavealpha($src, true);
$srcW = imagesx($src);
$srcH = imagesy($src);

foreach ($sizes as $size) {
    $canvas = imagecreatetruecolor($size, $size);
    $bgColor = imagecolorallocate($canvas, $bg[0], $bg[1], $bg[2]);
    imagefill($canvas, 0, 0, $bgColor);

    $padding = 0.15; // 15% breathing room on each side
    $maxDim = $size * (1 - $padding * 2);
    $scale = min($maxDim / $srcW, $maxDim / $srcH);
    $destW = (int) round($srcW * $scale);
    $destH = (int) round($srcH * $scale);
    $destX = (int) round(($size - $destW) / 2);
    $destY = (int) round(($size - $destH) / 2);

    imagecopyresampled($canvas, $src, $destX, $destY, 0, 0, $destW, $destH, $srcW, $srcH);
    imagepng($canvas, __DIR__ . "/../public/images/icon-{$size}.png");
    imagedestroy($canvas);
}

imagedestroy($src);
echo "Generated icon-192.png and icon-512.png\n";
```

Run it: `php scripts/generate-pwa-icons.php` — this produces the two PNG files, which get committed alongside the script (the script itself is kept for future regeneration if the logo changes, not run as part of the build).

- [ ] **Step 4: Write the manifest**

```json
{
    "name": "Sword",
    "short_name": "Sword",
    "description": "Read Scripture, track prayers, and keep commentary — online or off.",
    "start_url": "/",
    "display": "standalone",
    "background_color": "#0e1628",
    "theme_color": "#0e1628",
    "icons": [
        { "src": "/images/icon-192.png", "sizes": "192x192", "type": "image/png" },
        { "src": "/images/icon-512.png", "sizes": "512x512", "type": "image/png" }
    ]
}
```

Save as `public/manifest.json`.

- [ ] **Step 5: Link it from the layout**

In `resources/views/base/layout.blade.php`, after the `<meta name="csrf-token">` line (around line 7) and before the `<title>` line, add:

```blade
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#0e1628">
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=ManifestTest`
Expected: PASS (2 tests)

- [ ] **Step 7: Commit**

```bash
git add scripts/generate-pwa-icons.php public/images/icon-192.png public/images/icon-512.png \
        public/manifest.json resources/views/base/layout.blade.php tests/Feature/ManifestTest.php
git commit -m "Add PWA manifest and icons"
```

---

### Task 10: Service worker — app shell cache + offline navigation fallback

**Files:**
- Create: `public/sw.js`
- Create: `resources/views/offline-reader/index.blade.php` (minimal placeholder shell for now — filled in in Task 13)
- Modify: `app/Http/Controllers/OfflineReaderController.php` (new, minimal)
- Modify: `routes/web.php`
- Modify: `resources/js/app.js`
- Test: `tests/Feature/ServiceWorkerTest.php`

**Interfaces:**
- Consumes: the `offline-reader.index` route (created here, fleshed out in Task 13).
- Produces: `/sw.js` served with the right content-type; registered from `app.js` on page load.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ServiceWorkerTest extends TestCase
{
    public function test_service_worker_file_is_served_as_javascript(): void
    {
        $response = $this->get('/sw.js');

        $response->assertOk();
        $this->assertStringContainsString('javascript', $response->headers->get('Content-Type'));
    }

    public function test_offline_reader_route_exists_for_the_service_worker_to_precache(): void
    {
        $response = $this->get(route('offline-reader.index'));

        // Guests get redirected to login (it's behind auth), not a 404 — confirms the route is registered.
        $response->assertRedirect(route('login'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ServiceWorkerTest`
Expected: FAIL — neither route nor file exists yet.

- [ ] **Step 3: Add the Offline Reader route (minimal for now)**

In `routes/web.php`, inside the `auth` middleware group:

```php
Route::get('/offline-reader', [OfflineReaderController::class, 'index'])->name('offline-reader.index');
```

Add `use App\Http\Controllers\OfflineReaderController;` with the other imports.

```php
<?php

namespace App\Http\Controllers;

class OfflineReaderController extends Controller
{
    public function index()
    {
        return view('offline-reader.index');
    }
}
```

Minimal placeholder view (fleshed out fully in Task 13):

```blade
@extends('base.layout')

@section('title', 'Offline Reader')

@section('content')
<div id="offline-reader-root">
    <p>Loading…</p>
</div>
@endsection
```

- [ ] **Step 4: Write the service worker**

```javascript
// public/sw.js — static app-shell cache + offline navigation fallback only.
// Deliberately does NOT intercept or cache any /api/* or data requests — Bible text and
// personal content caching is handled explicitly via IndexedDB (see resources/js/offline/),
// not opportunistic HTTP caching, to avoid ever serving stale API data silently.
const SHELL_CACHE = 'sword-shell-v1';
const SHELL_URLS = [
    '/',
    '/offline-reader',
    '/manifest.json',
    '/images/icon-192.png',
    '/images/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(SHELL_CACHE).then((cache) => cache.addAll(SHELL_URLS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== SHELL_CACHE).map((key) => caches.delete(key)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.mode !== 'navigate') {
        return; // Only handle page navigations — API/data requests pass through untouched.
    }

    event.respondWith(
        fetch(event.request).catch(() => caches.match('/offline-reader'))
    );
});
```

- [ ] **Step 5: Register the service worker**

In `resources/js/app.js`, add near the top (after the existing imports, before the DOM-ready logic):

```javascript
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js');
    });
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=ServiceWorkerTest`
Expected: PASS (2 tests)

- [ ] **Step 7: Manual verification**

Run `npm run build` (or `npm run dev`), open the app in Chrome, open DevTools → Application → Service Workers, confirm `sw.js` is registered and activated, and confirm Chrome's install icon appears in the address bar.

- [ ] **Step 8: Commit**

```bash
git add public/sw.js app/Http/Controllers/OfflineReaderController.php \
        resources/views/offline-reader/index.blade.php routes/web.php \
        resources/js/app.js tests/Feature/ServiceWorkerTest.php
git commit -m "Add service worker for app-shell caching and offline navigation fallback"
```

---

### Task 11: IndexedDB wrapper + bundle sync

**Files:**
- Create: `resources/js/offline/db.js`
- Create: `resources/js/offline/bundle-sync.js`
- Modify: `resources/js/app.js`
- Modify: `vite.config.js:7-11` (add `resources/js/offline/bundle-sync.js` isn't a separate entry — it's imported by `app.js`, so no vite config change is actually needed; skip this file)

**Interfaces:**
- Produces: `window.swordOffline.db` — `{ open(), putAll(storeName, records), getAll(storeName), deleteRecord(storeName, key), clearStore(storeName), clearAll() }`. `window.swordOffline.bundleSync` — `{ sync() }`, fetches `/api/offline/bundle` and repopulates IndexedDB.

No automated test for this task — there is no JS test runner in this repo (confirmed: `package.json` has no test script or framework), and the spec's own Testing Plan designates IndexedDB behavior as manually verified via DevTools. Each step below is a manual verification using the Chrome DevTools console instead of an automated assertion.

- [ ] **Step 1: Write the IndexedDB wrapper**

```javascript
// resources/js/offline/db.js
const DB_NAME = 'sword-offline';
const DB_VERSION = 1;
const STORES = ['translations', 'chapters', 'verses', 'verseComments', 'chapterComments', 'highlights', 'prayers', 'prayerTypes', 'outbox'];

function open() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onupgradeneeded = () => {
            const db = request.result;
            if (!db.objectStoreNames.contains('verses')) {
                db.createObjectStore('verses', { keyPath: ['translation_id', 'chapter_id', 'number'] });
            }
            if (!db.objectStoreNames.contains('chapters')) {
                db.createObjectStore('chapters', { keyPath: 'id' });
            }
            if (!db.objectStoreNames.contains('translations')) {
                db.createObjectStore('translations', { keyPath: 'id' });
            }
            if (!db.objectStoreNames.contains('verseComments')) {
                // Keyed by the comment's own id (not chapter_id+verse_number) — a single verse
                // can have several distinct comments, and a compound key would silently drop
                // all but the last one on every bundle sync.
                db.createObjectStore('verseComments', { keyPath: 'id' });
            }
            if (!db.objectStoreNames.contains('chapterComments')) {
                // Same reasoning as verseComments above — a chapter can have several comments.
                db.createObjectStore('chapterComments', { keyPath: 'id' });
            }
            if (!db.objectStoreNames.contains('highlights')) {
                db.createObjectStore('highlights', { keyPath: ['chapter_id', 'verse_number'] });
            }
            if (!db.objectStoreNames.contains('prayers')) {
                db.createObjectStore('prayers', { keyPath: 'id', autoIncrement: true });
            }
            if (!db.objectStoreNames.contains('prayerTypes')) {
                db.createObjectStore('prayerTypes', { keyPath: 'id' });
            }
            if (!db.objectStoreNames.contains('outbox')) {
                db.createObjectStore('outbox', { keyPath: 'uuid' });
            }
        };

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

async function clearStore(storeName) {
    const db = await open();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(storeName, 'readwrite');
        tx.objectStore(storeName).clear();
        tx.oncomplete = () => resolve();
        tx.onerror = () => reject(tx.error);
    });
}

async function putAll(storeName, records) {
    const db = await open();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(storeName, 'readwrite');
        const store = tx.objectStore(storeName);
        records.forEach((record) => store.put(record));
        tx.oncomplete = () => resolve();
        tx.onerror = () => reject(tx.error);
    });
}

async function getAll(storeName) {
    const db = await open();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(storeName, 'readonly');
        const request = tx.objectStore(storeName).getAll();
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

async function deleteRecord(storeName, key) {
    const db = await open();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(storeName, 'readwrite');
        tx.objectStore(storeName).delete(key);
        tx.oncomplete = () => resolve();
        tx.onerror = () => reject(tx.error);
    });
}

async function clearAll() {
    for (const storeName of STORES) {
        await clearStore(storeName);
    }
}

export default { open, putAll, getAll, deleteRecord, clearStore, clearAll, STORES };
```

- [ ] **Step 2: Manual verification of the wrapper**

With `npm run dev` running and the app open in Chrome, in the DevTools console:

```javascript
await window.swordOffline.db.putAll('translations', [{ id: 1, name: 'KJV' }]);
await window.swordOffline.db.getAll('translations');
// Expect: [{ id: 1, name: 'KJV' }]
```

Confirm the `sword-offline` database and its object stores appear under DevTools → Application → IndexedDB.

- [ ] **Step 3: Write bundle-sync.js**

```javascript
// resources/js/offline/bundle-sync.js
import db from './db.js';

async function sync() {
    const response = await fetch('/api/offline/bundle', {
        headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
        throw new Error(`Bundle sync failed: ${response.status}`);
    }

    const bundle = await response.json();

    await db.clearStore('translations');
    await db.clearStore('chapters');
    await db.clearStore('verses');
    await db.clearStore('verseComments');
    await db.clearStore('chapterComments');
    await db.clearStore('highlights');
    await db.clearStore('prayers');
    await db.clearStore('prayerTypes');

    await db.putAll('translations', bundle.translations);
    await db.putAll('chapters', bundle.chapters);
    await db.putAll('verses', bundle.verses);
    await db.putAll('verseComments', bundle.verseComments);
    await db.putAll('chapterComments', bundle.chapterComments);
    await db.putAll('highlights', bundle.highlights);
    await db.putAll('prayers', bundle.prayers);
    await db.putAll('prayerTypes', bundle.prayerTypes);
}

export default { sync };
```

- [ ] **Step 4: Wire both modules into app.js**

In `resources/js/app.js`, add near the top:

```javascript
import offlineDb from './offline/db.js';
import bundleSync from './offline/bundle-sync.js';

window.swordOffline = window.swordOffline || {};
window.swordOffline.db = offlineDb;
window.swordOffline.bundleSync = bundleSync;

// If Offline Mode is already on, keep the bundle fresh on every load while online.
if (document.body.dataset.offlineEnabled === 'true' && navigator.onLine) {
    bundleSync.sync().catch((err) => console.error('Offline bundle sync failed', err));
}
```

Also add `data-offline-enabled="{{ auth()->check() && auth()->user()->offline_enabled ? 'true' : 'false' }}"` to the `<body>` tag in `resources/views/base/layout.blade.php`.

- [ ] **Step 5: Manual verification of bundle sync**

Log in as a user with Offline Mode on (toggle it from the profile page built in Task 2), reload the page, and in DevTools console run:

```javascript
await window.swordOffline.db.getAll('verses');
```

Expect a non-empty array of verse records. Confirm it matches the count from `php artisan tinker` → `App\Models\Verse::count()`.

- [ ] **Step 6: Commit**

```bash
git add resources/js/offline/db.js resources/js/offline/bundle-sync.js \
        resources/js/app.js resources/views/base/layout.blade.php
git commit -m "Add IndexedDB wrapper and offline bundle sync"
```

---

### Task 12: Outbox / sync manager

**Files:**
- Create: `resources/js/offline/sync-manager.js`
- Modify: `resources/js/app.js`

**Interfaces:**
- Consumes: `window.swordOffline.db` from Task 11.
- Produces: `window.swordOffline.syncManager` — `{ queue(type, payload), drain() }`. `queue()` is the only function the Offline Reader (Tasks 13-14) calls to record an offline write.

No automated test (same reasoning as Task 11 — no JS test runner, and this logic is fundamentally about `online`/`offline` browser events DevTools throttling exists specifically to simulate).

- [ ] **Step 1: Write sync-manager.js**

```javascript
// resources/js/offline/sync-manager.js
import db from './db.js';

const ENDPOINTS = {
    verse_comment: { url: '/commentary', method: 'POST' },
    chapter_comment: { url: '/commentary', method: 'POST' },
    prayer: { url: '/prayers', method: 'POST' },
    highlight: { url: '/verse-highlights/toggle', method: 'POST' },
    accountability_checkin: { url: '/accountability', method: 'POST' },
    digest: { url: '/digest/complete', method: 'POST' },
};

function queue(type, payload) {
    const uuid = crypto.randomUUID();
    const record = { uuid, type, payload, created_at: Date.now() };
    return db.putAll('outbox', [record]).then(() => record);
}

async function sendOne(record) {
    const endpoint = ENDPOINTS[record.type];
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const body = { ...record.payload, client_uuid: record.uuid };

    const response = await fetch(endpoint.url, {
        method: endpoint.method,
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(body),
    });

    return response;
}

async function drain() {
    const items = (await db.getAll('outbox')).sort((a, b) => a.created_at - b.created_at);
    const failed = [];

    for (const record of items) {
        let response;
        try {
            response = await sendOne(record);
        } catch (networkError) {
            // Network failure (not a server response at all) — stop draining; the rest
            // stay queued for the next online event. Retrying now would likely just fail again.
            break;
        }

        if (response.ok) {
            await db.deleteRecord('outbox', record.uuid);
        } else {
            // A real validation error (e.g. 422) — this specific item won't succeed by
            // itself retrying, so leave it queued and marked failed, but keep draining
            // the rest of the queue (unlike a network failure).
            failed.push(record.uuid);
        }
    }

    return { remaining: (await db.getAll('outbox')).length, failed: failed.length };
}

export default { queue, drain };
```

- [ ] **Step 2: Wire into app.js**

In `resources/js/app.js`, add:

```javascript
import syncManager from './offline/sync-manager.js';

window.swordOffline.syncManager = syncManager;

window.addEventListener('online', () => {
    syncManager.drain().catch((err) => console.error('Outbox drain failed', err));
});

if (navigator.onLine) {
    syncManager.drain().catch((err) => console.error('Outbox drain failed', err));
}
```

- [ ] **Step 3: Manual verification — successful drain**

In DevTools, go offline (Network tab → Offline), run:

```javascript
await window.swordOffline.syncManager.queue('verse_comment', { verse_id: 1, comment: 'Queued offline' });
await window.swordOffline.db.getAll('outbox');
// Expect: one record with type "verse_comment"
```

Go back online (Network tab → No throttling), wait a moment, then:

```javascript
await window.swordOffline.db.getAll('outbox');
// Expect: []
```

Confirm in `php artisan tinker` that `App\Models\VerseComment::where('comment', 'Queued offline')->count()` is `1`.

- [ ] **Step 4: Manual verification — validation failure doesn't block the queue**

Queue two items while offline: one with a deliberately invalid payload (e.g. a `verse_comment` missing `comment`) and one valid. Go online. Confirm via `getAll('outbox')` that only the invalid one remains, and via `tinker` that the valid one was created.

- [ ] **Step 5: Commit**

```bash
git add resources/js/offline/sync-manager.js resources/js/app.js
git commit -m "Add offline outbox sync manager"
```

---

### Task 13: Offline Reader — read-only navigation

**Files:**
- Modify: `resources/views/offline-reader/index.blade.php`
- Create: `resources/js/offline/offline-reader.js`
- Modify: `vite.config.js:7-11` (add `resources/js/offline/offline-reader.js` as a Vite input)

**Interfaces:**
- Consumes: `window.swordOffline.db` (Task 11).
- Produces: the Offline Reader page renders translation/book/chapter pickers and a verse list entirely from IndexedDB, with an explicit "enable Offline Mode first" empty state when no bundle has synced (Review Focus item).

- [ ] **Step 1: Add the Vite entry**

In `vite.config.js`, add `'resources/js/offline/offline-reader.js'` to the `input` array (alongside `app.js`).

- [ ] **Step 2: Build the view**

Replace `resources/views/offline-reader/index.blade.php` with:

```blade
@extends('base.layout')

@section('title', 'Offline Reader')

@push('css')
@vite(['resources/js/offline/offline-reader.js'])
@endpush

@section('content')
<div id="offline-reader-root">
    <div id="offline-reader-empty-state" class="d-none text-center py-5">
        <p class="mb-3">Offline Mode isn't set up on this device yet.</p>
        <a href="{{ route('profile.index') }}" class="btn btn-primary btn-sm">Go to Profile Settings</a>
    </div>

    <div id="offline-reader-content" class="d-none">
        <div class="row mb-3">
            <div class="col-4">
                <select id="or-translation" class="form-select"></select>
            </div>
            <div class="col-4">
                <select id="or-book" class="form-select"></select>
            </div>
            <div class="col-4">
                <select id="or-chapter" class="form-select"></select>
            </div>
        </div>
        <div id="or-verse-list"></div>

        <div class="card mt-3">
            <div class="card-header">Chapter Notes</div>
            <div class="card-body">
                <div id="or-chapter-comments-list" class="mb-3"></div>
                <textarea id="or-chapter-comment-input" class="form-control mb-2" placeholder="Add a note on this chapter"></textarea>
                <button type="button" id="or-save-chapter-comment" class="btn btn-primary btn-sm">Save Chapter Note</button>
            </div>
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 3: Write offline-reader.js**

```javascript
// resources/js/offline/offline-reader.js — entry point for the Offline Reader page only.
import db from './db.js';

async function init() {
    const [translations, chapters, verses, highlights, verseComments] = await Promise.all([
        db.getAll('translations'),
        db.getAll('chapters'),
        db.getAll('verses'),
        db.getAll('highlights'),
        db.getAll('verseComments'),
    ]);

    if (translations.length === 0 || verses.length === 0) {
        document.getElementById('offline-reader-empty-state').classList.remove('d-none');
        return;
    }

    document.getElementById('offline-reader-content').classList.remove('d-none');

    const highlightByKey = new Map(highlights.map((h) => [`${h.chapter_id}:${h.verse_number}`, h.highlight_color]));

    // A verse can have several distinct comments, so group them into arrays keyed by
    // chapter+verse rather than keeping only the last one.
    const commentsByVerseKey = new Map();
    verseComments.forEach((c) => {
        const key = `${c.chapter_id}:${c.verse_number}`;
        if (!commentsByVerseKey.has(key)) commentsByVerseKey.set(key, []);
        commentsByVerseKey.get(key).push(c);
    });

    const chapterCommentsById = new Map(); // chapter_id -> array of comment records, same grouping reasoning
    const allChapterComments = await db.getAll('chapterComments');
    allChapterComments.forEach((c) => {
        if (!chapterCommentsById.has(c.chapter_id)) chapterCommentsById.set(c.chapter_id, []);
        chapterCommentsById.get(c.chapter_id).push(c);
    });

    const translationSelect = document.getElementById('or-translation');
    translations.forEach((t) => {
        const option = document.createElement('option');
        option.value = t.id;
        option.textContent = t.name;
        translationSelect.appendChild(option);
    });

    const bookSelect = document.getElementById('or-book');
    const booksById = new Map();
    chapters.forEach((c) => {
        if (!booksById.has(c.book.id)) {
            booksById.set(c.book.id, c.book);
            const option = document.createElement('option');
            option.value = c.book.id;
            option.textContent = c.book.name;
            bookSelect.appendChild(option);
        }
    });

    const chapterSelect = document.getElementById('or-chapter');

    function renderChapterOptions(bookId) {
        chapterSelect.innerHTML = '';
        chapters
            .filter((c) => c.book.id === Number(bookId))
            .sort((a, b) => a.number - b.number)
            .forEach((c) => {
                const option = document.createElement('option');
                option.value = c.id;
                option.textContent = `Chapter ${c.number}`;
                chapterSelect.appendChild(option);
            });
    }

    function renderVerses() {
        const translationId = Number(translationSelect.value);
        const chapterId = Number(chapterSelect.value);
        const list = document.getElementById('or-verse-list');
        list.innerHTML = '';

        verses
            .filter((v) => v.translation_id === translationId && v.chapter_id === chapterId)
            .sort((a, b) => a.number - b.number)
            .forEach((v) => {
                const key = `${v.chapter_id}:${v.number}`;
                const color = highlightByKey.get(key);
                const comments = commentsByVerseKey.get(key) || [];

                const row = document.createElement('div');
                row.className = 'or-verse mb-2 ps-2';
                row.style.borderLeft = color ? `4px solid ${color}` : '4px solid transparent';
                row.dataset.chapterId = v.chapter_id;
                row.dataset.verseNumber = v.number;
                row.dataset.verseId = v.id; // the currently-selected translation's verse id — see Task 14
                row.innerHTML = `<strong>${v.number}</strong> ${v.text}` +
                    comments.map((c) => `<div class="text-muted small">${c.comment}</div>`).join('');
                list.appendChild(row);
            });

        renderChapterComments(chapterId);
    }

    function renderChapterComments(chapterId) {
        const list = document.getElementById('or-chapter-comments-list');
        if (!list) return;
        list.innerHTML = (chapterCommentsById.get(chapterId) || [])
            .map((c) => `<div class="small text-muted mb-1">${c.comment}</div>`)
            .join('') || '<div class="small text-muted">No chapter notes yet.</div>';
    }

    bookSelect.addEventListener('change', () => {
        renderChapterOptions(bookSelect.value);
        renderVerses();
    });
    chapterSelect.addEventListener('change', renderVerses);
    translationSelect.addEventListener('change', renderVerses);

    renderChapterOptions(bookSelect.value);
    renderVerses();
}

document.addEventListener('DOMContentLoaded', init);
```

- [ ] **Step 4: Manual verification**

With a user that has Offline Mode on and a synced bundle, visit `/offline-reader` with DevTools Network set to Offline. Confirm translation/book/chapter pickers populate and switching chapters shows the correct verse text, with any existing highlight shown as a colored left border and any existing comment shown beneath its verse.

Then, as a user who has never enabled Offline Mode, visit `/offline-reader` (online is fine) and confirm the "enable Offline Mode first" empty state shows instead of a blank page.

- [ ] **Step 5: Commit**

```bash
git add resources/views/offline-reader/index.blade.php resources/js/offline/offline-reader.js vite.config.js
git commit -m "Add Offline Reader read-only navigation"
```

---

### Task 14: Offline Reader — queued writes (comments, highlights, prayers, digest)

**Files:**
- Modify: `resources/views/offline-reader/index.blade.php`
- Modify: `resources/js/offline/offline-reader.js`

**Interfaces:**
- Consumes: `window.swordOffline.syncManager.queue()` (Task 12), the verse-row rendering from Task 13.

- [ ] **Step 1: Add the interaction UI to the view**

In `resources/views/offline-reader/index.blade.php`, add after the `or-verse-list` div:

```blade
<div class="modal fade" id="or-verse-panel" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h6 id="or-panel-reference"></h6>
                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-sm or-color-btn" data-color="yellow" style="background:#f1c40f;">&nbsp;</button>
                    <button type="button" class="btn btn-sm or-color-btn" data-color="blue" style="background:#3498db;">&nbsp;</button>
                    <button type="button" class="btn btn-sm or-color-btn" data-color="green" style="background:#2ecc71;">&nbsp;</button>
                    <button type="button" class="btn btn-sm or-color-btn" data-color="red" style="background:#e74c3c;">&nbsp;</button>
                </div>
                <textarea id="or-comment-input" class="form-control mb-2" placeholder="Add a comment"></textarea>
                <button type="button" id="or-save-comment" class="btn btn-primary btn-sm">Save Comment</button>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">Add Prayer</div>
    <div class="card-body">
        <select id="or-prayer-type" class="form-select mb-2"></select>
        <textarea id="or-prayer-content" class="form-control mb-2" placeholder="Prayer content"></textarea>
        <button type="button" id="or-save-prayer" class="btn btn-primary btn-sm">Save Prayer</button>
    </div>
</div>

<div id="or-sync-status" class="small text-muted mt-3"></div>
```

- [ ] **Step 2: Wire highlight + comment interactions**

In `resources/js/offline/offline-reader.js`, add (inside `init()`, after `renderVerses()` is defined):

```javascript
import syncManager from './sync-manager.js';

let activeVerseKey = null;

function currentHighlightColor(chapterId, verseNumber) {
    return highlightByKey.get(`${chapterId}:${verseNumber}`) || null;
}

document.getElementById('or-verse-list').addEventListener('click', (event) => {
    const row = event.target.closest('.or-verse');
    if (!row) return;

    activeVerseKey = {
        chapterId: Number(row.dataset.chapterId),
        verseNumber: Number(row.dataset.verseNumber),
        verseId: Number(row.dataset.verseId),
    };
    document.getElementById('or-panel-reference').textContent = `Verse ${activeVerseKey.verseNumber}`;
    document.getElementById('or-comment-input').value = '';
    new window.bootstrap.Modal(document.getElementById('or-verse-panel')).show();
});

document.querySelectorAll('.or-color-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
        if (!activeVerseKey) return;
        const clickedColor = btn.dataset.color;
        const key = `${activeVerseKey.chapterId}:${activeVerseKey.verseNumber}`;
        const current = currentHighlightColor(activeVerseKey.chapterId, activeVerseKey.verseNumber);
        const newColor = current === clickedColor ? null : clickedColor;

        highlightByKey.set(key, newColor);
        await db.putAll('highlights', [{ chapter_id: activeVerseKey.chapterId, verse_number: activeVerseKey.verseNumber, highlight_color: newColor }]);
        await syncManager.queue('highlight', {
            chapter_id: activeVerseKey.chapterId,
            verse_number: activeVerseKey.verseNumber,
            color: newColor,
            explicit: true,
        });
        renderVerses();
    });
});

document.getElementById('or-save-comment').addEventListener('click', async () => {
    if (!activeVerseKey) return;
    const comment = document.getElementById('or-comment-input').value.trim();
    if (!comment) return;

    const key = `${activeVerseKey.chapterId}:${activeVerseKey.verseNumber}`;
    const queued = await syncManager.queue('verse_comment', {
        verse_id: activeVerseKey.verseId,
        comment,
    });

    // Add to the in-memory + IndexedDB comment list immediately (optimistic), using the
    // outbox item's uuid as a local id since the server hasn't assigned a real one yet.
    const localRecord = { id: `local-${queued.uuid}`, chapter_id: activeVerseKey.chapterId, verse_number: activeVerseKey.verseNumber, comment };
    if (!commentsByVerseKey.has(key)) commentsByVerseKey.set(key, []);
    commentsByVerseKey.get(key).push(localRecord);
    await db.putAll('verseComments', [localRecord]);

    renderVerses();
    window.bootstrap.Modal.getInstance(document.getElementById('or-verse-panel')).hide();
});

document.getElementById('or-save-chapter-comment').addEventListener('click', async () => {
    const chapterId = Number(chapterSelect.value);
    const comment = document.getElementById('or-chapter-comment-input').value.trim();
    if (!comment) return;

    const queued = await syncManager.queue('chapter_comment', {
        type: 'chapter',
        chapter_id: chapterId,
        comment,
    });

    const localRecord = { id: `local-${queued.uuid}`, chapter_id: chapterId, comment };
    if (!chapterCommentsById.has(chapterId)) chapterCommentsById.set(chapterId, []);
    chapterCommentsById.get(chapterId).push(localRecord);
    await db.putAll('chapterComments', [localRecord]);

    document.getElementById('or-chapter-comment-input').value = '';
    renderChapterComments(chapterId);
});
```

- [ ] **Step 3: Display past prayers and wire the add-prayer form**

`prayerTypes` and `prayers` are already in IndexedDB as of Task 11/Task 8 (the bundle includes both, and `db.js` already has stores for them). In `offline-reader.js`, add inside `init()` (after the existing `Promise.all` load — extend it to also pull these two):

```javascript
const [translations, chapters, verses, highlights, verseComments, prayers, prayerTypes] = await Promise.all([
    db.getAll('translations'),
    db.getAll('chapters'),
    db.getAll('verses'),
    db.getAll('highlights'),
    db.getAll('verseComments'),
    db.getAll('prayers'),
    db.getAll('prayerTypes'),
]);
```

(This replaces the `Promise.all` destructure from Task 13 Step 3, adding `prayers`/`prayerTypes` to the five fields already loaded there.)

Then, still inside `init()`:

```javascript
const prayerTypeById = new Map(prayerTypes.map((pt) => [pt.id, pt.name]));
const prayerTypeSelect = document.getElementById('or-prayer-type');
prayerTypes.forEach((pt) => {
    const option = document.createElement('option');
    option.value = pt.id;
    option.textContent = pt.name;
    prayerTypeSelect.appendChild(option);
});

function renderPrayers() {
    const list = document.getElementById('or-prayer-list');
    list.innerHTML = prayers
        .slice()
        .sort((a, b) => (a.date < b.date ? 1 : -1))
        .map((p) => `<div class="small mb-2"><strong>${p.date}</strong> — ${prayerTypeById.get(p.prayer_type_id) || 'Prayer'}: ${p.content}</div>`)
        .join('') || '<div class="small text-muted">No prayers yet.</div>';
}
renderPrayers();

document.getElementById('or-save-prayer').addEventListener('click', async () => {
    const content = document.getElementById('or-prayer-content').value.trim();
    if (!content) return;
    const typeId = Number(prayerTypeSelect.value);
    const date = new Date().toISOString().slice(0, 10);

    await syncManager.queue('prayer', { date, [`type${typeId}`]: content });

    prayers.push({ id: `local-${crypto.randomUUID()}`, date, content, prayer_type_id: typeId });
    document.getElementById('or-prayer-content').value = '';
    document.getElementById('or-sync-status').textContent = 'Prayer queued — will sync once you\'re back online.';
    renderPrayers();
});
```

Add the matching list container to the view's "Add Prayer" card (Step 1 above), right before the `<select id="or-prayer-type">`:

```blade
<div id="or-prayer-list" class="mb-3"></div>
```

- [ ] **Step 4: Manual verification**

With DevTools Network set to Offline on `/offline-reader`: click a verse, set a highlight color, save a comment, and add a prayer. Confirm `db.getAll('outbox')` shows three queued records. Go back online, wait briefly, confirm `db.getAll('outbox')` is empty and `php artisan tinker` shows the new `VerseComment`, `Prayer`, and the `UserVersePreference` highlight color all persisted.

- [ ] **Step 5: Commit**

```bash
git add resources/views/offline-reader/index.blade.php resources/js/offline/offline-reader.js
git commit -m "Add queued offline comments, highlights, and prayers to the Offline Reader"
```

---

### Task 15: Offline Reader — digest reflection form

**Files:**
- Modify: `resources/views/offline-reader/index.blade.php`
- Modify: `resources/js/offline/offline-reader.js`

**Interfaces:**
- Consumes: `window.swordOffline.syncManager.queue('digest', payload)` (Task 12), the `week_start`/`client_uuid` handling in `SharedDigestController::store` (Task 6).

The canonical "Fruits of the Spirit" and "idols" checkbox options below are copied verbatim from `resources/views/digest/share.blade.php:160,201` so the offline form produces the exact same `fruits_needing_prayer[]`/`idols[]` values the online form does — not an invented subset.

- [ ] **Step 1: Add the form to the view**

In `resources/views/offline-reader/index.blade.php`, add after the "Add Prayer" card:

```blade
<div class="card mt-4">
    <div class="card-header">Weekly Digest Reflection</div>
    <div class="card-body">
        <p class="small text-muted">
            Your summary of chapters read, prayers, and commentary for this week will be
            attached automatically once this syncs — it isn't available offline.
        </p>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="or-digest-show-chapters" checked>
            <label class="form-check-label" for="or-digest-show-chapters">Include chapters read</label>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="or-digest-show-prayers" checked>
            <label class="form-check-label" for="or-digest-show-prayers">Include prayers</label>
        </div>

        <label class="form-label small fw-semibold">Fruits of the Spirit needing prayer</label>
        <div class="d-flex flex-wrap gap-2 mb-2" id="or-digest-fruits">
            @foreach(['Love', 'Joy', 'Peace', 'Patience', 'Kindness', 'Goodness', 'Faithfulness', 'Self Control'] as $fruit)
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" value="{{ $fruit }}">
                <label class="form-check-label">{{ $fruit }}</label>
            </div>
            @endforeach
        </div>
        <textarea id="or-digest-fruits-description" class="form-control mb-3" placeholder="Fruits description"></textarea>

        <label class="form-label small fw-semibold">Idols</label>
        <div class="d-flex flex-wrap gap-2 mb-2" id="or-digest-idols">
            @foreach(['Laziness', 'Comfort', 'Food', 'Work', 'Money', 'Status', 'Entertainment', 'Relationships', 'Control', 'Approval'] as $idol)
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" value="{{ $idol }}">
                <label class="form-check-label">{{ $idol }}</label>
            </div>
            @endforeach
        </div>
        <input type="text" id="or-digest-idols-other" class="form-control mb-2" placeholder="Other idols (comma separated)">
        <textarea id="or-digest-idols-description" class="form-control mb-3" placeholder="Idols description"></textarea>

        <textarea id="or-digest-impactful-scripture" class="form-control mb-3" placeholder="Most impactful scripture this week"></textarea>
        <textarea id="or-digest-additional-content" class="form-control mb-3" placeholder="Additional reflections"></textarea>
        <textarea id="or-digest-sermon-notes" class="form-control mb-3" placeholder="Sermon notes"></textarea>

        <button type="button" id="or-save-digest-draft" class="btn btn-secondary btn-sm">Save as Draft</button>
        <button type="button" id="or-save-digest-share" class="btn btn-primary btn-sm">Save &amp; Share</button>
    </div>
</div>
```

- [ ] **Step 2: Capture the target week and wire the submit handlers**

In `resources/js/offline/offline-reader.js`, add:

```javascript
function currentWeekStartISO() {
    const now = new Date();
    const day = now.getDay(); // 0 (Sun) .. 6 (Sat)
    const diffToMonday = day === 0 ? -6 : 1 - day;
    const monday = new Date(now);
    monday.setDate(now.getDate() + diffToMonday);
    return monday.toISOString().slice(0, 10);
}

async function submitDigest(submitAction) {
    const fruits = Array.from(document.querySelectorAll('#or-digest-fruits input:checked')).map((el) => el.value);
    const idols = Array.from(document.querySelectorAll('#or-digest-idols input:checked')).map((el) => el.value);

    const payload = {
        week_start: currentWeekStartISO(),
        submit_action: submitAction,
        show_chapters: document.getElementById('or-digest-show-chapters').checked,
        show_prayers: document.getElementById('or-digest-show-prayers').checked,
        fruits_needing_prayer: fruits,
        fruits_description: document.getElementById('or-digest-fruits-description').value,
        idols: idols,
        idols_other: document.getElementById('or-digest-idols-other').value,
        idols_description: document.getElementById('or-digest-idols-description').value,
        impactful_scripture: document.getElementById('or-digest-impactful-scripture').value,
        additional_content: document.getElementById('or-digest-additional-content').value,
        sermon_notes: document.getElementById('or-digest-sermon-notes').value,
    };

    await syncManager.queue('digest', payload);
    document.getElementById('or-sync-status').textContent = 'Digest reflection queued — your weekly summary will be attached once this syncs.';
}

document.getElementById('or-save-digest-draft').addEventListener('click', () => submitDigest('save'));
document.getElementById('or-save-digest-share').addEventListener('click', () => submitDigest('share'));
```

- [ ] **Step 3: Manual verification**

With DevTools Network set to Offline on `/offline-reader`: check a couple of fruits/idols, fill in the text areas, click "Save as Draft." Confirm `db.getAll('outbox')` shows one `digest` record with a `week_start` matching this week's Monday. Go back online, wait briefly, confirm the outbox is empty and, via `php artisan tinker`, that `App\Models\SharedDigest::where('user_id', $testUserId)->latest()->first()` has the correct `week_start`, `fruits_needing_prayer`, and `idols_other` values — and that its `snapshot` column is populated (built server-side from live data at sync time, per Task 6).

- [ ] **Step 4: Commit**

```bash
git add resources/views/offline-reader/index.blade.php resources/js/offline/offline-reader.js
git commit -m "Add offline digest reflection form to the Offline Reader"
```
