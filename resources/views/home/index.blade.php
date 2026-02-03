@extends('base.layout')

@section('title', 'Home')

@section('content')  

<div class="row">
    <div class="col-sm-6 mb-4 mb-xl-0">
        <div class="d-lg-flex align-items-center">
            <div>
                <h3 class="text-dark font-weight-bold mb-2">Hi, welcome back!</h3>
                <h6 class="font-weight-normal mb-2">Last login was 23 hours ago.</h6>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="d-flex align-items-center justify-content-md-end">
            <div class="pe-1 mb-3 mb-xl-0">
                    <button type="button" class="btn btn-outline-inverse-info btn-icon-text">
                        Feedback
                        <i class="mdi mdi-message-outline btn-icon-append"></i>                          
                    </button>
            </div>
            <div class="pe-1 mb-3 mb-xl-0">
                    <button type="button" class="btn btn-outline-inverse-info btn-icon-text">
                        Help
                        <i class="mdi mdi-help-circle-outline btn-icon-append"></i>                          
                </button>
            </div>
            <div class="pe-1 mb-3 mb-xl-0">
                    <button type="button" class="btn btn-outline-inverse-info btn-icon-text">
                        Print
                        <i class="mdi mdi-printer btn-icon-append"></i>                          
                    </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-2 grid-margin stretch-card">
        <div class="card">
            <div class="card-body text-center py-4">
                <p class="text-muted mb-2 text-uppercase font-weight-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Eph. 6:17</p>
                <p class="text-secondary mb-0" style="font-size: 0.85rem; line-height: 1.4;">
                    Take the helmet of salvation and the sword of the Spirit, which is the word of God.
                </p>
            </div>
        </div>
    </div>
    <div class="col-lg-2 grid-margin stretch-card">
        <a href="{{ route('prayers.index') }}" class="card text-decoration-none">
            <div class="card-body text-center py-4">
                <i class="mdi mdi-heart mdi-36px text-danger mb-2"></i>
                <h2 class="text-danger font-weight-bold mb-1">{{ $prayerCount }}</h2>
                <p class="text-muted mb-0 text-uppercase font-weight-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Prayer Entries</p>
            </div>
        </a>
    </div>
    <div class="col-lg-2 grid-margin stretch-card">
        <a href="{{ route('commentary.index') }}" class="card text-decoration-none">
            <div class="card-body text-center py-4">
                <i class="mdi mdi-file-document-outline mdi-36px text-info mb-2"></i>
                <h2 class="text-info font-weight-bold mb-1">{{ $commentaryCount }}</h2>
                <p class="text-muted mb-0 text-uppercase font-weight-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Commentary Entries</p>
            </div>
        </a>
    </div>
    <div class="col-lg-2 grid-margin stretch-card">
        <div class="card">
            <div class="card-body text-center py-4">
                <i class="mdi mdi-brain mdi-36px text-warning mb-2"></i>
                <h2 class="text-warning font-weight-bold mb-1">0</h2>
                <p class="text-muted mb-0 text-uppercase font-weight-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Verses Memorized</p>
            </div>
        </div>
    </div>
    <div class="col-lg-2 grid-margin stretch-card">
        <a href="{{ route('topics.index') }}" class="card text-decoration-none">
            <div class="card-body text-center py-4">
                <i class="mdi mdi-tag-multiple mdi-36px text-success mb-2"></i>
                <h2 class="text-success font-weight-bold mb-1">{{ $topicCount }}</h2>
                <p class="text-muted mb-0 text-uppercase font-weight-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Topics Studied</p>
            </div>
        </a>
    </div>
    <div class="col-lg-2 grid-margin stretch-card">
        <div class="card">
            <div class="card-body d-flex align-items-center justify-content-center py-4">
                <img src="/images/logo.png" alt="logo" style="max-width: 100%; max-height: 80px; object-fit: contain;"/>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-6 grid-margin grid-margin-md-0 stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="card-title">Bible Overview</h4>
                    <h4 class="text-success font-weight-bold">{{ $translationCount }} <span class="text-dark ms-1">Translations</span></h4>
                </div>
                <div class="row mt-4">
                    <div class="col-4 text-center">
                        <div class="border rounded p-3">
                            <h2 class="text-primary font-weight-bold mb-1">{{ $bookCount }}</h2>
                            <p class="text-muted mb-0">Books</p>
                        </div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="border rounded p-3">
                            <h2 class="text-info font-weight-bold mb-1">{{ number_format($chapterCount) }}</h2>
                            <p class="text-muted mb-0">Chapters</p>
                        </div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="border rounded p-3">
                            <h2 class="text-success font-weight-bold mb-1">{{ number_format($verseCount) }}</h2>
                            <p class="text-muted mb-0">Verses</p>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <h6 class="text-muted mb-3">Commentary Breakdown</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Chapter Comments</span>
                        <span class="badge bg-info">{{ $chapterCommentCount }}</span>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $commentaryCount > 0 ? ($chapterCommentCount / $commentaryCount) * 100 : 0 }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Verse Comments</span>
                        <span class="badge bg-primary">{{ $verseCommentCount }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $commentaryCount > 0 ? ($verseCommentCount / $commentaryCount) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 grid-margin grid-margin-md-0 stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-lg-flex align-items-center justify-content-between mb-4">
                    <h4 class="card-title">Prayer Journal</h4>
                    <p class="text-dark"><span class="text-success">{{ $recentPrayers }}</span> prayers this week</p>
                </div>
                @if($prayersByType->count() > 0)
                    <div class="row">
                        @foreach($prayersByType as $prayer)
                            <div class="col-6 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h4 class="font-weight-bold mb-0 text-{{ ['primary', 'success', 'warning', 'info', 'danger'][$loop->index % 5] }}">{{ $prayer->count }}</h4>
                                        <i class="mdi mdi-{{ ['heart', 'hand-heart', 'account-group', 'church', 'shield-cross'][$loop->index % 5] }} mdi-24px text-muted"></i>
                                    </div>
                                    <p class="text-muted mb-0 mt-2">{{ $prayer->type->name ?? 'Unknown' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="mdi mdi-heart mdi-48px text-muted"></i>
                        <p class="text-muted mt-2">No prayers recorded yet</p>
                        <a href="{{ route('prayers.create') }}" class="btn btn-primary btn-sm">Add Your First Prayer</a>
                    </div>
                @endif
                <div class="mt-3 pt-3 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Total Prayers</span>
                        <span class="font-weight-bold text-dark">{{ $prayerCount }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
