@extends('admin.layouts.app')

@section('title', 'Social Settings')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Social Media Settings</h1>
        <p class="text-gray-500 dark:text-gray-400">Manage social media links</p>
    </div>

    <form action="{{ route('admin.settings.social.update') }}" method="POST">
        @csrf

        <div class="max-w-3xl">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-2">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                            </svg>
                            Twitter URL
                        </span>
                    </label>
                    <input type="url" name="twitter_url" value="{{ App\Models\SiteSetting::get('twitter_url') }}"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                        placeholder="https://twitter.com/yourhandle">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                            </svg>
                            Facebook URL
                        </span>
                    </label>
                    <input type="url" name="facebook_url" value="{{ App\Models\SiteSetting::get('facebook_url') }}"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                        placeholder="https://facebook.com/yourpage">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">
                        <span class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 mr-2"
                                aria-hidden="true">
                                <defs>
                                    <linearGradient id="igGradient" x1="0%" y1="0%" x2="100%"
                                        y2="100%">
                                        <stop offset="0%" stop-color="#feda75" />
                                        <stop offset="30%" stop-color="#fa7e1e" />
                                        <stop offset="60%" stop-color="#d62976" />
                                        <stop offset="100%" stop-color="#962fbf" />
                                    </linearGradient>
                                </defs>
                                <path fill="url(#igGradient)"
                                    d="M7.75 2h8.5C19.44 2 22 4.56 22 7.75v8.5C22 19.44 19.44 22 16.25 22h-8.5C4.56 22 2 19.44 2 16.25v-8.5C2 4.56 4.56 2 7.75 2zm0 1.5A4.25 4.25 0 003.5 7.75v8.5A4.25 4.25 0 007.75 20.5h8.5a4.25 4.25 0 004.25-4.25v-8.5A4.25 4.25 0 0016.25 3.5h-8.5z" />
                                <path fill="url(#igGradient)"
                                    d="M12 7a5 5 0 110 10 5 5 0 010-10zm0 1.5a3.5 3.5 0 100 7 3.5 3.5 0 000-7z" />
                                <circle cx="17.5" cy="6.5" r="1.25" fill="url(#igGradient)" />
                            </svg>

                            Instagram URL
                        </span>
                    </label>
                    <input type="url" name="instagram_url" value="{{ App\Models\SiteSetting::get('instagram_url') }}"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                        placeholder="https://instagram.com/yourhandle">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                            </svg>
                            YouTube URL
                        </span>
                    </label>
                    <input type="url" name="youtube_url" value="{{ App\Models\SiteSetting::get('youtube_url') }}"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                        placeholder="https://youtube.com/@yourchannel">
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
