@extends('base.layout')

@section('title', 'Home')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 mb-xl-0">
    <div>
        <h3 class="font-weight-bold mb-2" style="color: var(--sword-navy);">Welcome, {{ explode(' ', trim(Auth::user()->name))[0] }}!</h3>
        <p class="page-subtitle mb-0">
            @if($lastLogin)
                Last login: {{ $lastLogin->logged_in_at->diffForHumans() }}
            @endif
        </p>
    </div>
    <div class="flex-shrink-0 d-sm-none">
        <a href="{{ route('translations.index') }}" class="btn btn-sm" style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-weight: 600; font-size: 0.82rem;">
            <i class="mdi mdi-book-open-page-variant"></i> Read
        </a>
    </div>
</div>

<div class="row">
    {{-- Scripture card --}}
    <div class="col-lg-2 grid-margin stretch-card">
        <div class="card" style="border-top: 3px solid var(--sword-gold); background: linear-gradient(160deg, #fff 70%, rgba(201,168,76,0.05) 100%);">
            <div class="card-body text-center py-4">
                <p class="mb-2 text-uppercase font-weight-bold" style="font-size: 0.7rem; letter-spacing: 0.08em; color: var(--sword-gold);">Eph. 6:17</p>
                <p class="mb-0" style="font-size: 0.82rem; line-height: 1.5; color: #4b5563; font-style: italic;">
                    Take the helmet of salvation and the sword of the Spirit, which is the word of God.
                </p>
            </div>
        </div>
    </div>

    {{-- Prayer Entries --}}
    <div class="col-6 col-lg-2 grid-margin stretch-card">
        <a href="{{ route('prayers.index') }}" class="card text-decoration-none dash-stat-card">
            <div class="card-body text-center py-4">
                <i class="mdi mdi-heart mdi-36px mb-2" style="color: var(--sword-gold);"></i>
                <h2 class="font-weight-bold mb-1" style="color: var(--sword-navy);">{{ $prayerCount }}</h2>
                <p class="mb-0 text-uppercase font-weight-bold" style="font-size: 0.7rem; letter-spacing: 0.08em; color: #9ca3af;">Prayer Entries</p>
            </div>
        </a>
    </div>

    {{-- Commentary Entries --}}
    <div class="col-6 col-lg-2 grid-margin stretch-card">
        <a href="{{ route('commentary.index') }}" class="card text-decoration-none dash-stat-card">
            <div class="card-body text-center py-4">
                <i class="mdi mdi-file-document-outline mdi-36px mb-2" style="color: var(--sword-gold);"></i>
                <h2 class="font-weight-bold mb-1" style="color: var(--sword-navy);">{{ $commentaryCount }}</h2>
                <p class="mb-0 text-uppercase font-weight-bold" style="font-size: 0.7rem; letter-spacing: 0.08em; color: #9ca3af;">Commentary Entries</p>
            </div>
        </a>
    </div>

    {{-- Verses Memorized --}}
    <div class="col-6 col-lg-2 grid-margin stretch-card">
        <a href="{{ route('memory.index') }}" class="card text-decoration-none dash-stat-card">
            <div class="card-body text-center py-4">
                <i class="mdi mdi-brain mdi-36px mb-2" style="color: var(--sword-gold);"></i>
                <h2 class="font-weight-bold mb-1" style="color: var(--sword-navy);">0</h2>
                <p class="mb-0 text-uppercase font-weight-bold" style="font-size: 0.7rem; letter-spacing: 0.08em; color: #9ca3af;">Verses Memorized</p>
            </div>
        </a>
    </div>

    {{-- Topics Studied --}}
    <div class="col-6 col-lg-2 grid-margin stretch-card">
        <a href="{{ route('topics.index') }}" class="card text-decoration-none dash-stat-card">
            <div class="card-body text-center py-4">
                <i class="mdi mdi-tag-multiple mdi-36px mb-2" style="color: var(--sword-gold);"></i>
                <h2 class="font-weight-bold mb-1" style="color: var(--sword-navy);">{{ $topicCount }}</h2>
                <p class="mb-0 text-uppercase font-weight-bold" style="font-size: 0.7rem; letter-spacing: 0.08em; color: #9ca3af;">Study Topics</p>
            </div>
        </a>
    </div>

    {{-- Logo card --}}
    <div class="col-lg-2 grid-margin stretch-card">
        <div class="card" style="background: var(--sword-navy);">
            <div class="card-body d-flex align-items-center justify-content-center py-4">
                <img src="/images/logo.png" alt="logo" style="max-width: 100%; max-height: 80px; object-fit: contain;"/>
            </div>
        </div>
    </div>
