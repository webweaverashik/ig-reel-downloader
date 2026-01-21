@extends('layouts.app')

@section('title', 'ig reel downloader – Best Instagram Downloader')
@section('description', 'with ig reel downloader, download any reels, videos and photos from instagram easily.')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-12">
        <!-- Hero Section -->
        <section class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 dark:text-white text-gray-900">
                <span class="gradient-text">ig reel downloader</span> – Best Instagram Downloader
            </h1>
            <p class="text-lg md:text-xl dark:text-gray-400 text-gray-600 max-w-2xl mx-auto">
                with ig reel downloader, download any reels, videos and photos from instagram easily.
            </p>
        </section>

        <!-- Download Form -->
        <section class="mb-8">
            <div class="gradient-border">
                <div class="gradient-border-inner p-6 md:p-8 dark:bg-dark-card bg-light-card">
                    <form id="downloadForm" class="space-y-4">
                        @csrf
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1 relative">
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                </div>
                                <input type="url" id="instagramUrl" name="url"
                                    placeholder="Paste Instagram URL here (Reel, Video, or Photo)"
                                    class="w-full pl-12 pr-4 py-4 rounded-xl border-2 transition-all duration-300 text-base focus:outline-none focus:ring-2 focus:ring-primary/50 dark:bg-dark-bg dark:border-dark-border dark:text-white dark:placeholder-gray-500 dark:focus:border-primary bg-white border-gray-200 text-gray-900 placeholder-gray-400 focus:border-primary"
                                    required>
                            </div>
                            <button type="submit" id="downloadBtn"
                                class="px-8 py-4 rounded-xl font-semibold text-white transition-all duration-300 transform hover:scale-105 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 bg-gradient-to-r from-accent via-primary to-secondary hover:shadow-lg hover:shadow-primary/30">
                                <span id="btnText" class="flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download
                                </span>
                                <span id="btnLoader" class="hidden items-center justify-center gap-2">
                                    <svg class="w-5 h-5 spinner" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Processing...
                                </span>
                            </button>
                        </div>

                        <!-- Error Message -->
                        <div id="errorMessage"
                            class="hidden p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-500">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span id="errorText">Error message here</span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- Loading Indicator -->
        <section id="loadingSection" class="hidden mb-8">
            <div class="gradient-border">
                <div class="gradient-border-inner p-8 dark:bg-dark-card bg-light-card text-center">
                    <div class="flex flex-col items-center gap-4">
                        <div class="relative">
                            <div
                                class="w-16 h-16 rounded-full bg-gradient-to-r from-accent via-primary to-secondary pulse-ring">
                            </div>
                            <div
                                class="absolute inset-2 rounded-full dark:bg-dark-card bg-light-card flex items-center justify-center">
                                <svg class="w-6 h-6 text-primary spinner" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <p class="text-lg font-medium dark:text-white text-gray-900">Fetching media from Instagram...</p>
                        <p class="text-sm dark:text-gray-400 text-gray-600">This may take a few seconds</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Preview Card -->
        <section id="previewSection" class="hidden mb-8 fade-in">
            <div class="gradient-border">
                <div class="gradient-border-inner p-6 md:p-8 dark:bg-dark-card bg-light-card">
                    <div class="flex flex-col md:flex-row gap-6">
                        <!-- Thumbnail -->
                        <div class="flex-shrink-0">
                            <div
                                class="relative w-full md:w-64 aspect-square rounded-xl overflow-hidden bg-gradient-to-br from-gray-700 to-gray-900">
                                <img id="previewThumbnail" src="" alt="Preview" class="w-full h-full object-cover">
                                <div id="mediaTypeBadge"
                                    class="absolute top-3 left-3 px-3 py-1 rounded-full text-xs font-semibold text-white bg-gradient-to-r from-primary to-secondary">
                                    REEL
                                </div>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 flex flex-col">
                            <div class="flex items-center gap-3 mb-4">
                                <div
                                    class="w-10 h-10 rounded-full bg-gradient-to-r from-accent via-primary to-secondary flex items-center justify-center text-white font-bold text-sm">
                                    <span id="previewUserInitial">U</span>
                                </div>
                                <div>
                                    <p id="previewUsername" class="font-semibold dark:text-white text-gray-900">@username
                                    </p>
                                    <p class="text-sm dark:text-gray-400 text-gray-600">Instagram User</p>
                                </div>
                            </div>

                            <p id="previewCaption" class="text-sm dark:text-gray-300 text-gray-700 mb-6 line-clamp-3">
                                Caption will appear here...
                            </p>

                            <!-- Download Options -->
                            <div class="mt-auto">
                                <p class="text-sm font-medium mb-3 dark:text-gray-400 text-gray-600">Download Options</p>
                                <div id="downloadOptions" class="flex flex-wrap gap-3">
                                    <!-- Download buttons will be inserted here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="grid md:grid-cols-3 gap-6 mt-16">
            <div
                class="p-6 rounded-2xl border transition-all duration-300 hover:border-primary/50 dark:bg-dark-card dark:border-dark-border bg-light-card border-light-border">
                <div
                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-accent to-primary flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold mb-2 dark:text-white text-gray-900">ig reel downloader – Fast & Free</h3>
                <p class="text-sm dark:text-gray-400 text-gray-600">Download Instagram content instantly with no
                    registration required.</p>
            </div>

            <div
                class="p-6 rounded-2xl border transition-all duration-300 hover:border-primary/50 dark:bg-dark-card dark:border-dark-border bg-light-card border-light-border">
                <div
                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold mb-2 dark:text-white text-gray-900">ig reel downloader – 100% Secure</h3>
                <p class="text-sm dark:text-gray-400 text-gray-600">Your data is never stored. We respect your privacy
                    completely.</p>
            </div>

            <div
                class="p-6 rounded-2xl border transition-all duration-300 hover:border-primary/50 dark:bg-dark-card dark:border-dark-border bg-light-card border-light-border">
                <div
                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-secondary to-accent flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold mb-2 dark:text-white text-gray-900">ig reel downloader – HD Quality</h3>
                <p class="text-sm dark:text-gray-400 text-gray-600">Download in the highest available quality for all
                    formats.</p>
            </div>
        </section>

        <!-- How To Section -->
        <section class="mt-16">
            <h2 class="text-2xl md:text-3xl font-bold text-center mb-8 dark:text-white text-gray-900">
                How to use <span class="gradient-text">ig reel downloader</span>?
            </h2>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="text-center p-6">
                    <div
                        class="w-16 h-16 rounded-full bg-gradient-to-r from-accent to-primary flex items-center justify-center mx-auto mb-4 text-2xl font-bold text-white">
                        1</div>
                    <h3 class="text-lg font-semibold mb-2 dark:text-white text-gray-900">Copy the URL</h3>
                    <p class="text-sm dark:text-gray-400 text-gray-600">Open Instagram and copy the link to any reel,
                        video, or photo.</p>
                </div>
                <div class="text-center p-6">
                    <div
                        class="w-16 h-16 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center mx-auto mb-4 text-2xl font-bold text-white">
                        2</div>
                    <h3 class="text-lg font-semibold mb-2 dark:text-white text-gray-900">Paste & Fetch</h3>
                    <p class="text-sm dark:text-gray-400 text-gray-600">Paste the URL above and click the download button.
                    </p>
                </div>
                <div class="text-center p-6">
                    <div
                        class="w-16 h-16 rounded-full bg-gradient-to-r from-secondary to-accent flex items-center justify-center mx-auto mb-4 text-2xl font-bold text-white">
                        3</div>
                    <h3 class="text-lg font-semibold mb-2 dark:text-white text-gray-900">Download</h3>
                    <p class="text-sm dark:text-gray-400 text-gray-600">Select your preferred quality and save to your
                        device.</p>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="mt-16">
            <h2 class="text-2xl md:text-3xl font-bold text-center mb-8 dark:text-white text-gray-900">
                <span class="gradient-text">ig reel downloader</span> FAQ
            </h2>
            <div class="space-y-4" id="faqContainer">
                @foreach ([['Is ig reel downloader free to use?', 'Yes! ig reel downloader is completely free to use. No hidden charges, no subscriptions, no registration required.'], ['What formats does ig reel downloader support?', 'ig reel downloader supports Instagram Reels, Videos, and Photos. All content is downloaded in the highest available quality.'], ['Can ig reel downloader download private content?', 'No, ig reel downloader can only download content from public Instagram accounts. Private content requires you to be logged in and have access.'], ['Is ig reel downloader safe and secure?', 'Absolutely! ig reel downloader doesn\'t store any user data or downloaded content. Your privacy and security are our top priority.']] as $faq)
                    <div
                        class="rounded-xl border transition-all duration-300 dark:bg-dark-card dark:border-dark-border bg-light-card border-light-border overflow-hidden">
                        <button class="faq-toggle w-full p-5 text-left flex items-center justify-between">
                            <span class="font-semibold dark:text-white text-gray-900">{{ $faq[0] }}</span>
                            <svg class="w-5 h-5 transition-transform duration-300 dark:text-gray-400 text-gray-600"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="faq-content hidden px-5 pb-5">
                            <p class="text-sm dark:text-gray-400 text-gray-600">{{ $faq[1] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/instagram-downloader.js') }}"></script>
@endpush
