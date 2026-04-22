<div class="horizontal-menu">
    <nav class="navbar top-navbar col-lg-12 col-12 p-0">
        <div class="container-fluid">
            <div class="navbar-menu-wrapper d-flex align-items-center justify-content-between">
                <div class="navbar-brand-wrapper d-flex align-items-center justify-content-start">
                    <a class="navbar-brand brand-logo" href="{{ route('home.index') }}"><img src="/images/logo.png" alt="logo"/></a>
                    <a class="navbar-brand brand-logo-mini" href="{{ route('home.index') }}"><img src="/images/logo.png" alt="logo"/></a>
                </div>
                <ul class="navbar-nav navbar-nav-left">
                    <li class="nav-item dropdown d-none d-lg-flex">
                        <a class="nav-link count-indicator dropdown-toggle d-flex align-items-center justify-content-center" id="notificationDropdown" href="#" data-bs-toggle="dropdown">
                            <i class="mdi mdi-bell mx-0"></i>
                            <span class="count bg-success">2</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
                            <p class="mb-0 font-weight-normal float-left dropdown-header">Notifications</p>
                            <a class="dropdown-item preview-item">
                                <div class="preview-thumbnail">
                                    <div class="preview-icon bg-success">
                                        <i class="mdi mdi-information mx-0"></i>
                                    </div>
                                </div>
                                <div class="preview-item-content">
                                    <h6 class="preview-subject font-weight-normal">Application Error</h6>
                                    <p class="font-weight-light small-text mb-0 text-muted">Just now</p>
                                </div>
                            </a>
                        </div>
                    </li>
                    <li class="nav-item nav-search d-none d-lg-block ms-3">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="search">
                                <i class="mdi mdi-magnify"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" placeholder="search" aria-label="search" aria-describedby="search">
                        </div>
                    </li>
                </ul>

                <ul class="navbar-nav navbar-nav-right">
<li class="nav-item nav-profile dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" id="profileDropdown">
                            <span class="nav-profile-name">{{ auth()->user()->name }}</span>
                            <span class="online-status"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
                            <a class="dropdown-item">
                                <i class="mdi mdi-settings text-primary"></i>
                                Settings
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="mdi mdi-logout text-primary"></i>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </li>
                </ul>

                {{-- Mobile: bell next to hamburger --}}
                <a class="mob-bell d-lg-none" href="#" data-bs-toggle="dropdown" data-bs-target="#mobBellDropdown" aria-label="Notifications">
                    <i class="mdi mdi-bell"></i>
                    <span class="mob-bell-count">2</span>
                </a>
                <div id="mobBellDropdown" class="dropdown-menu dropdown-menu-end navbar-dropdown preview-list">
                    <p class="mb-0 font-weight-normal float-left dropdown-header">Notifications</p>
                    <a class="dropdown-item preview-item">
                        <div class="preview-thumbnail">
                            <div class="preview-icon bg-success">
                                <i class="mdi mdi-information mx-0"></i>
                            </div>
                        </div>
                        <div class="preview-item-content">
                            <h6 class="preview-subject font-weight-normal">Application Error</h6>
                            <p class="font-weight-light small-text mb-0 text-muted">Just now</p>
                        </div>
                    </a>
                </div>

                {{-- Mobile hamburger --}}
                <button class="mob-hamburger d-lg-none" id="mobHamburger" aria-label="Open navigation" type="button">
                    <span class="mob-ham-bar"></span>
                    <span class="mob-ham-bar"></span>
                    <span class="mob-ham-bar"></span>
                </button>
            </div>
        </div>
    </nav>

    {{-- Desktop bottom nav (unchanged) --}}
    <nav class="bottom-navbar">
        <div class="container">
            <ul class="nav page-navigation">
                <li class="nav-item {{ request()->routeIs('home.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('home.index') }}">
                        <i class="mdi mdi-view-dashboard menu-icon"></i>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('translations.*') || request()->routeIs('books.*') || request()->routeIs('chapters.*') ? 'active' : '' }}">
                    <a href="{{ route('translations.index') }}" class="nav-link">
                        <i class="mdi mdi-book-open-page-variant menu-icon"></i>
                        <span class="menu-title">Translations</span>
                        <i class="menu-arrow"></i>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('prayers.*') ? 'active' : '' }}">
                    <a href="{{ route('prayers.index') }}" class="nav-link">
                        <i class="mdi mdi-heart menu-icon"></i>
                        <span class="menu-title">Prayers</span>
                        <i class="menu-arrow"></i>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('topics.*') ? 'active' : '' }}">
                    <a href="{{ route('topics.index') }}" class="nav-link">
                        <i class="mdi mdi-tag-multiple menu-icon"></i>
                        <span class="menu-title">Topics</span>
                        <i class="menu-arrow"></i>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('memory.*') ? 'active' : '' }}">
                    <a href="{{ route('memory.index') }}" class="nav-link">
                        <i class="mdi mdi-brain menu-icon"></i>
                        <span class="menu-title">Memory</span>
                        <i class="menu-arrow"></i>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('commentary.*') ? 'active' : '' }}">
                    <a href="{{ route('commentary.index') }}" class="nav-link">
                        <i class="mdi mdi-file-document-outline menu-icon"></i>
                        <span class="menu-title">Commentary</span>
                        <i class="menu-arrow"></i>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</div>

