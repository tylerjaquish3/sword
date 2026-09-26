# Proverbs Verse Groups Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let an admin user recategorize Proverbs verses into custom groups and read Proverbs group-by-group instead of chapter-by-chapter.

**Architecture:** Two new tables (`proverbs_groups`, `proverbs_verse_groups`) store per-user, translation-agnostic (`chapter_id` + `verse_number`) group assignments, one group per verse. A new `ProverbsGroupController`, gated entirely by the existing `admin` middleware, serves a manage screen (create/rename/delete groups, assign every Proverbs verse to a group via one-click pill buttons in a single form) and a read screen (a group's verses in canonical order, with prev/next navigation through all groups plus a trailing synthetic "Unassigned" bucket). A new admin-only tab on the existing Study Hub (`topics.index`) links into both screens.

**Tech Stack:** Laravel 10 (PHP 8.1+), Blade + Bootstrap 5 (`btn-check` toggle-button pattern for the pill radios), SQLite, PHPUnit feature/unit tests with `RefreshDatabase`.

**Spec:** `docs/superpowers/specs/2026-09-26-proverbs-verse-groups-design.md`

## Global Constraints

- Entire feature (manage screen, read screen, and the new Study Hub tab) is gated behind `auth()->user()->is_admin` / the `admin` route middleware — non-admins must never see or reach any of it.
- A verse belongs to at most one group at a time (unique DB constraint on `(user_id, chapter_id, verse_number)` in `proverbs_verse_groups`).
- Group membership is expressed via `chapter_id` + `verse_number`, never a translation-specific `verse_id` (matches `verse_links`/`verse_comments`).
- Verses always display in canonical chapter/verse order; groups always display in creation order with "Unassigned" as a fixed trailing entry — no manual reordering of either.
- All new models use `protected $guarded = []`, matching every other model in this codebase.
- All new user-scoped queries filter by `where('user_id', Auth::id())` in the controller (no global scopes), matching `TopicController`.

## Review Focus

- Reassigning a verse already in Group A to Group B must move it, not create a duplicate row or leave it in both groups.
- Deleting a group must cascade-delete its verse assignments so those verses read back as "Unassigned" with no orphaned rows or errors.
- A group ID belonging to another user must never be readable or writable by the current admin (403), whether requested directly via URL or smuggled into the bulk-assign payload.
- The "Unassigned" bucket must span all 31 Proverbs chapters in canonical order, not just verses from the first chapter or an arbitrary subset.
- Verse text on both the manage and read screens must reflect the current user's default translation (`default_translation_id`), not silently fall back to an unrelated translation.

---

## File Structure

- Create: `database/migrations/2026_09_26_000001_create_proverbs_groups_table.php`
- Create: `database/migrations/2026_09_26_000002_create_proverbs_verse_groups_table.php`
- Create: `app/Models/ProverbsGroup.php`
- Create: `app/Models/ProverbsVerseGroup.php`
- Create: `app/Http/Controllers/ProverbsGroupController.php` (built up across Tasks 2–5)
- Modify: `routes/web.php` (new admin-gated route group)
- Create: `resources/views/proverbs-groups/index.blade.php` (manage screen)
- Create: `resources/views/proverbs-groups/read.blade.php` (read screen)
- Modify: `resources/views/topics/index.blade.php` (new admin-only "Proverbs" tab)
- Modify: `app/Http/Controllers/TopicController.php` (`index()` gains Proverbs groups summary data)
- Create: `tests/Unit/ProverbsGroupModelTest.php`
- Create: `tests/Feature/ProverbsGroupCrudTest.php`
- Create: `tests/Feature/ProverbsGroupAssignTest.php`
- Create: `tests/Feature/ProverbsGroupManageScreenTest.php`
- Create: `tests/Feature/ProverbsGroupReadTest.php`
- Create: `tests/Feature/StudyHubProverbsTabTest.php`

---

### Task 1: Migrations and models

**Files:**
- Create: `database/migrations/2026_09_26_000001_create_proverbs_groups_table.php`
- Create: `database/migrations/2026_09_26_000002_create_proverbs_verse_groups_table.php`
- Create: `app/Models/ProverbsGroup.php`
- Create: `app/Models/ProverbsVerseGroup.php`
- Test: `tests/Unit/ProverbsGroupModelTest.php`

**Interfaces:**
- Produces: `App\Models\ProverbsGroup` (`id`, `user_id`, `name`, timestamps; `verseAssignments()` hasMany `ProverbsVerseGroup`), `App\Models\ProverbsVerseGroup` (`id`, `user_id`, `proverbs_group_id`, `chapter_id`, `verse_number`, timestamps; `group()` belongsTo `ProverbsGroup`, `chapter()` belongsTo `Chapter`). Every later task relies on these exact class/relation names.

- [ ] **Step 1: Write the failing test**

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ProverbsGroupModelTest`
Expected: FAIL — classes `App\Models\ProverbsGroup` / `ProverbsVerseGroup` don't exist yet.

- [ ] **Step 3: Create the migrations**

`database/migrations/2026_09_26_000001_create_proverbs_groups_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proverbs_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proverbs_groups');
    }
};
```

`database/migrations/2026_09_26_000002_create_proverbs_verse_groups_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proverbs_verse_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('proverbs_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('verse_number');
            $table->timestamps();
            $table->unique(['user_id', 'chapter_id', 'verse_number'], 'proverbs_verse_groups_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proverbs_verse_groups');
    }
};
```

- [ ] **Step 4: Create the models**

`app/Models/ProverbsGroup.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProverbsGroup extends Model
{
    protected $guarded = [];

    public function verseAssignments()
    {
        return $this->hasMany(ProverbsVerseGroup::class);
    }
}
```

`app/Models/ProverbsVerseGroup.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProverbsVerseGroup extends Model
{
    protected $guarded = [];

    public function group()
    {
        return $this->belongsTo(ProverbsGroup::class, 'proverbs_group_id');
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=ProverbsGroupModelTest`
Expected: PASS (3 tests)

- [ ] **Step 6: Apply the migration to the local dev database**

Run: `php artisan migrate`

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_09_26_000001_create_proverbs_groups_table.php \
        database/migrations/2026_09_26_000002_create_proverbs_verse_groups_table.php \
        app/Models/ProverbsGroup.php app/Models/ProverbsVerseGroup.php \
        tests/Unit/ProverbsGroupModelTest.php
git commit -m "Add proverbs_groups and proverbs_verse_groups tables and models"
```

---

### Task 2: Group CRUD (create, rename, delete) behind admin middleware

**Files:**
- Create: `app/Http/Controllers/ProverbsGroupController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/ProverbsGroupCrudTest.php`

**Interfaces:**
- Consumes: `App\Models\ProverbsGroup` (Task 1).
- Produces: routes `proverbs-groups.store` (POST `/proverbs-groups`), `proverbs-groups.update` (PUT `/proverbs-groups/{proverbsGroup}`), `proverbs-groups.destroy` (DELETE `/proverbs-groups/{proverbsGroup}`), all behind the `admin` middleware. Redirects to `proverbs-groups.index` on success (that route is added in Task 4 — until then, tests only assert the DB/HTTP status effects, not the redirect target's own content).

- [ ] **Step 1: Write the failing test**

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ProverbsGroupCrudTest`
Expected: FAIL — routes/controller don't exist yet.

- [ ] **Step 3: Create the controller**

`app/Http/Controllers/ProverbsGroupController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\ProverbsGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProverbsGroupController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        ProverbsGroup::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
        ]);

        return redirect()->route('proverbs-groups.index')->with('status', 'Group created.');
    }

    public function update(Request $request, ProverbsGroup $proverbsGroup)
    {
        abort_unless($proverbsGroup->user_id === Auth::id(), 403);

        $request->validate(['name' => 'required|string|max:255']);

        $proverbsGroup->update(['name' => $request->name]);

        return redirect()->route('proverbs-groups.index')->with('status', 'Group renamed.');
    }

    public function destroy(ProverbsGroup $proverbsGroup)
    {
        abort_unless($proverbsGroup->user_id === Auth::id(), 403);

        $proverbsGroup->delete();

        return redirect()->route('proverbs-groups.index')->with('status', 'Group deleted.');
    }
}
```

- [ ] **Step 4: Add the routes**

In `routes/web.php`, find this line (inside the `Route::middleware('auth')->group(...)` block, right before the `// Admin routes` comment):

