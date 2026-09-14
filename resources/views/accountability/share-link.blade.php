<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Share Link Ready | Sword</title>
    <link rel="shortcut icon" href="/images/logo.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">
    <style>
        :root { --sword-navy: #0e1628; --sword-gold: #c9a84c; }
        body { background: #f8f9fb; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1f2937; }
        .link-box {
            background: rgba(14,22,40,0.03);
            border: 1px solid rgba(201,168,76,0.3);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-family: monospace;
            font-size: 0.82rem;
            word-break: break-all;
            color: var(--sword-navy);
        }
    </style>
</head>
<body>

<div class="container py-5" style="max-width: 600px;">
    <div class="card" style="border-top: 2px solid var(--sword-gold); border-radius: 8px;">
        <div class="card-body text-center py-4">
            <div class="mb-3">
                <i class="mdi mdi-check-circle mdi-48px" style="color: var(--sword-gold);"></i>
            </div>
            <h5 class="fw-bold mb-1" style="color: var(--sword-navy);">Check-In Saved</h5>
            <p style="font-size: 0.85rem; color: #6b7280; margin-bottom: 1.5rem;">
                Copy the link below and send it to your Corner Man.
            </p>

            <div class="link-box mb-3" id="share-link">{{ route('accountability.shared.show', $checkIn->uuid) }}</div>

            <button class="btn btn-sm" style="background: var(--sword-navy); color: var(--sword-gold); border: 1px solid rgba(201,168,76,0.3); font-size: 0.85rem; font-weight: 600;" onclick="copyLink()" id="copy-btn">
                <i class="mdi mdi-content-copy me-1"></i> Copy Link
            </button>

            <div class="mt-4 pt-3" style="border-top: 1px solid rgba(14,22,40,0.06);">
                <a href="{{ route('accountability.shared.show', $checkIn->uuid) }}" target="_blank" style="font-size: 0.82rem; color: var(--sword-gold);">
                    Preview how it looks <i class="mdi mdi-open-in-new"></i>
                </a>
                @auth
                <div class="mt-2">
                    <a href="{{ route('digest.history') . '#accountability' }}" style="font-size: 0.82rem; color: var(--sword-navy);">
                        Back to History
                    </a>
                </div>
                @endauth
            </div>
        </div>
    </div>
</div>

<script>
function copyLink() {
    const link = document.getElementById('share-link').textContent.trim();
    const btn = document.getElementById('copy-btn');

    function markCopied() {
        btn.innerHTML = '<i class="mdi mdi-check me-1"></i> Copied!';
        setTimeout(() => { btn.innerHTML = '<i class="mdi mdi-content-copy me-1"></i> Copy Link'; }, 2500);
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(link).then(markCopied);
    } else {
        const ta = document.createElement('textarea');
        ta.value = link;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        markCopied();
    }
}
</script>

</body>
</html>