</div>

{{-- Active Memory Verse --}}
@if($activeMemory && $activeMemory->verses->isNotEmpty())
<div class="row mb-4">
    <div class="col-12">
        <div class="card" style="border-top: 3px solid var(--sword-gold); background: linear-gradient(160deg, #fff 70%, rgba(201,168,76,0.05) 100%);">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <p class="mb-0" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--sword-gold); font-weight: 700;">
                        <i class="mdi mdi-brain me-1"></i>Memory Verse{{ $activeMemory->verses->count() > 1 ? 's' : '' }}
                        @if($activeMemory->title)
                            &mdash; {{ $activeMemory->title }}
                        @endif
                    </p>
                    <a href="{{ route('memory.index') }}" class="btn btn-sm flex-shrink-0" style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-weight: 600; font-size: 0.78rem; white-space: nowrap;">
                        All Memory Verses <i class="mdi mdi-arrow-right"></i>
                    </a>
                </div>
                @foreach($activeMemory->verses->groupBy(fn($v) => $v->chapter->book->name . ' ' . $v->chapter->number) as $ref => $grouped)
                <div class="{{ !$loop->last ? 'mb-3' : '' }}">
                    <p class="mb-1 fw-bold" style="font-size: 0.78rem; color: var(--sword-navy);">
                        {{ $ref }}:{{ implode(',', $grouped->pluck('number')->all()) }}
                    </p>
                    <p class="mb-0" style="font-size: 0.88rem; color: #374151; line-height: 1.65; font-style: italic;">
                        @foreach($grouped->sortBy('number') as $verse)
                            <sup style="font-style: normal; font-weight: 700; color: var(--sword-gold); font-size: 0.62rem;">{{ $verse->number }}</sup>{{ $verse->text }}{{ $loop->last ? '' : ' ' }}
                        @endforeach
                    </p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

