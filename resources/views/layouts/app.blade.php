<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Zaga Technologies') | Zaga Tech Credit</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/zz.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/zz.png') }}">
</head>
<body>

@php $cartCount = array_sum(array_column(session('cart', []), 'quantity')); @endphp

<!-- ================================================================
     SITE HEADER
     ================================================================ -->
<header class="site-header" id="siteHeader">
    <div class="hdr-container">

        <!-- Logo -->
        <a href="{{ route('home') }}" class="hdr-logo">
            <img src="{{ asset('images/logo.png') }}" alt="Zaga Technologies">
        </a>

        <!-- Search (desktop) -->
        <form action="{{ route('shop.index') }}" method="GET" class="hdr-search-form" role="search">
            <span class="hdr-search-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.099zm-5.242 1.656a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z"/>
                </svg>
            </span>
            <input type="text" name="search" placeholder="Search products…" value="{{ request('search') }}" autocomplete="off" aria-label="Search products">
            <button type="submit">Search</button>
        </form>

        <!-- Desktop Nav Links -->
        <nav class="hdr-nav" aria-label="Main navigation">
            <a href="{{ route('home') }}"       class="hdr-nav-link {{ request()->routeIs('home')   ? 'is-active' : '' }}">Home</a>
            <a href="{{ route('shop.index') }}" class="hdr-nav-link {{ request()->routeIs('shop.*') ? 'is-active' : '' }}">Shop</a>
            <a href="{{ route('about') }}"      class="hdr-nav-link {{ request()->routeIs('about')  ? 'is-active' : '' }}">About</a>
            @auth
                @unless(auth()->user()->isAdmin())
                    <a href="{{ route('orders.index') }}" class="hdr-nav-link {{ request()->routeIs('orders.*') ? 'is-active' : '' }}">My Orders</a>
                @endunless
            @endauth
        </nav>

        <!-- Right-side Actions -->
        <div class="hdr-actions">

            <!-- Cart -->
            <a href="{{ route('cart.index') }}" class="hdr-cart-btn" aria-label="Cart ({{ $cartCount }} items)">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM5 13a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </svg>
                <span class="hdr-cart-badge" id="cartCount" style="{{ $cartCount > 0 ? '' : 'display:none' }}">{{ $cartCount }}</span>
            </a>

            @auth
                <!-- ── User Dropdown (desktop) ── -->
                <div class="hdr-user-wrap" id="hdrUserWrap">
                    <button class="hdr-user-btn" id="hdrUserBtn" aria-haspopup="true" aria-expanded="false" aria-controls="hdrUserDropdown">
                        <span class="hdr-avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="hdr-user-name">{{ explode(' ', trim(auth()->user()->name))[0] }}</span>
                        <svg class="hdr-chevron" xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                            <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
                        </svg>
                    </button>

                    <div class="hdr-user-dropdown" id="hdrUserDropdown" role="menu" aria-labelledby="hdrUserBtn">
                        <!-- Header -->
                        <div class="hdr-dd-header">
                            <span class="hdr-dd-avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            <div class="hdr-dd-info">
                                <div class="hdr-dd-name">{{ auth()->user()->name }}</div>
                                <div class="hdr-dd-email">{{ auth()->user()->email }}</div>
                            </div>
                        </div>
                        <div class="hdr-dd-divider" role="separator"></div>

                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="hdr-dd-item" role="menuitem">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M2 10h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1zm9-9h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-3a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zm0 9h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-3a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1zM2 1h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/></svg>
                                Admin Dashboard
                            </a>
                        @else
                            <a href="{{ route('orders.index') }}" class="hdr-dd-item" role="menuitem">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5z"/></svg>
                                My Orders
                            </a>
                        @endif

                        <a href="{{ route('profile.edit') }}" class="hdr-dd-item" role="menuitem">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4z"/></svg>
                            My Profile
                        </a>

                        <div class="hdr-dd-divider" role="separator"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="hdr-dd-item hdr-dd-logout" role="menuitem">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/><path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/></svg>
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>

            @else
                <!-- ── Guest Auth Buttons (desktop) ── -->
                <div class="hdr-auth-btns">
                    <a href="{{ route('login') }}"    class="hdr-signin-btn">Sign In</a>
                    <a href="{{ route('register') }}" class="hdr-register-btn">Register</a>
                </div>
            @endauth

            <!-- Hamburger (mobile only) -->
            <button class="hdr-hamburger" id="hdrHamburger" aria-label="Open menu" aria-expanded="false" aria-controls="mobileDrawer">
                <span></span>
                <span></span>
                <span></span>
            </button>

        </div><!-- /.hdr-actions -->
    </div><!-- /.hdr-container -->
