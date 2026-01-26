@extends('layouts.app')

@section('title', 'Privacy Policy - IGReelDownloader.net')
@section('description', 'Read our Privacy Policy to understand how IGReelDownloader.net collects, uses, and protects
    your information.')

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
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                    </path>
                </svg>
                Your Privacy Matters
            </div>

            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 dark:text-white mb-4 slide-up"
                style="animation-delay: 0.1s">
                Privacy Policy
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

                    <!-- Introduction -->
                    <div class="mb-10">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span
                                class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </span>
                            Introduction
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                            Welcome to IGReelDownloader.net ("we," "our," or "us"). We are committed to protecting your
                            privacy and ensuring that your personal information is handled responsibly. This Privacy Policy
                            explains how we collect, use, disclose, and safeguard your information when you visit our
                            website and use our Instagram content downloading services.
                        </p>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed mt-4">
                            By using IGReelDownloader.net, you agree to the collection and use of information in accordance
                            with this policy. If you do not agree with this policy, please do not use our services.
                        </p>
                    </div>

                    <!-- Information We Collect -->
                    <div class="mb-10">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span
                                class="w-10 h-10 rounded-xl bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                            </span>
                            Information We Collect
                        </h2>

                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">Information You Provide
                        </h3>
                        <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2">
                            <li><strong>Instagram URLs:</strong> When you use our service, you provide Instagram post, reel,
                                video, story, or carousel URLs for downloading content.</li>
                            <li><strong>Contact Information:</strong> If you contact us through our contact form, you may
                                provide your name, email address, and message content.</li>
                        </ul>

                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mt-6 mb-3">Information Automatically
                            Collected</h3>
                        <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2">
                            <li><strong>Log Data:</strong> Our servers automatically record information including your IP
                                address, browser type, operating system, referring URLs, pages visited, and access times.
                            </li>
                            <li><strong>Device Information:</strong> We may collect information about the device you use to
                                access our service, including device type, operating system, and unique device identifiers.
                            </li>
                            <li><strong>Cookies:</strong> We use cookies and similar tracking technologies to enhance your
                                experience. See our Cookie Policy section below for more details.</li>
                        </ul>
                    </div>

                    <!-- How We Use Your Information -->
                    <div class="mb-10">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span
                                class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                                    </path>
                                </svg>
                            </span>
                            How We Use Your Information
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
                            We use the information we collect for the following purposes:
                        </p>
                        <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2">
                            <li>To provide and maintain our Instagram downloading service</li>
                            <li>To process your download requests</li>
                            <li>To respond to your inquiries and provide customer support</li>
                            <li>To improve our website and user experience</li>
                            <li>To monitor and analyze usage patterns and trends</li>
                            <li>To detect, prevent, and address technical issues or abuse</li>
                            <li>To comply with legal obligations</li>
                        </ul>
                    </div>

                    <!-- Data Retention -->
                    <div class="mb-10">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span
                                class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </span>
                            Data Retention
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                            <strong>Downloaded Content:</strong> We do not permanently store the Instagram content you
                            download. Downloaded files are temporarily cached on our servers for processing and are
                            automatically deleted within 1 hour of download completion.
                        </p>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed mt-4">
                            <strong>URLs Submitted:</strong> We do not log or store the Instagram URLs you submit for
                            downloading.
                        </p>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed mt-4">
                            <strong>Server Logs:</strong> Server logs containing IP addresses and access information are
                            retained for up to 30 days for security and analytics purposes, after which they are
                            automatically deleted.
                        </p>
                    </div>

                    <!-- Cookies Policy -->
                    <div class="mb-10">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span
                                class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4">
                                    </path>
                                </svg>
                            </span>
                            Cookies Policy
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
                            We use cookies and similar technologies to:
                        </p>
                        <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2">
                            <li><strong>Essential Cookies:</strong> Required for basic site functionality and security.</li>
                            <li><strong>Preference Cookies:</strong> Remember your settings like theme preference
                                (dark/light mode).</li>
                            <li><strong>Analytics Cookies:</strong> Help us understand how visitors interact with our
                                website.</li>
                        </ul>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed mt-4">
                            You can control cookies through your browser settings. However, disabling cookies may affect
                            website functionality.
                        </p>
                    </div>

                    <!-- Third-Party Services -->
                    <div class="mb-10">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span
                                class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9">
                                    </path>
                                </svg>
                            </span>
                            Third-Party Services
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
                            We may use third-party services that collect, monitor, and analyze data:
                        </p>
                        <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2">
                            <li><strong>Google Analytics:</strong> For website traffic analysis</li>
                            <li><strong>Cloudflare:</strong> For content delivery and security</li>
                            <li><strong>Advertising Partners:</strong> To display relevant advertisements</li>
                        </ul>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed mt-4">
                            These third parties have their own privacy policies governing the use of your information.
                        </p>
                    </div>

                    <!-- Data Security -->
                    <div class="mb-10">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span
                                class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </span>
                            Data Security
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                            We implement appropriate technical and organizational security measures to protect your
                            information against unauthorized access, alteration, disclosure, or destruction. These measures
                            include:
                        </p>
                        <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 mt-4">
                            <li>SSL/TLS encryption for all data transmission</li>
                            <li>Regular security assessments and updates</li>
                            <li>Access controls and authentication mechanisms</li>
                            <li>Secure server infrastructure</li>
                        </ul>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed mt-4">
                            However, no method of transmission over the Internet is 100% secure, and we cannot guarantee
                            absolute security.
                        </p>
                    </div>

                    <!-- Your Rights -->
                    <div class="mb-10">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span
                                class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </span>
                            Your Rights
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
                            Depending on your location, you may have the following rights regarding your personal data:
                        </p>
                        <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2">
                            <li><strong>Access:</strong> Request access to your personal data we hold</li>
                            <li><strong>Rectification:</strong> Request correction of inaccurate data</li>
                            <li><strong>Erasure:</strong> Request deletion of your personal data</li>
                            <li><strong>Portability:</strong> Request a copy of your data in a portable format</li>
                            <li><strong>Objection:</strong> Object to certain processing of your data</li>
                            <li><strong>Withdraw Consent:</strong> Withdraw consent at any time where we rely on consent
                            </li>
                        </ul>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed mt-4">
                            To exercise any of these rights, please contact us using the information provided below.
                        </p>
                    </div>

                    <!-- Children's Privacy -->
                    <div class="mb-10">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span
                                class="w-10 h-10 rounded-xl bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                    </path>
                                </svg>
                            </span>
                            Children's Privacy
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                            Our service is not directed to children under 13 years of age. We do not knowingly collect
                            personal information from children under 13. If you are a parent or guardian and believe your
                            child has provided us with personal information, please contact us, and we will take steps to
                            delete such information.
                        </p>
                    </div>

                    <!-- Changes to This Policy -->
                    <div class="mb-10">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span
                                class="w-10 h-10 rounded-xl bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                    </path>
                                </svg>
                            </span>
                            Changes to This Policy
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                            We may update this Privacy Policy from time to time. We will notify you of any changes by
                            posting the new Privacy Policy on this page and updating the "Last updated" date. You are
                            advised to review this Privacy Policy periodically for any changes.
                        </p>
                    </div>

                    <!-- Contact Us -->
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span class="w-10 h-10 rounded-xl instagram-gradient flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </span>
                            Contact Us
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                            If you have any questions about this Privacy Policy or our data practices, please contact us:
                        </p>
                        <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                            <p class="text-gray-700 dark:text-gray-300">
                                <strong>Email:</strong> <a href="mailto:privacy@igreeldownloader.net"
                                    class="text-violet-600 dark:text-violet-400 hover:underline">privacy@igreeldownloader.net</a>
                            </p>
                            <p class="text-gray-700 dark:text-gray-300 mt-2">
                                <strong>Website:</strong> <a href="{{ route('contact') }}"
                                    class="text-violet-600 dark:text-violet-400 hover:underline">Contact Form</a>
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection