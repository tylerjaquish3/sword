<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accountability Check-In | Sword</title>
    <link rel="shortcut icon" href="/images/logo.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">
    <style>
        :root { --sword-navy: #0e1628; --sword-gold: #c9a84c; }
        body { background: #f8f9fb; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1f2937; }
        .acc-header { background: var(--sword-navy); color: #fff; padding: 2rem 0 1.5rem; }
        .acc-header .wordmark { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.15em; color: var(--sword-gold); font-weight: 700; margin-bottom: 0.5rem; }
        .acc-header h1 { font-size: 1.6rem; font-weight: 700; margin-bottom: 0.25rem; color: #fff; }
        .section-label { font-size: 0.62rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--sword-gold); font-weight: 700; margin-bottom: 0.75rem; }
        .acc-card { border-top: 2px solid var(--sword-gold); border-radius: 8px; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.06); margin-bottom: 1rem; }
        .acc-card .card-body { padding: 1.25rem; }
        .stat-card { background: #fff; border-top: 2px solid var(--sword-gold); border-radius: 8px; text-align: center; padding: 1rem 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
        .stat-val { font-size: 1.6rem; font-weight: 700; color: var(--sword-navy); line-height: 1; }
        .stat-label { font-size: 0.62rem; text-transform: uppercase; letter-spacing: 0.07em; color: #9ca3af; margin-top: 0.2rem; }
        .stars .mdi { color: rgba(14,22,40,0.15); font-size: 1.1rem; }
        .stars .mdi.is-filled { color: var(--sword-gold); }
        .touchpoint-item { display: flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0; font-size: 0.85rem; }
        .touchpoint-item .mdi { font-size: 1.1rem; }
        .overall-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            background: var(--sword-navy);
            color: var(--sword-gold);
            font-size: 1.3rem;
            font-weight: 700;
        }
        .divider-gold { border: 0; border-top: 1px solid rgba(201,168,76,0.25); margin: 1.5rem 0; }
        .comment-block { border-left: 3px solid rgba(201,168,76,0.3); padding: 0.6rem 0.9rem; margin-bottom: 0.85rem; background: rgba(201,168,76,0.04); border-radius: 0 6px 6px 0; }
        .comment-block:last-child { margin-bottom: 0; }
        .comment-author { font-size: 0.72rem; font-weight: 700; color: var(--sword-navy); letter-spacing: 0.02em; }
        .comment-date { font-size: 0.68rem; color: #9ca3af; margin-left: 0.5rem; }
        .comment-text { font-size: 0.85rem; color: #374151; line-height: 1.55; margin: 0.2rem 0 0; }
        .comment-form-card { background: #fff; border-top: 2px solid var(--sword-gold); border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); padding: 1.25rem; }
        .comment-form-card textarea, .comment-form-card input[type=text] {
            border: 1px solid rgba(14,22,40,0.15); border-radius: 6px; font-size: 0.85rem; color: #1f2937;
            padding: 0.55rem 0.75rem; width: 100%; outline: none; transition: border-color 0.15s;
        }
        .comment-form-card textarea:focus, .comment-form-card input[type=text]:focus {
            border-color: var(--sword-gold); box-shadow: 0 0 0 3px rgba(201,168,76,0.12);
        }
        .comment-form-card textarea { resize: vertical; min-height: 90px; }
        .btn-submit-comment {
            background: var(--sword-navy); color: var(--sword-gold); border: none; border-radius: 6px;
            font-size: 0.82rem; font-weight: 600; padding: 0.5rem 1.25rem; cursor: pointer; transition: opacity 0.15s;
        }
        .btn-submit-comment:hover { opacity: 0.88; }
        .footer-note { text-align: center; font-size: 0.75rem; color: #9ca3af; padding: 2rem 0 1.5rem; }
    </style>
</head>
<body>

<div class="acc-header">
    <div class="container" style="max-width: 720px;">
        <div class="wordmark"><i class="mdi mdi-sword me-1"></i>Sword — Accountability Check-In</div>
        <h1>Weekly Check-In</h1>
        @if($checkIn->sharer_name)
        <div class="mt-2 d-flex align-items-center gap-3" style="font-size: 0.82rem; color: rgba(255,255,255,0.75);">
            <span><i class="mdi mdi-account-outline me-1" style="color: var(--sword-gold);"></i>{{ $checkIn->sharer_name }}</span>
            <span style="color: rgba(255,255,255,0.35);">&bull;</span>
            <span><i class="mdi mdi-calendar-outline me-1" style="color: var(--sword-gold);"></i>Submitted {{ $checkIn->created_at->format('M j, Y') }}</span>
        </div>
        @else
        <div class="mt-2" style="font-size: 0.82rem; color: rgba(255,255,255,0.75);">Submitted {{ $checkIn->created_at->format('M j, Y') }}</div>
        @endif
    </div>
</div>

<div class="container py-4" style="max-width: 720px;">

    <div class="row g-3 mb-3">
        <div class="col-6 col-sm-3">
            <div class="stat-card">
                <div class="stat-val">{{ $checkIn->word_days }}/7</div>
                <div class="stat-label">Days in the Word</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="stat-card">
                <div class="stat-val">{{ $checkIn->prayer_days }}/7</div>
                <div class="stat-label">Days in Prayer</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="stat-card">
                <div class="stat-val">{{ $checkIn->fellowship_level }}</div>
                <div class="stat-label">Fellowship</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="stat-card">
                <div class="overall-badge">{{ $checkIn->overall_number }}</div>
                <div class="stat-label mt-1">Overall Number</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="acc-card">
                <div class="card-body">
                    <p class="section-label"><i class="mdi mdi-book-open-variant me-1"></i>Quality of Time in the Word</p>
                    <div class="stars">
                        @for($i = 1; $i <= 5; $i++)
                        <i class="mdi mdi-star {{ $i <= $checkIn->word_quality ? 'is-filled' : '' }}"></i>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="acc-card">
                <div class="card-body">
                    <p class="section-label"><i class="mdi mdi-heart me-1"></i>Quality of Time in Prayer</p>
                    <div class="stars">
                        @for($i = 1; $i <= 5; $i++)
                        <i class="mdi mdi-star {{ $i <= $checkIn->prayer_quality ? 'is-filled' : '' }}"></i>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="acc-card">
        <div class="card-body">
            <p class="section-label"><i class="mdi mdi-shield-account-outline me-1"></i>Touch with Corner Man</p>
            @foreach([
                ['corner_man_prayer_request', 'Asked for prayer'],
                ['corner_man_asked_how_to_pray', 'Asked how to pray for them'],
                ['corner_man_encouragement', 'Shared encouragement, scripture, or truth'],
                ['corner_man_multiple_touchpoints', 'Had multiple touchpoints'],
            ] as [$field, $label])
            <div class="touchpoint-item">
                <i class="mdi {{ $checkIn->$field ? 'mdi-check-circle' : 'mdi-close-circle-outline' }}" style="color: {{ $checkIn->$field ? 'var(--sword-gold)' : '#d1d5db' }};"></i>
                {{ $label }}
            </div>
            @endforeach
        </div>
    </div>

    @if($checkIn->overall_why)
    <div class="acc-card">
        <div class="card-body">
            <p class="section-label"><i class="mdi mdi-comment-question-outline me-1"></i>Why That Number?</p>
            <p class="mb-0" style="font-size: 0.87rem; color: #374151; line-height: 1.6; white-space: pre-wrap;">{{ $checkIn->overall_why }}</p>
        </div>
    </div>
    @endif

    @if($checkIn->one_praise || $checkIn->one_prayer)
    <div class="row g-3">
        @if($checkIn->one_praise)
        <div class="col-md-6">
            <div class="acc-card">
                <div class="card-body">
                    <p class="section-label"><i class="mdi mdi-hands-pray me-1"></i>One Praise</p>
                    <p class="mb-0" style="font-size: 0.87rem; color: #374151; line-height: 1.6; white-space: pre-wrap;">{{ $checkIn->one_praise }}</p>
                </div>
            </div>
        </div>
        @endif
        @if($checkIn->one_prayer)
        <div class="col-md-6">
            <div class="acc-card">
                <div class="card-body">
                    <p class="section-label"><i class="mdi mdi-heart-outline me-1"></i>One Prayer</p>
                    <p class="mb-0" style="font-size: 0.87rem; color: #374151; line-height: 1.6; white-space: pre-wrap;">{{ $checkIn->one_prayer }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Guest Comments --}}
    <hr class="divider-gold" id="comments">
    <div class="mb-4">
        <p class="section-label" style="font-size: 0.7rem; letter-spacing: 0.12em;"><i class="mdi mdi-comment-text-outline me-1"></i>Comments</p>

        @if(session('comment_success'))
        <div style="background: rgba(201,168,76,0.1); border: 1px solid rgba(201,168,76,0.3); border-radius: 6px; padding: 0.75rem 1rem; font-size: 0.85rem; color: var(--sword-navy); margin-bottom: 1rem;">
            <i class="mdi mdi-check-circle-outline me-1" style="color: var(--sword-gold);"></i> Your comment was sent!
        </div>
        @endif

        @foreach($comments as $c)
        <div class="comment-block">
            <span class="comment-author">{{ $c->displayName() }}</span>
            <span class="comment-date">{{ $c->created_at->format('M j, Y') }}</span>
            <p class="comment-text">{{ $c->comment }}</p>
        </div>
        @endforeach

        <div class="comment-form-card mt-3">
            <p style="font-size: 0.78rem; color: #6b7280; margin-bottom: 1rem;">Leave a note of encouragement or a question for {{ $checkIn->sharer_name ?? 'them' }}.</p>
            <form method="POST" action="{{ route('accountability.shared.comment', $checkIn->uuid) }}">
                @csrf
                <div style="margin-bottom: 0.75rem;">
                    <input type="text" name="name" placeholder="Your name (optional)" value="{{ old('name') }}" autocomplete="name">
                </div>
                <div style="margin-bottom: 0.75rem;">
                    <textarea name="comment" placeholder="Praying for you this week..." required>{{ old('comment') }}</textarea>
                    @error('comment')
                    <div style="font-size: 0.75rem; color: #dc2626; margin-top: 0.3rem;">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn-submit-comment">Send</button>
            </form>
        </div>
    </div>

    <div class="footer-note">
        Shared via <strong style="color: var(--sword-navy);">Sword</strong> &mdash; a personal Bible study tool
    </div>

</div>

</body>
</html>
