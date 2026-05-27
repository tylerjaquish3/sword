<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>My Sword App</title>
    <link rel="shortcut icon" href="/images/logo.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Crimson+Pro:ital,wght@0,300;0,400;0,600;1,300;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @vite(['resources/js/app.js', 'resources/css/sword.css', 'resources/css/landing.css'])
</head>
<body>

{{-- ══════════════════════════ HERO / LOGIN ══════════════════════ --}}
<section class="sw-hero">
    <div class="sw-hero-inner">

        {{-- Brand --}}
        <div class="sw-brand">
            <img src="/images/logo.png" alt="Sword">
            <div class="sw-brand-tag">Personal Bible Study</div>
        </div>

        {{-- Login card --}}
        <div class="sw-login-card">

            @if (session('status'))
                <div class="alert alert-success mb-3 py-2" style="font-size:0.83rem;">{{ session('status') }}</div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning mb-3 py-2" style="font-size:0.83rem;">{{ session('warning') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-2">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" type="email" name="email"
                        value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="your@email.com"
                        autofocus required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-2">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password" name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="••••••••" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Keep me signed in</label>
                </div>

                <button type="submit" class="sw-btn-signin">Sign In</button>
            </form>

            <div class="sw-login-links">
                <a href="{{ route('password.request') }}">Forgot password?</a>
                <a href="{{ route('register') }}">Create account</a>
            </div>
        </div>

        {{-- Scripture quote --}}
        <div class="sw-verse-quote">
            <div class="sw-verse-text">"Thy word is a lamp unto my feet, and a light unto my path."</div>
            <div class="sw-verse-ref">Psalm 119:105 · KJV</div>
        </div>
    </div>

    {{-- Scroll indicator --}}
    <a href="#landing" class="sw-scroll-indicator">
        <span class="sw-scroll-label">Explore</span>
        <svg class="sw-scroll-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </a>
</section>

{{-- ══════════════════════════ LANDING ═══════════════════════════ --}}
<div id="landing" class="sw-landing">

    {{-- Feature grid --}}
    <section class="sw-section">
        <div class="sw-container">
            <div class="sw-eyebrow">What Sword does</div>
            <h2 class="sw-h2">Every tool your study needs,<br>in one quiet place.</h2>
            <p class="sw-lead">Whether you're just beginning or have studied for years, Sword gives you a quiet place to read, reflect, and grow — one passage at a time.</p>
            <hr class="sw-rule">

            <div class="sw-features-grid">

                <div class="sw-feat">
                    <div class="sw-feat-icon"><i class="mdi mdi-book-open-page-variant"></i></div>
                    <div class="sw-feat-title">Multiple Translations</div>
                    <p class="sw-feat-desc">Read any chapter in KJV, NIV, NLT, and more. Switch translations instantly to compare wording and deepen understanding.</p>
                    <span class="sw-feat-tag">Bible Reading</span>
                </div>

                <div class="sw-feat">
                    <div class="sw-feat-icon"><i class="mdi mdi-pencil-outline"></i></div>
                    <div class="sw-feat-title">Personal Commentary</div>
                    <p class="sw-feat-desc">Write notes on individual verses or entire chapters. Your commentary stays attached to the passage, always one click away.</p>
                    <span class="sw-feat-tag">Annotations</span>
                </div>

                <div class="sw-feat">
                    <div class="sw-feat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1.25em;height:1.25em">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                            <circle cx="7" cy="7" r="1" fill="currentColor" stroke="none"/>
                            <line x1="17" y1="4" x2="22" y2="4"/>
                            <line x1="17" y1="7" x2="22" y2="7"/>
                            <line x1="17" y1="10" x2="20" y2="10"/>
                        </svg>
                    </div>
                    <div class="sw-feat-title">Topic Organization</div>
                    <p class="sw-feat-desc">Group related verses under user-defined topics. Build thematic studies around faith, forgiveness, grace — any theme you're exploring.</p>
                    <span class="sw-feat-tag">Study Tools</span>
                </div>

                <div class="sw-feat">
                    <div class="sw-feat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1.25em;height:1.25em">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <div class="sw-feat-title">Memory Verse Quiz</div>
                    <p class="sw-feat-desc">Add verses to your memory list and test yourself with a built-in quiz. Track which verses are mastered and which need more attention.</p>
                    <span class="sw-feat-tag">Memorization</span>
                </div>

                <div class="sw-feat">
                    <div class="sw-feat-icon"><i class="mdi mdi-link-variant"></i></div>
                    <div class="sw-feat-title">Cross-References</div>
                    <p class="sw-feat-desc">Link related verses together and build a web of connections across scripture. Navigate your cross-references directly from any passage.</p>
                    <span class="sw-feat-tag">Navigation</span>
                </div>

                <div class="sw-feat">
                    <div class="sw-feat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:1.25em;height:1.25em">
                            <line x1="12" y1="2" x2="12" y2="22"/>
                            <line x1="4.5" y1="8.5" x2="19.5" y2="8.5"/>
                        </svg>
                    </div>
                    <div class="sw-feat-title">Prayer Journal</div>
                    <p class="sw-feat-desc">Log prayers using the ACTS model. Review your history, see answered prayers, and develop a richer, more intentional prayer life.</p>
                    <span class="sw-feat-tag">Prayer</span>
                </div>

            </div>
        </div>
    </section>

    {{-- ACTS Prayer Section --}}
    <section class="sw-acts">
        <div class="sw-container">
            <div class="sw-eyebrow">Prayer Journal</div>
            <h2 class="sw-h2">Structured prayer. Deeper communion.</h2>
            <p class="sw-lead">The ACTS model gives your prayer time a rhythm — moving from worship to confession, gratitude to petition.</p>

            <div class="sw-acts-grid">
                <div class="sw-acts-card">
                    <div class="sw-acts-letter">A</div>
                    <div class="sw-acts-word">Adoration</div>
                    <p class="sw-acts-desc">Begin with praise. Acknowledge who God is before bringing your requests.</p>
                </div>
                <div class="sw-acts-card">
                    <div class="sw-acts-letter">C</div>
                    <div class="sw-acts-word">Confession</div>
                    <p class="sw-acts-desc">Examine your heart honestly and receive grace in return.</p>
                </div>
                <div class="sw-acts-card">
                    <div class="sw-acts-letter">T</div>
                    <div class="sw-acts-word">Thanksgiving</div>
                    <p class="sw-acts-desc">Recall His faithfulness. Record the prayers He has already answered.</p>
                </div>
                <div class="sw-acts-card">
                    <div class="sw-acts-letter">S</div>
                    <div class="sw-acts-word">Supplication</div>
                    <p class="sw-acts-desc">Bring your needs and intercede for others with confidence and faith.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Scripture showcase --}}
    <section class="sw-scripture">
        <div class="sw-container">
            <div class="sw-eyebrow">Scripture Reading</div>
            <h2 class="sw-h2">Every word, every translation.</h2>
            <p class="sw-lead">Sword stores the full text of every translation you add — always right where you left it, instantly navigable by book and chapter.</p>

            <div class="sw-scripture-card">
                <p class="sw-scripture-verse" id="sw-verse-text">
                    For the word of God is quick, and powerful, and sharper than any twoedged sword, piercing even to the dividing asunder of soul and spirit, and of the joints and marrow, and is a discerner of the thoughts and intents of the heart.
                </p>
                <div class="sw-scripture-ref" id="sw-verse-ref">Hebrews 4:12 · King James Version</div>
                <div class="sw-translation-pills">
                    <span class="sw-pill sw-pill-active"  data-trans="KJV">KJV</span>
                    <span class="sw-pill sw-pill-inactive" data-trans="NIV">NIV</span>
                    <span class="sw-pill sw-pill-inactive" data-trans="NLT">NLT</span>
                    <span class="sw-pill sw-pill-inactive" data-trans="ESV">ESV</span>
                    <span class="sw-pill sw-pill-inactive" data-trans="NASB">NASB</span>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="sw-cta">
        <div class="sw-container">
            <div class="sw-eyebrow">Get Started</div>
            <h2 class="sw-h2">Begin your study today.</h2>
            <p class="sw-lead">Create a free account and bring your Bible study into one focused, distraction&#8209;free place.</p>
            <a href="{{ route('register') }}" class="sw-cta-btn">
                <i class="mdi mdi-account-plus-outline"></i>
                Create Your Account
            </a>
        </div>
    </section>
