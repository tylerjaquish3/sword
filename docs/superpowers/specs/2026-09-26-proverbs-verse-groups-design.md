# Proverbs Verse Groups — Design Spec

Date: 2026-09-26
Status: Approved for planning

## Purpose

Proverbs is a collection of largely independent sayings rather than a
narrative, so reading it strictly chapter-by-chapter is not the most
fluid way to study it. This feature lets an admin user recategorize
Proverbs verses into custom topical groups, then read those groups as
if they were chapters — a more thematic way to move through the book.

This is a new, self-contained subsystem: new tables, a new controller,
new routes, and new views, wired into the existing Study Hub as a new
tab. It is admin-only for now (both creating groups and reading them),
matching the app's existing `is_admin` gating pattern used for
`/admin/*` and `/translations/section-editor`.

## Non-goals

- Not generalized to other books. Everything here (routes, controller,
  table names, views) is intentionally Proverbs-specific. If this is
  ever extended to other books, that is a separate future project.
- Not exposed to non-admin users in this iteration — the entire tab,
  manage screen, and read screen are hidden unless `auth()->user()->is_admin`.
- Not a replacement for the main Bible-reading UI
  (`resources/views/translations/index.blade.php`). The group "read"
  view is a separate, simpler page — it does not carry over comments,
  cross-references, highlights, or the compare/xref tooling from the
  main reading page.
- No manual verse reordering within a group — verses always display in
  canonical chapter/verse order.
- No manual group reordering — groups display in creation order, with
  an automatic "Unassigned" bucket always shown last.
- A verse belongs to at most one group at a time (assigning it to a new
  group removes it from any previous group).

## Data Model

Two new tables, following two existing conventions in this codebase:

- Verse identity is expressed as `chapter_id` + `verse_number` (not a
  translation-specific `verse_id`), the same pattern used by
  `verse_links`, `verse_comments`, and `verse_highlights`. This keeps
  group membership independent of which translation is being viewed.
- User-generated tables get real timestamps and are scoped by
  `user_id`, filtered in the controller (no global scope), matching
  `topics`.

### `proverbs_groups`

| Column     | Type              | Notes                        |
|------------|-------------------|------------------------------|
| id         | bigint, PK        |                              |
| user_id    | bigint, FK(users) | cascade delete                |
| name       | string            |                              |
| created_at | timestamp         |                              |
| updated_at | timestamp         |                              |

### `proverbs_verse_groups`

| Column            | Type                        | Notes                                             |
|-------------------|-----------------------------|----------------------------------------------------|
| id                | bigint, PK                  |                                                    |
| user_id           | bigint, FK(users)           | cascade delete                                     |
| proverbs_group_id | bigint, FK(proverbs_groups) | cascade delete                                     |
| chapter_id        | bigint, FK(chapters)        | cascade delete                                     |
| verse_number      | integer                     |                                                    |
| created_at        | timestamp                   |                                                    |
| updated_at        | timestamp                   |                                                    |

Unique constraint on `(user_id, chapter_id, verse_number)` — enforces
one group per verse per user at the database level. Deleting a group
cascades and deletes its `proverbs_verse_groups` rows, which
implicitly returns those verses to "Unassigned" (Unassigned is not a
stored row — it is simply the absence of a `proverbs_verse_groups`
row for that verse).

Both models use `protected $guarded = []`, matching the rest of the
codebase's models.

- `App\Models\ProverbsGroup`: `belongsTo(User::class)`,
  `hasMany(ProverbsVerseGroup::class)`
- `App\Models\ProverbsVerseGroup`: `belongsTo(ProverbsGroup::class)`,
  `belongsTo(Chapter::class)`

## Routes & Controller

New `App\Http\Controllers\ProverbsGroupController`, entirely behind the
existing `admin` route middleware alias:

```php
Route::middleware('admin')->group(function () {
    Route::get('/proverbs-groups', [ProverbsGroupController::class, 'index'])
        ->name('proverbs-groups.index');
    Route::post('/proverbs-groups', [ProverbsGroupController::class, 'store'])
        ->name('proverbs-groups.store');
    Route::put('/proverbs-groups/{proverbsGroup}', [ProverbsGroupController::class, 'update'])
        ->name('proverbs-groups.update');
    Route::delete('/proverbs-groups/{proverbsGroup}', [ProverbsGroupController::class, 'destroy'])
        ->name('proverbs-groups.destroy');
    Route::post('/proverbs-groups/assign', [ProverbsGroupController::class, 'assign'])
        ->name('proverbs-groups.assign');
    Route::get('/proverbs-groups/{proverbsGroup}/read', [ProverbsGroupController::class, 'read'])
        ->name('proverbs-groups.read');
    Route::get('/proverbs-groups/unassigned/read', [ProverbsGroupController::class, 'readUnassigned'])
        ->name('proverbs-groups.read-unassigned');
});
```

Note the unassigned route is declared before the parameterized `{proverbsGroup}/read` route would ever need to compete for the segment — `unassigned` is a fixed path prefix (`/proverbs-groups/unassigned/read`), so there is no routing ambiguity with `/proverbs-groups/{proverbsGroup}/read`.

All queries scope `ProverbsGroup`/`ProverbsVerseGroup` by
`where('user_id', Auth::id())`, matching the `TopicController` pattern.
Route model binding resolves `{proverbsGroup}` normally; the controller
still checks `user_id` ownership before acting (403 if mismatched).

