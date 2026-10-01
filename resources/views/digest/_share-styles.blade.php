<style>
.share-section-label {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--sword-gold);
    font-weight: 700;
    margin-bottom: 0.75rem;
}
.share-card {
    border-top: 2px solid var(--sword-gold);
}
.fruit-check, .idol-check {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.35rem 0.75rem;
    border: 1px solid rgba(14,22,40,0.15);
    border-radius: 20px;
    cursor: pointer;
    font-size: 0.82rem;
    transition: background 0.15s, border-color 0.15s;
    user-select: none;
}
.fruit-check input:checked + span,
.idol-check input:checked + span {
    font-weight: 600;
}
.fruit-check:has(input:checked) {
    background: rgba(201,168,76,0.12);
    border-color: var(--sword-gold);
}
.idol-check:has(input:checked) {
    background: rgba(14,22,40,0.07);
    border-color: var(--sword-navy);
}
.section-toggle {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.6rem 0;
    border-bottom: 1px solid rgba(14,22,40,0.06);
}
.section-toggle:last-child { border-bottom: none; }
.form-check-input:checked { background-color: var(--sword-navy); border-color: var(--sword-navy); }
</style>
