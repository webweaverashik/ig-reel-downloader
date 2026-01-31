@extends('layouts.app')

@section('title', $post->meta_title ?? $post->title . ' - ' . \App\Models\SiteSetting::get('site_name',
    'IGReelDownloader.net'))
@section('description', $post->meta_description ?? ($post->excerpt ?? Str::limit(strip_tags($post->content), 160)))
@section('keywords', $post->meta_keywords)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/blog-content.css') }}">
@endpush

@section('content')
    <article class="pt-8 pb-16">
        <!-- Breadcrumb -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
            <nav class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ route('home') }}" class="hover:text-violet-600 dark:hover:text-violet-400 transition-colors">
                    Home
                </a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <a href="{{ route('blog.index') }}"
                    class="hover:text-violet-600 dark:hover:text-violet-400 transition-colors">
                    Blog
                </a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-gray-900 dark:text-white font-medium truncate max-w-xs">{{ $post->title }}</span>
            </nav>
        </div>

        <!-- Header -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center mb-10">
            <div class="inline-flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400 mb-6">
                <time datetime="{{ $post->published_at->toIso8601String() }}">
                    {{ $post->published_at->format('F d, Y') }}
                </time>
                <span>•</span>
                <span>{{ ceil(str_word_count(strip_tags($post->content)) / 200) }} min read</span>
                @if ($post->user)
                    <span>•</span>
                    <span>By {{ $post->user->name }}</span>
                @endif
            </div>

            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 dark:text-white mb-6 leading-tight">
                {{ $post->title }}
            </h1>

            @if ($post->excerpt)
                <p class="text-xl text-gray-600 dark:text-gray-300 leading-relaxed max-w-3xl mx-auto">
                    {{ $post->excerpt }}
                </p>
            @endif
        </div>

        <!-- Featured Image -->
        @if ($post->featured_image)
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
                <div class="aspect-video rounded-2xl overflow-hidden shadow-2xl">
                    <img src="{{ $post->getImageUrl() }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                </div>
            </div>
        @endif

        <!-- Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 sm:p-10 lg:p-12">
                <div class="blog-content max-w-none">
                    {!! $post->content !!}
                </div>
            </div>

            <!-- Tags/Keywords -->
            @if ($post->meta_keywords)
                <div class="mt-8 flex flex-wrap gap-2">
                    @foreach (explode(',', $post->meta_keywords) as $keyword)
                        <span
                            class="px-3 py-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-sm rounded-full">
                            {{ trim($keyword) }}
                        </span>
                    @endforeach
                </div>
            @endif

            <!-- Share Buttons -->
            <div class="mt-10 pt-8 border-t border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Share this article:</span>
                    <div class="flex items-center space-x-3">
                        <!-- Twitter/X -->
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode(url()->current()) }}"
                            target="_blank" rel="noopener noreferrer"
                            class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black transition-all duration-200"
                            title="Share on X (Twitter)">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                            </svg>
                        </a>

                        <!-- Facebook -->
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                            target="_blank" rel="noopener noreferrer"
                            class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-blue-600 hover:text-white transition-all duration-200"
                            title="Share on Facebook">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                            </svg>
                        </a>

                        <!-- LinkedIn -->
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(url()->current()) }}&title={{ urlencode($post->title) }}"
                            target="_blank" rel="noopener noreferrer"
                            class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-blue-700 hover:text-white transition-all duration-200"
                            title="Share on LinkedIn">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                            </svg>
                        </a>

                        <!-- Copy Link -->
                        <button onclick="copyToClipboard('{{ url()->current() }}')"
                            class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-violet-600 hover:text-white transition-all duration-200"
                            title="Copy link">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Post Navigation -->
            @if ((isset($previousPost) && $previousPost) || (isset($nextPost) && $nextPost))
                <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if (isset($previousPost) && $previousPost)
                        <a href="{{ route('blog.show', $previousPost->slug) }}"
                            class="group flex items-center p-6 bg-gray-50 dark:bg-gray-800 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-5 h-5 mr-4 text-gray-400 group-hover:text-violet-500 transition-colors"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            <div class="flex-1 min-w-0">
                                <span
                                    class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Previous</span>
                                <p
                                    class="text-gray-900 dark:text-white font-medium truncate group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                                    {{ $previousPost->title }}
                                </p>
                            </div>
                        </a>
                    @else
                        <div></div>
                    @endif

                    @if (isset($nextPost) && $nextPost)
                        <a href="{{ route('blog.show', $nextPost->slug) }}"
                            class="group flex items-center justify-end p-6 bg-gray-50 dark:bg-gray-800 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-right">
                            <div class="flex-1 min-w-0">
                                <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Next</span>
                                <p
                                    class="text-gray-900 dark:text-white font-medium truncate group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                                    {{ $nextPost->title }}
                                </p>
                            </div>
                            <svg class="w-5 h-5 ml-4 text-gray-400 group-hover:text-violet-500 transition-colors"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <!-- Related Posts -->
        @if (isset($relatedPosts) && $relatedPosts->count() > 0)
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">Related Articles</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach ($relatedPosts as $related)
                        <article
                            class="group bg-white dark:bg-gray-800 rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 hover:shadow-lg transition-shadow">
                            <a href="{{ route('blog.show', $related->slug) }}" class="block">
                                @if ($related->featured_image)
                                    <div class="aspect-video overflow-hidden">
                                        <img src="{{ $related->getImageUrl() }}" alt="{{ $related->title }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    </div>
                                @else
                                    <div
                                        class="aspect-video bg-gradient-to-br from-violet-500 to-pink-500 flex items-center justify-center">
                                        <svg class="w-12 h-12 text-white/50" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="p-5">
                                    <time class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $related->published_at->format('M d, Y') }}
                                    </time>
                                    <h3
                                        class="mt-2 text-lg font-semibold text-gray-900 dark:text-white group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors line-clamp-2">
                                        {{ $related->title }}
                                    </h3>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </article>

    <!-- Copy to Clipboard Script -->
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Show toast notification
                const toast = document.createElement('div');
                toast.className =
                    'fixed bottom-4 left-1/2 transform -translate-x-1/2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-4 py-2 rounded-lg shadow-lg text-sm font-medium z-50';
                toast.textContent = 'Link copied to clipboard!';
                document.body.appendChild(toast);
                setTimeout(function() {
                    toast.remove();
                }, 2000);
            });
        }
    </script>
@endsection
