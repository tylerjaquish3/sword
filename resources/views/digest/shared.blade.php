<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Weekly Spiritual Digest – {{ $shared->week_start->format('M j') }}–{{ $shared->week_end->format('M j, Y') }}</title>
    <link rel="shortcut icon" href="/images/logo.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">
    <style>
        :root {
            --sword-navy: #0e1628;
            --sword-gold: #c9a84c;
        }
        body {
            background: #f8f9fb;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #1f2937;
        }
        .digest-header {
            background: var(--sword-navy);
            color: #fff;
            padding: 2rem 0 1.5rem;
        }
        .digest-header .wordmark {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--sword-gold);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .digest-header h1 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: #fff;
        }
        .digest-header .week-range {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.55);
        }
        .section-label {
            font-size: 0.62rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--sword-gold);
            font-weight: 700;
            margin-bottom: 0.75rem;
        }
        .digest-card {
            border-top: 2px solid var(--sword-gold);
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            margin-bottom: 1rem;
        }
        .digest-card .card-body { padding: 1.25rem; }
        .digest-item {
            padding: 0.55rem 0;
            border-bottom: 1px solid rgba(14,22,40,0.06);
        }
        .digest-item:last-child { border-bottom: none; }
        .digest-ref {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--sword-navy);
            letter-spacing: 0.03em;
        }
        .digest-snippet {
            font-size: 0.82rem;
            color: #4b5563;
            line-height: 1.5;
        }
        .digest-empty {
            text-align: center;
            padding: 1.25rem 0;
            color: #9ca3af;
            font-size: 0.82rem;
        }
        .stat-card {
            background: #fff;
            border-top: 2px solid var(--sword-gold);
            border-radius: 8px;
            text-align: center;
            padding: 1rem 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .stat-val {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--sword-navy);
            line-height: 1;
        }
        .stat-label {
            font-size: 0.62rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #9ca3af;
            margin-top: 0.2rem;
        }
        .past-note-card {
            background: linear-gradient(135deg, rgba(14,22,40,0.03) 0%, rgba(201,168,76,0.06) 100%);
            border: 1px solid rgba(201,168,76,0.25);
            border-left: 4px solid var(--sword-gold);
            border-radius: 8px;
        }
        .reflection-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            margin-bottom: 1rem;
        }
        .reflection-card .card-body { padding: 1.25rem; }
        .fruit-badge {
            display: inline-block;
            padding: 0.3rem 0.75rem;
            border-radius: 20px;
            background: rgba(201,168,76,0.12);
            border: 1px solid rgba(201,168,76,0.35);
            color: var(--sword-navy);
            font-size: 0.78rem;
            font-weight: 600;
            margin: 0.2rem;
        }
        .idol-badge {
            display: inline-block;
            padding: 0.3rem 0.75rem;
            border-radius: 20px;
            background: rgba(14,22,40,0.06);
            border: 1px solid rgba(14,22,40,0.15);
            color: #374151;
            font-size: 0.78rem;
            margin: 0.2rem;
        }
        .divider-gold {
            border: 0;
            border-top: 1px solid rgba(201,168,76,0.25);
            margin: 1.5rem 0;
        }
        .footer-note {
            text-align: center;
            font-size: 0.75rem;
            color: #9ca3af;
            padding: 2rem 0 1.5rem;
        }
    </style>
</head>
<body>

{{-- Header --}}
<div class="digest-header">
    <div class="container" style="max-width: 780px;">
        <div class="wordmark"><i class="mdi mdi-sword me-1"></i>Sword — Weekly Spiritual Digest</div>
        <h1>Week in Review</h1>
        <div class="week-range">{{ $shared->week_start->format('M j') }} – {{ $shared->week_end->format('M j, Y') }}</div>
        @if($shared->sharer_name)
        <div class="mt-2 d-flex align-items-center gap-3" style="font-size: 0.82rem; color: rgba(255,255,255,0.75);">
            <span><i class="mdi mdi-account-outline me-1" style="color: var(--sword-gold);"></i>{{ $shared->sharer_name }}</span>
            <span style="color: rgba(255,255,255,0.35);">&bull;</span>
            <span><i class="mdi mdi-calendar-outline me-1" style="color: var(--sword-gold);"></i>Submitted {{ $shared->created_at->format('M j, Y') }}</span>
        </div>
        @endif
    </div>
