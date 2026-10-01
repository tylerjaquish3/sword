# PWA Install + Offline Mode — Design Spec

Date: 2026-10-01
Status: Approved for planning

## Purpose

Sword now has a handful of real users (5), and the original ask was
"let me install this on my phone from Chrome and read my Bible with no
signal." Chrome only offers "Install app" for a page that qualifies as
a PWA (manifest + registered service worker + HTTPS — HTTPS is already
covered by `valet secure`). Once that scaffolding exists, it's a small
additional step to also let a user queue up personal content (verse
comments, chapter comments, prayers, highlights, accountability
check-ins, weekly digests) while offline and have it sync once they're
back online.

This is a new, mostly self-contained subsystem: a manifest, a service
worker, a new IndexedDB-backed "Offline Reader" page, a handful of
small server-side additions (a bundle endpoint, a few dedupe columns),
and a client-side sync queue. It does not change how the app behaves
for any user who never enables it.

## Non-goals

- Not a general offline-first rewrite of the app. The existing
  full-featured reading page
  (`resources/views/translations/index.blade.php`), with cross-references,
  translation-compare tooling, etc., stays server-rendered and
  online-only, same as today.
- No offline **editing or deleting** of existing comments, prayers,
  accountability check-ins, or digests — only **creating** new ones
  offline. Editing/deleting always requires a connection. (Verse
  highlights are the one exception — see Data Model — because a
  highlight is a single-owner scalar value, not free-text content, so
  there's no meaningful "conflict" to avoid.)
- No offline support for the public/guest-facing flows: shared digest
  comments (`digest.shared.comment`) and shared accountability comments
  (`accountability.shared.comment`) stay online-only. Those are hit by
  people without the app installed, so there's no realistic offline
  use case for them.
- No true background sync (Chrome's Background Sync API). Queued items
  send the moment the app is open and a connection is detected — no
  silent uploads while the app is closed. Simpler, works in any
  browser, and matches how the app is actually used.
- No incremental/delta sync for the offline read bundle. The verse
  text + personal content pulled for offline reading is small (~19MB
  total across all 5 translations), so each refresh just re-pulls the
  whole bundle rather than diffing.
- Cross-references are explicitly **not** available in the Offline
  Reader — only highlights and comments.

## Scope Summary

**Offline Mode is opt-in per user** via a new "Offline Mode" toggle on
the profile page (`offline_enabled` on `users`, default `false`).
Turning it on triggers the first bundle download; turning it off clears
the local IndexedDB stores.

**Readable offline** (once Offline Mode is on and the bundle has synced
at least once):
- Verse text for all 5 translations.
- The user's own verse comments, chapter comments, and verse
  highlights.
- The user's own prayers.

**Creatable offline** (queued, sent once back online):
- Verse comments, chapter comments, prayers.
- Verse highlights (toggle on/off — an upsert, not a strict create; see
  below for why this is safe).
- Accountability check-ins.
- Weekly digest reflections (the free-text/checkbox fields only — see
  "Digest reflections" below for how the auto-generated weekly summary
  part is handled).

**Offline navigation:** if the installed app is opened with no
connection, the service worker's navigation fallback serves a new,
dedicated **Offline Reader** page instead of a browser error page —
the same destination for any failed navigation, not a per-page offline
variant of every existing view.

## Data Model

### `users`

```php
Schema::table('users', function (Blueprint $table) {
    $table->boolean('offline_enabled')->default(false)->after('is_active');
});
```

Added to `User::$fillable` and `$casts` (`'offline_enabled' => 'boolean'`),
following the existing `is_admin`/`is_active` pattern.

### Dedupe columns (`client_uuid`)

Free-text create actions queued offline need a way to survive a retry
(flaky connection resends the same POST) without creating duplicate
rows. Each of the following tables gets a nullable column:

```php
$table->uuid('client_uuid')->nullable()->after('id');
$table->unique(['user_id', 'client_uuid']);
```

Applied to: `verse_comments`, `chapter_comments`, `prayers`,
`accountability_check_ins`, `shared_digests`.

The client generates a UUID (`crypto.randomUUID()`) at the moment an
item is queued and includes it on the POST. Each `store()` method does
`firstOrCreate(['user_id' => Auth::id(), 'client_uuid' => $uuid], $data)`
when a `client_uuid` is present (online-created rows simply omit it,
same as today). A replayed sync is then a no-op instead of a
duplicate.

`UserVersePreference` (highlights) does **not** get a `client_uuid`.
Highlighting already goes through `updateOrCreate` keyed on
`(user_id, chapter_id, verse_number)` — replaying the same "set this
verse to yellow" action twice naturally lands on the same row with the
same value, no dedupe key needed.

### Why "create-only" is safe here

All of this user's own content (comments, prayers, check-ins, digests,
highlights) is single-owner — no one else writes to these rows. Offline
queuing only ever adds a brand-new row (or, for highlights, sets a
value only this user controls). There's no scenario where an offline
queue item needs to merge against a conflicting edit someone else made
in the meantime, which is what makes skipping full edit/delete sync
support safe rather than just convenient.

## Server-Side Additions

### Bundle endpoint

`GET /api/offline/bundle` (behind `auth`), new
`OfflineBundleController@show`:

```php
return response()->json([
    'translations' => Translation::all(['id', 'name', 'abbreviation']),
    'verses' => Verse::select('chapter_id', 'translation_id', 'number', 'text')
        ->get(), // ~19MB across all 5 translations
    'chapters' => Chapter::with('book:id,name,sort_order')
        ->get(['id', 'book_id', 'number']),
    'verseComments' => VerseComment::withoutGlobalScopes()
        ->where('user_id', Auth::id())
        ->get(['id', 'client_uuid', 'chapter_id', 'verse_number', 'comment', 'created_at']),
    'chapterComments' => ChapterComment::withoutGlobalScopes()
        ->where('user_id', Auth::id())
        ->get(['id', 'client_uuid', 'chapter_id', 'comment', 'created_at']),
    'highlights' => UserVersePreference::where('user_id', Auth::id())
        ->whereNotNull('highlight_color')
        ->get(['chapter_id', 'verse_number', 'highlight_color']),
    'prayers' => Prayer::where('user_id', Auth::id())
        ->get(['id', 'client_uuid', 'date', 'content', 'prayer_type_id']),
]);
```

(`VerseComment`/`ChapterComment` already auto-scope to the current user
via their global scope — `withoutGlobalScopes()` here is just to make
the explicit `where('user_id', ...)` the one source of truth in this
read-only export, avoiding any confusion about double-scoping.)

### Store endpoints: accept `client_uuid`

`CommentaryController::store`, `PrayerController::store`,
`AccountabilityCheckInController::store` — each gains an optional
`client_uuid` input, validated as `nullable|uuid`, and switches its
`::create(...)` call to the `firstOrCreate` pattern described above.

### Highlights: explicit "set" instead of "toggle"

`VerseHighlightController::toggle` currently flips based on whatever
the *server's current* highlight color is, and resolves its verse via
a translation-specific `verse_id`. Neither works cleanly for a queued
offline action, so it gains:

- An alternative to `verse_id`: accept `chapter_id` + `verse_number`
  directly (what the Offline Reader has cached), resolving the same
  way internally.
- An explicit `color` (nullable, meaning "clear") instead of relying on
  server-side toggle logic — the Offline Reader computes the new state
  itself from its local cached copy (mirroring the same toggle-off-if-
  same-color rule) and sends the resulting target color. This makes
  the queued action idempotent no matter when it actually sends.

The existing online toggle button keeps using the current
`verse_id` + implicit-toggle behavior unchanged — this is an additive
alternative input shape, not a breaking change.

### Digest reflections

`SharedDigestController::store` currently always rebuilds the weekly
summary from `fetchWeeklyData(now())` — i.e., "this week" as of
whenever the request hits the server. That's wrong for a queued
offline request that might sync days later, into a different week. Fix:
accept an optional `week_start` input; if present, pass it through to
`fetchWeeklyData()` instead of defaulting to `now()`. The Offline
Reader's digest form captures the current date when the user opens it
and sends that as `week_start`.

The rest of `store()` is unchanged: the actual weekly summary/snapshot
is still always built fresh from the live database at whatever moment
the request is processed (online, or synced later) — never from
stale/cached data. This is correct by construction as long as the
outbox drains in creation order, since any prayers/comments entered
earlier in the same offline session sync first and are present in the
database by the time the digest's `fetchWeeklyData()` runs.

## Offline Reader

### Route & Controller

```php
Route::get('/offline-reader', [OfflineReaderController::class, 'index'])
    ->name('offline-reader.index');
```

A thin controller — this page is almost entirely client-rendered from
IndexedDB. The server side just returns the shell view; all data comes
from the bundle already synced into IndexedDB plus the JS sync layer.

### View (`resources/views/offline-reader/index.blade.php`)

- Translation / book / chapter pickers (populated from the cached
  `translations`/`chapters` bundle data).
- Verse list for the selected chapter: reference, text, current
  highlight color (if any) as a colored left border, and a small
  "comment" icon on verses that have a cached comment.
- Tapping a verse opens a lightweight panel with: the cached
  comment (if any) + a text field to add a new one, and the four
  highlight-color swatches (click a color to set it, click the active
  one again to clear — mirrors the existing toggle UX).
- A simple prayer list + "add prayer" form.
- A simplified digest form: just the reflection fields (fruits needing
  prayer, impactful scripture, idols, additional content, sermon
  notes, draft/share choice) with a note — *"Your weekly summary of
  chapters, prayers, and commentary will be attached automatically
  once this syncs."* No live summary preview (that data isn't cached
  offline).
