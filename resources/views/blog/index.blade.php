@extends('layouts.app')

@section('title', 'Blog - IGReelDownloader.net')
@section('description', 'Read our latest articles about Instagram tips, tricks, and downloading guides.')

@section('content')
    <!-- Hero Section -->
    <section class="relative py-12 sm:py-16 hero-gradient overflow-hidden">
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-violet-500/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-pink-500/10 rounded-full blur-3xl"></div>
        </div>

        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div
                class="inline-flex items-center px-4 py-2 rounded-full bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 text-sm font-medium mb-6 slide-up">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z">
                    </path>
                </svg>
                Our Latest Articles
            </div>

            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 dark:text-white mb-4 slide-up"
                style="animation-delay: 0.1s">
                Blog & Insights
            </h1>

            <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto slide-up" style="animation-delay: 0.2s">
                Tips, tricks, and guides for Instagram content creators and users.
            </p>
        </div>
    </section>

    <!-- Blog Grid -->
    <section class="py-12 sm:py-16 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($posts as $post)
                    <article
                        class="flex flex-col bg-gray-50 dark:bg-gray-800 rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700 hover:shadow-xl transition-shadow duration-300 group">
                        <a href="{{ route('blog.show', $post->slug) }}" class="relative aspect-video overflow-hidden">
                            @if ($post->featured_image)
                                <img src="{{ $post->getImageUrl() }}" alt="{{ $post->title }}"
                                    class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div
                                    class="w-full h-full bg-gradient-to-br from-violet-500 to-pink-500 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-white/50" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z">
                                        </path>
                                    </svg>
                                </div>
                            @endif
                            <div class="absolute top-4 left-4">
                                <span
                                    class="px-3 py-1 bg-white/90 dark:bg-gray-900/90 backdrop-blur text-xs font-bold uppercase tracking-wider rounded-full text-violet-600 dark:text-violet-400">
                                    Article
                                </span>
                            </div>
                        </a>

                        <div class="flex-1 p-6 flex flex-col">
                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-3">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                {{ $post->published_at ? $post->published_at->format('M d, Y') : $post->created_at->format('M d, Y') }}
                            </div>

                            <h2
                                class="text-xl font-bold text-gray-900 dark:text-white mb-3 line-clamp-2 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                                <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                            </h2>

                            <p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-3 flex-1">
                                {{ $post->excerpt ?? Str::limit(strip_tags($post->content), 120) }}
                            </p>

                            <a href="{{ route('blog.show', $post->slug) }}"
                                class="inline-flex items-center text-violet-600 dark:text-violet-400 font-semibold hover:underline">
                                Read Article
                                <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                </svg>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full text-center py-12">
                        <div
                            class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">No posts yet</h3>
                        <p class="text-gray-500 dark:text-gray-400">Check back later for updates.</p>
                    </div>
                @endforelse
            </div>

            @if ($posts->hasPages())
                <div class="mt-12 flex justify-center">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection