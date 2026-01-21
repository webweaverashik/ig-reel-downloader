<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ig reel downloader – Best Instagram Downloader')</title>
    <meta name="description" content="@yield('description', 'with ig reel downloader, download any reels, videos and photos from instagram easily.')">

    <!-- TailwindCSS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <style type="text/tailwindcss">
        @theme {
            --color-primary: #E1306C;
            --color-primary-hover: #C13584;
            --color-secondary: #833AB4;
            --color-accent: #F77737;
            --color-dark-bg: #0f0f0f;
            --color-dark-card: #1a1a1a;
            --color-dark-border: #2a2a2a;
            --color-light-bg: #f5f5f5;
            --color-light-card: #ffffff;
            --color-light-border: #e0e0e0;
        }
    </style>

    <style>
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #1a1a1a;
        }

        ::-webkit-scrollbar-thumb {
            background: #E1306C;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #C13584;
        }

        .gradient-text {
            background: linear-gradient(45deg, #F77737, #E1306C, #833AB4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .gradient-border {
            position: relative;
            background: linear-gradient(135deg, #F77737, #E1306C, #833AB4);
            padding: 2px;
            border-radius: 12px;
        }

        .gradient-border-inner {
            border-radius: 10px;
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(0.8);
                opacity: 1;
            }

            50% {
                transform: scale(1.2);
                opacity: 0.5;
            }

            100% {
                transform: scale(0.8);
                opacity: 1;
            }
        }

        .pulse-ring {
            animation: pulse-ring 1.5s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .toggle-switch {
            position: relative;
            width: 56px;
            height: 28px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #1a1a1a, #2a2a2a);
            transition: 0.4s;
            border-radius: 28px;
            border: 2px solid #3a3a3a;
        }

        .toggle-slider:before {
            position: absolute;
            content: "🌙";
            font-size: 14px;
            height: 20px;
            width: 20px;
            left: 4px;
            bottom: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.4s;
        }

        input:checked+.toggle-slider {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border-color: #fbbf24;
        }

        input:checked+.toggle-slider:before {
            transform: translateX(26px);
            content: "☀️";
        }
    </style>

    @stack('styles')
</head>

<body class="min-h-screen transition-colors duration-300 dark:bg-dark-bg bg-light-bg">
    @include('partials.header')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')

    @stack('scripts')
</body>

</html>