</header>

<!-- ================================================================
     MOBILE DRAWER
     ================================================================ -->
<div class="mobile-drawer" id="mobileDrawer" aria-hidden="true" aria-label="Mobile navigation">
    <div class="drawer-panel">

        <!-- Drawer Header -->
        <div class="drawer-header">
            <a href="{{ route('home') }}">
                <img src="{{ asset('images/logo.png') }}" alt="Zaga Technologies" class="drawer-logo">
            </a>
            <button class="drawer-close" id="drawerClose" aria-label="Close menu">&times;</button>
        </div>

        <!-- Drawer Search -->
        <form action="{{ route('shop.index') }}" method="GET" class="drawer-search" role="search">
            <input type="text" name="search" placeholder="Search products…" value="{{ request('search') }}" autocomplete="off" aria-label="Search products">
            <button type="submit">Search</button>
        </form>

        <!-- Drawer Nav -->
        <nav class="drawer-nav" aria-label="Mobile navigation">
            <a href="{{ route('home') }}"       class="drawer-link {{ request()->routeIs('home')   ? 'is-active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5v-4h3v4H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.354 1.146z"/></svg>
                Home
            </a>
            <a href="{{ route('shop.index') }}" class="drawer-link {{ request()->routeIs('shop.*') ? 'is-active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M2.97 1.35A1 1 0 0 1 3.73 1h8.54a1 1 0 0 1 .76.35l2.609 3.044A1.5 1.5 0 0 1 16 5.37v.255a2.375 2.375 0 0 1-4.25 1.458A2.371 2.371 0 0 1 9.875 8 2.37 2.37 0 0 1 8 7.083 2.37 2.37 0 0 1 6.125 8a2.37 2.37 0 0 1-1.875-.917A2.375 2.375 0 0 1 0 5.625V5.37a1.5 1.5 0 0 1 .361-.976l2.61-3.045z"/></svg>
                Shop
            </a>
            <a href="{{ route('about') }}"      class="drawer-link {{ request()->routeIs('about')  ? 'is-active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/></svg>
                About Us
            </a>
            <a href="{{ route('cart.index') }}"  class="drawer-link drawer-cart-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5z"/></svg>
                Cart
                @if($cartCount > 0)
                    <span class="drawer-cart-count">{{ $cartCount }}</span>
                @endif
            </a>
            @auth
                @unless(auth()->user()->isAdmin())
                    <a href="{{ route('orders.index') }}" class="drawer-link {{ request()->routeIs('orders.*') ? 'is-active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5z"/></svg>
                        My Orders
                    </a>
                @endunless
            @endauth
        </nav>

        <!-- Spacer -->
        <div class="drawer-spacer"></div>

        @auth
            <!-- Logged-in user section -->
            <div class="drawer-user-section">
                <div class="drawer-user-info">
                    <span class="drawer-avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <div>
                        <div class="drawer-user-name">{{ auth()->user()->name }}</div>
                        <div class="drawer-user-email">{{ auth()->user()->email }}</div>
                    </div>
                </div>
                <a href="{{ route('profile.edit') }}" class="drawer-profile-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4z"/></svg>
                    My Profile
                </a>
                <form method="POST" action="{{ route('logout') }}" style="padding: 0 16px 16px;">
                    @csrf
                    <button type="submit" class="drawer-logout-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/><path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/></svg>
                        Sign Out
                    </button>
                </form>
            </div>
        @else
            <!-- Guest auth section -->
            <div class="drawer-auth-section">
                <a href="{{ route('login') }}"    class="drawer-signin-btn">Sign In</a>
                <a href="{{ route('register') }}" class="drawer-register-btn">Create Account</a>
            </div>
        @endauth

    </div><!-- /.drawer-panel -->