{{-- ============================================================
     Mobile full-screen drawer (only rendered on mobile via CSS)
     ============================================================ --}}
<div id="mobNavOverlay" aria-hidden="true"></div>

<nav id="mobNavDrawer" aria-label="Mobile navigation" role="navigation">
    {{-- Header --}}
    <div class="mob-drawer-header">
        <a href="{{ route('home.index') }}" class="mob-drawer-logo">
            <img src="/images/logo.png" alt="Sword">
            <span class="mob-drawer-logo-text">Sword</span>
        </a>
        <button id="mobDrawerClose" class="mob-drawer-close" aria-label="Close navigation">
            <i class="mdi mdi-close"></i>
        </button>
    </div>

    {{-- Nav section label --}}
    <p class="mob-drawer-section-label">Navigate</p>

    {{-- Main nav links --}}
    <ul class="mob-drawer-nav">
        <li class="{{ request()->routeIs('home.*') ? 'mob-active' : '' }}">
            <a href="{{ route('home.index') }}">
                <span class="mob-nav-icon"><i class="mdi mdi-view-dashboard"></i></span>
                <span class="mob-nav-label">Dashboard</span>
                @if(request()->routeIs('home.*'))
                    <span class="mob-nav-pip"></span>
                @endif
            </a>
        </li>
        <li class="{{ (request()->routeIs('translations.*') || request()->routeIs('books.*') || request()->routeIs('chapters.*')) ? 'mob-active' : '' }}">
            <a href="{{ route('translations.index') }}">
                <span class="mob-nav-icon"><i class="mdi mdi-book-open-page-variant"></i></span>
                <span class="mob-nav-label">Translations</span>
                @if(request()->routeIs('translations.*') || request()->routeIs('books.*') || request()->routeIs('chapters.*'))
                    <span class="mob-nav-pip"></span>
                @endif
            </a>
        </li>
        <li class="{{ request()->routeIs('prayers.*') ? 'mob-active' : '' }}">
            <a href="{{ route('prayers.index') }}">
                <span class="mob-nav-icon"><i class="mdi mdi-heart"></i></span>
                <span class="mob-nav-label">Prayers</span>
                @if(request()->routeIs('prayers.*'))
                    <span class="mob-nav-pip"></span>
                @endif
            </a>
        </li>
        <li class="{{ request()->routeIs('topics.*') ? 'mob-active' : '' }}">
            <a href="{{ route('topics.index') }}">
                <span class="mob-nav-icon"><i class="mdi mdi-tag-multiple"></i></span>
                <span class="mob-nav-label">Topics</span>
                @if(request()->routeIs('topics.*'))
                    <span class="mob-nav-pip"></span>
                @endif
            </a>
        </li>
        <li class="{{ request()->routeIs('memory.*') ? 'mob-active' : '' }}">
            <a href="{{ route('memory.index') }}">
                <span class="mob-nav-icon"><i class="mdi mdi-brain"></i></span>
                <span class="mob-nav-label">Memory</span>
                @if(request()->routeIs('memory.*'))
                    <span class="mob-nav-pip"></span>
                @endif
            </a>
        </li>
        <li class="{{ request()->routeIs('commentary.*') ? 'mob-active' : '' }}">
            <a href="{{ route('commentary.index') }}">
                <span class="mob-nav-icon"><i class="mdi mdi-file-document-outline"></i></span>
                <span class="mob-nav-label">Commentary</span>
                @if(request()->routeIs('commentary.*'))
                    <span class="mob-nav-pip"></span>
                @endif
            </a>
        </li>
    </ul>

    {{-- Footer area --}}
    <div class="mob-drawer-footer">
        <div class="mob-drawer-divider"></div>
        <p class="mob-drawer-section-label">Account</p>
        <ul class="mob-drawer-nav mob-drawer-nav-footer">
            <li>
                <a href="#">
                    <span class="mob-nav-icon"><i class="mdi mdi-cog-outline"></i></span>
                    <span class="mob-nav-label">Settings</span>
                </a>
            </li>
            <li>
                <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                    @csrf
                    <button type="submit" class="mob-logout-btn">
                        <span class="mob-nav-icon"><i class="mdi mdi-logout"></i></span>
                        <span class="mob-nav-label">Sign out</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
