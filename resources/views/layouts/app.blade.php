<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-P89WMWVV');
    </script>
    <!-- End Google Tag Manager -->

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', \App\Models\SiteSetting::get('default_meta_title', 'IG Reel Downloader - Best Instagram Downloader | IGReelDownloader.net'))</title>
    <meta name="description" content="@yield('description', \App\Models\SiteSetting::get('default_meta_description', 'With IG Reel Downloader, download any reels, videos and photos from Instagram easily. Free, fast, and no login required.'))">
    <meta name="keywords" content="@yield('keywords', \App\Models\SiteSetting::get('default_meta_keywords', 'instagram downloader, reels downloader, ig video downloader'))">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="@yield('title', 'IG Reel Downloader - Best Instagram Downloader')">
    <meta property="og:description" content="@yield('description', 'Download Instagram Reels, Videos, Photos in HD quality. Free & Fast.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ \App\Models\SiteSetting::get('site_name', 'IGReelDownloader.net') }}">

    <!-- Google Verification -->
    <meta name="google-site-verification" content="HLm7mAoZRSn3hThxLNb4XWm_Qu4g5meVXmyoZxzP_Dg" />

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'IG Reel Downloader')">
    <meta name="twitter:description" content="@yield('description', 'Download Instagram content easily.')">

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Tailwind v4 Dark Mode Configuration -->
    <style type="text/tailwindcss">
        @custom-variant dark (&:where(.dark, .dark *));
    </style>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml"
        href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%23E1306C' rx='20' width='100' height='100'/><text x='50%' y='50%' dominant-baseline='central' text-anchor='middle' font-size='50'>📥</text></svg>">

    <style>
        * {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .instagram-gradient {
            background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
        }

        .instagram-gradient-text {
            background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-gradient {
            background: radial-gradient(ellipse at top, rgba(139, 92, 246, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at bottom right, rgba(236, 72, 153, 0.1) 0%, transparent 50%);
        }

        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(168, 85, 247, 0.4);
            }

            50% {
                box-shadow: 0 0 40px rgba(168, 85, 247, 0.6);
            }
        }

        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .loader {
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-bottom-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .skeleton {
            background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite;
        }

        html:not(.dark) .skeleton {
            background: linear-gradient(90deg, #e5e7eb 25%, #d1d5db 50%, #e5e7eb 75%);
            background-size: 200% 100%;
        }

        @keyframes skeleton-loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-up {
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Mobile menu */
        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }

        .mobile-menu.open {
            transform: translateX(0);
        }

        /* Nav link active state */
        .nav-link.active {
            color: #a855f7 !important;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(45deg, #f09433, #dc2743, #bc1888);
            border-radius: 2px;
        }

        .mobile-nav-link.active {
            background-color: rgba(139, 92, 246, 0.1);
            color: #a855f7;
        }

        /* Card hover effects */
        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .dark .feature-card:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #1f2937;
        }

        html:not(.dark) ::-webkit-scrollbar-track {
            background: #f3f4f6;
        }

        ::-webkit-scrollbar-thumb {
            background: #4b5563;
            border-radius: 4px;
        }

        html:not(.dark) ::-webkit-scrollbar-thumb {
            background: #9ca3af;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }

        /* Theme transition */
        html.theme-transition,
        html.theme-transition *,
        html.theme-transition *:before,
        html.theme-transition *:after {
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease !important;
        }

        /* Scroll to Top Button */
        #scrollToTop {
            position: fixed !important;
            bottom: 1.5rem !important;
            right: 1.5rem !important;
            left: auto !important;
        }

        .scroll-top-float {
            animation: scrollTopFloat 3s ease-in-out infinite;
            cursor: pointer;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1),
                box-shadow 0.3s ease,
                opacity 0.3s ease,
                visibility 0.3s ease;
        }

        .scroll-top-float:hover {
            animation: none;
            transform: translateY(-12px) scale(1.1);
            box-shadow: 0 20px 40px rgba(236, 72, 153, 0.4),
                0 0 20px rgba(168, 85, 247, 0.3),
                0 0 40px rgba(236, 72, 153, 0.2);
        }

        @keyframes scrollTopFloat {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        .scroll-top-visible {
            opacity: 1 !important;
            visibility: visible !important;
        }

        .scroll-top-float::before {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 50%;
            background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .scroll-top-float:hover::before {
            opacity: 1;
            animation: glowPulse 1.5s ease-in-out infinite;
        }

        @keyframes glowPulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.7;
            }

            50% {
                transform: scale(1.15);
                opacity: 0.4;
            }
        }

        .scroll-top-ripple {
            position: relative;
            overflow: hidden;
        }

        .scroll-top-ripple::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.4s ease, height 0.4s ease;
        }

        .scroll-top-ripple:active::after {
            width: 200%;
            height: 200%;
        }

        .scroll-top-float svg {
            transition: transform 0.3s ease;
        }

        .scroll-top-float:hover svg {
            transform: translateY(-2px);
            animation: arrowBounce 0.6s ease-in-out infinite;
        }

        @keyframes arrowBounce {

            0%,
            100% {
                transform: translateY(-2px);
            }

            50% {
                transform: translateY(-6px);
            }
        }
    </style>

    <!-- Dark Mode Script (runs before page renders to prevent flash) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (savedTheme === 'light') {
                document.documentElement.classList.remove('dark');
            } else if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
            } else if (prefersDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>

<body class="bg-gray-50 dark:bg-gray-950 min-h-screen transition-colors duration-300">

    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-P89WMWVV" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    
    @php
        $mainMenu = \App\Models\Menu::getItems('main');
        $footerDownloaders = \App\Models\Menu::getItems('footer-downloaders');
        $footerLegal = \App\Models\Menu::getItems('footer-legal');
    @endphp

    <!-- Header -->
    <header
        class="sticky top-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-lg border-b border-gray-200 dark:border-gray-800 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center space-x-2 group">
                    <div
                        class="w-10 h-10 instagram-gradient rounded-xl flex items-center justify-center transform group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                        </svg>
                    </div>
                    <span class="font-bold text-xl text-gray-900 dark:text-white hidden sm:block">
                        {{ \App\Models\SiteSetting::get('site_name', 'IGReelDownloader') }}<span
                            class="text-violet-500">.net</span>
                    </span>
                </a>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center space-x-1">
                    @forelse($mainMenu as $item)
                        <a href="{{ $item['url'] }}" target="{{ $item['target'] ?? '_self' }}"
                            class="nav-link relative px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-violet-600 dark:hover:text-violet-400 transition-colors {{ $item['is_active'] ? 'active' : '' }}">
                            @if (!empty($item['icon']))
                                {{ $item['icon'] }}
                            @endif{{ $item['title'] }}
                        </a>
                    @empty
                        <a href="{{ route('home') }}"
                            class="nav-link relative px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-violet-600 dark:hover:text-violet-400 transition-colors">Home</a>
                    @endforelse
                </nav>

                <!-- Right Section -->
                <div class="flex items-center space-x-3">
                    <!-- Dark/Light Mode Toggle -->
                    <button id="themeToggle"
                        class="p-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                        title="Toggle theme" aria-label="Toggle dark mode">
                        <svg id="sunIcon" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                        <svg id="moonIcon" class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z">
                            </path>
                        </svg>
                    </button>

                    <!-- Mobile Menu Button -->
                    <button id="mobileMenuBtn"
                        class="md:hidden p-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="mobile-menu fixed inset-0 z-50 md:hidden">
            <div class="absolute inset-0 bg-black/50" id="mobileMenuOverlay"></div>
            <div class="absolute left-0 top-0 bottom-0 w-72 bg-white dark:bg-gray-900 shadow-xl">
                <div class="p-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <span class="font-bold text-lg text-gray-900 dark:text-white">Menu</span>
                    <button id="mobileMenuClose" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <nav class="p-4 space-y-2">
                    @forelse($mainMenu as $item)
                        <a href="{{ $item['url'] }}" target="{{ $item['target'] ?? '_self' }}"
                            class="mobile-nav-link block px-4 py-3 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 font-medium {{ $item['is_active'] ? 'active' : '' }}">
                            @if (!empty($item['icon']))
                                {{ $item['icon'] }}
                            @endif{{ $item['title'] }}
                        </a>
                    @empty
                        <a href="{{ route('home') }}"
                            class="mobile-nav-link block px-4 py-3 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 font-medium">🏠
                            Home</a>
                    @endforelse

                    <div class="border-t border-gray-200 dark:border-gray-700 my-4"></div>

                    @foreach ($footerLegal as $item)
                        <a href="{{ $item['url'] }}" target="{{ $item['target'] ?? '_self' }}"
                            class="mobile-nav-link block px-4 py-3 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 font-medium">
                            @if (!empty($item['icon']))
                                {{ $item['icon'] }}
                            @endif{{ $item['title'] }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Scroll to Top Button -->
    <button id="scrollToTop"
        class="fixed bottom-6 right-6 z-50 w-14 h-14 rounded-full instagram-gradient text-white shadow-lg shadow-pink-500/30 flex items-center justify-center opacity-0 invisible translate-y-4 focus:outline-none focus:ring-4 focus:ring-violet-500/30 scroll-top-float scroll-top-ripple"
        aria-label="Scroll to top" title="Scroll to top">
        <svg class="w-6 h-6 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18">
            </path>
        </svg>
    </button>

    <!-- Footer -->
    <footer class="bg-gray-100 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Brand -->
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-10 h-10 instagram-gradient rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                            </svg>
                        </div>
                        <span
                            class="font-bold text-xl text-gray-900 dark:text-white">{{ \App\Models\SiteSetting::get('site_name', 'IGReelDownloader') }}<span
                                class="text-violet-500">.net</span></span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm max-w-md mb-4">
                        {{ \App\Models\SiteSetting::get('site_description', 'The fastest and most reliable way to download Instagram Reels, Videos, Photos, Stories, and Carousel posts in HD quality. Free, fast, and no login required.') }}
                    </p>
                    <p class="text-gray-500 dark:text-gray-500 text-xs">
                        {{ \App\Models\SiteSetting::get('footer_text', 'We respect intellectual property rights. Please download content for personal use only.') }}
                    </p>
                </div>

                <!-- Downloaders Menu -->
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Downloaders</h4>
                    <ul class="space-y-2">
                        @forelse($footerDownloaders as $item)
                            <li>
                                <a href="{{ $item['url'] }}" target="{{ $item['target'] ?? '_self' }}"
                                    class="text-gray-600 dark:text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 text-sm transition-colors">
                                    {{ $item['title'] }}
                                </a>
                            </li>
                        @empty
                            <li><a href="{{ route('instagram.reels') }}"
                                    class="text-gray-600 dark:text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 text-sm transition-colors">Reels
                                    Downloader</a></li>
                            <li><a href="{{ route('instagram.video') }}"
                                    class="text-gray-600 dark:text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 text-sm transition-colors">Video
                                    Downloader</a></li>
                            <li><a href="{{ route('instagram.photo') }}"
                                    class="text-gray-600 dark:text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 text-sm transition-colors">Photo
                                    Downloader</a></li>
                        @endforelse
                    </ul>
                </div>

                <!-- Legal Menu -->
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Legal</h4>
                    <ul class="space-y-2">
                        @forelse($footerLegal as $item)
                            <li>
                                <a href="{{ $item['url'] }}" target="{{ $item['target'] ?? '_self' }}"
                                    class="text-gray-600 dark:text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 text-sm transition-colors">
                                    {{ $item['title'] }}
                                </a>
                            </li>
                        @empty
                            <li><a href="{{ route('privacy-policy') }}"
                                    class="text-gray-600 dark:text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 text-sm transition-colors">Privacy
                                    Policy</a></li>
                            <li><a href="{{ route('terms') }}"
                                    class="text-gray-600 dark:text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 text-sm transition-colors">Terms
                                    of Service</a></li>
                            <li><a href="{{ route('contact') }}"
                                    class="text-gray-600 dark:text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 text-sm transition-colors">Contact
                                    Us</a></li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-800 mt-8 pt-8 text-center">
                <p class="text-gray-500 dark:text-gray-500 text-sm">
                    {{ \App\Models\SiteSetting::get('copyright_text', '© ' . date('Y') . ' IGReelDownloader.net. All rights reserved. Not affiliated with Instagram or Meta.') }}
                </p>
            </div>
        </div>
    </footer>

    <!-- Core JavaScript -->
    <script>
        (function() {
            'use strict';

            function toggleTheme() {
                document.documentElement.classList.add('theme-transition');

                const isDark = document.documentElement.classList.contains('dark');

                if (isDark) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }

                setTimeout(() => {
                    document.documentElement.classList.remove('theme-transition');
                }, 300);
            }

            function openMobileMenu() {
                document.getElementById('mobileMenu').classList.add('open');
                document.body.style.overflow = 'hidden';
            }

            function closeMobileMenu() {
                document.getElementById('mobileMenu').classList.remove('open');
                document.body.style.overflow = '';
            }

            function init() {
                const themeToggle = document.getElementById('themeToggle');
                if (themeToggle) {
                    themeToggle.addEventListener('click', toggleTheme);
                }

                const mobileMenuBtn = document.getElementById('mobileMenuBtn');
                const mobileMenuClose = document.getElementById('mobileMenuClose');
                const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');

                if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', openMobileMenu);
                if (mobileMenuClose) mobileMenuClose.addEventListener('click', closeMobileMenu);
                if (mobileMenuOverlay) mobileMenuOverlay.addEventListener('click', closeMobileMenu);

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        closeMobileMenu();
                    }
                });

                initScrollToTop();
            }

            function initScrollToTop() {
                const scrollToTopBtn = document.getElementById('scrollToTop');
                if (!scrollToTopBtn) return;

                let isScrolling = false;

                function toggleScrollButton() {
                    const scrollY = window.scrollY || window.pageYOffset;
                    const showThreshold = 300;

                    if (scrollY > showThreshold) {
                        scrollToTopBtn.classList.add('scroll-top-visible');
                    } else {
                        scrollToTopBtn.classList.remove('scroll-top-visible');
                    }
                }

                function handleScroll() {
                    if (!isScrolling) {
                        window.requestAnimationFrame(function() {
                            toggleScrollButton();
                            isScrolling = false;
                        });
                        isScrolling = true;
                    }
                }

                function scrollToTop() {
                    scrollToTopBtn.classList.add('scroll-top-ripple');

                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });

                    setTimeout(function() {
                        scrollToTopBtn.classList.remove('scroll-top-ripple');
                    }, 300);
                }

                window.addEventListener('scroll', handleScroll, {
                    passive: true
                });
                scrollToTopBtn.addEventListener('click', scrollToTop);

                toggleScrollButton();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
    </script>

    @stack('scripts')
</body>

</html>
