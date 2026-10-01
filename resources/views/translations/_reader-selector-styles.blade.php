<style>
/* ── Reader selector bar ─────────────────────────────────────── */
.reader-selector-bar {
    display: flex;
    align-items: stretch;
    background: #0e1628;
    border-radius: 0.375rem 0.375rem 0 0;
    overflow: hidden;
    min-height: 56px;
}

.rsel-group {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    padding: 26px 16px 8px;
    position: relative;
    cursor: pointer;
    transition: background 0.18s;
    min-width: 0;
}
.rsel-group:hover { background: rgba(201,168,76,0.08); }

.rsel-translation { flex: 0 0 auto; min-width: 80px; }
.rsel-book        { flex: 1 1 auto; }
.rsel-chapter     { flex: 0 0 auto; min-width: 64px; }

.rsel-label {
    position: absolute;
    top: 10px;
    left: 16px;
    font-size: 0.58rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: rgba(201,168,76,0.6);
    pointer-events: none;
}

/* Native selects (translation + chapter) */
.rsel-native {
    background: transparent;
    border: none;
    outline: none;
    color: #fff;
    font-size: 0.92rem;
    font-weight: 600;
    padding: 0;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    width: 100%;
}
.rsel-native option,
.rsel-native optgroup { background: #0e1628; color: #e2e8f0; }

.rsel-chevron {
    position: absolute;
    right: 10px;
    bottom: 12px;
    font-size: 0.85rem;
    color: rgba(201,168,76,0.5);
    pointer-events: none;
}

/* Dividers */
.rsel-divider {
    width: 1px;
    background: rgba(201,168,76,0.15);
    align-self: stretch;
    flex-shrink: 0;
}

/* ── select2 inside the bar ──────────────────────────────────── */
.rsel-book .select2-container {
    width: 100% !important;
}
.rsel-book .select2-container--default .select2-selection--single {
    background: transparent !important;
    border: none !important;
    height: auto !important;
    min-height: 0 !important;
    padding: 0 !important;
    box-shadow: none !important;
    line-height: 1 !important;
}
.rsel-book .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #fff !important;
    font-size: 0.92rem !important;
    font-weight: 600 !important;
    line-height: normal !important;
    padding: 0 20px 0 0 !important;
}
.rsel-book .select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: rgba(255,255,255,0.35) !important;
    font-weight: 400 !important;
}
.rsel-book .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 100% !important;
    right: 0 !important;
}
.rsel-book .select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: rgba(201,168,76,0.5) transparent transparent transparent !important;
}
.rsel-book .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
    border-color: transparent transparent rgba(201,168,76,0.8) transparent !important;
}
.rsel-book .select2-container--default .select2-selection--single .select2-selection__clear {
    color: rgba(201,168,76,0.6) !important;
    font-size: 1rem !important;
    margin-right: 18px !important;
}
.rsel-book .select2-container--default.select2-container--open .select2-selection--single {
    border: none !important;
    box-shadow: none !important;
}

/* ── Quick next-chapter button in selector bar ──────────────── */
.rsel-next-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    outline: none;
    color: rgba(201,168,76,0.85);
    font-size: 1.5rem;
    padding: 10px 14px 0;
    cursor: pointer;
    transition: color 0.15s, background 0.15s;
    align-self: stretch;
    flex-shrink: 0;
}
.rsel-next-btn:hover:not(:disabled) {
    color: rgba(201,168,76,1);
    background: rgba(201,168,76,0.08);
}
.rsel-next-btn:disabled {
    color: rgba(201,168,76,0.2);
    cursor: default;
}

/* ── Bottom gold accent line on active group ─────────────────── */
.reader-selector-bar::after {
    content: '';
    position: absolute;
    left: 0; right: 0; bottom: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(201,168,76,0.4), transparent);
    pointer-events: none;
}
.reader-selector-bar { position: relative; }
</style>