</div>

<div class="container py-4" style="max-width: 780px;">

    @php $snapshot = $shared->snapshot; @endphp

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-sm-3">
            <div class="stat-card">
                <div class="stat-val">{{ $snapshot['daysStudied'] }}</div>
                <div class="stat-label">Days Studied</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="stat-card">
                <div class="stat-val">{{ $snapshot['totalChapters'] }}</div>
                <div class="stat-label">Chapters Read</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="stat-card">
                <div class="stat-val">{{ $snapshot['totalPrayers'] }}</div>
                <div class="stat-label">Prayers Written</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="stat-card">
                <div class="stat-val">{{ $snapshot['totalNotes'] }}</div>
                <div class="stat-label">Notes Added</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">

        {{-- Left column --}}
        <div class="col-lg-6">

            @if($shared->show_chapters)
            <div class="digest-card">
                <div class="card-body">
                    <p class="section-label"><i class="mdi mdi-book-open-variant me-1"></i>Chapters Read</p>
                    @if(!empty($snapshot['chaptersRead']))
                        @foreach($snapshot['chaptersRead'] as $entry)
                        <div class="digest-item d-flex align-items-center justify-content-between">
                            <span class="digest-ref">{{ $entry['book'] }}</span>
                            <span class="badge" style="background: rgba(201,168,76,0.12); color: var(--sword-navy); font-weight: 600; font-size: 0.72rem;">
                                {{ $entry['count'] }} {{ $entry['count'] === 1 ? 'chapter' : 'chapters' }}
                            </span>
                        </div>
                        @endforeach
                    @else
                        <div class="digest-empty">No chapters recorded</div>
                    @endif
                </div>
            </div>
            @endif

            @if($shared->show_prayers)
            <div class="digest-card">
                <div class="card-body">
                    <p class="section-label"><i class="mdi mdi-heart me-1"></i>Prayers Written</p>
                    @if(!empty($snapshot['prayers']))
                        @foreach($snapshot['prayers'] as $prayer)
                        <div class="digest-item">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                @if($prayer['type'])
                                    <span class="badge" style="background: var(--sword-navy); color: var(--sword-gold); font-size: 0.65rem; font-weight: 600;">{{ $prayer['type'] }}</span>
                                @endif
                                <span style="font-size: 0.7rem; color: #9ca3af;">{{ $prayer['date'] }}</span>
                            </div>
                            <p class="digest-snippet mb-0">{{ Str::limit($prayer['content'], 120) }}</p>
                        </div>
                        @endforeach
                    @else
                        <div class="digest-empty">No prayers recorded</div>
                    @endif
                </div>
            </div>
            @endif

        </div>

        {{-- Right column --}}
        <div class="col-lg-6">

            @if($shared->show_memory)
            <div class="digest-card" style="margin-bottom: 1rem;">
                <div class="card-body">
                    <p class="section-label"><i class="mdi mdi-brain me-1"></i>Memory Practice</p>

                    @if(!empty($snapshot['completedMemories']))
                        @foreach($snapshot['completedMemories'] as $mem)
                        <div class="mb-3 p-2 rounded" style="background: rgba(201,168,76,0.08); border: 1px solid rgba(201,168,76,0.2);">
                            <div class="mb-1" style="font-size: 0.82rem; color: var(--sword-navy); font-weight: 600;">
                                <i class="mdi mdi-trophy" style="color: var(--sword-gold);"></i>
                                {{ $mem['title'] }} — completed!
                            </div>
                            @foreach($mem['verses'] as $verse)
                            <div style="font-size: 0.75rem; color: var(--sword-gold); font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em;">{{ $verse['reference'] }}</div>
                            <p class="mb-1" style="font-size: 0.78rem; line-height: 1.55; color: #4b5563; font-style: italic;">{{ $verse['text'] }}</p>
                            @endforeach
                        </div>
                        @endforeach
                    @elseif($snapshot['completedThisWeek'] > 0)
                        {{-- backward compat: old snapshots only have the count --}}
                        <div class="mb-3 p-2 rounded" style="background: rgba(201,168,76,0.08); border: 1px solid rgba(201,168,76,0.2);">
                            <span style="font-size: 0.82rem; color: var(--sword-navy); font-weight: 600;">
                                <i class="mdi mdi-trophy" style="color: var(--sword-gold);"></i>
                                {{ $snapshot['completedThisWeek'] }} {{ $snapshot['completedThisWeek'] === 1 ? 'set' : 'sets' }} completed this week!
                            </span>
                        </div>
                    @endif

                    @foreach($snapshot['startedThisWeek'] ?? [] as $mem)
                    <div class="digest-item mb-2">
                        <div style="font-size: 0.75rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2px;">New memory goal started</div>
                        <span class="digest-ref">{{ $mem['title'] }}</span>
                        <div style="font-size: 0.78rem; color: #6b7280; margin-top: 2px;">
                            {{ collect($mem['verses'])->pluck('reference')->join(' · ') }}
                        </div>
                    </div>
                    @endforeach

                    @if(!empty($snapshot['memories']))
                        @foreach($snapshot['memories'] as $memory)
                        <div class="digest-item d-flex align-items-center justify-content-between">
                            <span class="digest-ref">{{ $memory['title'] }}</span>
                            <span style="font-size: 0.75rem; color: #9ca3af;">{{ $memory['verses'] }} {{ $memory['verses'] === 1 ? 'verse' : 'verses' }}</span>
                        </div>
                        @endforeach
                    @elseif(empty($snapshot['completedMemories']) && ($snapshot['completedThisWeek'] ?? 0) === 0 && empty($snapshot['startedThisWeek']))
                        <div class="digest-empty">No active memory sets</div>
                    @endif
                </div>
            </div>
            @endif

            @if($shared->show_commentary)
            <div class="digest-card">
                <div class="card-body">
                    <p class="section-label"><i class="mdi mdi-pencil me-1"></i>Commentary Added</p>
                    @if(!empty($snapshot['commentary']))
                        @foreach($snapshot['commentary'] as $note)
                        <div class="digest-item">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="digest-ref">{{ $note['ref'] }}</span>
                                <span class="badge" style="background: rgba(14,22,40,0.07); color: #6b7280; font-size: 0.65rem;">{{ $note['type'] === 'verse' ? 'Verse' : 'Chapter' }}</span>
                            </div>
                            @if(strlen($note['comment']) > 120)
                            <p class="digest-snippet mb-0">
                                <span class="snip-short">{{ Str::limit($note['comment'], 120) }}<a href="#" class="snip-toggle" style="color: var(--sword-gold); font-size: 0.75rem; margin-left: 4px;">More</a></span>
                                <span class="snip-full" hidden>{{ $note['comment'] }}<a href="#" class="snip-toggle" style="color: var(--sword-gold); font-size: 0.75rem; margin-left: 4px;">Less</a></span>
                            </p>
                            @else
                            <p class="digest-snippet mb-0">{{ $note['comment'] }}</p>
                            @endif
                        </div>
                        @endforeach
                    @else
                        <div class="digest-empty">No commentary recorded</div>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>

    @if($shared->show_past_note && !empty($snapshot['pastNote']))
    @php $pastNote = $snapshot['pastNote']; @endphp
    <div class="past-note-card p-4 mb-4">
        <p class="section-label mb-2">
            <i class="mdi mdi-clock-time-eight-outline me-1"></i>From Their Notes, One Year Ago
        </p>
        <div class="d-flex align-items-start gap-3">
            <div style="flex-shrink: 0;">
                <i class="mdi mdi-format-quote-open mdi-36px" style="color: rgba(201,168,76,0.4);"></i>
            </div>
            <div>
                <p class="mb-1" style="font-size: 0.9rem; color: #374151; line-height: 1.6; font-style: italic;">{{ $pastNote['comment'] }}</p>
                <p class="mb-0 digest-ref">{{ $pastNote['ref'] }}</p>
                <p class="mb-0" style="font-size: 0.7rem; color: #9ca3af; margin-top: 2px;">{{ $pastNote['date'] }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Reflections from the sharer --}}
    @php
        $hasFruits = !empty($shared->fruits_needing_prayer);
        $hasScripture = !empty(trim($shared->impactful_scripture ?? ''));
        $hasIdols = !empty($shared->idols);
        $hasAdditional = !empty(trim($shared->additional_content ?? ''));
        $hasSermonNotes = !empty(trim($shared->sermon_notes ?? ''));
        $hasAnyReflection = $hasFruits || $hasScripture || $hasIdols || $hasAdditional || $hasSermonNotes;
    @endphp

    @if($hasAnyReflection)
    <hr class="divider-gold">
    <p class="section-label" style="font-size: 0.7rem; letter-spacing: 0.12em;">Personal Reflections</p>

    <div class="row g-3">

        @if($hasFruits)
        <div class="{{ ($hasScripture || $hasIdols || $hasAdditional) ? 'col-lg-6' : 'col-12' }}">
            <div class="reflection-card">
                <div class="card-body">
                    <p class="section-label"><i class="mdi mdi-spa me-1"></i>Fruits Needing Prayer</p>
                    <div class="mb-2">
                        @foreach($shared->fruits_needing_prayer as $fruit)
                            <span class="fruit-badge">{{ $fruit }}</span>
                        @endforeach
                    </div>
                    @if($shared->fruits_description)
                    <p style="font-size: 0.83rem; color: #374151; line-height: 1.6; white-space: pre-wrap; margin: 0;">{{ $shared->fruits_description }}</p>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if($hasIdols)
        <div class="{{ ($hasFruits) ? 'col-lg-6' : 'col-12' }}">
            <div class="reflection-card">
                <div class="card-body">
                    <p class="section-label"><i class="mdi mdi-alert-circle-outline me-1"></i>Idols to Surrender</p>
                    <div class="mb-2">
                        @foreach($shared->idols as $idol)
                            <span class="idol-badge">{{ $idol }}</span>
                        @endforeach
                    </div>
                    @if($shared->idols_description)
                    <p style="font-size: 0.83rem; color: #374151; line-height: 1.6; white-space: pre-wrap; margin: 0;">{{ $shared->idols_description }}</p>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if($hasScripture)
        <div class="col-12">
            <div class="reflection-card">
                <div class="card-body">
                    <p class="section-label"><i class="mdi mdi-star me-1"></i>Impactful Scripture</p>
                    <p style="font-size: 0.88rem; color: #374151; line-height: 1.65; white-space: pre-wrap; margin: 0;">{{ $shared->impactful_scripture }}</p>
                </div>
            </div>
        </div>
        @endif

        @if($hasSermonNotes)
        <div class="col-12">
            <div class="reflection-card">
                <div class="card-body">
                    <p class="section-label"><i class="mdi mdi-microphone me-1"></i>Sermon Notes</p>
                    <p style="font-size: 0.88rem; color: #374151; line-height: 1.65; white-space: pre-wrap; margin: 0;">{{ $shared->sermon_notes }}</p>
                </div>
            </div>
        </div>
        @endif

        @if($hasAdditional)
        <div class="col-12">
            <div class="reflection-card">
                <div class="card-body">
                    <p class="section-label"><i class="mdi mdi-text me-1"></i>Additional Thoughts</p>
                    <p style="font-size: 0.88rem; color: #374151; line-height: 1.65; white-space: pre-wrap; margin: 0;">{{ $shared->additional_content }}</p>
                </div>
            </div>
        </div>
        @endif

    </div>
    @endif

    <div class="footer-note">
        Shared via <strong style="color: var(--sword-navy);">Sword</strong> &mdash; a personal Bible study tool
    </div>

</div>

<script>
document.querySelectorAll('.snip-toggle').forEach(function(link) {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        var p = link.closest('p');
        p.querySelector('.snip-short').hidden = !p.querySelector('.snip-short').hidden;
        p.querySelector('.snip-full').hidden = !p.querySelector('.snip-full').hidden;
    });
});
</script>
</body>
</html>
