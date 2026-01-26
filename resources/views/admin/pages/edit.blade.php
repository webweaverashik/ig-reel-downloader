@extends('admin.layouts.app')

@section('title', 'Edit Page')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.pages.index') }}"
            class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 flex items-center text-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Pages
        </a>
        <h1 class="text-2xl font-bold mt-2">Edit Page: {{ $page->title }}</h1>
    </div>

    <form action="{{ route('admin.pages.update', $page) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                    <h2 class="text-lg font-semibold mb-4">Basic Information</h2>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Slug <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" required
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none"
                                    placeholder="page-slug"
                                    {{ in_array($page->slug, ['home', 'reels', 'video', 'photo', 'story', 'carousel']) ? 'readonly' : '' }}>
                                @error('slug')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Title <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="title" value="{{ old('title', $page->title) }}" required
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none"
                                    placeholder="Page Title">
                                @error('title')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Hero Title</label>
                            <input type="text" name="hero_title" value="{{ old('hero_title', $page->hero_title) }}"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none"
                                placeholder="Hero Title">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Hero Highlight</label>
                            <input type="text" name="hero_highlight"
                                value="{{ old('hero_highlight', $page->hero_highlight) }}"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none"
                                placeholder="Highlighted text with gradient">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Subtitle</label>
                            <textarea name="subtitle" rows="2"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none resize-none"
                                placeholder="Page subtitle or description">{{ old('subtitle', $page->subtitle) }}</textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Badge</label>
                                <input type="text" name="badge" value="{{ old('badge', $page->badge) }}"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none"
                                    placeholder="100% Free Downloads">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Placeholder</label>
                                <input type="text" name="placeholder"
                                    value="{{ old('placeholder', $page->placeholder) }}"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none"
                                    placeholder="Input placeholder text...">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Formats (comma separated)</label>
                            <input type="text" name="formats"
                                value="{{ old('formats', is_array($page->formats) ? implode(', ', $page->formats) : $page->formats) }}"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none"
                                placeholder="Reels, Videos, Photos, HD Quality">
                        </div>
                    </div>
                </div>

                <!-- Page Content (WYSIWYG) -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                    <h2 class="text-lg font-semibold mb-4">Page Content</h2>
                    <textarea name="content" id="content" rows="15"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none"
                        placeholder="Page content (HTML supported for static pages like Privacy Policy)">{{ old('content', $page->content) }}</textarea>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Publish -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                    <h2 class="text-lg font-semibold mb-4">Publish</h2>

                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm">Status</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                                {{ old('is_active', $page->is_active) ? 'checked' : '' }}>
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-violet-300 dark:peer-focus:ring-violet-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-violet-600">
                            </div>
                            <span class="ml-2 text-sm">Active</span>
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full instagram-gradient text-white font-medium py-2 rounded-lg hover:opacity-90 transition-opacity">
                        Update Page
                    </button>

                    <a href="{{ route('admin.faqs.index', ['page_slug' => $page->slug]) }}"
                        class="w-full mt-2 py-2 rounded-lg border border-gray-300 dark:border-gray-700 text-center block hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        Manage FAQs ({{ $page->faqs->count() }})
                    </a>
                </div>

                <!-- SEO -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                    <h2 class="text-lg font-semibold mb-4">SEO Settings</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Meta Title</label>
                            <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none text-sm"
                                placeholder="SEO Title">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Meta Description</label>
                            <textarea name="meta_description" rows="3"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none text-sm resize-none"
                                placeholder="SEO Description">{{ old('meta_description', $page->meta_description) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Meta Keywords</label>
                            <textarea name="meta_keywords" rows="2"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none text-sm resize-none"
                                placeholder="keyword1, keyword2, keyword3">{{ old('meta_keywords', $page->meta_keywords) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Info -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 text-sm text-gray-500 dark:text-gray-400">
                    <p><strong>Created:</strong> {{ $page->created_at->format('M d, Y H:i') }}</p>
                    <p><strong>Updated:</strong> {{ $page->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>
    </form>
@endsection
