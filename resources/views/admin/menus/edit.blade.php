@extends('admin.layouts.app')

@section('title', 'Edit Menu')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.menus.index') }}"
            class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 flex items-center text-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Menus
        </a>
        <h1 class="text-2xl font-bold mt-2">Edit Menu: {{ $menu->name }}</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Menu Settings -->
        <div class="lg:col-span-1">
            <form action="{{ route('admin.menus.update', $menu) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 space-y-4">
                    <h2 class="font-semibold text-lg mb-4">Menu Settings</h2>

                    <div>
                        <label class="block text-sm font-medium mb-2">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $menu->name) }}" required
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Slug <span class="text-red-500">*</span></label>
                        <input type="text" name="slug" value="{{ old('slug', $menu->slug) }}" required
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                            {{ in_array($menu->slug, ['main', 'footer-downloaders', 'footer-legal']) ? 'readonly' : '' }}>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Location</label>
                        <select name="location"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none">
                            <option value="">Select Location</option>
                            <option value="header" {{ old('location', $menu->location) === 'header' ? 'selected' : '' }}>
                                Header</option>
                            <option value="footer" {{ old('location', $menu->location) === 'footer' ? 'selected' : '' }}>
                                Footer</option>
                            <option value="sidebar" {{ old('location', $menu->location) === 'sidebar' ? 'selected' : '' }}>
                                Sidebar</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Description</label>
                        <textarea name="description" rows="2"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none resize-none">{{ old('description', $menu->description) }}</textarea>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-800">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                                {{ old('is_active', $menu->is_active) ? 'checked' : '' }}>
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-violet-300 dark:peer-focus:ring-violet-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-violet-600">
                            </div>
                            <span class="ml-2 text-sm">Active</span>
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full instagram-gradient text-white font-medium py-2 rounded-lg hover:opacity-90 transition-opacity">
                        Update Menu
                    </button>
                </div>
            </form>

            <!-- Add New Item -->
            <form action="{{ route('admin.menus.items.add', $menu) }}" method="POST" class="mt-6">
                @csrf
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 space-y-4">
                    <h2 class="font-semibold text-lg mb-4">Add Menu Item</h2>

                    <div>
                        <label class="block text-sm font-medium mb-2">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" required
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                            placeholder="Menu item title">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Link to Page</label>
                        <select name="page_id" id="addPageSelect"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none">
                            <option value="">-- Custom URL --</option>
                            @foreach ($pages as $page)
                                <option value="{{ $page->id }}">{{ $page->title }} ({{ $page->slug }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="addCustomUrl">
                        <label class="block text-sm font-medium mb-2">Custom URL</label>
                        <input type="text" name="url"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                            placeholder="https://example.com or /path">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Icon (Emoji)</label>
                            <input type="text" name="icon"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                                placeholder="📹">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Target</label>
                            <select name="target"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none">
                                <option value="_self">Same Tab</option>
                                <option value="_blank">New Tab</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" checked id="addItemActive"
                            class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-violet-600 focus:ring-violet-500 dark:bg-gray-800">
                        <label for="addItemActive" class="ml-2 text-sm">Active</label>
                    </div>

                    <button type="submit"
                        class="w-full py-2 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors font-medium">
                        Add Item
                    </button>
                </div>
            </form>
        </div>

        <!-- Menu Items -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                    <h2 class="font-semibold text-lg">Menu Items ({{ $menu->items->count() }})</h2>
                    <p class="text-sm text-gray-500">Drag to reorder items</p>
                </div>

                <div id="menuItemsList" class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($menu->items as $item)
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50" data-id="{{ $item->id }}">
                            <div class="flex items-center gap-4">
                                <!-- Drag Handle -->
                                <div class="cursor-move text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 8h16M4 16h16"></path>
                                    </svg>
                                </div>

                                <!-- Item Info -->
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        @if ($item->icon)
                                            <span>{{ $item->icon }}</span>
                                        @endif
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $item->title }}</span>
                                        @if (!$item->is_active)
                                            <span
                                                class="px-1.5 py-0.5 text-xs rounded bg-gray-100 dark:bg-gray-800 text-gray-500">Hidden</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500 mt-1">
                                        @if ($item->page)
                                            <span class="text-violet-600 dark:text-violet-400">Page:
                                                {{ $item->page->slug }}</span>
                                        @elseif($item->url)
                                            <span>URL: {{ $item->url }}</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="editItem({{ $item->id }})"
                                        class="p-2 text-gray-500 hover:text-blue-600 dark:hover:text-blue-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <form action="{{ route('admin.menus.items.delete', $item) }}" method="POST"
                                        onsubmit="return confirm('Delete this item?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="p-2 text-gray-500 hover:text-red-600 dark:hover:text-red-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Edit Form (hidden by default) -->
                            <div id="editForm{{ $item->id }}"
                                class="hidden mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                <form action="{{ route('admin.menus.items.update', $item) }}" method="POST"
                                    class="space-y-4">
                                    @csrf
                                    @method('PUT')

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Title</label>
                                            <input type="text" name="title" value="{{ $item->title }}" required
                                                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 focus:border-violet-500 outline-none text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Icon</label>
                                            <input type="text" name="icon" value="{{ $item->icon }}"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 focus:border-violet-500 outline-none text-sm">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Link to Page</label>
                                            <select name="page_id"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 focus:border-violet-500 outline-none text-sm">
                                                <option value="">-- Custom URL --</option>
                                                @foreach ($pages as $page)
                                                    <option value="{{ $page->id }}"
                                                        {{ $item->page_id == $page->id ? 'selected' : '' }}>
                                                        {{ $page->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Custom URL</label>
                                            <input type="text" name="url" value="{{ $item->url }}"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 focus:border-violet-500 outline-none text-sm">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Target</label>
                                            <select name="target"
                                                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 focus:border-violet-500 outline-none text-sm">
                                                <option value="_self" {{ $item->target === '_self' ? 'selected' : '' }}>
                                                    Same Tab</option>
                                                <option value="_blank" {{ $item->target === '_blank' ? 'selected' : '' }}>
                                                    New Tab</option>
                                            </select>
                                        </div>
                                        <div class="flex items-end">
                                            <label class="flex items-center">
                                                <input type="checkbox" name="is_active" value="1"
                                                    {{ $item->is_active ? 'checked' : '' }}
                                                    class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-violet-600 focus:ring-violet-500">
                                                <span class="ml-2 text-sm">Active</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="flex justify-end gap-2">
                                        <button type="button" onclick="editItem({{ $item->id }})"
                                            class="px-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                            class="px-4 py-2 text-sm rounded-lg instagram-gradient text-white">
                                            Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500">
                            <p>No items in this menu. Add one using the form on the left.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function editItem(id) {
                const form = document.getElementById('editForm' + id);
                form.classList.toggle('hidden');
            }

            // Toggle custom URL field based on page selection
            document.getElementById('addPageSelect')?.addEventListener('change', function() {
                const customUrlDiv = document.getElementById('addCustomUrl');
                if (this.value) {
                    customUrlDiv.style.display = 'none';
                } else {
                    customUrlDiv.style.display = 'block';
                }
            });
        </script>
    @endpush
@endsection