{{-- Weekly Digest Preview --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card" style="border-top: 3px solid var(--sword-gold); background: linear-gradient(135deg, rgba(14,22,40,0.02) 0%, rgba(201,168,76,0.04) 100%);">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <p class="mb-0" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--sword-gold); font-weight: 700;">This Week's Digest</p>
                    <a href="{{ route('digest.weekly') }}" class="btn btn-sm flex-shrink-0" style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-weight: 600; font-size: 0.78rem; white-space: nowrap;">
                        View Full Digest <i class="mdi mdi-arrow-right"></i>
                    </a>
                </div>
                <div class="d-flex align-items-center gap-4 flex-wrap">
                    <p class="mb-0" style="font-size: 0.78rem; color: #9ca3af;">{{ now()->startOfWeek()->format('M j') }} – {{ now()->endOfWeek()->format('M j') }}</p>
                    <div class="text-center">
                        <div class="fw-bold" style="color: var(--sword-navy); font-size: 1.05rem;">{{ $digestStats['days'] }}</div>
                        <div style="color: #9ca3af; font-size: 0.62rem; text-transform: uppercase; letter-spacing: 0.06em;">Days</div>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold" style="color: var(--sword-navy); font-size: 1.05rem;">{{ $digestStats['chapters'] }}</div>
                        <div style="color: #9ca3af; font-size: 0.62rem; text-transform: uppercase; letter-spacing: 0.06em;">Chapters</div>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold" style="color: var(--sword-navy); font-size: 1.05rem;">{{ $digestStats['prayers'] }}</div>
                        <div style="color: #9ca3af; font-size: 0.62rem; text-transform: uppercase; letter-spacing: 0.06em;">Prayers</div>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold" style="color: var(--sword-navy); font-size: 1.05rem;">{{ $digestStats['notes'] }}</div>
                        <div style="color: #9ca3af; font-size: 0.62rem; text-transform: uppercase; letter-spacing: 0.06em;">Notes</div>
                    </div>
                    @if($digestPastNote)
                    <div class="d-none d-xl-flex align-items-center ps-4" style="border-left: 1px solid rgba(201,168,76,0.3); max-width: 300px;">
                        <div>
                            <p class="mb-1" style="font-size: 0.62rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--sword-gold);">From your notes, one year ago</p>
                            <p class="mb-0" style="font-size: 0.78rem; color: #4b5563; line-height: 1.4; font-style: italic;">"{{ Str::limit($digestPastNote->comment, 80) }}"</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Bible Overview --}}
    <div class="col-12 col-lg-6 grid-margin grid-margin-md-0 stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0" style="color: var(--sword-navy);">Bible Overview</h4>
                    <span>
                        <span class="font-weight-bold" style="color: var(--sword-gold); font-size: 1.1rem;">{{ $translationCount }}</span>
                        <span class="ms-1" style="color: #6b7280; font-size: 0.9rem;">Translations</span>
                    </span>
                </div>
                <div class="row mt-4">
                    <div class="col-4 text-center">
                        <div class="rounded p-3" style="border: 1px solid rgba(14,22,40,0.12); background: rgba(14,22,40,0.02);">
                            <h2 class="font-weight-bold mb-1" style="color: var(--sword-navy); font-size: clamp(1.1rem, 5vw, 1.75rem); white-space: nowrap;">{{ $bookCount }}</h2>
                            <p class="mb-0" style="color: #9ca3af; font-size: 0.8rem;">Books</p>
                        </div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="rounded p-3" style="border: 1px solid rgba(14,22,40,0.12); background: rgba(14,22,40,0.02);">
                            <h2 class="font-weight-bold mb-1" style="color: var(--sword-navy); font-size: clamp(1.1rem, 5vw, 1.75rem); white-space: nowrap;">{{ number_format($chapterCount) }}</h2>
                            <p class="mb-0" style="color: #9ca3af; font-size: 0.8rem;">Chapters</p>
                        </div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="rounded p-3" style="border: 1px solid rgba(14,22,40,0.12); background: rgba(14,22,40,0.02);">
                            <h2 class="font-weight-bold mb-1" style="color: var(--sword-navy); font-size: clamp(1.1rem, 5vw, 1.75rem); white-space: nowrap;">{{ number_format($verseCount) }}</h2>
                            <p class="mb-0" style="color: #9ca3af; font-size: 0.8rem;">Verses</p>
                        </div>
                    </div>
                </div>
                @php
                    $readPct = $totalBibleChapters > 0 ? round(($chaptersRead / $totalBibleChapters) * 100, 1) : 0;
                    $commentPct = $commentaryCount > 0 ? round(($chapterCommentCount / $commentaryCount) * 100) : 0;
                    $highlightTypes = [
                        'yellow' => ['label' => 'Important',  'color' => '#fbbf24'],
                        'blue'   => ['label' => 'Prophecy',   'color' => '#60a5fa'],
                        'green'  => ['label' => 'Promise',    'color' => '#34d399'],
                        'red'    => ['label' => 'Command',    'color' => '#f87171'],
                    ];
                @endphp

                {{-- Bible Reading Progress --}}
                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="mb-0" style="color: #9ca3af; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em;">Bible Read</h6>
                        <span style="font-size: 0.78rem; color: var(--sword-navy); font-weight: 600;">{{ number_format($chaptersRead) }} <span style="color:#9ca3af;font-weight:400;">/ {{ number_format($totalBibleChapters) }} chapters</span></span>
                    </div>
                    <div class="progress mb-1" style="height: 8px; background: rgba(14,22,40,0.08); border-radius: 4px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $readPct }}%; background: var(--sword-gold); border-radius: 4px;"></div>
                    </div>
                    <div class="text-end" style="font-size: 0.7rem; color: #9ca3af;">{{ $readPct }}% complete</div>
                </div>

                {{-- Highlighted Verses --}}
                <div class="mt-3">
                    <h6 class="mb-2" style="color: #9ca3af; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em;">Highlighted Verses</h6>
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach($highlightTypes as $color => $meta)
                        <div class="d-flex align-items-center gap-1 px-2 py-1 rounded" style="background: rgba(14,22,40,0.04); border: 1px solid rgba(14,22,40,0.08);">
                            <div style="width:10px;height:10px;border-radius:50%;background:{{ $meta['color'] }};flex-shrink:0;"></div>
                            <span style="font-size:0.72rem;color:#6b7280;">{{ $meta['label'] }}</span>
                            <span style="font-size:0.78rem;font-weight:600;color:var(--sword-navy);">{{ $highlightsByColor->get($color, 0) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Combined Comments --}}
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="mb-0" style="color: #9ca3af; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em;">Comments</h6>
                        <span style="font-size: 0.78rem; color: var(--sword-navy); font-weight: 600;">{{ $commentaryCount }} <span style="color:#9ca3af;font-weight:400;">total</span></span>
                    </div>
                    @php
                        $versePct = $commentaryCount > 0 ? round(($verseCommentCount / $commentaryCount) * 100) : 0;
                    @endphp
                    <div class="progress mb-1" style="height: 8px; background: rgba(14,22,40,0.08); border-radius: 4px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $commentPct }}%; background: var(--sword-navy); border-radius: 0;"></div>
                        <div class="progress-bar" role="progressbar" style="width: {{ $versePct }}%; background: var(--sword-gold); border-radius: 0;"></div>
                    </div>
                    <div class="d-flex justify-content-between" style="font-size: 0.7rem; color: #9ca3af;">
                        <span><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--sword-navy);margin-right:3px;vertical-align:middle;"></span>{{ $chapterCommentCount }} chapter</span>
                        <span>{{ $verseCommentCount }} verse<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--sword-gold);margin-left:3px;vertical-align:middle;"></span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Prayer Journal --}}
    <div class="col-12 col-lg-6 grid-margin grid-margin-md-0 stretch-card mt-4 mt-lg-0">
        <div class="card">
            <div class="card-body">
                <div class="d-lg-flex align-items-center justify-content-between mb-4">
                    <h4 class="card-title mb-0" style="color: var(--sword-navy);">Prayer Journal</h4>
                    <p class="mb-0" style="color: #6b7280; font-size: 0.9rem;">
                        <span class="font-weight-bold" style="color: var(--sword-gold);">{{ $recentPrayers }}</span> prayers this week
                    </p>
                </div>
                @php
                $prayerTypeIcons = [
                    'Adoration'    => 'star-circle',
                    'Confession'   => 'shield-alert',
                    'Thanksgiving' => 'gift',
                    'Supplication' => 'human-handsup',
                ];
                @endphp
                @if($prayersByType->count() > 0)
                    <div class="row">
                        @foreach($prayersByType as $prayer)
                            @php $icon = $prayerTypeIcons[$prayer->type->name ?? ''] ?? 'heart'; @endphp
                            <div class="col-6 mb-3">
                                <div class="rounded p-3 h-100" style="border: 1px solid rgba(14,22,40,0.1); background: rgba(14,22,40,0.02);">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h4 class="font-weight-bold mb-0" style="color: var(--sword-navy);">{{ $prayer->count }}</h4>
                                        <i class="mdi mdi-{{ $icon }} mdi-24px" style="color: var(--sword-gold);"></i>
                                    </div>
                                    <p class="mb-0 mt-2" style="color: #9ca3af; font-size: 0.82rem;">{{ $prayer->type->name ?? 'Unknown' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="mdi mdi-heart mdi-48px" style="color: rgba(14,22,40,0.15);"></i>
                        <p class="mt-2" style="color: #9ca3af;">No prayers recorded yet</p>
                        <a href="{{ route('prayers.create') }}" class="btn btn-sm" style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-weight: 600; letter-spacing: 0.03em;">Add Your First Prayer</a>
                    </div>
                @endif
                <div class="mt-3 pt-3" style="border-top: 1px solid rgba(14,22,40,0.08);">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="color: #9ca3af; font-size: 0.9rem;">Total Prayers</span>
                        <span class="font-weight-bold" style="color: var(--sword-navy);">{{ $prayerCount }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Reading Activity Heatmap --}}
<div class="row mt-4">
    <div class="col-12">
        <div class="card" style="border-top: 3px solid var(--sword-gold);">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <h4 class="card-title mb-0" style="color: var(--sword-navy);">Reading Activity</h4>
                    <div class="d-flex gap-4">
                        <div class="text-center">
                            <div class="font-weight-bold" style="color: var(--sword-navy); font-size: 1.1rem;">
                                <i class="mdi mdi-fire" style="color: var(--sword-gold);"></i> {{ $currentStreak }}
                            </div>
                            <div style="color: #9ca3af; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.06em;">Current Streak</div>
                        </div>
                        <div class="text-center">
                            <div class="font-weight-bold" style="color: var(--sword-navy); font-size: 1.1rem;">
                                <i class="mdi mdi-trophy" style="color: var(--sword-gold);"></i> {{ $longestStreak }}
                            </div>
                            <div style="color: #9ca3af; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.06em;">Best Streak</div>
                        </div>
                        <div class="text-center">
                            <div class="font-weight-bold" style="color: var(--sword-navy); font-size: 1.1rem;">
                                @if($todayReadCount > 0)
                                    <i class="mdi mdi-check-circle" style="color: var(--sword-gold);"></i> {{ $todayReadCount }}
                                @else
                                    <i class="mdi mdi-circle-outline" style="color: #9ca3af;"></i> 0
                                @endif
                            </div>
                            <div style="color: #9ca3af; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.06em;">Today</div>
                        </div>
                    </div>
                </div>
                <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                    <div style="min-width: max-content;">
                        <div id="heatmap-months" style="position: relative; height: 16px; margin-bottom: 4px;"></div>
                        <div id="reading-heatmap" style="display: grid; grid-auto-rows: 12px; gap: 2px;"></div>
                        <div class="d-flex align-items-center gap-2 mt-2" style="font-size: 0.75rem; color: #9ca3af;">
                            <span>Less</span>
                            <div style="width:12px;height:12px;border-radius:2px;background:rgba(201,168,76,0.08);display:inline-block;"></div>
                            <div style="width:12px;height:12px;border-radius:2px;background:rgba(201,168,76,0.3);display:inline-block;"></div>
                            <div style="width:12px;height:12px;border-radius:2px;background:rgba(201,168,76,0.5);display:inline-block;"></div>
                            <div style="width:12px;height:12px;border-radius:2px;background:rgba(201,168,76,0.7);display:inline-block;"></div>
                            <div style="width:12px;height:12px;border-radius:2px;background:#c9a84c;display:inline-block;"></div>
                            <span>More</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Mobile-only search card --}}