```php
    Route::resource('translations', TranslationController::class);

    // Admin routes
```

Replace it with:

```php
    Route::resource('translations', TranslationController::class);

    // Proverbs verse groups (admin-only for now)
    Route::middleware('admin')->group(function () {
        Route::get('/proverbs-groups', [ProverbsGroupController::class, 'index'])->name('proverbs-groups.index');
        Route::post('/proverbs-groups', [ProverbsGroupController::class, 'store'])->name('proverbs-groups.store');
        Route::put('/proverbs-groups/{proverbsGroup}', [ProverbsGroupController::class, 'update'])->name('proverbs-groups.update');
        Route::delete('/proverbs-groups/{proverbsGroup}', [ProverbsGroupController::class, 'destroy'])->name('proverbs-groups.destroy');
        Route::post('/proverbs-groups/assign', [ProverbsGroupController::class, 'assign'])->name('proverbs-groups.assign');
        Route::get('/proverbs-groups/unassigned/read', [ProverbsGroupController::class, 'readUnassigned'])->name('proverbs-groups.read-unassigned');
        Route::get('/proverbs-groups/{proverbsGroup}/read', [ProverbsGroupController::class, 'read'])->name('proverbs-groups.read');
    });

    // Admin routes
```

Note: `index` and `assign`/`readUnassigned` are referenced here but not implemented until Tasks 3–5; the route list is added in full now so route names are stable, but calling `proverbs-groups.index` before Task 4 will 500 (no view/method yet) — that's fine, this task's tests never hit `index`.

