@extends('layouts.app')

@section('title', 'Page Not Found - IGReelDownloader.net')
@section('description', 'The page you are looking for could not be found.')

@section('content')
<!-- 404 Hero Section -->
<section class="relative py-20 sm:py-32 hero-gradient overflow-hidden min-h-[70vh] flex items-center">
    <!-- Background decoration -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-violet-500/10 rounded-full blur-3xl float-animation"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-pink-500/10 rounded-full blur-3xl float-animation" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-orange-500/5 rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <!-- 404 Number -->
        <div class="slide-up">
            <span class="text-8xl sm:text-9xl lg:text-[12rem] font-black bg-gradient-to-r from-violet-600 via-pink-500 to-orange-400 bg-clip-text text-transparent leading-none">
                404
            </span>
        </div>

        <!-- Error Message -->
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mt-4 mb-4 slide-up" style="animation-delay: 0.1s;">
            Page Not Found
        </h1>

        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8 max-w-xl mx-auto slide-up" style="animation-delay: 0.2s;">
            Oops! The page you're looking for doesn't exist or has been moved. Don't worry, let's get you back on track.
        </p>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 slide-up" style="animation-delay: 0.3s;">
            <a href="javascript:history.back()" 
               class="w-full sm:w-auto px-6 py-3 rounded-xl border-2 border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors flex items-center justify-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Go Back</span>
            </a>
            <a href="{{ route('home') }}" 
               class="w-full sm:w-auto instagram-gradient text-white font-semibold px-8 py-3 rounded-xl hover:opacity-90 transition-all transform hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center space-x-2 shadow-lg shadow-pink-500/25">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span>Go Back Home</span>
            </a>
        </div>

        <!-- Quick Links -->
        <div class="mt-12 slide-up" style="animation-delay: 0.4s;">
            <p class="text-sm text-gray-500 dark:text-gray-500 mb-4">Or try one of our popular downloaders:</p>
            <div class="flex flex-wrap justify-center gap-3">
                @php
                    $quickLinks = [
                        ['route' => 'instagram.reels', 'slug' => 'reels', 'label' => '📹 Reels', 'color' => 'pink'],
                        ['route' => 'instagram.video', 'slug' => 'video', 'label' => '🎬 Video', 'color' => 'violet'],
                        ['route' => 'instagram.photo', 'slug' => 'photo', 'label' => '📷 Photo', 'color' => 'blue'],
                        ['route' => 'instagram.story', 'slug' => 'story', 'label' => '⏰ Story', 'color' => 'orange'],
                        ['route' => 'instagram.carousel', 'slug' => 'carousel', 'label' => '🎠 Carousel', 'color' => 'green'],
                        ['route' => 'instagram.highlights', 'slug' => 'highlights', 'label' => '✨ Highlights', 'color' => 'yellow'],
                    ];
                @endphp
                
                @foreach($quickLinks as $link)
                    @if(\App\Models\Page::where('slug', $link['slug'])->where('is_active', true)->exists())
                        <a href="{{ route($link['route']) }}" 
                           class="px-4 py-2 rounded-xl bg-{{ $link['color'] }}-100 dark:bg-{{ $link['color'] }}-900/30 text-{{ $link['color'] }}-700 dark:text-{{ $link['color'] }}-300 font-medium hover:bg-{{ $link['color'] }}-200 dark:hover:bg-{{ $link['color'] }}-900/50 transition-colors text-sm">
                            {{ $link['label'] }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Decorative Element -->
        <div class="mt-16 slide-up" style="animation-delay: 0.5s;">
            <div class="inline-flex items-center space-x-2 text-gray-400 dark:text-gray-600">
                <div class="w-12 h-px bg-gray-300 dark:bg-gray-700"></div>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
                <div class="w-12 h-px bg-gray-300 dark:bg-gray-700"></div>
            </div>
        </div>
    </div>
</section>

<!-- Help Section -->
<section class="py-12 bg-white dark:bg-gray-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Need Help?</h2>
            <p class="text-gray-600 dark:text-gray-400">Here are some things you can try:</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Check URL -->
            <div class="p-6 rounded-2xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-center">
                <div class="w-12 h-12 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Check the URL</h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Make sure the web address is spelled correctly.</p>
            </div>

            <!-- Go Home -->
            <div class="p-6 rounded-2xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-center">
                <div class="w-12 h-12 rounded-xl bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Start Fresh</h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Go back to our homepage and start over.</p>
            </div>

            <!-- Contact Us -->
            <div class="p-6 rounded-2xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-center">
                <div class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Contact Us</h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm">
                    <a href="{{ route('contact') }}" class="text-violet-600 dark:text-violet-400 hover:underline">Get in touch</a> if you need assistance.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection