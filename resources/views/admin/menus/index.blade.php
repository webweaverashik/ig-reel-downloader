@extends('admin.layouts.app')

@section('title', 'Menus')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Menus</h1>
            <p class="text-gray-500 dark:text-gray-400">Manage navigation menus</p>
        </div>
        <a href="{{ route('admin.menus.create') }}"
            class="instagram-gradient text-white font-medium px-4 py-2 rounded-lg hover:opacity-90 transition-opacity flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Menu
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($menus as $menu)
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white text-lg">{{ $menu->name }}</h3>
                            <code class="text-sm text-violet-600 dark:text-violet-400">{{ $menu->slug }}</code>
                        </div>
                        @if ($menu->is_active)
                            <span
                                class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">Active</span>
                        @else
                            <span
                                class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">Inactive</span>
                        @endif
                    </div>

                    @if ($menu->description)
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">{{ $menu->description }}</p>
                    @endif

                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $menu->items_count }}</span> items
                        </span>
                        <span class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                            {{ $menu->location ?? 'No location' }}
                        </span>
                    </div>
                </div>

                <div
                    class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <a href="{{ route('admin.menus.edit', $menu) }}"
                        class="text-violet-600 dark:text-violet-400 hover:underline text-sm font-medium">
                        Manage Items →
                    </a>

                    @if (!in_array($menu->slug, ['main', 'footer-downloaders', 'footer-legal']))
                        <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST"
                            onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="text-red-600 dark:text-red-400 hover:underline text-sm">Delete</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500">
                <p>No menus found. <a href="{{ route('admin.menus.create') }}"
                        class="text-violet-600 hover:underline">Create one</a></p>
            </div>
        @endforelse
    </div>
@endsection
