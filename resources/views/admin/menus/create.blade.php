@extends('admin.layouts.app')

@section('title', 'Create Menu')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.menus.index') }}"
            class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 flex items-center text-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Menus
        </a>
        <h1 class="text-2xl font-bold mt-2">Create Menu</h1>
    </div>

    <form action="{{ route('admin.menus.store') }}" method="POST">
        @csrf

        <div class="max-w-2xl">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                            placeholder="Menu Name">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Slug <span class="text-red-500">*</span></label>
                        <input type="text" name="slug" value="{{ old('slug') }}" required
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                            placeholder="menu-slug">
                        @error('slug')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Location</label>
                    <select name="location"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none">
                        <option value="">Select Location</option>
                        <option value="header" {{ old('location') === 'header' ? 'selected' : '' }}>Header</option>
                        <option value="footer" {{ old('location') === 'footer' ? 'selected' : '' }}>Footer</option>
                        <option value="sidebar" {{ old('location') === 'sidebar' ? 'selected' : '' }}>Sidebar</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Description</label>
                    <textarea name="description" rows="2"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none resize-none"
                        placeholder="Brief description of this menu">{{ old('description') }}</textarea>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-800">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                            {{ old('is_active', true) ? 'checked' : '' }}>
                        <div
                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-violet-300 dark:peer-focus:ring-violet-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-violet-600">
                        </div>
                        <span class="ml-2 text-sm">Active</span>
                    </label>

                    <button type="submit"
                        class="instagram-gradient text-white font-medium px-6 py-2 rounded-lg hover:opacity-90 transition-opacity">
                        Create Menu
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection
