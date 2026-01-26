@extends('admin.layouts.app')

@section('title', 'FAQs')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold">FAQs</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm sm:text-base">Manage frequently asked questions for each page
            </p>
        </div>
        <a href="{{ route('admin.faqs.create', ['page_slug' => request('page_slug')]) }}"
            class="instagram-gradient text-white font-medium px-4 py-2 rounded-lg hover:opacity-90 transition-opacity flex items-center justify-center sm:justify-start whitespace-nowrap">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add FAQ
        </a>
    </div>

    <!-- Filter -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 mb-6">
        <form method="GET" action="{{ route('admin.faqs.index') }}"
            class="flex flex-col sm:flex-row gap-3 sm:gap-4 sm:items-end">
            <div class="flex-1 min-w-0">
                <label class="block text-sm font-medium mb-2">Filter by Page</label>
                <select name="page_slug"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none text-sm sm:text-base">
                    <option value="">All Pages</option>
                    @foreach ($pages as $slug => $title)
                        <option value="{{ $slug }}" {{ request()->input('page_slug') === $slug ? 'selected' : '' }}>
                            {{ $title }} ({{ $slug }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="flex-1 sm:flex-none px-4 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors text-sm sm:text-base">
                    Filter
                </button>
                @if (request()->input('page_slug'))
                    <a href="{{ route('admin.faqs.index') }}"
                        class="flex-1 sm:flex-none px-4 py-2 text-gray-500 hover:text-gray-700 text-center text-sm sm:text-base">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-3 sm:p-4">
            <p class="text-xl sm:text-2xl font-bold text-violet-600">{{ $stats['total'] ?? 0 }}</p>
            <p class="text-xs sm:text-sm text-gray-500">Total FAQs</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-3 sm:p-4">
            <p class="text-xl sm:text-2xl font-bold text-green-600">{{ $stats['active'] ?? 0 }}</p>
            <p class="text-xs sm:text-sm text-gray-500">Active FAQs</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-3 sm:p-4">
            <p class="text-xl sm:text-2xl font-bold text-orange-600">{{ $stats['inactive'] ?? 0 }}</p>
            <p class="text-xs sm:text-sm text-gray-500">Inactive FAQs</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-3 sm:p-4">
            <p class="text-xl sm:text-2xl font-bold text-blue-600">{{ $stats['pages'] ?? 0 }}</p>
            <p class="text-xs sm:text-sm text-gray-500">Pages with FAQs</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <!-- Desktop Table -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th
                            class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16 lg:w-20">
                            Order</th>
                        <th
                            class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Question</th>
                        <th
                            class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">
                            Page</th>
                        <th
                            class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status</th>
                        <th
                            class="px-4 lg:px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody id="faqTableBody" class="divide-y divide-gray-200 dark:divide-gray-800">
                    @if ($faqs->count() > 0)
                        @foreach ($faqs as $faq)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                                data-id="{{ $faq->id }}">
                                <td class="px-4 lg:px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="drag-handle cursor-move text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
                                            title="Drag to reorder">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                            </svg>
                                        </div>
                                        <span
                                            class="order-number text-gray-500 font-mono text-sm">{{ $faq->order }}</span>
                                    </div>
                                </td>
                                <td class="px-4 lg:px-6 py-4">
                                    <div class="font-medium text-gray-900 dark:text-white text-sm">
                                        {{ Str::limit($faq->question, 50) }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs lg:max-w-md">
                                        {{ Str::limit($faq->answer, 60) }}</div>
                                </td>
                                <td class="px-4 lg:px-6 py-4 hidden md:table-cell">
                                    <code
                                        class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-800 text-xs text-violet-600 dark:text-violet-400">{{ $faq->page_slug }}</code>
                                </td>
                                <td class="px-4 lg:px-6 py-4">
                                    @if ($faq->is_active)
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">Active</span>
                                    @else
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 lg:px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.faqs.edit', $faq->id) }}"
                                            class="p-1.5 text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST"
                                            class="inline"
                                            onsubmit="return confirm('Are you sure you want to delete this FAQ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-1.5 text-gray-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
                                                title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="text-gray-400 mb-4">
                                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400 mb-4">
                                    @if (request()->input('page_slug'))
                                        No FAQs found for page "{{ request()->input('page_slug') }}".
                                    @else
                                        No FAQs found in the database. Run: <code
                                            class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">php artisan
                                            db:seed</code>
                                    @endif
                                </p>
                                <a href="{{ route('admin.faqs.create', ['page_slug' => request()->input('page_slug')]) }}"
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

        <!-- Mobile Card Layout -->
        <div class="sm:hidden divide-y divide-gray-200 dark:divide-gray-800">
            @forelse($faqs as $faq)
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50" data-id="{{ $faq->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <code
                                    class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-xs text-violet-600 dark:text-violet-400">{{ $faq->page_slug }}</code>
                                @if ($faq->is_active)
                                    <span
                                        class="px-1.5 py-0.5 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">Active</span>
                                @else
                                    <span
                                        class="px-1.5 py-0.5 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">Inactive</span>
                                @endif
                            </div>
                            <p class="font-medium text-gray-900 dark:text-white text-sm mb-1">
                                {{ Str::limit($faq->question, 60) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($faq->answer, 80) }}</p>
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <a href="{{ route('admin.faqs.edit', $faq->id) }}"
                                class="p-2 text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST"
                                onsubmit="return confirm('Delete this FAQ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="p-2 text-gray-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <p>No FAQs found. <a href="{{ route('admin.faqs.create') }}"
                            class="text-violet-600 hover:underline">Create one</a></p>
                </div>
            @endforelse
        </div>

        @if ($faqs->hasPages())
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200 dark:border-gray-800">
                {{ $faqs->withQueryString()->links() }}
            </div>
        @endif
    </div>

    <!-- Quick Actions by Page -->
    <div class="mt-6 sm:mt-8 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 sm:p-6">
        <h3 class="font-semibold mb-3 sm:mb-4 text-sm sm:text-base">Quick Filter by Page</h3>
        <div class="flex flex-wrap gap-2">
            @php
                $pageSlugs = ['home', 'reels', 'video', 'photo', 'story', 'carousel'];
            @endphp
            @foreach ($pageSlugs as $slug)
                @php
                    $count = \Illuminate\Support\Facades\DB::table('faqs')->where('page_slug', $slug)->count();
                @endphp
                <a href="{{ route('admin.faqs.index', ['page_slug' => $slug]) }}"
                    class="px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm {{ request()->input('page_slug') === $slug ? 'instagram-gradient text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }} transition-colors">
                    {{ ucfirst($slug) }}
                    <span
                        class="ml-1 px-1 sm:px-1.5 py-0.5 text-xs rounded-full {{ request()->input('page_slug') === $slug ? 'bg-white/20' : 'bg-gray-200 dark:bg-gray-700' }}">{{ $count }}</span>
                </a>
            @endforeach
        </div>
    </div>

    @push('scripts')
        <!-- SortableJS for drag and drop -->
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        <style>
            .sortable-ghost {
                background-color: rgba(139, 92, 246, 0.1) !important;
                opacity: 0.8;
            }

            .sortable-chosen {
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3) !important;
            }

            .sortable-drag {
                opacity: 0.5 !important;
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const faqTableBody = document.getElementById('faqTableBody');

                if (faqTableBody && faqTableBody.children.length > 0) {
                    const sortable = new Sortable(faqTableBody, {
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        handle: '.drag-handle',
                        onEnd: function(evt) {
                            // Get all FAQ IDs with their new order
                            const orders = [];
                            faqTableBody.querySelectorAll('[data-id]').forEach(function(el, index) {
                                orders.push({
                                    id: el.getAttribute('data-id'),
                                    order: index
                                });
                            });

                            // Send reorder request to server
                            fetch('{{ route('admin.faqs.reorder') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        orders: orders
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        showToast('FAQ order updated!', 'success');
                                        // Update order numbers in the table
                                        faqTableBody.querySelectorAll('[data-id]').forEach(function(el,
                                            index) {
                                            const orderSpan = el.querySelector('.order-number');
                                            if (orderSpan) orderSpan.textContent = index;
                                        });
                                    } else {
                                        showToast('Failed to update order', 'error');
                                    }
                                })
                                .catch(error => {
                                    console.error('Reorder error:', error);
                                    showToast('Failed to update order', 'error');
                                });
                        }
                    });
                }
            });

            function showToast(message, type) {
                const toast = document.createElement('div');
                toast.className =
                    `fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white font-medium shadow-lg z-50 transition-all ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
                toast.textContent = message;
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(20px)';
                }, 2000);

                setTimeout(() => toast.remove(), 2500);
            }
        </script>
    @endpush
@endsection