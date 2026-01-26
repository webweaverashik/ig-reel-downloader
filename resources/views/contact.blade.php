@extends('layouts.app')

@section('title', 'Contact Us - IGReelDownloader.net')
@section('description', 'Get in touch with IGReelDownloader.net. We are here to help with any questions, feedback, or
    support requests.')

@section('content')
    <!-- Hero Section -->
    <section class="relative py-12 sm:py-16 hero-gradient overflow-hidden">
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-violet-500/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-pink-500/10 rounded-full blur-3xl"></div>
        </div>

        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div
                class="inline-flex items-center px-4 py-2 rounded-full bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 text-sm font-medium mb-6 slide-up">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                    </path>
                </svg>
                We're Here to Help
            </div>

            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 dark:text-white mb-4 slide-up"
                style="animation-delay: 0.1s">
                Contact Us
            </h1>

            <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto slide-up" style="animation-delay: 0.2s">
                Have a question, feedback, or need help? We'd love to hear from you. Fill out the form below and we'll get
                back to you as soon as possible.
            </p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-12 sm:py-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Contact Information -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Quick Contact Cards -->
                    <div
                        class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Contact</h3>

                        <!-- Email -->
                        <div class="flex items-start space-x-4 mb-6">
                            <div
                                class="w-12 h-12 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-violet-600 dark:text-violet-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Email Us</h4>
                                <a href="mailto:support@igreeldownloader.net"
                                    class="text-violet-600 dark:text-violet-400 hover:underline text-sm">support@igreeldownloader.net</a>
                                <p class="text-gray-500 dark:text-gray-500 text-xs mt-1">We respond within 24-48 hours</p>
                            </div>
                        </div>

                        <!-- DMCA -->
                        <div class="flex items-start space-x-4 mb-6">
                            <div
                                class="w-12 h-12 rounded-xl bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">DMCA Requests</h4>
                                <a href="mailto:dmca@igreeldownloader.net"
                                    class="text-violet-600 dark:text-violet-400 hover:underline text-sm">dmca@igreeldownloader.net</a>
                                <p class="text-gray-500 dark:text-gray-500 text-xs mt-1">Copyright takedown requests</p>
                            </div>
                        </div>

                        <!-- Privacy -->
                        <div class="flex items-start space-x-4">
                            <div
                                class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Privacy Concerns</h4>
                                <a href="mailto:privacy@igreeldownloader.net"
                                    class="text-violet-600 dark:text-violet-400 hover:underline text-sm">privacy@igreeldownloader.net</a>
                                <p class="text-gray-500 dark:text-gray-500 text-xs mt-1">Data and privacy inquiries</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Link -->
                    <div
                        class="bg-gradient-to-br from-violet-500/10 to-pink-500/10 rounded-2xl border border-violet-200 dark:border-violet-800 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Looking for answers?</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
                            Check out our FAQ sections on each downloader page for quick answers to common questions.
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('instagram.reels') }}#faq"
                                class="px-3 py-1.5 bg-white dark:bg-gray-800 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                Reels FAQ
                            </a>
                            <a href="{{ route('instagram.video') }}#faq"
                                class="px-3 py-1.5 bg-white dark:bg-gray-800 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                Video FAQ
                            </a>
                            <a href="{{ route('instagram.photo') }}#faq"
                                class="px-3 py-1.5 bg-white dark:bg-gray-800 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                Photo FAQ
                            </a>
                        </div>
                    </div>

                    <!-- Response Time -->
                    <div
                        class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Response Times</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400 text-sm">General Inquiries</span>
                                <span
                                    class="px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-medium rounded-full">24-48
                                    hours</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400 text-sm">Technical Support</span>
                                <span
                                    class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-xs font-medium rounded-full">1-3
                                    days</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-400 text-sm">DMCA Requests</span>
                                <span
                                    class="px-2 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 text-xs font-medium rounded-full">3-5
                                    days</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="lg:col-span-2">
                    <div
                        class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Send us a Message</h2>

                        <form id="contactForm" class="space-y-6">
                            @csrf

                            <!-- Name & Email Row -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label for="name"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Your Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="name" name="name" required
                                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-violet-500 dark:focus:border-violet-500 focus:ring-4 focus:ring-violet-500/20 outline-none transition-all"
                                        placeholder="John Doe">
                                </div>
                                <div>
                                    <label for="email"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Your Email <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" id="email" name="email" required
                                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-violet-500 dark:focus:border-violet-500 focus:ring-4 focus:ring-violet-500/20 outline-none transition-all"
                                        placeholder="john@example.com">
                                </div>
                            </div>

                            <!-- Subject -->
                            <div>
                                <label for="subject"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Subject <span class="text-red-500">*</span>
                                </label>
                                <select id="subject" name="subject" required
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-violet-500 dark:focus:border-violet-500 focus:ring-4 focus:ring-violet-500/20 outline-none transition-all">
                                    <option value="">Select a subject...</option>
                                    <option value="general">General Inquiry</option>
                                    <option value="support">Technical Support</option>
                                    <option value="bug">Bug Report</option>
                                    <option value="feature">Feature Request</option>
                                    <option value="feedback">Feedback</option>
                                    <option value="partnership">Partnership/Business</option>
                                    <option value="dmca">DMCA/Copyright</option>
                                    <option value="privacy">Privacy Concern</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <!-- URL (Optional) -->
                            <div>
                                <label for="url"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Related URL <span class="text-gray-400 font-normal">(Optional)</span>
                                </label>
                                <input type="url" id="url" name="url"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-violet-500 dark:focus:border-violet-500 focus:ring-4 focus:ring-violet-500/20 outline-none transition-all"
                                    placeholder="https://instagram.com/...">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">If reporting an issue with a
                                    specific Instagram URL, paste it here.</p>
                            </div>

                            <!-- Message -->
                            <div>
                                <label for="message"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Message <span class="text-red-500">*</span>
                                </label>
                                <textarea id="message" name="message" rows="6" required
                                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-violet-500 dark:focus:border-violet-500 focus:ring-4 focus:ring-violet-500/20 outline-none transition-all resize-none"
                                    placeholder="Describe your question, issue, or feedback in detail..."></textarea>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">Please provide as much detail as
                                    possible so we can help you better.</p>
                            </div>

                            <!-- Consent -->
                            <div class="flex items-start space-x-3">
                                <input type="checkbox" id="consent" name="consent" required
                                    class="w-5 h-5 mt-0.5 rounded border-gray-300 dark:border-gray-600 text-violet-600 focus:ring-violet-500 dark:bg-gray-800">
                                <label for="consent" class="text-sm text-gray-600 dark:text-gray-400">
                                    I agree that my information will be used to respond to my inquiry. See our
                                    <a href="{{ route('privacy-policy') }}"
                                        class="text-violet-600 dark:text-violet-400 hover:underline">Privacy Policy</a>
                                    for more details. <span class="text-red-500">*</span>
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" id="submitBtn"
                                class="w-full instagram-gradient text-white font-semibold px-8 py-4 rounded-xl hover:opacity-90 transition-all transform hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center space-x-2 shadow-lg shadow-pink-500/25 disabled:opacity-75 disabled:cursor-not-allowed">
                                <span id="submitBtnText">Send Message</span>
                                <div id="submitBtnLoader" class="loader hidden"></div>
                                <svg id="submitBtnIcon" class="w-5 h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            </button>
                        </form>

                        <!-- Success Message -->
                        <div id="successMessage"
                            class="hidden mt-6 p-6 rounded-2xl bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800">
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-full bg-green-500 flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-green-800 dark:text-green-200 text-lg">Message Sent
                                        Successfully!</h4>
                                    <p class="text-green-700 dark:text-green-300 text-sm mt-1">Thank you for contacting us.
                                        We'll get back to you within 24-48 hours.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Error Message -->
                        <div id="errorMessage"
                            class="hidden mt-6 p-6 rounded-2xl bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-full bg-red-500 flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-red-800 dark:text-red-200 text-lg">Something went wrong
                                    </h4>
                                    <p id="errorText" class="text-red-700 dark:text-red-300 text-sm mt-1">Please try again
                                        or email us directly.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Common Issues Section -->
    <section class="py-12 sm:py-16 bg-white dark:bg-gray-900">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    Common Issues & Solutions
                </h2>
                <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    Before contacting us, check if your issue is listed below. You might find a quick solution!
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Issue 1 -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700">
                    <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Download Not Working</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Make sure the Instagram content is from a <strong>public account</strong>. Private content cannot be
                        downloaded. Also, verify the URL is correct.
                    </p>
                </div>

                <!-- Issue 2 -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700">
                    <div
                        class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Slow Downloads</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Download speed depends on your internet connection and Instagram's servers. If it's too slow, try
                        again later when there's less traffic.
                    </p>
                </div>

                <!-- Issue 3 -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700">
                    <div
                        class="w-12 h-12 rounded-xl bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21">
                            </path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Private Content Error</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        We can only download content from <strong>public profiles</strong>. Ask the account owner to make
                        their account public, or request permission directly.
                    </p>
                </div>

                <!-- Issue 4 -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700">
                    <div
                        class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                            </path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Invalid URL Error</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Make sure you're using the correct Instagram URL format. Copy the link directly from Instagram using
                        the share button.
                    </p>
                </div>

                <!-- Issue 5 -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700">
                    <div
                        class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Low Quality Downloads</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        We always download in the highest quality available. The quality depends on what the original
                        uploader posted.
                    </p>
                </div>

                <!-- Issue 6 -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700">
                    <div
                        class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Still Need Help?</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        If none of these solutions work, please use the contact form above with details about your issue.
                        Include the URL you're trying to download.
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (function() {
            'use strict';

            const form = document.getElementById('contactForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitBtnText = document.getElementById('submitBtnText');
            const submitBtnLoader = document.getElementById('submitBtnLoader');
            const submitBtnIcon = document.getElementById('submitBtnIcon');
            const successMessage = document.getElementById('successMessage');
            const errorMessage = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');

            if (!form) return;

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                // Reset messages
                successMessage.classList.add('hidden');
                errorMessage.classList.add('hidden');

                // Show loading state
                submitBtn.disabled = true;
                submitBtnText.textContent = 'Sending...';
                submitBtnLoader.classList.remove('hidden');
                submitBtnIcon.classList.add('hidden');

                try {
                    const formData = new FormData(form);
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content');

                    const response = await fetch('/api/contact', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Show success message
                        successMessage.classList.remove('hidden');
                        form.reset();

                        // Scroll to success message
                        successMessage.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    } else {
                        throw new Error(data.message || 'Failed to send message');
                    }
                } catch (error) {
                    // Show error message
                    errorText.textContent = error.message ||
                        'Please try again or email us directly at support@igreeldownloader.net';
                    errorMessage.classList.remove('hidden');

                    // Scroll to error message
                    errorMessage.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                } finally {
                    // Reset button state
                    submitBtn.disabled = false;
                    submitBtnText.textContent = 'Send Message';
                    submitBtnLoader.classList.add('hidden');
                    submitBtnIcon.classList.remove('hidden');
                }
            });
        })();
    </script>
@endpush