- A small persistent banner showing sync status: "All synced" / "N
  items waiting to sync" / "N items failed to sync" (see Error
  Handling).

No cross-references, no translation-compare, no "read" tracking —
intentionally the minimal set: read verse text, read/add comments,
read/set highlights, read/add prayers, add a digest reflection.

## PWA Shell

### `public/manifest.json`

Standard Web App Manifest: `name`/`short_name` "Sword", `start_url: "/"`,
`display: "standalone"`, `theme_color`/`background_color` matching the
app's existing navbar color, and an `icons` array pointing at 192px and
512px PNGs generated from the existing `public/images/logo.png`.

### Service worker (`public/sw.js`, registered from `resources/js/app.js`)

Two responsibilities, kept separate:

1. **App shell caching** (install/activate): precache the built
   JS/CSS bundle output, `manifest.json`, icons, and the Offline Reader
   page's own assets, via the Cache API — standard "cache on install"
   PWA boilerplate.
2. **Navigation fallback** (fetch handler): for `request.mode ===
   'navigate'` requests, try the network first; on failure, respond
   with the cached Offline Reader page instead of the browser's
   default offline error. This is what makes "opening the installed
   app with no signal" land you on the Offline Reader automatically,
   per your requirement — it's a single fallback target, not a
   per-route offline variant of every page.