Also add the `use App\Http\Controllers\ProverbsGroupController;` import at the top of `routes/web.php` next to the other controller `use` statements (match whatever ordering convention the existing `use` block already follows — insert alphabetically next to the other `App\Http\Controllers\*` imports).

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=ProverbsGroupCrudTest`
Expected: PASS (6 tests). Note this will currently fail on any attempt to hit `index` (undefined method) — the test suite above does not do that, so it should be green.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/ProverbsGroupController.php routes/web.php tests/Feature/ProverbsGroupCrudTest.php
git commit -m "Add admin-only Proverbs group CRUD routes and controller"
```

---

### Task 3: Bulk verse assignment endpoint

**Files:**
- Modify: `app/Http/Controllers/ProverbsGroupController.php`
- Test: `tests/Feature/ProverbsGroupAssignTest.php`

**Interfaces:**
- Consumes: `App\Models\ProverbsGroup`, `App\Models\ProverbsVerseGroup` (Task 1).
- Produces: `POST /proverbs-groups/assign` (route `proverbs-groups.assign`, already declared in Task 2) accepting `assignments[{chapter_id}][{verse_number}] = "{group_id}"|"unassigned"`. Later tasks (the manage-screen view in Task 4) rely on this exact nested field-name shape.

- [ ] **Step 1: Write the failing test**

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ProverbsGroupAssignTest`
Expected: FAIL — `assign` method doesn't exist yet.

- [ ] **Step 3: Add the `assign` method**

In `app/Http/Controllers/ProverbsGroupController.php`, add these imports at the top:

```php
use App\Models\ProverbsVerseGroup;
use Illuminate\Support\Facades\DB;
```

Then add this method inside the class, after `destroy()`:

```php
    public function assign(Request $request)
    {
        $data = $request->validate([
            'assignments' => 'required|array',
            'assignments.*' => 'array',
            'assignments.*.*' => 'nullable|string',
        ]);

        $ownedGroupIds = ProverbsGroup::where('user_id', Auth::id())->pluck('id')->all();

        foreach ($data['assignments'] as $verses) {
            foreach ($verses as $value) {
                if ($value !== 'unassigned' && $value !== null && $value !== '' && !in_array((int) $value, $ownedGroupIds, true)) {
                    abort(422, 'Unknown group.');
                }
            }
        }

        DB::transaction(function () use ($data) {
            foreach ($data['assignments'] as $chapterId => $verses) {
                foreach ($verses as $verseNumber => $value) {
                    if ($value === 'unassigned' || $value === null || $value === '') {
                        ProverbsVerseGroup::where('user_id', Auth::id())
                            ->where('chapter_id', $chapterId)
                            ->where('verse_number', $verseNumber)
                            ->delete();

                        continue;
                    }

                    ProverbsVerseGroup::updateOrCreate(
                        [
                            'user_id' => Auth::id(),
                            'chapter_id' => $chapterId,
                            'verse_number' => $verseNumber,
                        ],
                        ['proverbs_group_id' => (int) $value]
                    );
                }
            }
        });

        return redirect()->route('proverbs-groups.index')->with('status', 'Assignments saved.');
    }
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ProverbsGroupAssignTest`
Expected: PASS (4 tests)

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/ProverbsGroupController.php tests/Feature/ProverbsGroupAssignTest.php
git commit -m "Add bulk verse-to-group assignment endpoint"
```

