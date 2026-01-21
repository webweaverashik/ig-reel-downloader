<!-- Header -->
<header
    class="sticky top-0 z-50 backdrop-blur-lg border-b transition-colors duration-300 dark:bg-dark-bg/90 dark:border-dark-border bg-light-card/90 border-light-border">
    <div class="max-w-6xl mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
            <!-- Logo -->
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div
                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-accent via-primary to-secondary flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                    </svg>
                </div>
                <span class="text-xl font-bold gradient-text">ig reel downloader</span>
            </a>

            <!-- Navigation -->
            <nav class="hidden md:flex items-center gap-6">
                <a href="{{ url('/instagram-downloader') }}"
                    class="text-sm font-medium transition-colors dark:text-gray-300 dark:hover:text-white text-gray-600 hover:text-gray-900">Reel
                    Downloader</a>
                <a href="{{ url('/instagram-downloader') }}"
                    class="text-sm font-medium transition-colors dark:text-gray-300 dark:hover:text-white text-gray-600 hover:text-gray-900">Video
                    Downloader</a>
                <a href="{{ url('/instagram-downloader') }}"
                    class="text-sm font-medium transition-colors dark:text-gray-300 dark:hover:text-white text-gray-600 hover:text-gray-900">Photo
                    Downloader</a>
            </nav>

            <!-- Dark/Light Mode Toggle -->
            <div class="flex items-center gap-4">
                <label class="toggle-switch">
                    <input type="checkbox" id="themeToggle">
                    <span class="toggle-slider"></span>
                </label>

                <!-- Mobile menu button -->
                <button id="mobileMenuBtn"
                    class="md:hidden p-2 rounded-lg transition-colors dark:text-gray-300 dark:hover:bg-dark-card text-gray-600 hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <nav id="mobileMenu" class="hidden md:hidden mt-4 pt-4 border-t dark:border-dark-border border-light-border">
            <div class="flex flex-col gap-3">
                <a href="{{ url('/instagram-downloader') }}"
                    class="text-sm font-medium py-2 transition-colors dark:text-gray-300 dark:hover:text-white text-gray-600 hover:text-gray-900">Reel
                    Downloader</a>
                <a href="{{ url('/instagram-downloader') }}"
                    class="text-sm font-medium py-2 transition-colors dark:text-gray-300 dark:hover:text-white text-gray-600 hover:text-gray-900">Video
                    Downloader</a>
                <a href="{{ url('/instagram-downloader') }}"
                    class="text-sm font-medium py-2 transition-colors dark:text-gray-300 dark:hover:text-white text-gray-600 hover:text-gray-900">Photo
                    Downloader</a>
            </div>
        </nav>
    </div>
</header>