The service worker does **not** intercept or cache any API/data
requests — all Bible text and personal content caching goes through
the explicit IndexedDB bundle sync, not opportunistic HTTP caching.
Keeping these two caching mechanisms (Cache API for static shell,
IndexedDB for data) separate avoids the classic PWA foot-gun of stale
API responses silently served from an HTTP cache.

## Client-Side Architecture

### IndexedDB (`resources/js/offline/db.js`)

One database, `sword-offline`, with object stores: `verses` (keyed by
`[translation_id, chapter_id, number]`), `chapters`, `translations`,
`verseComments` (keyed by `[chapter_id, verse_number]`),
`chapterComments` (keyed by `chapter_id`), `highlights` (keyed by
`[chapter_id, verse_number]`), `prayers`, and `outbox` (keyed by the
client-generated UUID).

### Bundle sync (`resources/js/offline/bundle-sync.js`)

- Runs once immediately after the user flips on Offline Mode (profile
  page AJAX call triggers it), and again on every app load while
  `offline_enabled` is true and `navigator.onLine` is true.
- Fetches `/api/offline/bundle`, clears and repopulates the read-side
  IndexedDB stores (simple full replace, not a diff).

### Outbox / sync manager (`resources/js/offline/sync-manager.js`)

- Exposes `queue(type, payload)` — used by the Offline Reader's forms
  (and nowhere else; the normal online pages keep POSTing directly as
  they do today) when `!navigator.onLine`. Writes a row to the
  `outbox` store with a generated UUID, `type`, `payload`, and
  `created_at`.
- On `window.addEventListener('online', ...)` and once on app load (if
  already online), walks the outbox **in `created_at` order**, POSTing
  each item to its corresponding existing endpoint (with
  `client_uuid`/explicit highlight `color` included per the server-side
  changes above). Removes an item from the outbox on a successful
  response.
- Ordering matters for the digest case: as long as comments/prayers
  queued in the same offline session drain before a digest reflection
  queued after them, the digest's server-side `fetchWeeklyData()` sees
  them.

## Error Handling

- A queued item that fails to send because of a genuine validation
  error (422) — as opposed to a network failure — stays in the outbox
  and is surfaced in the sync-status banner as "N items failed to
  sync," with a way to view/discard the specific failed item. It is
  not silently retried in a tight loop.
- A network failure during drain (e.g., connection drops mid-sync)
  simply stops the walk; the remaining items stay queued for the next
  `online` event.
- Turning off Offline Mode while items are still queued in the outbox
  warns the user ("N items haven't synced yet — turning off Offline
  Mode will discard them") before clearing IndexedDB.

## Testing Plan

PHPUnit feature tests:
- `tests/Feature/OfflineBundleControllerTest.php`: bundle only returns
  the authenticated user's own comments/prayers/highlights, never
  another user's; returns all 5 translations' verse text.
- `client_uuid` dedupe: posting the same `client_uuid` twice to
  `commentary.store`, `prayers.store`, and `accountability.store` each
  creates exactly one row.
- `VerseHighlightController::toggle` accepts `chapter_id`+`verse_number`
  as an alternative to `verse_id`, and an explicit `color` produces
  that exact state regardless of prior state (not a toggle-flip).
- `SharedDigestController::store` with an explicit `week_start` builds
  the snapshot for that week, not the current week.
- `offline_enabled` toggle endpoint updates the flag and clearing it
  doesn't delete any server-side data (only a client-side concern).

Manual testing (service worker / IndexedDB aren't practically
unit-testable): Chrome DevTools → Application tab, offline throttling,
to verify the install prompt appears, the Offline Reader loads with no
network, highlighting/commenting/praying offline queues correctly, and
reconnecting drains the outbox in order.