---

### Task 4: Manage screen (group manager + verse assignment form)

**Files:**
- Modify: `app/Http/Controllers/ProverbsGroupController.php`
- Create: `resources/views/proverbs-groups/index.blade.php`
- Test: `tests/Feature/ProverbsGroupManageScreenTest.php`

**Interfaces:**
- Consumes: `assignments[{chapter_id}][{verse_number}]` field shape from Task 3; `ProverbsGroup`/`ProverbsVerseGroup` from Task 1.
- Produces: `GET /proverbs-groups` (route `proverbs-groups.index`) rendering the manage screen. Radio input markup format (exact attribute order, used by this task's own tests):
  `<input type="radio" class="btn-check" name="assignments[{chapterId}][{verseNumber}]" id="verse-{chapterId}-{verseNumber}-{group_id|unassigned}" value="{group_id|unassigned}" autocomplete="off" {checked?}>`

- [ ] **Step 1: Write the failing test**

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ProverbsGroupManageScreenTest`
Expected: FAIL — `index` method / view don't exist yet.

- [ ] **Step 3: Add the `index` method**

In `app/Http/Controllers/ProverbsGroupController.php`, add these imports:

```php
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Translation;
```

Add this method inside the class, before `store()`:

```php
    public function index()
    {
        $book = Book::where('name', 'Proverbs')->first();
        $translationId = Auth::user()->default_translation_id ?? Translation::first()?->id;

        $chapters = collect();
        if ($book) {
            $chapters = Chapter::where('book_id', $book->id)
                ->orderBy('number')
                ->with(['verses' => fn ($q) => $q->where('translation_id', $translationId)->orderBy('number')])
                ->get();
        }

        $groups = ProverbsGroup::where('user_id', Auth::id())
            ->withCount('verseAssignments')
            ->orderBy('created_at')
            ->get();

        $assignments = ProverbsVerseGroup::where('user_id', Auth::id())
            ->get()
            ->mapWithKeys(fn ($row) => [$row->chapter_id.'-'.$row->verse_number => $row->proverbs_group_id]);

        return view('proverbs-groups.index', compact('chapters', 'groups', 'assignments'));
    }
```

- [ ] **Step 4: Create the manage screen view**

`resources/views/proverbs-groups/index.blade.php`:

```blade
@extends('base.layout')

@section('title', 'Proverbs Groups')

@section('content')

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
    <div>
        <h3 class="font-weight-bold mb-1" style="color: var(--sword-navy);">Proverbs Groups</h3>
        <p class="mb-0" style="font-size: 0.85rem; color: #9ca3af;">Recategorize verses into topical groups, then read Proverbs by group.</p>
    </div>
    <a href="{{ route('topics.index') }}" class="btn btn-sm btn-outline-secondary">Back to Study</a>
</div>

@if(session('status'))
<div class="alert alert-success">{{ session('status') }}</div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <h6 class="fw-600 mb-3">Groups</h6>

        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach($groups as $group)
                <div class="d-flex align-items-center gap-1 border rounded px-2 py-1">
                    <form method="POST" action="{{ route('proverbs-groups.update', $group) }}" class="d-flex align-items-center gap-1 mb-0">
                        @csrf @method('PUT')
                        <input type="text" name="name" value="{{ $group->name }}" class="form-control form-control-sm" style="width: 140px;">
                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Rename"><i class="mdi mdi-content-save"></i></button>
                    </form>
                    <form method="POST" action="{{ route('proverbs-groups.destroy', $group) }}" class="mb-0" onsubmit="return confirm('Delete this group? Its verses will become Unassigned.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="mdi mdi-delete"></i></button>
                    </form>
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('proverbs-groups.store') }}" class="d-flex gap-2 mb-0">
            @csrf
            <input type="text" name="name" placeholder="New group name" class="form-control form-control-sm" style="width: 220px;" required>
            <button type="submit" class="btn btn-sm" style="background: var(--sword-navy); color: var(--sword-gold);">Add Group</button>
        </form>
    </div>
</div>

