@extends('base.layout')

@section('title', "What's New")

@section('content')

<div class="row mb-4">
    <div class="col-12">
        <h3 class="font-weight-bold mb-1" style="color: var(--sword-navy);">What's New</h3>
        <p class="page-subtitle mb-0">A record of updates and improvements to Sword App</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 col-12">

        {{-- ── v1.8.0 ──────────────────────────────────────────────── --}}
        <div class="changelog-entry mb-4">
            <div class="changelog-header d-flex align-items-center gap-3 mb-3">
                <span class="changelog-version">v1.8</span>
                <div>
                    <div class="changelog-title">Book Studies, Digest Comments & Polish</div>
                    <div class="changelog-date">May 2026</div>
                </div>
                <span class="badge changelog-badge-latest ms-auto">Latest</span>
            </div>
            <div class="card changelog-card">
                <div class="card-body">
                    <ul class="changelog-list">
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Book Studies — track active and completed book-by-book studies from the Topics page, with progress indicators and a "Study Complete" button on the book study view
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Guest Comments on Shared Digests — anyone with a share link can now leave a named or anonymous comment; comments notify the digest owner and appear on the digest detail view
                        </li>
                        <li>
                            <span class="changelog-tag tag-improved">Improved</span>
                            Digest commentary now shows the actual verse text beneath each verse note, making it easier to read without jumping to the Bible
                        </li>
                        <li>
                            <span class="changelog-tag tag-improved">Improved</span>
                            Long comments and prayers in the digest are now collapsible with More / Less toggles instead of being hard-truncated
                        </li>
                        <li>
                            <span class="changelog-tag tag-improved">Improved</span>
                            Memory verse section in digest now shows a "Still working on…" label above in-progress sets
                        </li>
                        <li>
                            <span class="changelog-tag tag-improved">Improved</span>
                            Dashboard now shows a total memorized verse count alongside active memory cards
                        </li>
                        <li>
                            <span class="changelog-tag tag-improved">Improved</span>
                            Section Title input in the verse commentary modal now auto-title-cases as you type
                        </li>
                        <li>
                            <span class="changelog-tag tag-improved">Improved</span>
                            Favorite button moved into the Highlight section header for a cleaner modal layout
                        </li>
                        <li>
                            <span class="changelog-tag tag-fix">Fix</span>
                            Prayers now sort newest-first on the prayers page
                        </li>
                        <li>
                            <span class="changelog-tag tag-fix">Fix</span>
                            HTML entities in commentary section titles decoded correctly when loading the verse modal
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- ── v1.7.0 ──────────────────────────────────────────────── --}}
        <div class="changelog-entry mb-4">
            <div class="changelog-header d-flex align-items-center gap-3 mb-3">
                <span class="changelog-version">v1.7</span>
                <div>
                    <div class="changelog-title">Scripture Memory Quiz</div>
                    <div class="changelog-date">May 2026</div>
                </div>
            </div>
            <div class="card changelog-card">
                <div class="card-body">
                    <ul class="changelog-list">
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Scripture Memory Flashcard Quiz — each active memory card now has a Quiz button that opens an interactive modal to drill your verses
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Quiz shows the verse reference, you type from memory, then reveal the correct text — the system automatically scores how closely your answer matches
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Mastery badge on each memory card shows your average quiz score across all verses in that set, updating live after each attempt
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Quiz session summary shows per-verse similarity scores and all-time mastery percentages
                        </li>
                        <li>
                            <span class="changelog-tag tag-improved">Improved</span>
                            Verse text on memory cards is hidden while the quiz modal is open so you can't peek at the answer
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- ── v1.6.0 ──────────────────────────────────────────────── --}}
        <div class="changelog-entry mb-4">
            <div class="changelog-header d-flex align-items-center gap-3 mb-3">
                <span class="changelog-version">v1.6</span>
                <div>
                    <div class="changelog-title">Digest & Memory Improvements</div>
                    <div class="changelog-date">May 2026</div>
                </div>
            </div>
            <div class="card changelog-card">
                <div class="card-body">
                    <ul class="changelog-list">
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Memory page: click any verse reference on a completed goal to see a modal with the full verse text
                        </li>
                        <li>
                            <span class="changelog-tag tag-fix">Fix</span>
                            Memory page: days elapsed on completed goals now rounds to the nearest whole day instead of truncating
                        </li>
                        <li>
                            <span class="changelog-tag tag-improved">Improved</span>
                            Weekly digest memory section now shows the full verse reference and text for any sets completed that week
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Weekly digest now calls out newly started memory goals with their verse references
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Commentary snippets in the digest now have a "More" link to expand the full text inline
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Digest form: optional description field on Fruits of the Spirit to explain why those areas are on your heart
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Digest form: optional description field on Idols to Surrender for personal reflection
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Digest form: Sermon Notes section to capture theme, takeaways, and questions from the week's sermon
                        </li>
                        <li>
                            <span class="changelog-tag tag-improved">Improved</span>
                            Digest form redesigned as a single centered column — cleaner to fill out on any screen size
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- ── v1.5.0 ──────────────────────────────────────────────── --}}
        <div class="changelog-entry mb-4">
            <div class="changelog-header d-flex align-items-center gap-3 mb-3">
                <span class="changelog-version">v1.5</span>
                <div>
                    <div class="changelog-title">Multi-User Support</div>
                    <div class="changelog-date">May 2026</div>
                </div>
            </div>
            <div class="card changelog-card">
                <div class="card-body">
                    <ul class="changelog-list">
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Verse highlights, favorites, and section formatting are now per-user — changes you make are only visible to you
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Book study notes (author, timeframe, description, history, themes) are now per-user
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Topics and topic notes are now per-user — each user builds their own set of study topics independently
                        </li>
                        <li>
                            <span class="changelog-tag tag-improved">Improved</span>
                            Dashboard greeting now shows your first name
                        </li>
                        <li>
                            <span class="changelog-tag tag-fix">Fix</span>
                            Dashboard verse count now shows the correct total Bible verse count for all users, not 0 for new accounts
                        </li>
                        <li>
                            <span class="changelog-tag tag-fix">Fix</span>
                            Dashboard topic count now reflects only your own topics
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- ── v1.4.0 ──────────────────────────────────────────────── --}}
        <div class="changelog-entry mb-4">
            <div class="changelog-header d-flex align-items-center gap-3 mb-3">
                <span class="changelog-version">v1.4</span>
                <div>
                    <div class="changelog-title">Digest History, Reading Log & Dashboard</div>
                    <div class="changelog-date">May 2026</div>
                </div>
            </div>
            <div class="card changelog-card">
                <div class="card-body">
                    <ul class="changelog-list">
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Digest history page — browse all past weekly digests, view them in-app, and share any saved digest at any time
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            "Complete Digest" replaces "Share" — save your weekly reflection privately or generate a shareable link; digests are now preserved even if never shared
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Saturday reminder notification when the week's digest hasn't been filled out yet
                        </li>
                        <li>
                            <span class="changelog-tag tag-improved">Improved</span>
                            Reading activity heatmap now spans Jan–Dec of the current year instead of a rolling 52-week window
                        </li>
                        <li>
                            <span class="changelog-tag tag-fix">Fix</span>
                            Current streak now correctly shows days even when today hasn't been read yet; longest streak calculation fixed
                        </li>
                        <li>
                            <span class="changelog-tag tag-improved">Improved</span>
                            "Mark as Read" now logs every read session as a separate record, building a full reading history rather than overwriting
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Active memory verse displayed on the dashboard for quick reference
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Study page link appears under the book title on the Read page
                        </li>
                        <li>
                            <span class="changelog-tag tag-fix">Fix</span>
                            Changing Bible version on the Read page no longer resets the current chapter
                        </li>
                        <li>
                            <span class="changelog-tag tag-improved">Improved</span>
                            Prayer counts in the digest and weekly stats now reflect unique prayer sessions per day, not per type
                        </li>
                        <li>
                            <span class="changelog-tag tag-improved">Improved</span>
                            Notifications job scheduled to run automatically daily at 8am
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- ── v1.3.0 ──────────────────────────────────────────────── --}}
        <div class="changelog-entry mb-4">
            <div class="changelog-header d-flex align-items-center gap-3 mb-3">
                <span class="changelog-version">v1.3</span>
                <div>
                    <div class="changelog-title">In-App Notifications</div>
                    <div class="changelog-date">April 2026</div>
                </div>
            </div>
            <div class="card changelog-card">
                <div class="card-body">
                    <ul class="changelog-list">
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Notification bell in the navbar shows a live unread count that disappears when everything is read
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Notifications page lists all updates with read/unread state and direct links to relevant sections
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Reading streak milestones (7, 14, 30, 100 days) generate automatic congratulations
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Prayer reminder when no prayers have been logged this week
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Alert when a memory verse set is expiring within 3 days
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            GenerateNotifications job can be run manually or scheduled; includes a static announce() helper for app-wide broadcasts
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- ── v1.2.0 ──────────────────────────────────────────────── --}}
        <div class="changelog-entry mb-4">
            <div class="changelog-header d-flex align-items-center gap-3 mb-3">
                <span class="changelog-version">v1.2</span>
                <div>
                    <div class="changelog-title">Verse Keywords & ESV</div>
                    <div class="changelog-date">April 2026</div>
                </div>
            </div>
            <div class="card changelog-card">
                <div class="card-body">
                    <ul class="changelog-list">
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            ESV translation imported and available across all reading and study views
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            DetermineKeyWords job extracts meaningful keywords from every verse, filtering common words — used for search and topic discovery
                        </li>
                        <li>
                            <span class="changelog-tag tag-improved">Improved</span>
                            Weekly digest updated with richer stats, shareable links, and configurable sections (chapters, prayers, commentary, memory, past notes)
                        </li>
                        <li>
                            <span class="changelog-tag tag-improved">Improved</span>
                            Read page and user profile enhancements
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- ── v1.1.0 ──────────────────────────────────────────────── --}}
        <div class="changelog-entry mb-4">
            <div class="changelog-header d-flex align-items-center gap-3 mb-3">
                <span class="changelog-version">v1.1</span>
                <div>
                    <div class="changelog-title">Highlights, Favorites & Sharing</div>
                    <div class="changelog-date">Early 2026</div>
                </div>
            </div>
            <div class="card changelog-card">
                <div class="card-body">
                    <ul class="changelog-list">
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Verse highlighting — mark passages for quick visual reference while reading
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Verse favorites — save verses to a personal collection
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Shareable weekly digest — generate a public link to your weekly Bible study summary to share with a group or mentor
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Book timeframes and author descriptions for historical context
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Text-to-voice reading on verse pages
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Reading activity heatmap on the dashboard — visualize your study patterns over the past year
                        </li>
                        <li>
                            <span class="changelog-tag tag-improved">Improved</span>
                            Dashboard redesigned with streak tracking, longest streak, and weekly digest preview
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Admin users page for managing accounts
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- ── v1.0.0 ──────────────────────────────────────────────── --}}
        <div class="changelog-entry mb-4">
            <div class="changelog-header d-flex align-items-center gap-3 mb-3">
                <span class="changelog-version">v1.0</span>
                <div>
                    <div class="changelog-title">Initial Release</div>
                    <div class="changelog-date">2025</div>
                </div>
            </div>
            <div class="card changelog-card">
                <div class="card-body">
                    <ul class="changelog-list">
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Multi-translation Bible reader (KJV, NIV, NLT, ESV) with chapter navigation and read-tracking
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Verse and chapter commentary — write personal notes anchored to specific passages
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Prayer journal with type categorization and date-based history
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Topics — create study topics and attach related verses from across translations
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Memory verse sets with start/end dates, verse selection, and completion tracking
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Full-text verse search across all loaded translations
                        </li>
                        <li>
                            <span class="changelog-tag tag-new">New</span>
                            Default translation preference saved to your profile
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>

    
</div>

