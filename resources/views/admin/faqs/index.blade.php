@extends('admin.layouts.app')

@section('title', 'FAQs')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">FAQs</h1>
            <p class="text-gray-500 dark:text-gray-400">Manage frequently asked questions for each page</p>
        </div>
        <a href="{{ route('admin.faqs.create', ['page_slug' => request('page_slug')]) }}"
            class="instagram-gradient text-white font-medium px-4 py-2 rounded-lg hover:opacity-90 transition-opacity flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add FAQ
        </a>
    </div>

    <!-- Filter -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium mb-2">Filter by Page</label>
                <select name="page_slug"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none">
                    <option value="">All Pages</option>
                    @foreach ($pages as $slug => $title)
                        <option value="{{ $slug }}" {{ request('page_slug') === $slug ? 'selected' : '' }}>
                            {{ $title }} ({{ $slug }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                class="px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                Filter
            </button>
            @if (request('page_slug'))
                <a href="{{ route('admin.faqs.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700">Clear</a>
            @endif
        </form>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
            <p class="text-2xl font-bold text-violet-600">{{ $stats['total'] ?? 0 }}</p>
            <p class="text-sm text-gray-500">Total FAQs</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
            <p class="text-2xl font-bold text-green-600">{{ $stats['active'] ?? 0 }}</p>
            <p class="text-sm text-gray-500">Active FAQs</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
            <p class="text-2xl font-bold text-orange-600">{{ $stats['inactive'] ?? 0 }}</p>
            <p class="text-sm text-gray-500">Inactive FAQs</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
            <p class="text-2xl font-bold text-blue-600">{{ $stats['pages'] ?? 0 }}</p>
            <p class="text-sm text-gray-500">Pages with FAQs</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16">
                            Order</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Question</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Page</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status</th>
                        <th
                            class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @if ($faqs->count() > 0)
                        @foreach ($faqs as $faq)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-6 py-4 text-center">
                                    <span class="text-gray-500 font-mono">{{ $faq->order }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ Str::limit($faq->question, 60) }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-md">
                                        {{ Str::limit($faq->answer, 80) }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <code
                                        class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-800 text-sm text-violet-600 dark:text-violet-400">{{ $faq->page_slug }}</code>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($faq->is_active)
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">Active</span>
                                    @else
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="{{ route('admin.faqs.edit', $faq) }}"
                                        class="text-gray-500 hover:text-blue-600 dark:hover:text-blue-400" title="Edit">
                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete this FAQ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-gray-500 hover:text-red-600 dark:hover:text-red-400" title="Delete">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="text-gray-400 mb-4">
                                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400 mb-4">
                                    @if (request('page_slug'))
                                        No FAQs found for page "{{ request('page_slug') }}".
                                    @else
                                        No FAQs found in the database. Run: <code
                                            class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">php artisan
                                            db:seed</code>
                                    @endif
                                </p>
                                <a href="{{ route('admin.faqs.create', ['page_slug' => request('page_slug')]) }}"
                                    class="inline-flex items-center px-4 py-2 instagram-gradient text-white rounded-lg hover:opacity-90">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Create First FAQ
                                </a>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if ($faqs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800">
                {{ $faqs->withQueryString()->links() }}
            </div>
        @endif
    </div>

    <!-- Quick Actions by Page -->
    <div class="mt-8 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="font-semibold mb-4">Quick Filter by Page</h3>
        <div class="flex flex-wrap gap-2">
            @php
                $pageSlugs = ['home', 'reels', 'video', 'photo', 'story', 'carousel'];
            @endphp
            @foreach ($pageSlugs as $slug)
                @php
                    $count = \App\Models\Faq::where('page_slug', $slug)->count();
                @endphp
                <a href="{{ route('admin.faqs.index', ['page_slug' => $slug]) }}"
                    class="px-3 py-2 rounded-lg {{ request('page_slug') === $slug ? 'instagram-gradient text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }} transition-colors">
                    {{ ucfirst($slug) }}
                    <span
                        class="ml-1 px-1.5 py-0.5 text-xs rounded-full {{ request('page_slug') === $slug ? 'bg-white/20' : 'bg-gray-200 dark:bg-gray-700' }}">{{ $count }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endsection