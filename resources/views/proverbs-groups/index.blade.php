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