### Controller methods

- **`index()`** — Manage screen. Loads the current user's
  `ProverbsGroup`s (with verse counts), the full Proverbs verse list
  (all chapters, in canonical order, verse text in the user's default
  translation) with each verse's current group (or null/Unassigned),
  and renders `proverbs-groups.index`.
- **`store(Request $request)`** — Validates `name` (required, string,
  max 255), creates a `ProverbsGroup` for the current user.
- **`update(Request $request, ProverbsGroup $proverbsGroup)`** —
  Renames a group.
- **`destroy(ProverbsGroup $proverbsGroup)`** — Deletes a group
  (cascade removes its verse assignments).
- **`assign(Request $request)`** — Bulk save. Accepts an array of
  `{chapter_id, verse_number, proverbs_group_id}` (`proverbs_group_id`
  nullable to mean "Unassigned"). In a DB transaction: for each entry,
  if `proverbs_group_id` is null, delete any existing
  `proverbs_verse_groups` row for that `(user_id, chapter_id,
  verse_number)`; otherwise `updateOrCreate` it. Validates that each
  referenced `proverbs_group_id` belongs to the current user.
- **`read(ProverbsGroup $proverbsGroup, Request $request)`** — Read
  screen for one real group. Resolves the group's verses (via
  `proverbs_verse_groups` → `chapter_id`+`verse_number`) against the
  requested/default translation, in canonical chapter/verse order.
  Also loads the ordered list of all groups (creation order) to build
  prev/next navigation, with a link to `proverbs-groups.read-unassigned`
  as the fixed final "next" stop after the last real group.
- **`readUnassigned(Request $request)`** — Read screen for the
  synthetic "Unassigned" bucket: all Proverbs verses with no
  `proverbs_verse_groups` row for the current user, in canonical order.
  Shares the same view as `read()` (passing a null group / "Unassigned"
  as the label), with "prev" linking back to the last real group and
  no "next" (Unassigned is always last).

## Views / UX Flow

### Study Hub tab (`resources/views/topics/index.blade.php`)

A new "Proverbs" tab/pane, wrapped in `@if(auth()->user()->is_admin)`,
added the same way the existing Topics/Books tabs work (button +
`.tab-pane`). Shows each group's name and verse count, the Unassigned
count, and a "Manage Groups" button linking to `proverbs-groups.index`.
Clicking a group name links to `proverbs-groups.read`.

`TopicController@index` gains the groups summary data behind the same
`is_admin` check (query it in the controller and pass `$proverbsGroups`
/ `$unassignedCount` to the view — no changes to the Topics logic
itself).

### Manage screen (`resources/views/proverbs-groups/index.blade.php`)

New standalone page (not embedded in the Study Hub — ~915 verses is
too much for a tab pane):

- Inline group manager at the top: text input + "Add Group" button;
  existing groups listed with inline rename and a delete button
  (SweetAlert2 confirm, matching existing delete confirmations
  elsewhere in the app).
- A chapter jump list (links 1–31) for quick navigation down the page.
- The full verse list, grouped visually by chapter heading, each verse
  showing its reference, text (default translation), and a row of
  pill-style radio buttons — one per existing group plus "Unassigned" —
  for one-click (re)assignment. Client-side state tracks any changed
  assignments.
- One "Save" button (sticky/fixed at bottom for a long page) that
  submits all changed assignments to `proverbs-groups.assign` via a
  single AJAX POST.

### Read screen (`resources/views/proverbs-groups/read.blade.php`)

A simple, focused reading page:

- Group name as the heading.
- Verses in canonical order, in the selected translation (a translation
  `<select>`, defaulting to the user's profile default translation,
  same convention as elsewhere).
- Prev/next buttons cycling through: groups in creation order, then
  Unassigned as the trailing entry, wrapping or disabling at the ends
  (matching the disable-at-ends convention used by
  `#btn-prev-chapter`/`#btn-next-chapter` on the main reading page).
- No comments, cross-references, highlights, or compare-translation
  tooling — plain verse text only.

## Error Handling

- All routes 404 via standard route-model-binding if a group doesn't
  exist; controller returns 403 if a resolved group belongs to another
  user.
- `assign` validates every `proverbs_group_id` in the payload belongs
  to `Auth::id()`, rejecting the whole batch (422) if not — no partial
  saves.
- `store`/`update` validate `name` is present and non-empty.

## Testing Plan

PHPUnit feature tests in `tests/Feature/ProverbsGroupControllerTest.php`:

- Non-admin users get redirected/blocked on every new route (via the
  `admin` middleware — mirror existing tests for `/admin/*` or
  `/translations/section-editor` if any exist, otherwise a fresh
  assertion per route).
- Admin can create, rename, and delete a group.
- Deleting a group returns its verses to Unassigned (no orphaned
  `proverbs_verse_groups` rows, no error reading Unassigned afterward).
- `assign` enforces one-group-per-verse: assigning a verse already in
  Group A to Group B removes it from Group A.
- `assign` rejects a `proverbs_group_id` belonging to another user.
- `read` returns a group's verses in canonical chapter/verse order.
- `read` for Unassigned returns exactly the verses with no assignment.
- Prev/next navigation data is ordered correctly with Unassigned last.
