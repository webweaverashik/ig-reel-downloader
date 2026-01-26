@extends('admin.layouts.app')

@section('title', 'General Settings')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold">General Settings</h1>
        <p class="text-gray-500 dark:text-gray-400">Manage site name, description, and basic settings</p>
    </div>

    <form action="{{ route('admin.settings.general.update') }}" method="POST">
        @csrf

        <div class="max-w-3xl">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Site Name</label>
                    <input type="text" name="site_name"
                        value="{{ App\Models\SiteSetting::get('site_name', 'IGReelDownloader.net') }}"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                        placeholder="IGReelDownloader.net">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Site Tagline</label>
                    <input type="text" name="site_tagline"
                        value="{{ App\Models\SiteSetting::get('site_tagline', 'Best Instagram Downloader') }}"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                        placeholder="Best Instagram Downloader">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Site Description</label>
                    <textarea name="site_description" rows="3"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none resize-none"
                        placeholder="Brief description of your site">{{ App\Models\SiteSetting::get('site_description') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Footer Text</label>
                    <textarea name="footer_text" rows="2"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none resize-none"
                        placeholder="Footer disclaimer text">{{ App\Models\SiteSetting::get('footer_text') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Copyright Text</label>
                    <input type="text" name="copyright_text" value="{{ App\Models\SiteSetting::get('copyright_text') }}"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                        placeholder="© 2024 IGReelDownloader.net. All rights reserved.">
                </div>

                <div class="pt-4 border-t border-gray-200 dark:border-gray-800">
                    <button type="submit"
                        class="instagram-gradient text-white font-medium px-6 py-2 rounded-lg hover:opacity-90 transition-opacity">
                        Save Settings
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection
