@extends('admin.layouts.app')

@section('title', 'Contact Settings')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Contact Settings</h1>
        <p class="text-gray-500 dark:text-gray-400">Manage contact information and response times</p>
    </div>

    <form action="{{ route('admin.settings.contact.update') }}" method="POST">
        @csrf

        <div class="max-w-3xl">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 space-y-6">
                <h3 class="font-semibold text-lg">Email Addresses</h3>

                <div>
                    <label class="block text-sm font-medium mb-2">Contact Email</label>
                    <input type="email" name="contact_email"
                        value="{{ App\Models\SiteSetting::get('contact_email', 'support@igreeldownloader.net') }}"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                        placeholder="support@igreeldownloader.net">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">DMCA Email</label>
                    <input type="email" name="dmca_email"
                        value="{{ App\Models\SiteSetting::get('dmca_email', 'dmca@igreeldownloader.net') }}"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                        placeholder="dmca@igreeldownloader.net">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Privacy Email</label>
                    <input type="email" name="privacy_email"
                        value="{{ App\Models\SiteSetting::get('privacy_email', 'privacy@igreeldownloader.net') }}"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                        placeholder="privacy@igreeldownloader.net">
                </div>

                <hr class="border-gray-200 dark:border-gray-700">

                <h3 class="font-semibold text-lg">Response Times</h3>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">General Inquiries</label>
                        <input type="text" name="response_time_general"
                            value="{{ App\Models\SiteSetting::get('response_time_general', '24-48 hours') }}"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                            placeholder="24-48 hours">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Technical Support</label>
                        <input type="text" name="response_time_support"
                            value="{{ App\Models\SiteSetting::get('response_time_support', '1-3 days') }}"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                            placeholder="1-3 days">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">DMCA Requests</label>
                        <input type="text" name="response_time_dmca"
                            value="{{ App\Models\SiteSetting::get('response_time_dmca', '3-5 days') }}"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:border-violet-500 outline-none"
                            placeholder="3-5 days">
                    </div>
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