</nav>

<style>
/* ================================================================
   MOBILE DRAWER NAVIGATION
   Only active below lg breakpoint (992px)
   ================================================================ */

/* Hide mobile elements on desktop */
@media (min-width: 992px) {
    .mob-hamburger,
    #mobNavOverlay,
    #mobNavDrawer { display: none !important; }
}

/* Hide old hamburger on mobile */
@media (max-width: 991px) {
    .navbar-toggler.navbar-toggler-right { display: none !important; }
}

/* ── Hamburger button ─────────────────────────────────────────── */
.mob-hamburger {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 5px;
    width: 40px;
    height: 40px;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 6px;
    border-radius: 8px;
    transition: background 0.2s;
    flex-shrink: 0;
}
.mob-hamburger:hover { background: rgba(70, 77, 238, 0.08); }

.mob-ham-bar {
    display: block;
    width: 22px;
    height: 2px;
    background: #27367f;
    border-radius: 2px;
    transition: transform 0.3s cubic-bezier(.4,0,.2,1), opacity 0.2s, width 0.3s;
    transform-origin: center;
}

/* Hamburger → X when open */
body.mob-nav-open .mob-ham-bar:nth-child(1) {
    transform: translateY(7px) rotate(45deg);
}
body.mob-nav-open .mob-ham-bar:nth-child(2) {
    opacity: 0;
    width: 0;
}
body.mob-nav-open .mob-ham-bar:nth-child(3) {
    transform: translateY(-7px) rotate(-45deg);
}

/* ── Overlay ──────────────────────────────────────────────────── */
#mobNavOverlay {
    position: fixed;
    inset: 0;
    z-index: 1040;
    background: rgba(8, 12, 30, 0.65);
    backdrop-filter: blur(3px);
    -webkit-backdrop-filter: blur(3px);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.35s cubic-bezier(.4,0,.2,1);
}
body.mob-nav-open #mobNavOverlay {
    opacity: 1;
    pointer-events: all;
}