<div class="row mt-4 d-lg-none">
    <div class="col-12">
        <div class="card" style="border-top: 3px solid var(--sword-gold);">
            <div class="card-body py-4">
                <p class="mb-3 text-uppercase font-weight-bold text-center" style="font-size: 0.7rem; letter-spacing: 0.1em; color: var(--sword-gold);">Search God's Word</p>
                <form method="GET" action="{{ route('search.index') }}">
                    <div class="input-group">
                        <input
                            type="text"
                            name="q"
                            class="form-control"
                            placeholder="Search verses…"
                            style="border-color: rgba(14,22,40,0.2);"
                        >
                        <button class="btn" type="submit" style="background: var(--sword-navy); color: var(--sword-gold); border-color: var(--sword-navy);">
                            <i class="mdi mdi-magnify"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.dash-stat-card {
    border-top: 3px solid transparent;
    transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;
}
.dash-stat-card:hover {
    border-top-color: var(--sword-gold);
    box-shadow: 0 4px 20px rgba(14,22,40,0.1) !important;
    transform: translateY(-2px);
}
</style>

@endsection

@push('js')
<script>
(function () {
    const readData = @json($readsByDate);
    const todayDate = new Date();
    todayDate.setHours(0, 0, 0, 0);

    const year = todayDate.getFullYear();
    const jan1  = new Date(year, 0, 1);
    const dec31 = new Date(year, 11, 31);

    // Align start to the Sunday on or before Jan 1
    const startDate = new Date(jan1);
    startDate.setDate(jan1.getDate() - jan1.getDay());

    // How many week columns to cover through Dec 31
    const daySpan = Math.floor((dec31 - startDate) / 86400000) + 1;
    const WEEKS = Math.ceil(daySpan / 7);

    const CELL = 12;
    const GAP  = 2;

    const grid     = document.getElementById('reading-heatmap');
    const monthBar = document.getElementById('heatmap-months');
    const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    grid.style.gridTemplateColumns = 'repeat(' + WEEKS + ', ' + CELL + 'px)';

    function toKey(d) {
        return d.getFullYear() + '-' +
            String(d.getMonth() + 1).padStart(2, '0') + '-' +
            String(d.getDate()).padStart(2, '0');
    }

    function getColor(n) {
        if (n <= 0) return 'rgba(201,168,76,0.08)';
        if (n === 1) return 'rgba(201,168,76,0.3)';
        if (n === 2) return 'rgba(201,168,76,0.5)';
        if (n === 3) return 'rgba(201,168,76,0.7)';
        return '#c9a84c';
    }

    // Month labels — only emit when we cross into a new month within the current year
    let lastMonth = -1;
    for (let w = 0; w < WEEKS; w++) {
        const d = new Date(startDate);
        d.setDate(startDate.getDate() + w * 7);
        const m = d.getMonth();
        if (m !== lastMonth && d.getFullYear() === year) {
            lastMonth = m;
            const span = document.createElement('span');
            span.textContent = monthNames[m];
            span.style.cssText = 'position:absolute;left:' + (w * (CELL + GAP)) + 'px;font-size:10px;color:#9ca3af;white-space:nowrap;';
            monthBar.appendChild(span);
        }
    }

    // Grid cells: columns = weeks, rows = days (0=Sun … 6=Sat)
    for (let w = 0; w < WEEKS; w++) {
        for (let d = 0; d < 7; d++) {
            const date = new Date(startDate);
            date.setDate(startDate.getDate() + w * 7 + d);

            const div = document.createElement('div');
            div.style.width  = CELL + 'px';
            div.style.height = CELL + 'px';
            div.style.borderRadius = '2px';
            div.style.gridColumn = (w + 1).toString();
            div.style.gridRow    = (d + 1).toString();

            const inYear = date >= jan1 && date <= dec31;

            if (!inYear || date > todayDate) {
                div.style.background = 'transparent';
            } else {
                const key   = toKey(date);
                const count = readData[key] || 0;
                div.style.background = getColor(count);
                if (date.getTime() === todayDate.getTime()) {
                    div.style.outline = '1.5px solid var(--sword-gold)';
                    div.style.outlineOffset = '1px';
                }
                div.title = monthNames[date.getMonth()] + ' ' + date.getDate() +
                    ' — ' + (count > 0 ? count + ' chapter' + (count > 1 ? 's' : '') : 'no reading');
            }

            grid.appendChild(div);
        }
    }
})();
</script>
@endpush