<style>
.changelog-version {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--sword-gold);
    letter-spacing: -0.02em;
    line-height: 1;
    min-width: 3rem;
    font-variant-numeric: tabular-nums;
}
.changelog-title {
    font-weight: 700;
    color: var(--sword-navy);
    font-size: 0.97rem;
    line-height: 1.2;
}
.changelog-date {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 2px;
}
.changelog-badge-latest {
    font-size: 0.65rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    background: rgba(201,168,76,0.15);
    color: var(--sword-gold);
    border: 1px solid rgba(201,168,76,0.35);
    font-weight: 700;
    padding: 3px 8px;
}
.changelog-card {
    border-top: 3px solid var(--sword-gold);
    border-radius: 8px;
}
.changelog-list {
    list-style: none;
    margin: 0;
    padding: 0;
}
.changelog-list li {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 7px 0;
    font-size: 0.88rem;
    color: #374151;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}
.changelog-list li:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.changelog-list li:first-child {
    padding-top: 0;
}
.changelog-tag {
    flex-shrink: 0;
    display: inline-block;
    min-width: 4.8rem;
    text-align: center;
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    padding: 2px 7px;
    border-radius: 3px;
    line-height: 1.6;
    margin-top: 2px;
}
.tag-new      { background: rgba(70,77,238,0.1);  color: #464dee; }
.tag-improved { background: rgba(201,168,76,0.15); color: #a07820; }
.tag-fix      { background: rgba(22,163,74,0.1);   color: #16a34a; }
</style>

@endsection