/* ── Drawer panel ─────────────────────────────────────────────── */
#mobNavDrawer {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: min(320px, 88vw);
    z-index: 1050;
    display: flex;
    flex-direction: column;

    /* Illuminated manuscript palette */
    background: #0e1628;
    background-image:
        radial-gradient(ellipse at 0% 0%, rgba(201,168,76,0.06) 0%, transparent 55%),
        radial-gradient(ellipse at 100% 100%, rgba(70,77,238,0.08) 0%, transparent 50%);
    border-left: 1px solid rgba(201, 168, 76, 0.18);

    transform: translateX(100%);
    transition: transform 0.38s cubic-bezier(.4,0,.2,1);
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;

    /* Subtle grain texture via svg */
    --grain: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
}

body.mob-nav-open #mobNavDrawer {
    transform: translateX(0);
    box-shadow: -8px 0 40px rgba(0,0,0,0.5);
}

/* Prevent body scroll when drawer open */
body.mob-nav-open { overflow: hidden; }

/* ── Drawer header ────────────────────────────────────────────── */
.mob-drawer-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px 16px;
    border-bottom: 1px solid rgba(201, 168, 76, 0.12);
    flex-shrink: 0;
}

.mob-drawer-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}
.mob-drawer-logo img {
    height: 36px;
    width: auto;
    object-fit: contain;
}
.mob-drawer-logo-text {
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 1.15rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    color: #c9a84c;
    text-transform: uppercase;
}

.mob-drawer-close {
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px;
    color: rgba(255,255,255,0.5);
    font-size: 1.2rem;
    cursor: pointer;
    transition: all 0.2s;
    padding: 0;
    line-height: 1;
}
.mob-drawer-close:hover {
    background: rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.9);
}
.mob-drawer-close .mdi { line-height: 1; }

/* ── Section labels ───────────────────────────────────────────── */
.mob-drawer-section-label {
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(201, 168, 76, 0.55);
    padding: 18px 24px 6px;
    margin: 0;
}

/* ── Nav list ─────────────────────────────────────────────────── */
.mob-drawer-nav {
    list-style: none;
    margin: 0;
    padding: 4px 0;
}

.mob-drawer-nav li {
    position: relative;
}

.mob-drawer-nav li a,
.mob-logout-btn {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 13px 24px;
    text-decoration: none;
    color: rgba(220, 225, 240, 0.72);
    font-size: 0.93rem;
    font-weight: 500;
    letter-spacing: 0.01em;
    transition: all 0.18s;
    position: relative;
    border-radius: 0;
    width: 100%;
    background: transparent;
    border: none;
    cursor: pointer;
    text-align: left;
    font-family: inherit;
}

.mob-drawer-nav li a::before,
.mob-logout-btn::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 0;
    background: #c9a84c;
    border-radius: 0 2px 2px 0;
    transition: height 0.2s cubic-bezier(.4,0,.2,1);
}

.mob-drawer-nav li a:hover,
.mob-logout-btn:hover {
    color: rgba(255, 255, 255, 0.95);
    background: rgba(255,255,255,0.04);
}

.mob-drawer-nav li a:hover::before,
.mob-logout-btn:hover::before {
    height: 60%;
}

/* Active state */
.mob-drawer-nav li.mob-active > a {
    color: #fff;
    background: rgba(201, 168, 76, 0.08);
}
.mob-drawer-nav li.mob-active > a::before {
    height: 65%;
    background: #c9a84c;
}

/* ── Nav icon ─────────────────────────────────────────────────── */
.mob-nav-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: rgba(255,255,255,0.05);
    font-size: 1.05rem;
    color: rgba(201, 168, 76, 0.7);
    transition: all 0.18s;
    flex-shrink: 0;
}

.mob-drawer-nav li a:hover .mob-nav-icon,
.mob-logout-btn:hover .mob-nav-icon {
    background: rgba(201, 168, 76, 0.12);
    color: #c9a84c;
}

.mob-drawer-nav li.mob-active > a .mob-nav-icon {
    background: rgba(201, 168, 76, 0.15);
    color: #c9a84c;
}

