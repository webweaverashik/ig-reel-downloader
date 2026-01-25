<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Instagram Downloader - IGReelDownloader.net')</title>
    <meta name="description" content="@yield('description', 'Download Instagram Reels, Videos, Photos, and Stories in HD quality. Free, fast, and no login required.')">

    <!-- Tailwind CSS via CDN (Phase 1) -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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
    </style>

    <!-- Dark Mode Script (runs before page renders to prevent flash) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (savedTheme === 'light') {
                document.documentElement.classList.remove('dark');
            } else if (savedTheme === 'dark' || prefersDark) {
                document.documentElement.classList.add('dark');
            } else {
                // Default to dark
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>

<body class="bg-gray-50 dark:bg-gray-950 min-h-screen transition-colors duration-300">
    <!-- Header -->
    <header
        class="sticky top-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-lg border-b border-gray-200 dark:border-gray-800 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="{{ route('instagram.reels') }}" class="flex items-center space-x-2">
                    <div class="w-10 h-10 instagram-gradient rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                        </svg>
                    </div>
                    <span class="font-bold text-xl text-gray-900 dark:text-white hidden sm:block">IGReelDownloader<span
                            class="text-violet-500">.net</span></span>
                </a>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('instagram.reels') }}"
                        class="nav-link relative px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-violet-600 dark:hover:text-violet-400 transition-colors {{ ($pageType ?? '') === 'reels' ? 'active' : '' }}">
                        Reels
                    </a>
                    <a href="{{ route('instagram.video') }}"
                        class="nav-link relative px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-violet-600 dark:hover:text-violet-400 transition-colors {{ ($pageType ?? '') === 'video' ? 'active' : '' }}">
                        Video
                    </a>
                    <a href="{{ route('instagram.photo') }}"
                        class="nav-link relative px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-violet-600 dark:hover:text-violet-400 transition-colors {{ ($pageType ?? '') === 'photo' ? 'active' : '' }}">
                        Photo
                    </a>
                    <a href="{{ route('instagram.story') }}"
                        class="nav-link relative px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-violet-600 dark:hover:text-violet-400 transition-colors {{ ($pageType ?? '') === 'story' ? 'active' : '' }}">
                        Story
                    </a>
                    <a href="{{ route('instagram.carousel') }}"
                        class="nav-link relative px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-violet-600 dark:hover:text-violet-400 transition-colors {{ ($pageType ?? '') === 'carousel' ? 'active' : '' }}">
                        Carousel
                    </a>
                </nav>

                <!-- Right Section -->
                <div class="flex items-center space-x-3">
                    <!-- Dark/Light Mode Toggle -->
                    <button id="themeToggle"
                        class="p-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                        title="Toggle theme">
                        <!-- Sun Icon (shown in dark mode) -->
                        <svg id="sunIcon" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                        <!-- Moon Icon (shown in light mode) -->
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
                    <a href="{{ route('instagram.reels') }}"
                        class="mobile-nav-link block px-4 py-3 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 font-medium {{ ($pageType ?? '') === 'reels' ? 'active' : '' }}">
                        📹 Reels Downloader
                    </a>
                    <a href="{{ route('instagram.video') }}"
                        class="mobile-nav-link block px-4 py-3 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 font-medium {{ ($pageType ?? '') === 'video' ? 'active' : '' }}">
                        🎬 Video Downloader
                    </a>
                    <a href="{{ route('instagram.photo') }}"
                        class="mobile-nav-link block px-4 py-3 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 font-medium {{ ($pageType ?? '') === 'photo' ? 'active' : '' }}">
                        📷 Photo Downloader
                    </a>
                    <a href="{{ route('instagram.story') }}"
                        class="mobile-nav-link block px-4 py-3 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 font-medium {{ ($pageType ?? '') === 'story' ? 'active' : '' }}">
                        ⏰ Story Downloader
                    </a>
                    <a href="{{ route('instagram.carousel') }}"
                        class="mobile-nav-link block px-4 py-3 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 font-medium {{ ($pageType ?? '') === 'carousel' ? 'active' : '' }}">
                        🎠 Carousel Downloader
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

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
                        <span class="font-bold text-xl text-gray-900 dark:text-white">IGReelDownloader<span
                                class="text-violet-500">.net</span></span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm max-w-md">
                        The fastest and most reliable way to download Instagram Reels, Videos, Photos, Stories, and
                        Carousel posts in HD quality.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Downloaders</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('instagram.reels') }}"
                                class="text-gray-600 dark:text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 text-sm">Reels
                                Downloader</a></li>
                        <li><a href="{{ route('instagram.video') }}"
                                class="text-gray-600 dark:text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 text-sm">Video
                                Downloader</a></li>
                        <li><a href="{{ route('instagram.photo') }}"
                                class="text-gray-600 dark:text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 text-sm">Photo
                                Downloader</a></li>
                        <li><a href="{{ route('instagram.story') }}"
                                class="text-gray-600 dark:text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 text-sm">Story
                                Downloader</a></li>
                        <li><a href="{{ route('instagram.carousel') }}"
                                class="text-gray-600 dark:text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 text-sm">Carousel
                                Downloader</a></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Legal</h4>
                    <ul class="space-y-2">
                        <li><a href="#"
                                class="text-gray-600 dark:text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 text-sm">Privacy
                                Policy</a></li>
                        <li><a href="#"
                                class="text-gray-600 dark:text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 text-sm">Terms
                                of Service</a></li>
                        <li><a href="#"
                                class="text-gray-600 dark:text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 text-sm">Contact
                                Us</a></li>
                        <li><a href="#"
                                class="text-gray-600 dark:text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 text-sm">About</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-800 mt-8 pt-8 text-center">
                <p class="text-gray-500 dark:text-gray-500 text-sm">
                    © {{ date('Y') }} IGReelDownloader.net. All rights reserved. Not affiliated with Instagram or
                    Meta.
                </p>
            </div>
        </div>
    </footer>

    <!-- Core JavaScript -->
    <script>
        (function() {
            'use strict';

            // ============================================
            // THEME MANAGEMENT
            // ============================================
            function toggleTheme() {
                const isDark = document.documentElement.classList.contains('dark');

                if (isDark) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
            }

            // ============================================
            // MOBILE MENU
            // ============================================
            function openMobileMenu() {
                document.getElementById('mobileMenu').classList.add('open');
                document.body.style.overflow = 'hidden';
            }

            function closeMobileMenu() {
                document.getElementById('mobileMenu').classList.remove('open');
                document.body.style.overflow = '';
            }

            // ============================================
            // INITIALIZATION
            // ============================================
            function init() {
                // Theme toggle
                const themeToggle = document.getElementById('themeToggle');
                if (themeToggle) {
                    themeToggle.addEventListener('click', toggleTheme);
                }

                // Mobile menu
                const mobileMenuBtn = document.getElementById('mobileMenuBtn');
                const mobileMenuClose = document.getElementById('mobileMenuClose');
                const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');

                if (mobileMenuBtn) {
                    mobileMenuBtn.addEventListener('click', openMobileMenu);
                }
                if (mobileMenuClose) {
                    mobileMenuClose.addEventListener('click', closeMobileMenu);
                }
                if (mobileMenuOverlay) {
                    mobileMenuOverlay.addEventListener('click', closeMobileMenu);
                }
            }

            // Initialize when DOM is ready
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