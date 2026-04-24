@extends('base.layout')

@section('title', 'Home')

@section('content')

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="font-weight-bold mb-2" style="color: var(--sword-navy);">Hi, welcome back!</h3>
                <p class="page-subtitle mb-0">
                    @if($lastLogin)
                        Last login: {{ $lastLogin->logged_in_at->diffForHumans() }}
                    @else
                        Welcome!
                    @endif
                </p>
            </div>
        </div>
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
        <div class="card dash-stat-card">
            <div class="card-body text-center py-4">
                <i class="mdi mdi-brain mdi-36px mb-2" style="color: var(--sword-gold);"></i>
                <h2 class="font-weight-bold mb-1" style="color: var(--sword-navy);">0</h2>
                <p class="mb-0 text-uppercase font-weight-bold" style="font-size: 0.7rem; letter-spacing: 0.08em; color: #9ca3af;">Verses Memorized</p>
            </div>
        </div>
    </div>

    {{-- Topics Studied --}}
    <div class="col-6 col-lg-2 grid-margin stretch-card">
        <a href="{{ route('topics.index') }}" class="card text-decoration-none dash-stat-card">
            <div class="card-body text-center py-4">
                <i class="mdi mdi-tag-multiple mdi-36px mb-2" style="color: var(--sword-gold);"></i>
                <h2 class="font-weight-bold mb-1" style="color: var(--sword-navy);">{{ $topicCount }}</h2>
                <p class="mb-0 text-uppercase font-weight-bold" style="font-size: 0.7rem; letter-spacing: 0.08em; color: #9ca3af;">Topics Studied</p>
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

<div class="row">
    {{-- Bible Overview --}}
    <div class="col-sm-6 grid-margin grid-margin-md-0 stretch-card">
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
                <div class="mt-4">
                    <h6 class="mb-3" style="color: #9ca3af; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em;">Commentary Breakdown</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span style="color: #4b5563; font-size: 0.9rem;">Chapter Comments</span>
                        <span class="badge" style="background: var(--sword-navy); color: #fff;">{{ $chapterCommentCount }}</span>
                    </div>
                    <div class="progress mb-3" style="height: 6px; background: rgba(14,22,40,0.08);">
                        <div class="progress-bar" role="progressbar" style="width: {{ $commentaryCount > 0 ? ($chapterCommentCount / $commentaryCount) * 100 : 0 }}%; background: var(--sword-gold);"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span style="color: #4b5563; font-size: 0.9rem;">Verse Comments</span>
                        <span class="badge" style="background: var(--sword-navy); color: #fff;">{{ $verseCommentCount }}</span>
                    </div>
                    <div class="progress" style="height: 6px; background: rgba(14,22,40,0.08);">
                        <div class="progress-bar" role="progressbar" style="width: {{ $commentaryCount > 0 ? ($verseCommentCount / $commentaryCount) * 100 : 0 }}%; background: var(--sword-gold);"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Prayer Journal --}}
    <div class="col-sm-6 grid-margin grid-margin-md-0 stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-lg-flex align-items-center justify-content-between mb-4">
                    <h4 class="card-title mb-0" style="color: var(--sword-navy);">Prayer Journal</h4>
                    <p class="mb-0" style="color: #6b7280; font-size: 0.9rem;">
                        <span class="font-weight-bold" style="color: var(--sword-gold);">{{ $recentPrayers }}</span> prayers this week
                    </p>
                </div>
                @if($prayersByType->count() > 0)
                    <div class="row">
                        @foreach($prayersByType as $prayer)
                            <div class="col-6 mb-3">
                                <div class="rounded p-3 h-100" style="border: 1px solid rgba(14,22,40,0.1); background: rgba(14,22,40,0.02);">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h4 class="font-weight-bold mb-0" style="color: var(--sword-navy);">{{ $prayer->count }}</h4>
                                        <i class="mdi mdi-{{ ['heart', 'hand-heart', 'account-group', 'church', 'shield-cross'][$loop->index % 5] }} mdi-24px" style="color: var(--sword-gold);"></i>
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

{{-- Weekly Digest Preview --}}
<div class="row mt-4">
    <div class="col-12">
        <div class="card" style="border-top: 3px solid var(--sword-gold); background: linear-gradient(135deg, rgba(14,22,40,0.02) 0%, rgba(201,168,76,0.04) 100%);">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-4 flex-wrap">
                        <div>
                            <p class="mb-0" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--sword-gold); font-weight: 700;">This Week's Digest</p>
                            <p class="mb-0" style="font-size: 0.78rem; color: #9ca3af;">{{ now()->startOfWeek()->format('M j') }} – {{ now()->endOfWeek()->format('M j') }}</p>
                        </div>
                        <div class="d-flex gap-4">
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
                        </div>
                        @if($digestPastNote)
                        <div class="d-none d-xl-block ps-4" style="border-left: 1px solid rgba(201,168,76,0.3); max-width: 300px;">
                            <p class="mb-1" style="font-size: 0.62rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--sword-gold);">From your notes, one year ago</p>
                            <p class="mb-0" style="font-size: 0.78rem; color: #4b5563; line-height: 1.4; font-style: italic;">"{{ Str::limit($digestPastNote->comment, 80) }}"</p>
                        </div>
                        @endif
                    </div>
                    <a href="{{ route('digest.weekly') }}" class="btn btn-sm" style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-weight: 600; font-size: 0.78rem; white-space: nowrap;">
                        View Full Digest <i class="mdi mdi-arrow-right"></i>
                    </a>
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
                        <div id="reading-heatmap" style="display: grid; grid-template-columns: repeat(53, 12px); grid-auto-rows: 12px; gap: 2px;"></div>
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

    // Align start to the Sunday of the week that is 52 weeks before today's week
    const startDate = new Date(todayDate);
    startDate.setDate(todayDate.getDate() - 364 - todayDate.getDay());

    const WEEKS = 53;
    const CELL = 12;
    const GAP = 2;

    const grid = document.getElementById('reading-heatmap');
    const monthBar = document.getElementById('heatmap-months');
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

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

    // Month labels above the grid
    let lastMonth = -1;
    for (let w = 0; w < WEEKS; w++) {
        const d = new Date(startDate);
        d.setDate(startDate.getDate() + w * 7);
        if (d.getMonth() !== lastMonth) {
            lastMonth = d.getMonth();
            const span = document.createElement('span');
            span.textContent = monthNames[lastMonth];
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
            div.style.width = CELL + 'px';
            div.style.height = CELL + 'px';
            div.style.borderRadius = '2px';
            div.style.gridColumn = (w + 1).toString();
            div.style.gridRow = (d + 1).toString();

            if (date > todayDate) {
                div.style.background = 'transparent';
            } else {
                const key = toKey(date);
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
