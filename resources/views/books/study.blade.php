@extends('base.layout')

@section('title', $book->name . ' — Study')

@push('css')
<style>
.study-field-label {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.09em;
    color: var(--sword-gold);
    margin-bottom: 0.4rem;
    display: block;
}
.study-textarea {
    border: 1px solid rgba(14,22,40,0.15);
    border-radius: 6px;
    font-size: 0.9rem;
    line-height: 1.6;
    color: #374151;
    resize: vertical;
    transition: border-color 0.2s, box-shadow 0.2s;
    width: 100%;
    padding: 0.6rem 0.85rem;
    background: #fff;
}
.study-textarea:focus {
    outline: none;
    border-color: var(--sword-gold);
    box-shadow: 0 0 0 0.15rem rgba(201,168,76,0.18);
}
.study-input {
    border: 1px solid rgba(14,22,40,0.15);
    border-radius: 6px;
    font-size: 0.9rem;
    color: #374151;
    transition: border-color 0.2s, box-shadow 0.2s;
    width: 100%;
    padding: 0.5rem 0.85rem;
    background: #fff;
}
.study-input:focus {
    outline: none;
    border-color: var(--sword-gold);
    box-shadow: 0 0 0 0.15rem rgba(201,168,76,0.18);
}
.stat-block {
    text-align: center;
    padding: 0.75rem;
    border-radius: 6px;
    background: rgba(14,22,40,0.03);
    border: 1px solid rgba(14,22,40,0.08);
}
.stat-block-val {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--sword-navy);
    line-height: 1;
}
.stat-block-label {
    font-size: 0.62rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #9ca3af;
    margin-top: 0.2rem;
}
.testament-badge {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 2px 8px;
    border-radius: 20px;
}
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <a href="{{ route('topics.index') }}#books" style="font-size: 0.78rem; color: var(--sword-gold); text-decoration: none;">
                <i class="mdi mdi-arrow-left"></i> Study Hub
            </a>
        </div>
        <div class="d-flex align-items-center gap-3">
            <h3 class="mb-0 fw-bold" style="color: var(--sword-navy);">{{ $book->name }}</h3>
            <span class="testament-badge {{ $book->new_testament ? '' : '' }}"
                style="background: {{ $book->new_testament ? 'rgba(201,168,76,0.12)' : 'rgba(14,22,40,0.07)' }}; color: {{ $book->new_testament ? 'var(--sword-gold)' : 'var(--sword-navy)' }};">
                {{ $book->new_testament ? 'New Testament' : 'Old Testament' }}
            </span>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('translations.index') }}?book={{ $book->id }}"
           class="btn btn-sm"
           style="background: transparent; color: var(--sword-navy); border: 1px solid rgba(14,22,40,0.2); font-size: 0.8rem;">
            <i class="mdi mdi-book-open-variant"></i> Read
        </a>
    </div>
</div>

