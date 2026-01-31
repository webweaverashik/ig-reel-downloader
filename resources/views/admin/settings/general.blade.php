@extends('admin.layouts.app')

@section('title', 'General Settings')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">General Settings</h1>
        <p class="text-gray-500 dark:text-gray-400">Manage site branding and basic information</p>
    </div>

    <form action="{{ route('admin.settings.general.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Settings -->
            <div class="lg:col-span-2 space-y-6">
                <div
                    class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 space-y-6 shadow-sm">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Site Name</label>
                        <input type="text" name="site_name"
                            value="{{ App\Models\SiteSetting::get('site_name', 'IGReelDownloader.net') }}"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-violet-500 outline-none transition-colors"
                            placeholder="IGReelDownloader.net">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Site Tagline</label>
                        <input type="text" name="site_tagline"
                            value="{{ App\Models\SiteSetting::get('site_tagline', 'Best Instagram Downloader') }}"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-violet-500 outline-none transition-colors"
                            placeholder="Best Instagram Downloader">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Site
                            Description</label>
                        <textarea name="site_description" rows="3"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-violet-500 outline-none resize-none transition-colors"
                            placeholder="Brief description of your site">{{ App\Models\SiteSetting::get('site_description') }}</textarea>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                        <button type="submit"
                            class="instagram-gradient text-white font-medium px-8 py-2.5 rounded-lg hover:opacity-90 transition-opacity shadow-lg shadow-pink-500/20">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>

            <!-- Branding Sidebar -->
            <div class="space-y-6">
                <!-- Logo Section -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        Branding
                    </h3>

                    <div class="space-y-6">
                        <!-- Site Logo -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Site
                                Logo</label>
                            @if ($logo = \App\Models\SiteSetting::get('site_logo'))
                                <div
                                    class="mb-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 relative group">
                                    <img src="{{ asset('uploads/' . $logo) }}" alt="Logo"
                                        class="max-h-12 w-auto mx-auto">
                                    <label
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1.5 cursor-pointer shadow-lg hover:bg-red-600 transition-colors"
                                        title="Delete Logo">
                                        <input type="checkbox" name="delete_logo" class="hidden">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </label>
                                </div>
                            @endif
                            <input type="file" name="site_logo" accept="image/*"
                                class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100 dark:file:bg-violet-900/30 dark:file:text-violet-300 transition-all">
                            <p class="text-[10px] text-gray-400 mt-2">Recommended: 200x50px (PNG/SVG)</p>
                        </div>

                        <!-- Site Favicon -->
                        <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                            <label
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Favicon</label>
                            @if ($favicon = \App\Models\SiteSetting::get('site_favicon'))
                                <div
                                    class="mb-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 w-fit mx-auto relative group">
                                    <img src="{{ asset('uploads/' . $favicon) }}" alt="Favicon" class="w-8 h-8">
                                    <label
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1.5 cursor-pointer shadow-lg hover:bg-red-600 transition-colors"
                                        title="Delete Favicon">
                                        <input type="checkbox" name="delete_favicon" class="hidden">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </label>
                                </div>
                            @endif
                            <input type="file" name="site_favicon" accept="image/*"
                                class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100 dark:file:bg-pink-900/30 dark:file:text-pink-300 transition-all">
                            <p class="text-[10px] text-gray-400 mt-2">Recommended: 32x32px (ICO/PNG)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
