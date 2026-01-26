@extends('layouts.app')

@section('title', $page->meta_title ?? 'Terms of Service - IGReelDownloader.net')
@section('description', $page->meta_description ?? 'Read our Terms of Service to understand the rules and guidelines for
    using IGReelDownloader.net.')

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
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                Legal Agreement
            </div>

            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 dark:text-white mb-4 slide-up"
                style="animation-delay: 0.1s">
                Terms of Service
            </h1>

            <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto slide-up" style="animation-delay: 0.2s">
                Last updated: {{ date('F d, Y') }}
            </p>
        </div>
    </section>

    <!-- Content Section -->
    <section class="py-12 sm:py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                <div class="p-6 sm:p-10 prose prose-gray dark:prose-invert max-w-none">

                    @if ($page && $page->content)
                        {!! $page->content !!}
                    @else
                        <!-- Default Terms Content -->
                        <!-- Important Notice -->
                        <div
                            class="mb-10 p-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 mt-0.5 mr-3 flex-shrink-0"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                    </path>
                                </svg>
                                <div>
                                    <h3 class="text-lg font-semibold text-amber-800 dark:text-amber-200 mb-2">Important
                                        Notice</h3>
                                    <p class="text-amber-700 dark:text-amber-300 text-sm">
                                        By using IGReelDownloader.net, you acknowledge that you have read, understood, and
                                        agree to be bound by these Terms of Service. If you do not agree to these terms,
                                        please do not use our service.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Acceptance of Terms -->
                        <div class="mb-10">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                <span
                                    class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center mr-3">
                                    <span class="text-violet-600 dark:text-violet-400 font-bold">1</span>
                                </span>
                                Acceptance of Terms
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                                These Terms of Service ("Terms") govern your access to and use of IGReelDownloader.net
                                ("Service," "we," "us," or "our"). By accessing or using our Service, you agree to be bound
                                by these Terms and our Privacy Policy.
                            </p>
                            <p class="text-gray-600 dark:text-gray-400 leading-relaxed mt-4">
                                We reserve the right to modify these Terms at any time. Changes will be effective
                                immediately upon posting. Your continued use of the Service after any changes constitutes
                                acceptance of the new Terms.
                            </p>
                        </div>

                        <!-- Description of Service -->
                        <div class="mb-10">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                <span
                                    class="w-10 h-10 rounded-xl bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center mr-3">
                                    <span class="text-pink-600 dark:text-pink-400 font-bold">2</span>
                                </span>
                                Description of Service
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                                IGReelDownloader.net provides a free online tool that allows users to download publicly
                                available content from Instagram, including but not limited to:
                            </p>
                            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 mt-4">
                                <li>Instagram Reels</li>
                                <li>Instagram Videos (including IGTV)</li>
                                <li>Instagram Photos</li>
                                <li>Instagram Stories</li>
                                <li>Instagram Carousel Posts</li>
                            </ul>
                            <p class="text-gray-600 dark:text-gray-400 leading-relaxed mt-4">
                                Our Service is provided "as is" without warranties of any kind. We do not guarantee
                                uninterrupted access or error-free operation.
                            </p>
                        </div>

                        <!-- User Responsibilities -->
                        <div class="mb-10">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                <span
                                    class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mr-3">
                                    <span class="text-blue-600 dark:text-blue-400 font-bold">3</span>
                                </span>
                                User Responsibilities
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
                                By using our Service, you agree to:
                            </p>
                            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2">
                                <li>Use the Service only for lawful purposes</li>
                                <li>Not download content from private Instagram accounts without permission</li>
                                <li>Respect the intellectual property rights of content creators</li>
                                <li>Use downloaded content for personal, non-commercial purposes only unless you have
                                    permission from the content owner</li>
                                <li>Not use the Service to harass, stalk, or harm others</li>
                                <li>Not attempt to bypass any security measures or access controls</li>
                                <li>Comply with Instagram's Terms of Service</li>
                            </ul>
                        </div>

                        <!-- Intellectual Property -->
                        <div class="mb-10">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                <span
                                    class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center mr-3">
                                    <span class="text-orange-600 dark:text-orange-400 font-bold">4</span>
                                </span>
                                Intellectual Property Rights
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                                <strong>Content on Instagram:</strong> All content available on Instagram belongs to its
                                respective owners. We do not claim any ownership rights over the content you download using
                                our Service.
                            </p>
                            <p class="text-gray-600 dark:text-gray-400 leading-relaxed mt-4">
                                <strong>Your Responsibility:</strong> You are solely responsible for ensuring that your use
                                of downloaded content complies with applicable copyright laws and the rights of content
                                creators. We encourage you to always credit original creators when sharing content.
                            </p>
                        </div>

                        <!-- Contact Information -->
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                <span class="w-10 h-10 rounded-xl instagram-gradient flex items-center justify-center mr-3">
                                    <span class="text-white font-bold">5</span>
                                </span>
                                Contact Information
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                                If you have any questions about these Terms, please contact us:
                            </p>
                            <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                                <p class="text-gray-700 dark:text-gray-300">
                                    <strong>General Inquiries:</strong> <a href="mailto:support@igreeldownloader.net"
                                        class="text-violet-600 dark:text-violet-400 hover:underline">support@igreeldownloader.net</a>
                                </p>
                                <p class="text-gray-700 dark:text-gray-300 mt-2">
                                    <strong>DMCA Notices:</strong> <a href="mailto:dmca@igreeldownloader.net"
                                        class="text-violet-600 dark:text-violet-400 hover:underline">dmca@igreeldownloader.net</a>
                                </p>
                                <p class="text-gray-700 dark:text-gray-300 mt-2">
                                    <strong>Contact Form:</strong> <a href="{{ route('contact') }}"
                                        class="text-violet-600 dark:text-violet-400 hover:underline">Click Here</a>
                                </p>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </section>
@endsection