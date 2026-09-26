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
