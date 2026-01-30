@extends('admin.layouts.app')

@section('title', 'SEO Settings')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold">SEO Settings</h1>
        <p class="text-gray-500 dark:text-gray-400">Manage meta tags, analytics, and search engine optimization</p>
    </div>

    <form action="{{ route('admin.settings.seo.update') }}" method="POST">
        @csrf

        <div class="max-w-3xl">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Default Meta Title</label>
                    <input type="text" name="default_meta_title"
                        value="{{ App\Models\SiteSetting::get('default_meta_title') }}"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                        placeholder="IG Reel Downloader - Best Instagram Downloader">
                    <p class="text-xs text-gray-500 mt-1">Used when pages don't have a custom title (max 60 characters
                        recommended)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Default Meta Description</label>
                    <textarea name="default_meta_description" rows="3"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none resize-none"
                        placeholder="Download Instagram Reels, Videos, Photos in HD quality...">{{ App\Models\SiteSetting::get('default_meta_description') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Max 160 characters recommended</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Default Meta Keywords</label>
                    <textarea name="default_meta_keywords" rows="2"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none resize-none"
                        placeholder="instagram downloader, reels downloader, ig video downloader">{{ App\Models\SiteSetting::get('default_meta_keywords') }}</textarea>
                </div>

                <hr class="border-gray-200 dark:border-gray-700">

                <div>
                    <label class="block text-sm font-medium mb-2">Google Analytics ID</label>
                    <input type="text" name="google_analytics_id"
                        value="{{ App\Models\SiteSetting::get('google_analytics_id') }}"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                        placeholder="G-XXXXXXXXXX or UA-XXXXXXXX-X">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Google Site Verification</label>
                    <input type="text" name="google_site_verification"
                        value="{{ App\Models\SiteSetting::get('google_site_verification') }}"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                        placeholder="Verification code from Google Search Console">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Google Tag Manager ID</label>
                    <input type="text" name="google_tag_manager_id"
                        value="{{ App\Models\SiteSetting::get('google_tag_manager_id') }}"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                        placeholder="GTM-XXXXXXX">
                    <p class="text-xs text-gray-500 mt-1">Container ID for Google Tag Manager</p>
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
