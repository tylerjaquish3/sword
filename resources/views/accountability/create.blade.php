<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($checkIn) ? 'Edit' : 'New' }} Accountability Check-In | Sword</title>
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
        .acc-header {
            background: var(--sword-navy);
            color: #fff;
            padding: 2rem 0 1.5rem;
        }
        .acc-header .wordmark {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--sword-gold);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .acc-header h1 { font-size: 1.6rem; font-weight: 700; margin-bottom: 0.25rem; color: #fff; }
        .acc-section-label {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--sword-gold);
            font-weight: 700;
            margin-bottom: 0.75rem;
        }
        .acc-card { border-top: 2px solid var(--sword-gold); border-radius: 8px; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.06); margin-bottom: 1rem; }
        .acc-card .card-body { padding: 1.25rem; }
        .pill-check {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.9rem;
            border: 1px solid rgba(14,22,40,0.15);
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: background 0.15s, border-color 0.15s;
            user-select: none;
        }
        .pill-check input { display: none; }
        .pill-check:has(input:checked) {
            background: rgba(201,168,76,0.12);
            border-color: var(--sword-gold);
            font-weight: 600;
        }
        .star-rating { display: inline-flex; gap: 0.15rem; font-size: 1.6rem; cursor: pointer; }
        .star-rating .mdi { color: rgba(14,22,40,0.15); transition: color 0.1s; }
        .star-rating .mdi.is-filled { color: var(--sword-gold); }
        .form-select, .form-control { font-size: 0.88rem; }
        .slider-row { display: flex; align-items: center; gap: 0.85rem; }
        .acc-slider {
            flex: 1;
            -webkit-appearance: none;
            appearance: none;
            height: 6px;
            border-radius: 3px;
            background: rgba(14,22,40,0.1);
            outline: none;
        }
        .acc-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--sword-navy);
            border: 2px solid var(--sword-gold);
            cursor: pointer;
        }
        .acc-slider::-moz-range-thumb {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--sword-navy);
            border: 2px solid var(--sword-gold);
            cursor: pointer;
        }
        .slider-value {
            min-width: 2.25rem;
            height: 2.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(201,168,76,0.12);
            color: var(--sword-navy);
            font-weight: 700;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="acc-header">
    <div class="container" style="max-width: 780px;">
        <div class="wordmark"><i class="mdi mdi-sword me-1"></i>Sword — Accountability Check-In</div>
        <h1>{{ isset($checkIn) ? 'Edit Check-In' : 'Weekly Accountability Check-In' }}</h1>
        @auth
        <p class="mb-0" style="font-size: 0.85rem; color: rgba(255,255,255,0.65);">
            No account needed to fill this out. Use <strong>Save</strong> to keep a private record, or <strong>Save &amp; Share</strong> to get a link you can send to your Corner Man.
        </p>
        <a href="{{ route('digest.history') . '#accountability' }}" class="d-inline-block mt-2" style="font-size: 0.8rem; color: var(--sword-gold);">
            <i class="mdi mdi-arrow-left"></i> Back to History
        </a>
        @else
        <p class="mb-0" style="font-size: 0.85rem; color: rgba(255,255,255,0.65);">
            No account needed to fill this out — use <strong>Save &amp; Share</strong> to get a link you can send to your Corner Man.
        </p>
        <p class="mb-0 mt-2" style="font-size: 0.85rem; color: rgba(255,255,255,0.65);">
            Want to keep a running history of your check-ins? <a href="{{ route('home.index') }}" style="color: var(--sword-gold);">Check out the features</a> to see if you want to create a free Sword account.
        </p>
        @endauth
    </div>
</div>

<div class="container py-4" style="max-width: 780px;">

@php
    $fellowshipLevels = ['No Fellowship', 'Minimal', 'Decent', 'Good', 'Abundant'];
    $cornerManFields = [
        'corner_man_prayer_request' => 'Asked for prayer',
        'corner_man_asked_how_to_pray' => 'Asked how to pray for them',
        'corner_man_encouragement' => 'Shared encouragement, scripture, or truth',
        'corner_man_multiple_touchpoints' => 'Had multiple touchpoints',
    ];
@endphp

@if ($errors->any())
<div class="alert alert-danger" style="font-size: 0.85rem;">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ $formAction }}" method="POST">
    @csrf
    @if(isset($checkIn))
        @method('PUT')
    @endif

    @guest
    <div class="acc-card">
        <div class="card-body">
            <p class="acc-section-label"><i class="mdi mdi-account-outline me-1"></i>Your Name (optional)</p>
            <input type="text" name="sharer_name" class="form-control" placeholder="So your Corner Man knows who it's from" value="{{ old('sharer_name') }}">
        </div>
    </div>
    @endguest

    <div class="acc-card">
        <div class="card-body">
            <p class="acc-section-label"><i class="mdi mdi-book-open-variant me-1"></i>Be in the Word</p>
            <div class="row g-3">
                <div class="col-sm-6">
                    <label class="form-label" style="font-size: 0.82rem;">Days reading this week</label>
                    <div class="slider-row">
                        <input type="range" name="word_days" class="acc-slider" min="0" max="7" step="1" value="{{ old('word_days', $checkIn->word_days ?? 0) }}">
                        <span class="slider-value" data-slider-value>{{ old('word_days', $checkIn->word_days ?? 0) }}</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label d-block" style="font-size: 0.82rem;">Quality of time in the Word</label>
                    <div class="star-rating" data-input="word_quality">
                        @for($i = 1; $i <= 5; $i++)
                        <i class="mdi mdi-star" data-value="{{ $i }}"></i>
                        @endfor
                    </div>
                    <input type="hidden" name="word_quality" value="{{ old('word_quality', $checkIn->word_quality ?? '') }}" required>
                </div>
            </div>
        </div>
    </div>

    <div class="acc-card">
        <div class="card-body">
            <p class="acc-section-label"><i class="mdi mdi-heart me-1"></i>Be in Prayer</p>
            <div class="row g-3">
                <div class="col-sm-6">
                    <label class="form-label" style="font-size: 0.82rem;">Days praying this week</label>
                    <div class="slider-row">
                        <input type="range" name="prayer_days" class="acc-slider" min="0" max="7" step="1" value="{{ old('prayer_days', $checkIn->prayer_days ?? 0) }}">
                        <span class="slider-value" data-slider-value>{{ old('prayer_days', $checkIn->prayer_days ?? 0) }}</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label d-block" style="font-size: 0.82rem;">Quality of time in prayer</label>
                    <div class="star-rating" data-input="prayer_quality">
                        @for($i = 1; $i <= 5; $i++)
                        <i class="mdi mdi-star" data-value="{{ $i }}"></i>
                        @endfor
                    </div>
                    <input type="hidden" name="prayer_quality" value="{{ old('prayer_quality', $checkIn->prayer_quality ?? '') }}" required>
                </div>
            </div>
        </div>
    </div>

    <div class="acc-card">
        <div class="card-body">
            <p class="acc-section-label"><i class="mdi mdi-account-group-outline me-1"></i>Be in Fellowship</p>
            <div class="d-flex flex-wrap gap-2">
                @foreach($fellowshipLevels as $level)
                <label class="pill-check">
                    <input type="radio" name="fellowship_level" value="{{ $level }}" {{ old('fellowship_level', $checkIn->fellowship_level ?? '') === $level ? 'checked' : '' }} required>
                    <span>{{ $level }}</span>
                </label>
                @endforeach
            </div>
        </div>
    </div>

    <div class="acc-card">
        <div class="card-body">
            <p class="acc-section-label"><i class="mdi mdi-shield-account-outline me-1"></i>Be in Touch with Your Corner Man</p>
            @foreach($cornerManFields as $field => $label)
            <div class="d-flex align-items-center justify-content-between py-2" style="border-bottom: 1px solid rgba(14,22,40,0.06);">
                <span style="font-size: 0.85rem;">{{ $label }}</span>
                <div class="d-flex gap-2">
                    <label class="pill-check">
                        <input type="radio" name="{{ $field }}" value="1" {{ old($field, isset($checkIn) ? $checkIn->$field : false) ? 'checked' : '' }}>
                        <span>Yes</span>
                    </label>
                    <label class="pill-check">
                        <input type="radio" name="{{ $field }}" value="0" {{ !old($field, isset($checkIn) ? $checkIn->$field : false) ? 'checked' : '' }}>
                        <span>No</span>
                    </label>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="acc-card">
        <div class="card-body">
            <p class="acc-section-label"><i class="mdi mdi-gauge me-1"></i>What's Your Overall Number for the Week?</p>
            <div class="slider-row mb-3" style="max-width: 320px;">
                <input type="range" name="overall_number" class="acc-slider" min="1" max="10" step="1" value="{{ old('overall_number', $checkIn->overall_number ?? 1) }}">
                <span class="slider-value" data-slider-value>{{ old('overall_number', $checkIn->overall_number ?? 1) }}</span>
            </div>
            <label class="form-label" style="font-size: 0.82rem;">Why?</label>
            <textarea name="overall_why" class="form-control" rows="3" placeholder="What made it that number?">{{ old('overall_why', $checkIn->overall_why ?? '') }}</textarea>
        </div>
    </div>

    <div class="acc-card">
        <div class="card-body">
            <p class="acc-section-label"><i class="mdi mdi-text me-1"></i>One Praise and One Prayer</p>
            <label class="form-label" style="font-size: 0.82rem;">One Praise</label>
            <textarea name="one_praise" class="form-control mb-3" rows="2" placeholder="Something to celebrate this week">{{ old('one_praise', $checkIn->one_praise ?? '') }}</textarea>
            <label class="form-label" style="font-size: 0.82rem;">One Prayer</label>
            <textarea name="one_prayer" class="form-control" rows="2" placeholder="Something you need prayer for">{{ old('one_prayer', $checkIn->one_prayer ?? '') }}</textarea>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-4">
        @auth
        <button type="submit" name="submit_action" value="save" class="btn btn-sm" style="background: transparent; color: var(--sword-navy); border: 1px solid rgba(14,22,40,0.2); font-weight: 600; padding: 0.4rem 1.25rem;">
            <i class="mdi mdi-content-save-outline me-1"></i> Save
        </button>
        @endauth
        <button type="submit" name="submit_action" value="share" class="btn btn-sm" style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-weight: 600; padding: 0.4rem 1.25rem;">
            <i class="mdi mdi-share-variant me-1"></i> Save &amp; Share
        </button>
    </div>
</form>

</div>

<script>
document.querySelectorAll('.acc-slider').forEach(function (slider) {
    var display = slider.closest('.slider-row').querySelector('[data-slider-value]');
    slider.addEventListener('input', function () {
        display.textContent = slider.value;
    });
});

document.querySelectorAll('.star-rating').forEach(function (widget) {
    var input = document.querySelector('input[name="' + widget.dataset.input + '"]');
    var stars = widget.querySelectorAll('.mdi');

    function paint(value) {
        stars.forEach(function (star) {
            star.classList.toggle('is-filled', parseInt(star.dataset.value, 10) <= value);
        });
    }

    paint(parseInt(input.value, 10) || 0);

    stars.forEach(function (star) {
        star.addEventListener('click', function () {
            input.value = star.dataset.value;
            paint(parseInt(star.dataset.value, 10));
        });
    });
});
</script>

</body>
</html>