</div><!-- /#mobileDrawer -->

<!-- Drawer overlay -->
<div class="drawer-overlay" id="drawerOverlay"></div>

<!-- ================================================================
     FLASH MESSAGES
     ================================================================ -->
@if(session('success'))
    <div class="alert alert-success" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-error" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/></svg>
        {{ session('error') }}
    </div>
@endif

<main>
    @yield('content')
</main>

<!-- ================================================================
     FOOTER
     ================================================================ -->
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About Us</h3>
                <p><a href="#">How it works</a></p>
                <p><a href="#">Our Partners</a></p>
                <p><a href="{{ route('about') }}#testimonials-heading">Financed success stories</a></p>
                <p><a href="#">FAQs</a></p>
            </div>
            <div class="footer-section">
                <h3>Payment Terms</h3>
                <p><a href="https://wa.me/256700706809" target="_blank" rel="noopener">Apply Now</a></p>
                <p><a href="#">Terms &amp; Conditions</a></p>
                <p><a href="https://wa.me/256700706809" target="_blank" rel="noopener">Delivery Tracking</a></p>
                <p><a href="#">Privacy Policy</a></p>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>Address: Kabaka Kintu House Level 1 Shop no C-03 Kampala Road-Kampala-Uganda</p>
                <p>Email: sales2.zagatechnologiesltd@gmail.com</p>
                <p>Phone: +256 700 706809</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} Zaga Technologies Ltd. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="{{ asset('js/script.js') }}"></script>
<script>
(function () {
    'use strict';

    /* ── Hamburger / Drawer ── */
    const hamburger   = document.getElementById('hdrHamburger');
    const drawer      = document.getElementById('mobileDrawer');
    const overlay     = document.getElementById('drawerOverlay');
    const drawerClose = document.getElementById('drawerClose');

    function openDrawer() {
        drawer.classList.add('is-open');
        overlay.classList.add('is-open');
        hamburger.classList.add('is-open');
        hamburger.setAttribute('aria-expanded', 'true');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
        drawer.classList.remove('is-open');
        overlay.classList.remove('is-open');
        hamburger.classList.remove('is-open');
        hamburger.setAttribute('aria-expanded', 'false');
        drawer.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    hamburger.addEventListener('click', () =>
        drawer.classList.contains('is-open') ? closeDrawer() : openDrawer()
    );
    overlay.addEventListener('click', closeDrawer);
    drawerClose.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeDrawer(); });

    /* ── User Dropdown ── */
    const userBtn      = document.getElementById('hdrUserBtn');
    const userDropdown = document.getElementById('hdrUserDropdown');

    if (userBtn && userDropdown) {
        userBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const open = userDropdown.classList.toggle('is-open');
            userBtn.setAttribute('aria-expanded', String(open));
        });

        document.addEventListener('click', (e) => {
            if (!userBtn.contains(e.target)) {
                userDropdown.classList.remove('is-open');
                userBtn.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && userDropdown.classList.contains('is-open')) {
                userDropdown.classList.remove('is-open');
                userBtn.setAttribute('aria-expanded', 'false');
                userBtn.focus();
            }
        });
    }

    /* ── Auto-dismiss flash alerts ── */
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity .4s';
            alert.style.opacity    = '0';
            setTimeout(() => alert.remove(), 400);
        }, 4000);
    });

})();
</script>
@stack('scripts')
</body>
</html>