<div class="row g-4">

    {{-- Left: Study Form --}}
    <div class="col-lg-8">

        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center gap-2 mb-3 py-2" style="font-size: 0.85rem; border: 1px solid rgba(201,168,76,0.3); background: rgba(201,168,76,0.08); color: var(--sword-navy); border-radius: 6px;">
                <i class="mdi mdi-check-circle" style="color: var(--sword-gold);"></i>
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('books.update-study', $book) }}">
            @csrf
            @method('PUT')

            {{-- Author + Description row --}}
            <div class="card mb-3" style="border-top: 2px solid var(--sword-gold);">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="study-field-label">Author</label>
                            <input type="text"
                                   name="author"
                                   class="study-input"
                                   value="{{ old('author', $book->author) }}"
                                   placeholder="e.g. Moses, Paul, Unknown">
                        </div>
                        <div class="col-sm-6">
                            <label class="study-field-label">Description / Overview</label>
                            <input type="text"
                                   name="description"
                                   class="study-input"
                                   value="{{ old('description', $book->description) }}"
                                   placeholder="One-line summary">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Word Cloud --}}
            <div class="card mb-3">
                <div class="card-body">
                    <label class="study-field-label">Key Words</label>
                    @if($wordCloud->isNotEmpty())
                        <div id="word-cloud-container" style="width: 100%; height: 280px; position: relative;">
                            <svg id="word-cloud-svg" style="width: 100%; height: 100%;"></svg>
                        </div>
                    @else
                        <div style="text-align: center; padding: 2rem 0; color: #9ca3af;">
                            <i class="mdi mdi-cloud-outline mdi-36px d-block mb-2" style="color: rgba(14,22,40,0.15);"></i>
                            <p class="mb-0" style="font-size: 0.82rem;">No keyword data yet — run the keyword extraction job to generate this cloud.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Historical Context --}}
            <div class="card mb-3">
                <div class="card-body">
                    <label class="study-field-label">Historical Context</label>
                    <textarea name="history"
                              class="study-textarea"
                              rows="5"
                              placeholder="When was it written? What was happening in that era? Who was the audience?">{{ old('history', $book->history) }}</textarea>
                </div>
            </div>

            {{-- Key Themes --}}
            <div class="card mb-3">
                <div class="card-body">
                    <label class="study-field-label">Key Themes</label>
                    <textarea name="themes"
                              class="study-textarea"
                              rows="5"
                              placeholder="What are the major theological themes, motifs, or ideas?">{{ old('themes', $book->themes) }}</textarea>
                </div>
            </div>

            {{-- Personal Notes --}}
            <div class="card mb-3">
                <div class="card-body">
                    <label class="study-field-label">Personal Study Notes</label>
                    <textarea name="notes"
                              class="study-textarea"
                              rows="8"
                              placeholder="Your personal insights, questions, reflections, and observations...">{{ old('notes', $book->notes) }}</textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="submit"
                        class="btn"
                        style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-weight: 600; font-size: 0.88rem; padding: 0.5rem 1.5rem;">
                    <i class="mdi mdi-content-save me-1"></i> Save Notes
                </button>
            </div>

        </form>
    </div>

    {{-- Right: Stats Sidebar --}}
    <div class="col-lg-4">
        <div class="card mb-3" style="border-top: 2px solid var(--sword-gold); position: sticky; top: 16px;">
            <div class="card-body">
                <p style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--sword-gold);" class="mb-3">Reading Progress</p>

                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <div class="stat-block">
                            <div class="stat-block-val">{{ $chapterCount }}</div>
                            <div class="stat-block-label">Chapters</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-block">
                            <div class="stat-block-val">{{ $chaptersRead }}</div>
                            <div class="stat-block-label">Read</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-block">
                            <div class="stat-block-val">{{ $commentaryCount }}</div>
                            <div class="stat-block-label">Notes</div>
                        </div>
                    </div>
                </div>

                @php $pct = $chapterCount > 0 ? round($chaptersRead / $chapterCount * 100) : 0; @endphp
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span style="font-size: 0.75rem; color: #6b7280;">{{ $pct }}% complete</span>
                    <span style="font-size: 0.75rem; color: #6b7280;">{{ $chaptersRead }}/{{ $chapterCount }}</span>
                </div>
                <div class="progress mb-4" style="height: 6px; background: rgba(14,22,40,0.08);">
                    <div class="progress-bar" role="progressbar"
                         style="width: {{ $pct }}%; background: var(--sword-gold);"></div>
                </div>

                <div class="d-grid gap-2">
                    <a href="{{ route('translations.index') }}?book={{ $book->id }}"
                       class="btn btn-sm"
                       style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-weight: 600; font-size: 0.8rem;">
                        <i class="mdi mdi-book-open-variant me-1"></i> Read {{ $book->name }}
                    </a>
                    @if($commentaryCount > 0)
                    <a href="{{ route('commentary.index') }}"
                       class="btn btn-sm"
                       style="background: transparent; color: var(--sword-navy); border: 1px solid rgba(14,22,40,0.2); font-size: 0.8rem;">
                        <i class="mdi mdi-file-document-outline me-1"></i> View Commentary
                    </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Commentary Notes --}}
        @if($bookNotes->isNotEmpty())
        <div class="card mb-3">
            <div class="card-body py-3">
                <p style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--sword-gold);" class="mb-2">Notes in This Book</p>
                @foreach($bookNotes as $note)
                    <div class="{{ !$loop->last ? 'mb-2 pb-2' : '' }}" style="{{ !$loop->last ? 'border-bottom: 1px solid rgba(14,22,40,0.06);' : '' }}">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span style="font-size: 0.65rem; font-weight: 700; color: var(--sword-navy); letter-spacing: 0.03em;">
                                {{ $book->abbr }} {{ $note['ref'] }}
                            </span>
                            <span style="font-size: 0.6rem; padding: 1px 5px; border-radius: 3px; background: {{ $note['type'] === 'verse' ? 'rgba(201,168,76,0.12)' : 'rgba(14,22,40,0.06)' }}; color: {{ $note['type'] === 'verse' ? 'var(--sword-gold)' : '#6b7280' }}; font-weight: 600;">
                                {{ $note['type'] }}
                            </span>
                        </div>
                        <p class="mb-0" style="font-size: 0.76rem; color: #4b5563; line-height: 1.4;">{{ Str::limit($note['text'], 72) }}</p>
                    </div>
                @endforeach
                @if($commentaryCount > 6)
                <div class="mt-2 pt-1" style="border-top: 1px solid rgba(14,22,40,0.06);">
                    <a href="{{ route('commentary.index') }}" style="font-size: 0.72rem; color: var(--sword-gold); text-decoration: none;">
                        +{{ $commentaryCount - 6 }} more &rarr;
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Fields filled indicator --}}
        @php
            $filled = collect(['author', 'description', 'history', 'themes', 'notes'])
                ->filter(fn($f) => !empty($book->$f))
                ->count();
        @endphp
        @if($filled < 5)
        <div class="card" style="border: 1px dashed rgba(201,168,76,0.35); background: rgba(201,168,76,0.03);">
            <div class="card-body py-3">
                <p style="font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.09em; color: var(--sword-gold);" class="mb-2">Study Progress</p>
                @foreach(['author' => 'Author', 'description' => 'Description', 'history' => 'Historical Context', 'themes' => 'Key Themes', 'notes' => 'Personal Notes'] as $field => $label)
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="mdi {{ !empty($book->$field) ? 'mdi-check-circle' : 'mdi-circle-outline' }}"
                       style="font-size: 0.85rem; color: {{ !empty($book->$field) ? 'var(--sword-gold)' : 'rgba(14,22,40,0.2)' }};"></i>
                    <span style="font-size: 0.78rem; color: {{ !empty($book->$field) ? '#374151' : '#9ca3af' }};">{{ $label }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="card" style="border: 1px solid rgba(201,168,76,0.3); background: rgba(201,168,76,0.05);">
            <div class="card-body py-3 text-center">
                <i class="mdi mdi-trophy mdi-24px" style="color: var(--sword-gold);"></i>
                <p class="mb-0 mt-1" style="font-size: 0.8rem; color: var(--sword-navy); font-weight: 600;">Study complete!</p>
                <p class="mb-0" style="font-size: 0.72rem; color: #9ca3af;">All fields filled in</p>
            </div>
        </div>
        @endif
    </div>

</div>

@endsection

@if($wordCloud->isNotEmpty())
@push('js')
<script src="https://cdn.jsdelivr.net/npm/d3@7/dist/d3.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/d3-cloud@1/build/d3.layout.cloud.js"></script>
<script>
(function () {
    var words = @json($wordCloud);
    if (!words.length) return;

    var container = document.getElementById('word-cloud-container');
    var W = container.offsetWidth || 600;
    var H = 280;

    var sizes = words.map(function (w) { return w.size; });
    var minS = Math.min.apply(null, sizes);
    var maxS = Math.max.apply(null, sizes);

    var fontScale = d3.scaleLinear().domain([minS, maxS]).range([13, 58]).clamp(true);

    function pickColor(size, max) {
        var ratio = size / max;
        if (ratio > 0.65) return '#0e1628';
        if (ratio > 0.35) return '#c9a84c';
        return '#9ca3af';
    }

    d3.layout.cloud()
        .size([W, H])
        .words(words.map(function (w) {
            return { text: w.text, size: fontScale(w.size), rawSize: w.size };
        }))
        .padding(4)
        .rotate(function () { return Math.random() < 0.15 ? 90 : 0; })
        .font('system-ui, sans-serif')
        .fontSize(function (d) { return d.size; })
        .on('end', function (placed) {
            var svg = d3.select('#word-cloud-svg')
                .attr('width', W)
                .attr('height', H);

            svg.append('g')
                .attr('transform', 'translate(' + W / 2 + ',' + H / 2 + ')')
                .selectAll('text')
                .data(placed)
                .enter().append('text')
                .style('font-size', function (d) { return d.size + 'px'; })
                .style('font-family', 'system-ui, sans-serif')
                .style('font-weight', function (d) { return d.rawSize / maxS > 0.4 ? '700' : '500'; })
                .style('fill', function (d) { return pickColor(d.rawSize, maxS); })
                .style('cursor', 'default')
                .attr('text-anchor', 'middle')
                .attr('transform', function (d) {
                    return 'translate(' + [d.x, d.y] + ')rotate(' + d.rotate + ')';
                })
                .text(function (d) { return d.text; })
                .append('title').text(function (d) { return d.text + ' (' + d.rawSize + 'x)'; });
        })
        .start();
}());
</script>
@endpush
@endif