/* ── Active pip ───────────────────────────────────────────────── */
.mob-nav-pip {
    margin-left: auto;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #c9a84c;
    flex-shrink: 0;
}

/* ── Drawer footer ────────────────────────────────────────────── */
.mob-drawer-footer {
    margin-top: auto;
    flex-shrink: 0;
}

.mob-drawer-divider {
    height: 1px;
    background: rgba(201, 168, 76, 0.1);
    margin: 8px 0 0;
}

/* Footer nav items — slightly dimmer icons */
.mob-drawer-nav-footer .mob-nav-icon {
    color: rgba(160,168,200,0.6);
}
.mob-drawer-nav-footer li a:hover .mob-nav-icon,
.mob-drawer-nav-footer .mob-logout-btn:hover .mob-nav-icon {
    color: #e07070;
    background: rgba(224, 112, 112, 0.1);
}

/* ── Staggered entrance animation ────────────────────────────── */
#mobNavDrawer .mob-drawer-header,
#mobNavDrawer .mob-drawer-section-label,
#mobNavDrawer .mob-drawer-nav li,
#mobNavDrawer .mob-drawer-footer {
    opacity: 0;
    transform: translateX(16px);
    transition: opacity 0.28s ease, transform 0.28s ease;
}

body.mob-nav-open #mobNavDrawer .mob-drawer-header   { opacity: 1; transform: none; transition-delay: 0.06s; }
body.mob-nav-open #mobNavDrawer .mob-drawer-section-label:first-of-type { opacity: 1; transform: none; transition-delay: 0.10s; }
body.mob-nav-open #mobNavDrawer .mob-drawer-nav:not(.mob-drawer-nav-footer) li:nth-child(1) { opacity: 1; transform: none; transition-delay: 0.13s; }
body.mob-nav-open #mobNavDrawer .mob-drawer-nav:not(.mob-drawer-nav-footer) li:nth-child(2) { opacity: 1; transform: none; transition-delay: 0.16s; }
body.mob-nav-open #mobNavDrawer .mob-drawer-nav:not(.mob-drawer-nav-footer) li:nth-child(3) { opacity: 1; transform: none; transition-delay: 0.19s; }
body.mob-nav-open #mobNavDrawer .mob-drawer-nav:not(.mob-drawer-nav-footer) li:nth-child(4) { opacity: 1; transform: none; transition-delay: 0.22s; }
body.mob-nav-open #mobNavDrawer .mob-drawer-nav:not(.mob-drawer-nav-footer) li:nth-child(5) { opacity: 1; transform: none; transition-delay: 0.25s; }
body.mob-nav-open #mobNavDrawer .mob-drawer-nav:not(.mob-drawer-nav-footer) li:nth-child(6) { opacity: 1; transform: none; transition-delay: 0.28s; }
body.mob-nav-open #mobNavDrawer .mob-drawer-footer   { opacity: 1; transform: none; transition-delay: 0.32s; }
</style>

<script>
(function () {
    function openDrawer() {
        document.body.classList.add('mob-nav-open');
        document.getElementById('mobNavDrawer').setAttribute('aria-hidden', 'false');
        document.getElementById('mobNavOverlay').setAttribute('aria-hidden', 'false');
    }
    function closeDrawer() {
        document.body.classList.remove('mob-nav-open');
        document.getElementById('mobNavDrawer').setAttribute('aria-hidden', 'true');
        document.getElementById('mobNavOverlay').setAttribute('aria-hidden', 'true');
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('mobHamburger').addEventListener('click', function () {
            document.body.classList.contains('mob-nav-open') ? closeDrawer() : openDrawer();
        });
        document.getElementById('mobDrawerClose').addEventListener('click', closeDrawer);
        document.getElementById('mobNavOverlay').addEventListener('click', closeDrawer);

        // Close on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeDrawer();
        });
    });
}());
</script>