</div>

<footer class="sw-footer">
    <p class="sw-footer-text">Sword &middot; Personal Bible Study &middot; &copy; {{ date('Y') }}</p>
</footer>

<script>
(function () {
    const verses = {
        KJV:  { text: 'For the word of God is quick, and powerful, and sharper than any twoedged sword, piercing even to the dividing asunder of soul and spirit, and of the joints and marrow, and is a discerner of the thoughts and intents of the heart.', ref: 'Hebrews 4:12 · King James Version' },
        NIV:  { text: 'For the word of God is alive and active. Sharper than any double-edged sword, it penetrates even to dividing soul and spirit, joints and marrow; it judges the thoughts and attitudes of the heart.', ref: 'Hebrews 4:12 · New International Version' },
        NLT:  { text: 'For the word of God is alive and powerful. It is sharper than the sharpest two-edged sword, cutting between soul and spirit, between joint and marrow. It exposes our innermost thoughts and desires.', ref: 'Hebrews 4:12 · New Living Translation' },
        ESV:  { text: 'For the word of God is living and active, sharper than any two-edged sword, piercing to the division of soul and of spirit, of joints and of marrow, and discerning the thoughts and intentions of the heart.', ref: 'Hebrews 4:12 · English Standard Version' },
        NASB: { text: 'For the word of God is living and active, and sharper than any two-edged sword, even penetrating as far as the division of soul and spirit, of both joints and marrow, and able to judge the thoughts and intentions of the heart.', ref: 'Hebrews 4:12 · New American Standard Bible' }
    };

    const verseEl = document.getElementById('sw-verse-text');
    const refEl   = document.getElementById('sw-verse-ref');

    document.querySelectorAll('.sw-pill[data-trans]').forEach(function (pill) {
        pill.addEventListener('click', function () {
            const trans = this.dataset.trans;
            if (!verses[trans]) return;

            verseEl.style.opacity = '0';
            refEl.style.opacity   = '0';

            setTimeout(function () {
                verseEl.textContent = verses[trans].text;
                refEl.textContent   = verses[trans].ref;
                verseEl.style.opacity = '1';
                refEl.style.opacity   = '1';
            }, 180);

            document.querySelectorAll('.sw-pill[data-trans]').forEach(function (p) {
                p.classList.replace('sw-pill-active', 'sw-pill-inactive');
            });
            this.classList.replace('sw-pill-inactive', 'sw-pill-active');
        });
    });
}());
</script>

</body>
</html>