<div class="mb-4 d-flex flex-wrap gap-1">
    @foreach($chapters as $chapter)
        <a href="#chapter-{{ $chapter->number }}" class="btn btn-sm btn-outline-secondary">{{ $chapter->number }}</a>
    @endforeach
</div>

<form method="POST" action="{{ route('proverbs-groups.assign') }}">
    @csrf

    @foreach($chapters as $chapter)
        <h5 id="chapter-{{ $chapter->number }}" class="mt-4">Chapter {{ $chapter->number }}</h5>

        @foreach($chapter->verses as $verse)
            @php $current = $assignments[$chapter->id.'-'.$verse->number] ?? null; @endphp
            <div class="d-flex flex-wrap align-items-start gap-2 py-2 border-bottom">
                <div style="min-width: 260px; flex: 1;">
                    <span class="fw-600">{{ $chapter->number }}:{{ $verse->number }}</span>
                    {{ $verse->text }}
                </div>
                <div class="d-flex flex-wrap gap-1">
                    <input type="radio" class="btn-check" name="assignments[{{ $chapter->id }}][{{ $verse->number }}]" id="verse-{{ $chapter->id }}-{{ $verse->number }}-unassigned" value="unassigned" autocomplete="off" {{ $current === null ? 'checked' : '' }}>
                    <label class="btn btn-sm btn-outline-secondary" for="verse-{{ $chapter->id }}-{{ $verse->number }}-unassigned">Unassigned</label>

                    @foreach($groups as $group)
                        <input type="radio" class="btn-check" name="assignments[{{ $chapter->id }}][{{ $verse->number }}]" id="verse-{{ $chapter->id }}-{{ $verse->number }}-{{ $group->id }}" value="{{ $group->id }}" autocomplete="off" {{ $current === $group->id ? 'checked' : '' }}>
                        <label class="btn btn-sm btn-outline-primary" for="verse-{{ $chapter->id }}-{{ $verse->number }}-{{ $group->id }}">{{ $group->name }}</label>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endforeach

    <div class="position-sticky bottom-0 bg-white py-3 border-top mt-4 d-flex justify-content-end" style="z-index: 10;">
        <button type="submit" class="btn" style="background: var(--sword-navy); color: var(--sword-gold); font-weight: 600;">Save Assignments</button>
    </div>
</form>

@endsection
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=ProverbsGroupManageScreenTest`
Expected: PASS (4 tests)

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/ProverbsGroupController.php resources/views/proverbs-groups/index.blade.php tests/Feature/ProverbsGroupManageScreenTest.php
git commit -m "Add Proverbs groups manage screen"
```

---

### Task 5: Read screen with prev/next group navigation

**Files:**
- Modify: `app/Http/Controllers/ProverbsGroupController.php`
- Create: `resources/views/proverbs-groups/read.blade.php`
- Test: `tests/Feature/ProverbsGroupReadTest.php`

**Interfaces:**
- Consumes: `ProverbsGroup`, `ProverbsVerseGroup` (Task 1); routes `proverbs-groups.read` / `proverbs-groups.read-unassigned` (declared in Task 2).
- Produces: view data contract for `proverbs-groups.read` template: `$title` (string), `$verses` (Collection of `Verse` models, each with `chapter` loaded, in canonical order), `$translationId` (int), `$translations` (Collection of `Translation`), `$prevUrl`/`$nextUrl` (string|null).

- [ ] **Step 1: Write the failing test**

```php
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
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ProverbsGroupReadTest`
Expected: FAIL — `read`/`readUnassigned` methods and view don't exist yet.

- [ ] **Step 3: Add `read`, `readUnassigned`, and their shared helpers**

