@extends('layouts.app')

@section('title', 'Instagram Downloader - Download Reels, Videos, Photos & Carousels')
@section('description', 'Download Instagram Reels, Videos, Photos, and Carousels in HD quality. Free, fast, and no login
    required.')

@section('content')
    <!-- Hero Section -->
    <section class="py-12 sm:py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Badge -->
            <div
                class="inline-flex items-center px-4 py-2 rounded-full bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 text-sm font-medium mb-6">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"></path>
                </svg>
                Free & Unlimited Downloads
            </div>

            <!-- Title -->
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 dark:text-white mb-6">
                Download Instagram
                <span class="bg-gradient-to-r from-violet-600 via-pink-500 to-orange-400 bg-clip-text text-transparent">
                    Videos & Reels</span>
            </h1>

            <!-- Subtitle -->
            <p class="text-lg sm:text-xl text-gray-600 dark:text-gray-400 mb-10 max-w-2xl mx-auto">
                Download any Instagram content in HD quality. Supports Reels, Videos, Photos, and Carousel posts. No login
                required.
            </p>

            <!-- URL Input Form -->
            <div class="max-w-2xl mx-auto">
                <form id="downloadForm" class="relative flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                                </path>
                            </svg>
                        </div>
                        <input type="text" id="urlInput" name="url" placeholder="Paste Instagram URL here..."
                            class="w-full pl-12 pr-4 py-4 rounded-2xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-violet-500 dark:focus:border-violet-500 focus:ring-4 focus:ring-violet-500/20 outline-none transition-all text-base"
                            autocomplete="off">
                    </div>
                    <button type="submit" id="downloadBtn"
                        class="instagram-gradient text-white font-semibold px-8 py-4 rounded-2xl hover:opacity-90 transition-all transform hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center space-x-2 shadow-lg shadow-pink-500/25 disabled:opacity-75 disabled:cursor-not-allowed disabled:transform-none">
                        <span id="btnText">Download</span>
                        <div id="btnLoader" class="loader hidden"></div>
                        <svg id="btnIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                    </button>
                </form>

                <!-- Error Message -->
                <div id="errorMessage"
                    class="hidden mt-4 p-4 rounded-xl bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-sm fade-in">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span id="errorText"></span>
                    </div>
                </div>

                <!-- Success Message -->
                <div id="successMessage"
                    class="hidden mt-4 p-4 rounded-xl bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-sm fade-in">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span id="successText"></span>
                    </div>
                </div>
            </div>

            <!-- Supported Formats -->
            <div class="flex flex-wrap justify-center gap-3 mt-8">
                <span
                    class="px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-sm font-medium">Reels</span>
                <span
                    class="px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-sm font-medium">Videos</span>
                <span
                    class="px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-sm font-medium">Photos</span>
                <span
                    class="px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-sm font-medium">Carousel</span>
                <span
                    class="px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-sm font-medium">Stories</span>
            </div>
        </div>
    </section>

    <!-- Preview Section (Initially Hidden) -->
    <section id="previewSection" class="hidden pb-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-800 overflow-hidden fade-in">
                <!-- Preview Header -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                    <div class="flex items-center space-x-4">
                        <div id="profilePic"
                            class="w-14 h-14 rounded-full instagram-gradient flex items-center justify-center overflow-hidden">
                            <span id="profileInitial" class="text-white font-bold text-xl">U</span>
                            <img id="profileImage" class="w-full h-full object-cover hidden" alt="Profile">
                        </div>
                        <div>
                            <h3 id="username" class="font-semibold text-gray-900 dark:text-white text-lg">@username</h3>
                            <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                                <span id="contentType"
                                    class="px-2 py-0.5 rounded-md bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 font-medium">Reel</span>
                                <span>•</span>
                                <span id="mediaCount">1 item</span>
                            </div>
                        </div>
                    </div>
                    <p id="caption" class="mt-4 text-gray-600 dark:text-gray-400 text-sm line-clamp-3"></p>
                </div>

                <!-- Media Grid -->
                <div id="mediaGrid" class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Media items will be inserted here by JavaScript -->
                </div>

                <!-- Bulk Download -->
                <div class="p-6 border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                    <a id="downloadAllBtn" href="#"
                        class="w-full instagram-gradient text-white font-semibold py-4 rounded-xl hover:opacity-90 transition-all flex items-center justify-center space-x-2 block text-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        <span>Download All (ZIP)</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Loading Skeleton (Initially Hidden) -->
    <section id="loadingSection" class="hidden pb-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                    <div class="flex items-center space-x-4">
                        <div class="w-14 h-14 rounded-full skeleton"></div>
                        <div class="flex-1">
                            <div class="h-5 w-32 skeleton rounded mb-2"></div>
                            <div class="h-4 w-24 skeleton rounded"></div>
                        </div>
                    </div>
                    <div class="mt-4 h-4 w-full skeleton rounded"></div>
                    <div class="mt-2 h-4 w-3/4 skeleton rounded"></div>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="aspect-[4/5] skeleton rounded-2xl"></div>
                    <div class="aspect-[4/5] skeleton rounded-2xl"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-white dark:bg-gray-900">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">Why Choose Our Downloader?
                </h2>
                <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">The fastest and most reliable Instagram
                    downloader with premium features.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="p-6 rounded-2xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <div
                        class="w-12 h-12 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white text-lg mb-2">Lightning Fast</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Download any content in seconds with our optimized
                        servers.</p>
                </div>

                <!-- Feature 2 -->
                <div class="p-6 rounded-2xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <div
                        class="w-12 h-12 rounded-xl bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white text-lg mb-2">100% Secure</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">No data stored. Your privacy is our top priority.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="p-6 rounded-2xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <div
                        class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white text-lg mb-2">HD Quality</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Always get the highest quality available for your
                        downloads.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">How It Works</h2>
                <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">Download Instagram content in 3 simple steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div
                        class="w-16 h-16 rounded-full instagram-gradient mx-auto flex items-center justify-center mb-4 pulse-glow">
                        <span class="text-white font-bold text-2xl">1</span>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white text-lg mb-2">Copy URL</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Open Instagram app or website and copy the link to
                        the post.</p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="w-16 h-16 rounded-full instagram-gradient mx-auto flex items-center justify-center mb-4 pulse-glow"
                        style="animation-delay: 0.5s">
                        <span class="text-white font-bold text-2xl">2</span>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white text-lg mb-2">Paste & Analyze</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Paste the link above and click the download button.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="w-16 h-16 rounded-full instagram-gradient mx-auto flex items-center justify-center mb-4 pulse-glow"
                        style="animation-delay: 1s">
                        <span class="text-white font-bold text-2xl">3</span>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white text-lg mb-2">Download</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Select your preferred quality and download the
                        content.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 bg-white dark:bg-gray-900">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">Frequently Asked Questions
                </h2>
            </div>

            <div class="space-y-4">
                <!-- FAQ 1 -->
                <div class="faq-item border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden">
                    <button
                        class="faq-toggle w-full p-5 text-left flex items-center justify-between bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <span class="font-semibold text-gray-900 dark:text-white">Is this service free to use?</span>
                        <svg class="faq-icon w-5 h-5 text-gray-500 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                    <div class="faq-content hidden p-5 bg-white dark:bg-gray-800/50">
                        <p class="text-gray-600 dark:text-gray-400">Yes, our Instagram downloader is completely free to use
                            with no hidden charges or subscription fees.</p>
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="faq-item border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden">
                    <button
                        class="faq-toggle w-full p-5 text-left flex items-center justify-between bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <span class="font-semibold text-gray-900 dark:text-white">What content types are supported?</span>
                        <svg class="faq-icon w-5 h-5 text-gray-500 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                    <div class="faq-content hidden p-5 bg-white dark:bg-gray-800/50">
                        <p class="text-gray-600 dark:text-gray-400">We support all Instagram content types including Reels,
                            Videos, Photos, Carousel posts (multiple images/videos), and Stories.</p>
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="faq-item border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden">
                    <button
                        class="faq-toggle w-full p-5 text-left flex items-center justify-between bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <span class="font-semibold text-gray-900 dark:text-white">Do I need to login to download?</span>
                        <svg class="faq-icon w-5 h-5 text-gray-500 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                    <div class="faq-content hidden p-5 bg-white dark:bg-gray-800/50">
                        <p class="text-gray-600 dark:text-gray-400">No, you don't need to login or create an account. Just
                            paste the URL and download instantly.</p>
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="faq-item border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden">
                    <button
                        class="faq-toggle w-full p-5 text-left flex items-center justify-between bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <span class="font-semibold text-gray-900 dark:text-white">What quality can I download?</span>
                        <svg class="faq-icon w-5 h-5 text-gray-500 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                    <div class="faq-content hidden p-5 bg-white dark:bg-gray-800/50">
                        <p class="text-gray-600 dark:text-gray-400">We always provide the highest quality available for
                            each content. For videos, this is typically 1080p HD or the original upload quality.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/instagram-downloader.js') }}"></script>
@endpush
