@extends('base.layout')

@section('title', 'Home')

@section('content')

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="font-weight-bold mb-2" style="color: var(--sword-navy);">Hi, welcome back!</h3>
                <h6 class="font-weight-normal mb-2" style="color: #6b7280;">
                    @if($lastLogin)
                        Last login: {{ $lastLogin->logged_in_at->diffForHumans() }}
                    @else
                        Welcome!
                    @endif
                </h6>
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