In `app/Http/Controllers/ProverbsGroupController.php`, add this import (`Translation` was already imported in Task 4 — don't duplicate it):

```php
use App\Models\Verse;
```

Add these methods inside the class, after `assign()`:

```php
    public function read(ProverbsGroup $proverbsGroup, Request $request)
    {
        abort_unless($proverbsGroup->user_id === Auth::id(), 403);

        $translationId = (int) ($request->query('translation_id') ?: (Auth::user()->default_translation_id ?? Translation::first()?->id));

        $rows = ProverbsVerseGroup::where('user_id', Auth::id())
            ->where('proverbs_group_id', $proverbsGroup->id)
            ->get();

        [$prevUrl, $nextUrl] = $this->buildNav($proverbsGroup->id);

        return view('proverbs-groups.read', [
            'title' => $proverbsGroup->name,
            'verses' => $this->resolveOrderedVerses($rows, $translationId),
            'translationId' => $translationId,
            'translations' => Translation::orderBy('name')->get(),
            'prevUrl' => $prevUrl,
            'nextUrl' => $nextUrl,
        ]);
    }

    public function readUnassigned(Request $request)
    {
        $translationId = (int) ($request->query('translation_id') ?: (Auth::user()->default_translation_id ?? Translation::first()?->id));

        $book = Book::where('name', 'Proverbs')->first();
        $chapterIds = $book ? Chapter::where('book_id', $book->id)->pluck('id') : collect();

        $assignedKeys = ProverbsVerseGroup::where('user_id', Auth::id())
            ->get()
            ->map(fn ($row) => $row->chapter_id.'-'.$row->verse_number)
            ->all();

        $verses = Verse::where('translation_id', $translationId)
            ->whereIn('chapter_id', $chapterIds)
            ->with('chapter')
            ->get()
            ->reject(fn ($verse) => in_array($verse->chapter_id.'-'.$verse->number, $assignedKeys, true))
            ->sortBy([
                fn ($a, $b) => $a->chapter->number <=> $b->chapter->number,
                fn ($a, $b) => $a->number <=> $b->number,
            ])
            ->values();

        [$prevUrl, $nextUrl] = $this->buildNav(null);

        return view('proverbs-groups.read', [
            'title' => 'Unassigned',
            'verses' => $verses,
            'translationId' => $translationId,
            'translations' => Translation::orderBy('name')->get(),
            'prevUrl' => $prevUrl,
            'nextUrl' => $nextUrl,
        ]);
    }

    private function resolveOrderedVerses($rows, int $translationId)
    {
        $chapterNumbers = Chapter::whereIn('id', $rows->pluck('chapter_id')->unique())->pluck('number', 'id');

        return $rows
            ->sortBy([
                fn ($a, $b) => $chapterNumbers[$a->chapter_id] <=> $chapterNumbers[$b->chapter_id],
                fn ($a, $b) => $a->verse_number <=> $b->verse_number,
            ])
            ->map(fn ($row) => Verse::where('chapter_id', $row->chapter_id)
                ->where('translation_id', $translationId)
                ->where('number', $row->verse_number)
                ->with('chapter')
                ->first())
            ->filter()
            ->values();
    }

    private function buildNav(?int $currentGroupId): array
    {
        $groups = ProverbsGroup::where('user_id', Auth::id())->orderBy('created_at')->orderBy('id')->get();

        $stops = $groups
            ->map(fn ($g) => ['id' => $g->id, 'url' => route('proverbs-groups.read', $g)])
            ->push(['id' => null, 'url' => route('proverbs-groups.read-unassigned')])
            ->values();

        $currentIndex = $stops->search(fn ($stop) => $stop['id'] === $currentGroupId);

        $prevUrl = $currentIndex > 0 ? $stops[$currentIndex - 1]['url'] : null;
        $nextUrl = $currentIndex < $stops->count() - 1 ? $stops[$currentIndex + 1]['url'] : null;

        return [$prevUrl, $nextUrl];
    }
```

- [ ] **Step 4: Create the read screen view**

`resources/views/proverbs-groups/read.blade.php`:

```blade
@extends('base.layout')

@section('title', $title)

@section('content')

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
    <div>
        <h3 class="font-weight-bold mb-1" style="color: var(--sword-navy);">{{ $title }}</h3>
        <p class="mb-0" style="font-size: 0.85rem; color: #9ca3af;">Proverbs by group</p>
    </div>
    <a href="{{ route('proverbs-groups.index') }}" class="btn btn-sm btn-outline-secondary">Manage Groups</a>
</div>

<form method="GET" class="mb-3" style="max-width: 220px;">
    <select name="translation_id" class="form-select form-select-sm" onchange="this.form.submit()">
        @foreach($translations as $translation)
            <option value="{{ $translation->id }}" {{ $translationId == $translation->id ? 'selected' : '' }}>{{ $translation->name }}</option>
        @endforeach
    </select>
</form>

<div class="mb-4">
    @forelse($verses as $verse)
        <p><span class="fw-600">{{ $verse->chapter->number }}:{{ $verse->number }}</span> {{ $verse->text }}</p>
    @empty
        <p class="text-muted">No verses in this group yet.</p>
    @endforelse
</div>

<div class="d-flex justify-content-between">
    @if($prevUrl)
        <a href="{{ $prevUrl }}" class="btn btn-outline-secondary reading-nav-btn">&laquo; Previous</a>
    @else
        <button class="btn btn-outline-secondary reading-nav-btn" disabled>&laquo; Previous</button>
    @endif

    @if($nextUrl)
        <a href="{{ $nextUrl }}" class="btn btn-outline-secondary reading-nav-btn">Next &raquo;</a>
    @else
        <button class="btn btn-outline-secondary reading-nav-btn" disabled>Next &raquo;</button>
    @endif
</div>

@endsection
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=ProverbsGroupReadTest`
Expected: PASS (5 tests)

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/ProverbsGroupController.php resources/views/proverbs-groups/read.blade.php tests/Feature/ProverbsGroupReadTest.php
git commit -m "Add Proverbs group read screen with prev/next navigation"
```

---

### Task 6: Study Hub "Proverbs" tab (admin-only)

**Files:**
- Modify: `app/Http/Controllers/TopicController.php`
- Modify: `resources/views/topics/index.blade.php`
- Test: `tests/Feature/StudyHubProverbsTabTest.php`

**Interfaces:**
- Consumes: `ProverbsGroup::verseAssignments()` (Task 1), routes `proverbs-groups.index`/`proverbs-groups.read` (Tasks 2/5).
- Produces: `topics.index` view now receives `$proverbsGroups` (Collection of `ProverbsGroup`, each with `verse_assignments_count`) and `$proverbsUnassignedCount` (int), both empty/zero and unused when the viewer is not an admin.

- [ ] **Step 1: Write the failing test**

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=StudyHubProverbsTabTest`
Expected: FAIL — tab doesn't exist yet, `$proverbsGroups` undefined.

- [ ] **Step 3: Update `TopicController::index()`**

In `app/Http/Controllers/TopicController.php`, add these imports:

```php
use App\Models\Chapter;
use App\Models\ProverbsGroup;
use App\Models\ProverbsVerseGroup;
use App\Models\Translation;
```

Replace:

```php
        $chaptersReadByBook = UserRead::where('user_id', Auth::id())
            ->whereIn('book_id', $studyBookIds)
            ->selectRaw('book_id, COUNT(DISTINCT chapter_number) as read_count')
            ->groupBy('book_id')
            ->pluck('read_count', 'book_id');

        return view('topics.index', compact('topics', 'activeStudies', 'completedStudies', 'allBooks', 'chaptersReadByBook'));
```

With:

```php
        $chaptersReadByBook = UserRead::where('user_id', Auth::id())
            ->whereIn('book_id', $studyBookIds)
            ->selectRaw('book_id, COUNT(DISTINCT chapter_number) as read_count')
            ->groupBy('book_id')
            ->pluck('read_count', 'book_id');

        $proverbsGroups = collect();
        $proverbsUnassignedCount = 0;

        if (Auth::user()->is_admin) {
            $proverbsGroups = ProverbsGroup::where('user_id', Auth::id())
                ->withCount('verseAssignments')
                ->orderBy('created_at')
                ->get();

            $proverbsBook = Book::where('name', 'Proverbs')->first();
            if ($proverbsBook) {
                $translationId = Auth::user()->default_translation_id;
                $proverbsChapterIds = Chapter::where('book_id', $proverbsBook->id)->pluck('id');
                $totalProverbsVerses = Verse::where('translation_id', $translationId)
                    ->whereIn('chapter_id', $proverbsChapterIds)
                    ->count();
                $assignedProverbsVerses = ProverbsVerseGroup::where('user_id', Auth::id())
                    ->whereIn('chapter_id', $proverbsChapterIds)
                    ->count();
                $proverbsUnassignedCount = max(0, $totalProverbsVerses - $assignedProverbsVerses);
            }
        }

        return view('topics.index', compact('topics', 'activeStudies', 'completedStudies', 'allBooks', 'chaptersReadByBook', 'proverbsGroups', 'proverbsUnassignedCount'));
```

- [ ] **Step 4: Add the tab button**

In `resources/views/topics/index.blade.php`, find:

```blade
    <li class="nav-item" role="presentation">
        <button class="nav-link px-4 py-2" id="tab-books" data-bs-toggle="tab" data-bs-target="#pane-books" type="button" role="tab"
            style="border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; border-radius: 0; font-size: 0.88rem; color: #6b7280; background: transparent; font-weight: 600;">
            <i class="mdi mdi-book-open-variant me-1"></i> Books
            <span class="ms-1 badge" style="background: rgba(14,22,40,0.08); color: var(--sword-navy); font-size: 0.68rem;">{{ $activeStudies->count() }}</span>
        </button>
    </li>
</ul>
```

Replace it with:

```blade
    <li class="nav-item" role="presentation">
        <button class="nav-link px-4 py-2" id="tab-books" data-bs-toggle="tab" data-bs-target="#pane-books" type="button" role="tab"
            style="border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; border-radius: 0; font-size: 0.88rem; color: #6b7280; background: transparent; font-weight: 600;">
            <i class="mdi mdi-book-open-variant me-1"></i> Books
            <span class="ms-1 badge" style="background: rgba(14,22,40,0.08); color: var(--sword-navy); font-size: 0.68rem;">{{ $activeStudies->count() }}</span>
        </button>
    </li>
    @if(auth()->user()->is_admin)
    <li class="nav-item" role="presentation">
        <button class="nav-link px-4 py-2" id="tab-proverbs" data-bs-toggle="tab" data-bs-target="#pane-proverbs" type="button" role="tab"
            style="border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; border-radius: 0; font-size: 0.88rem; color: #6b7280; background: transparent; font-weight: 600;">
            <i class="mdi mdi-shape-outline me-1"></i> Proverbs
            <span class="ms-1 badge" style="background: rgba(14,22,40,0.08); color: var(--sword-navy); font-size: 0.68rem;">{{ $proverbsGroups->count() }}</span>
        </button>
    </li>
    @endif
</ul>
```

- [ ] **Step 5: Add the tab pane**

In `resources/views/topics/index.blade.php`, find the end of the Books pane (the closing `</div>` that matches `<div class="tab-pane fade pt-3" id="pane-books" role="tabpanel">`, immediately before the final `</div>` that closes `<div class="tab-content">`). Insert this new pane right after the Books pane's closing `</div>` and before `</div>` (the `tab-content` close):

```blade
    @if(auth()->user()->is_admin)
    <div class="tab-pane fade pt-3" id="pane-proverbs" role="tabpanel">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <p class="mb-0" style="font-size: 0.85rem; color: #9ca3af;">
                {{ $proverbsGroups->count() }} {{ Str::plural('group', $proverbsGroups->count()) }}, {{ $proverbsUnassignedCount }} unassigned
            </p>
            <a href="{{ route('proverbs-groups.index') }}" class="btn btn-sm" style="background: var(--sword-navy); color: var(--sword-gold); font-weight: 600; font-size: 0.82rem;">
                <i class="mdi mdi-pencil"></i> Manage Groups
            </a>
        </div>

        @if($proverbsGroups->isNotEmpty())
        <div class="row g-2">
            @foreach($proverbsGroups as $group)
            <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                <a href="{{ route('proverbs-groups.read', $group) }}" class="card h-100 text-decoration-none" style="border-top: 2px solid var(--sword-gold);">
                    <div class="card-body">
                        <div class="fw-600" style="color: var(--sword-navy);">{{ $group->name }}</div>
                        <div style="font-size: 0.8rem; color: #9ca3af;">{{ $group->verse_assignments_count }} {{ Str::plural('verse', $group->verse_assignments_count) }}</div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-muted">No groups yet. Click "Manage Groups" to create one.</p>
        @endif
    </div>
    @endif
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=StudyHubProverbsTabTest`
Expected: PASS (2 tests)

- [ ] **Step 7: Run the full test suite**

Run: `php artisan test`
Expected: PASS (all tests, including every test added in Tasks 1–6)

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/TopicController.php resources/views/topics/index.blade.php tests/Feature/StudyHubProverbsTabTest.php
git commit -m "Add admin-only Proverbs tab to the Study Hub"
```

- [ ] **Step 9: Update the changelog**

Per this repo's `CLAUDE.md` changelog convention: bump the version in `resources/views/changelog.blade.php`, move the `Latest` badge to a new top entry, and add a `tag-new` line describing this feature (admin-only Proverbs verse grouping and thematic reading).
