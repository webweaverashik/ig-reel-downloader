<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;

class InstagramDownloaderController extends Controller
{
    /**
     * Get page configuration from database or fallback to defaults
     * Returns null if page is inactive
     */
    private function getPageConfig(string $pageSlug): ?array
    {
        // Check if page exists in database
        $page = Page::where('slug', $pageSlug)->first();
        
        // If page exists but is inactive, return null to signal redirection
        if ($page && !$page->is_active) {
            return null;
        }
        
        // Get FAQs from database
        $faqs = Faq::where('page_slug', $pageSlug)
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(fn($faq) => ['q' => $faq->question, 'a' => $faq->answer])
            ->toArray();
        
        if ($page) {
            return [
                'title' => $page->meta_title ?: $page->title,
                'meta_description' => $page->meta_description ?? '',
                'meta_keywords' => $page->meta_keywords ?? '',
                'hero_title' => $page->hero_title ?: $page->title,
                'hero_highlight' => $page->hero_highlight ?? '',
                'subtitle' => $page->subtitle ?? '',
                'badge' => $page->badge ?? '',
                'placeholder' => $page->placeholder ?? 'Paste Instagram URL here...',
                'formats' => $page->formats ?? [],
                'faqs' => !empty($faqs) ? $faqs : $this->getDefaultFaqs($pageSlug),
            ];
        }
        
        // Fallback to default configurations
        $defaults = $this->getDefaultPageConfigs();
        $config = $defaults[$pageSlug] ?? $defaults['home'];
        
        // If we have FAQs from database, use them
        if (!empty($faqs)) {
            $config['faqs'] = $faqs;
        }
        
        return $config;
    }

    /**
     * Get default FAQs for a page
     */
    private function getDefaultFaqs(string $pageSlug): array
    {
        $defaults = $this->getDefaultPageConfigs();
        return $defaults[$pageSlug]['faqs'] ?? $defaults['home']['faqs'] ?? [];
    }

    /**
     * Default page configurations (fallback when database is empty)
     */
    private function getDefaultPageConfigs(): array
    {
        return [
            'home' => [
                'title' => 'IG Reel Downloader - Best Instagram Downloader | IGReelDownloader.net',
                'meta_description' => 'With IG Reel Downloader, download any reels, videos and photos from Instagram easily. Free, fast, and no login required.',
                'hero_title' => 'IG Reel Downloader',
                'hero_highlight' => 'Best Instagram Downloader',
                'subtitle' => 'With IG Reel Downloader, download any reels, videos and photos from Instagram easily. Free, fast, and no login required.',
                'badge' => '100% Free & Unlimited Downloads',
                'placeholder' => 'Paste Instagram URL here (Reels, Videos, Photos)...',
                'formats' => ['Reels', 'Videos', 'Photos', 'Stories', 'Carousel', 'HD Quality'],
                'faqs' => [
                    ['q' => 'What is IG Reel Downloader?', 'a' => 'IG Reel Downloader is the best free online tool to download Instagram Reels, Videos, Photos, Stories, and Carousel posts in HD quality. No login or registration required.'],
                    ['q' => 'How do I download Instagram content?', 'a' => 'Simply copy the Instagram URL (Reel, Video, Photo, Story, or Carousel), paste it in the input field above, and click Download. Your content will be ready in seconds.'],
                    ['q' => 'Is IG Reel Downloader free to use?', 'a' => 'Yes! IG Reel Downloader is completely free with no hidden charges, no subscription fees, and unlimited downloads.'],
                    ['q' => 'What quality can I download in?', 'a' => 'We always provide the highest quality available - typically 1080p HD for videos and original resolution for photos.'],
                    ['q' => 'Do I need to login to download?', 'a' => 'No, you don\'t need to login or create an account. Just paste the URL and download instantly.'],
                    ['q' => 'Can I download from private accounts?', 'a' => 'No, only public content can be downloaded. Private account content requires the owner\'s permission.'],
                    ['q' => 'Does IG Reel Downloader work on mobile?', 'a' => 'Yes! Our downloader works perfectly on all devices including smartphones, tablets, and desktop computers.'],
                    ['q' => 'Is it safe to use IG Reel Downloader?', 'a' => 'Absolutely! We don\'t store any of your data or downloaded content. Your privacy is our top priority.'],
                ],
            ],
            'reels' => [
                'title' => 'Instagram Reels Downloader - Download Reels in HD | IGReelDownloader.net',
                'meta_description' => 'Download Instagram Reels in HD quality. Free, fast, and no login required. Save your favorite Reels instantly with IG Reel Downloader.',
                'hero_title' => 'Instagram Reels Downloader',
                'hero_highlight' => 'Download Reels in HD',
                'subtitle' => 'Download any Instagram Reels in HD quality. Fast, free, and no login required. Save your favorite Reels instantly.',
                'badge' => 'Free & Unlimited Reels Downloads',
                'placeholder' => 'Paste Instagram Reel URL here...',
                'formats' => ['Reels', 'HD Quality', 'MP4 Format', 'No Watermark', 'Fast Download'],
                'faqs' => [
                    ['q' => 'How do I download Instagram Reels?', 'a' => 'Simply copy the Reel URL from Instagram, paste it in the input field above, and click Download. Your Reel will be ready in seconds.'],
                    ['q' => 'Is downloading Reels free?', 'a' => 'Yes, our Instagram Reels downloader is completely free with no hidden charges or subscription fees.'],
                    ['q' => 'What quality can I download Reels in?', 'a' => 'We always provide the highest quality available, typically 1080p HD or the original upload quality.'],
                    ['q' => 'Do I need to login to download Reels?', 'a' => 'No, you don\'t need to login or create an account. Just paste the URL and download instantly.'],
                    ['q' => 'Can I download Reels on mobile?', 'a' => 'Yes! Our downloader works perfectly on all devices including smartphones and tablets.'],
                    ['q' => 'Are Reels downloaded without watermark?', 'a' => 'Yes, we download Reels in their original quality without any added watermarks.'],
                ],
            ],
            'video' => [
                'title' => 'Instagram Video Downloader - Download IG Videos in HD | IGReelDownloader.net',
                'meta_description' => 'Download Instagram Videos in HD quality. Free, fast, and works on all devices. Save IGTV and video posts instantly.',
                'hero_title' => 'Instagram Video Downloader',
                'hero_highlight' => 'Download Videos in HD',
                'subtitle' => 'Download any Instagram video in original HD quality. Fast, free, and works on all devices. Save IGTV and video posts instantly.',
                'badge' => 'Free HD Video Downloads',
                'placeholder' => 'Paste Instagram Video URL here...',
                'formats' => ['IGTV', 'Video Posts', 'HD 1080p', 'MP4 Format', 'Original Quality'],
                'faqs' => [
                    ['q' => 'How do I download Instagram videos?', 'a' => 'Copy the video URL from Instagram, paste it above, and click Download. We support all Instagram video formats including IGTV.'],
                    ['q' => 'What video formats are supported?', 'a' => 'We support all Instagram video types: regular video posts, IGTV, and video content from carousel posts.'],
                    ['q' => 'Is the video quality preserved?', 'a' => 'Yes, we always download videos in the highest available quality, up to 1080p HD.'],
                    ['q' => 'Can I download private account videos?', 'a' => 'No, only public videos can be downloaded. Private account content requires the owner\'s permission.'],
                    ['q' => 'Are there any download limits?', 'a' => 'No limits! Download as many videos as you want, completely free.'],
                    ['q' => 'What is the video format?', 'a' => 'Videos are downloaded in MP4 format, which is compatible with all devices and media players.'],
                ],
            ],
            'photo' => [
                'title' => 'Instagram Photo Downloader - Download IG Photos in HD | IGReelDownloader.net',
                'meta_description' => 'Download Instagram photos in full resolution. Save profile pictures, posts, and images in original quality instantly.',
                'hero_title' => 'Instagram Photo Downloader',
                'hero_highlight' => 'Download Photos in HD',
                'subtitle' => 'Download Instagram photos in full resolution. Save profile pictures, posts, and images in original quality instantly.',
                'badge' => 'Free HD Photo Downloads',
                'placeholder' => 'Paste Instagram Photo URL here...',
                'formats' => ['Photos', 'Profile Pictures', 'Full Resolution', 'JPG/PNG', 'Original Size'],
                'faqs' => [
                    ['q' => 'How do I download Instagram photos?', 'a' => 'Copy the photo post URL from Instagram, paste it in the field above, and click Download to save it in full resolution.'],
                    ['q' => 'What image quality will I get?', 'a' => 'We download photos in their original full resolution, exactly as uploaded by the creator.'],
                    ['q' => 'Can I download multiple photos from a post?', 'a' => 'Yes! For carousel posts with multiple photos, we provide a "Download All" option to save everything at once.'],
                    ['q' => 'What format are photos saved in?', 'a' => 'Photos are saved in their original format, typically JPG or PNG, maintaining full quality.'],
                    ['q' => 'Can I download profile pictures?', 'a' => 'Yes, you can download profile pictures in full resolution using our tool.'],
                    ['q' => 'Is there a size limit for photos?', 'a' => 'No, we download photos in their original size without any compression.'],
                ],
            ],
            'story' => [
                'title' => 'Instagram Story Downloader - Download IG Stories | IGReelDownloader.net',
                'meta_description' => 'Download Instagram Stories before they disappear. Save photos and videos from stories in HD quality anonymously.',
                'hero_title' => 'Instagram Story Downloader',
                'hero_highlight' => 'Download Stories Anonymously',
                'subtitle' => 'Download Instagram Stories before they disappear. Save photos and videos from stories in HD quality anonymously.',
                'badge' => 'Anonymous Story Downloads',
                'placeholder' => 'Paste Instagram Story URL here...',
                'formats' => ['Stories', 'Highlights', 'Photos', 'Videos', 'Anonymous'],
                'faqs' => [
                    ['q' => 'How do I download Instagram Stories?', 'a' => 'Copy the story URL from Instagram (or the story highlight URL), paste it above, and click Download.'],
                    ['q' => 'Will the user know I downloaded their story?', 'a' => 'Our tool downloads stories anonymously. The user won\'t be notified that you saved their content.'],
                    ['q' => 'Can I download story highlights?', 'a' => 'Yes! You can download both regular stories and story highlights using our tool.'],
                    ['q' => 'What if the story has expired?', 'a' => 'Unfortunately, expired stories cannot be downloaded. You need to save them before they disappear after 24 hours.'],
                    ['q' => 'Are story videos and photos supported?', 'a' => 'Yes, we support both photo and video stories in their original quality.'],
                    ['q' => 'Can I download stories from private accounts?', 'a' => 'No, only stories from public accounts can be downloaded.'],
                ],
            ],
            'carousel' => [
                'title' => 'Instagram Carousel Downloader - Download Multiple Photos/Videos | IGReelDownloader.net',
                'meta_description' => 'Download all photos and videos from Instagram carousel posts at once. Save multiple items in HD quality with one click.',
                'hero_title' => 'Instagram Carousel Downloader',
                'hero_highlight' => 'Download All Carousel Items',
                'subtitle' => 'Download all photos and videos from Instagram carousel posts at once. Save multiple items in HD quality with one click.',
                'badge' => 'Bulk Carousel Downloads',
                'placeholder' => 'Paste Instagram Carousel URL here...',
                'formats' => ['Multiple Photos', 'Multiple Videos', 'Bulk Download', 'ZIP Archive', 'HD Quality'],
                'faqs' => [
                    ['q' => 'What is a carousel post?', 'a' => 'A carousel is an Instagram post containing multiple photos or videos that you can swipe through. We can download all items at once.'],
                    ['q' => 'How many items can I download at once?', 'a' => 'Instagram allows up to 10 items per carousel, and we can download all of them in a single click.'],
                    ['q' => 'Will I get all items from the carousel?', 'a' => 'Yes! We detect and download every photo and video in the carousel, providing them in a convenient ZIP file.'],
                    ['q' => 'What if the carousel has both photos and videos?', 'a' => 'No problem! We handle mixed carousel posts and download all content types in their original quality.'],
                    ['q' => 'Can I download individual items?', 'a' => 'Yes, you can choose to download items individually or use "Download All" to get everything as a ZIP.'],
                    ['q' => 'What format is the download?', 'a' => 'Individual items download in their original format. "Download All" creates a ZIP archive.'],
                ],
            ],
            'highlights' => [
                'title' => 'Instagram Highlights Downloader - Download IG Highlights | IGReelDownloader.net',
                'meta_description' => 'Download Instagram Highlights to your device. Save permanent stories from profiles in HD quality.',
                'hero_title' => 'Instagram Highlights Downloader',
                'hero_highlight' => 'Download Highlights',
                'subtitle' => 'Download Instagram Highlights easily. Save your favorite profile highlights forever.',
                'badge' => 'Free Highlights Downloader',
                'placeholder' => 'Paste Instagram Highlight URL here...',
                'formats' => ['Highlights', 'Stories Archive', 'HD Quality', 'Anonymous', 'Permanent Save'],
                'faqs' => [
                    ['q' => 'How do I download Instagram Highlights?', 'a' => 'Copy the highlight URL from an Instagram profile, paste it above, and click Download.'],
                    ['q' => 'Can I download all highlights at once?', 'a' => 'Currently we support downloading individual highlight items. You can paste the link to a specific highlight story.'],
                    ['q' => 'Is it anonymous?', 'a' => 'Yes, the user will not know you viewed or downloaded their highlight.'],
                    ['q' => 'Do I need an account?', 'a' => 'No, you don\'t need to login to download public highlights.'],
                    ['q' => 'What about private accounts?', 'a' => 'We can only download highlights from public Instagram accounts.'],
                ],
            ],
        ];
    }

    /**
     * Python executable path from config - always get fresh value
     */
    private function getPythonPath(): string
    {
        $path = env('PYTHON_PATH', null);
        if ($path === null) {
            $path = config('services.python.path', '/usr/bin/python3');
        }
        return $path;
    }

    /**
     * Get yt-dlp path from config - always get fresh value
     */
    private function getYtDlpPath(): string
    {
        $path = env('YTDLP_PATH', null);
        if ($path === null) {
            $path = config('services.ytdlp.path', '/usr/local/bin/yt-dlp');
        }
        return $path;
    }

    /**
     * Get all available cookie files with absolute paths - FRESH each time
     */
    private function getCookieFiles(): array
    {
        $cookiesDir = realpath(base_path('python_worker/cookies'));

        if (! $cookiesDir || ! is_dir($cookiesDir)) {
            Log::warning('Cookies directory not found', [
                'expected' => base_path('python_worker/cookies'),
                'realpath' => $cookiesDir,
            ]);
            return [];
        }

        clearstatcache();
        $files = glob($cookiesDir . '/*.txt');

        if (empty($files)) {
            Log::warning('No cookie files found in cookies directory', ['path' => $cookiesDir]);
            return [];
        }

        $validFiles = [];
        foreach ($files as $file) {
            clearstatcache(true, $file);
            $absolutePath = realpath($file);
            if ($absolutePath && is_readable($absolutePath)) {
                $size = filesize($absolutePath);
                if ($size > 50) {
                    $validFiles[] = $absolutePath;
                    Log::debug('Valid cookie file found', [
                        'file' => basename($absolutePath),
                        'size' => $size,
                        'path' => $absolutePath,
                    ]);
                }
            }
        }

        usort($validFiles, function ($a, $b) {
            $aName = basename($a);
            $bName = basename($b);
            if ($aName === 'instagram.txt') return -1;
            if ($bName === 'instagram.txt') return 1;
            clearstatcache(true, $a);
            clearstatcache(true, $b);
            return filemtime($b) - filemtime($a);
        });

        Log::info('Cookie files loaded', [
            'count' => count($validFiles),
            'files' => array_map('basename', $validFiles),
        ]);

        return $validFiles;
    }

    /**
     * Display Home/Landing page
     */
    public function home()
    {
        $config = $this->getPageConfig('home');
        
        // Ideally home should always be active, but just in case
        if ($config === null) {
            abort(404); // Or show a maintenance page
        }
        
        return view('home', [
            'pageType' => 'home',
            'config' => $config,
        ]);
    }

    /**
     * Display Reels Downloader page
     */
    public function reels()
    {
        $config = $this->getPageConfig('reels');
        
        if ($config === null) {
            return redirect()->route('home');
        }
        
        return view('instagram-downloader', [
            'pageType' => 'reels',
            'config' => $config,
        ]);
    }

    /**
     * Display Video Downloader page
     */
    public function video()
    {
        $config = $this->getPageConfig('video');
        
        if ($config === null) {
            return redirect()->route('home');
        }
        
        return view('instagram-downloader', [
            'pageType' => 'video',
            'config' => $config,
        ]);
    }

    /**
     * Display Photo Downloader page
     */
    public function photo()
    {
        $config = $this->getPageConfig('photo');
        
        if ($config === null) {
            return redirect()->route('home');
        }
        
        return view('instagram-downloader', [
            'pageType' => 'photo',
            'config' => $config,
        ]);
    }

    /**
     * Display Story Downloader page
     */
    public function story()
    {
        $config = $this->getPageConfig('story');
        
        if ($config === null) {
            return redirect()->route('home');
        }
        
        return view('instagram-downloader', [
            'pageType' => 'story',
            'config' => $config,
        ]);
    }

    /**
     * Display Carousel Downloader page
     */
    public function carousel()
    {
        $config = $this->getPageConfig('carousel');
        
        if ($config === null) {
            return redirect()->route('home');
        }
        
        return view('instagram-downloader', [
            'pageType' => 'carousel',
            'config' => $config,
        ]);
    }

    /**
     * Display Highlights Downloader page
     */
    public function highlights()
    {
        $config = $this->getPageConfig('highlights');
        
        if ($config === null) {
            return redirect()->route('home');
        }
        
        return view('instagram-downloader', [
            'pageType' => 'highlights',
            'config' => $config,
        ]);
    }

    /**
     * Check cookie status (for debugging)
     */
    public function cookieStatus()
    {
        clearstatcache();
        $cookies = $this->getCookieFiles();
        $status = [];

        foreach ($cookies as $cookieFile) {
            clearstatcache(true, $cookieFile);
            $name = basename($cookieFile);
            $size = filesize($cookieFile);
            $modified = date('Y-m-d H:i:s', filemtime($cookieFile));
            $readable = is_readable($cookieFile);
            $status[] = [
                'name' => $name,
                'path' => $cookieFile,
                'size' => $size,
                'modified' => $modified,
                'readable' => $readable,
                'valid' => $size > 50 && $readable,
            ];
        }

        $pythonPath = $this->getPythonPath();
        $ytdlpPath = $this->getYtDlpPath();
        $pythonVersion = null;
        $ytdlpVersion = null;

        try {
            exec($pythonPath . ' --version 2>&1', $pythonOutput, $pythonCode);
            $pythonVersion = $pythonCode === 0 ? implode(' ', $pythonOutput) : 'Error: ' . implode(' ', $pythonOutput);
        } catch (\Exception $e) {
            $pythonVersion = 'Exception: ' . $e->getMessage();
        }

        try {
            exec($ytdlpPath . ' --version 2>&1', $ytdlpOutput, $ytdlpCode);
            $ytdlpVersion = $ytdlpCode === 0 ? implode(' ', $ytdlpOutput) : 'Error: ' . implode(' ', $ytdlpOutput);
        } catch (\Exception $e) {
            $ytdlpVersion = 'Exception: ' . $e->getMessage();
        }

        try {
            exec($pythonPath . ' -c "import requests; print(requests.__version__)" 2>&1', $requestsOutput, $requestsCode);
            $requestsVersion = $requestsCode === 0 ? implode(' ', $requestsOutput) : 'Not installed';
        } catch (\Exception $e) {
            $requestsVersion = 'Check failed';
        }

        $testCmd = escapeshellarg($pythonPath) . ' -c "import sys, json; print(json.dumps({\'success\': True}))" 2>&1';
        $testOutput = shell_exec($testCmd);
        $pythonWorks = strpos($testOutput, 'success') !== false;

        return response()->json([
            'success' => true,
            'timestamp' => now()->toIso8601String(),
            'total_cookies' => count($cookies),
            'python_path' => $pythonPath,
            'python_version' => $pythonVersion,
            'python_works' => $pythonWorks,
            'ytdlp_path' => $ytdlpPath,
            'ytdlp_version' => $ytdlpVersion,
            'requests_library' => $requestsVersion,
            'base_path' => base_path(),
            'cookies_dir' => realpath(base_path('python_worker/cookies')) ?: base_path('python_worker/cookies'),
            'web_user' => get_current_user(),
            'process_user' => function_exists('posix_geteuid') ? (posix_getpwuid(posix_geteuid())['name'] ?? 'unknown') : php_uname('n'),
            'cookies' => $status,
        ]);
    }

    /**
     * Fetch Instagram content via Python worker with multiple cookie support
     */
    public function fetch(Request $request)
    {
        clearstatcache();

        $request->validate(
            [
                'url' => ['required', 'url', 'regex:/^https?:\/\/(www\.)?instagram\.com\/(p|reel|reels|tv|stories)\/[\w\-\.]+/i'],
            ],
            [
                'url.required' => 'Please enter an Instagram URL.',
                'url.url' => 'Please enter a valid URL.',
                'url.regex' => 'Please enter a valid Instagram URL (post, reel, video, or story).',
            ],
        );

        $url = $request->input('url');

        try {
            $sessionId = Str::uuid()->toString();
            $downloadPath = storage_path('app/downloads/' . $sessionId);

            if (! file_exists($downloadPath)) {
                mkdir($downloadPath, 0755, true);
            }

            $cookieFiles = $this->getCookieFiles();
            if (empty($cookieFiles)) {
                Log::error('No Instagram cookies configured');
                return response()->json([
                    'success' => false,
                    'error' => 'No Instagram cookies configured. Please add valid cookie files.',
                    'error_type' => 'cookies_missing',
                ], 400);
            }

            $pythonScript = realpath(base_path('python_worker/instagram_fetch.py'));
            if (! $pythonScript || ! file_exists($pythonScript)) {
                Log::error('Python script not found', ['path' => base_path('python_worker/instagram_fetch.py')]);
                return response()->json([
                    'success' => false,
                    'error' => 'Server configuration error. Python script not found.',
                    'error_type' => 'script_missing',
                ], 500);
            }

            $python = $this->getPythonPath();
            $ytDlpPath = $this->getYtDlpPath();

            if (is_dir($ytDlpPath)) {
                Log::info('yt-dlp path is a directory, letting Python find it as module');
                $ytDlpPath = '';
            }

            $cookiesJson = json_encode($cookieFiles);

            Log::info('Starting Instagram fetch', [
                'url' => $url,
                'session_id' => $sessionId,
                'cookie_count' => count($cookieFiles),
                'cookies' => array_map('basename', $cookieFiles),
                'python' => $python,
                'yt_dlp' => $ytDlpPath,
                'script' => $pythonScript,
                'download_path' => $downloadPath,
            ]);

            $escapedPython = escapeshellarg($python);
            $escapedScript = escapeshellarg($pythonScript);
            $escapedUrl = escapeshellarg($url);
            $escapedDownloadPath = escapeshellarg($downloadPath);
            $escapedCookiesJson = escapeshellarg($cookiesJson);
            $escapedYtDlpPath = escapeshellarg($ytDlpPath);

            $cmd = "{$escapedPython} {$escapedScript} {$escapedUrl} {$escapedDownloadPath} {$escapedCookiesJson} {$escapedYtDlpPath} 2>&1";

            Log::debug('Executing command', ['cmd' => substr($cmd, 0, 500) . '...']);

            $originalCwd = getcwd();
            $scriptDir = dirname($pythonScript);
            chdir($scriptDir);

            $envBackup = [
                'HOME' => getenv('HOME'),
                'PATH' => getenv('PATH'),
            ];

            putenv('HOME=/tmp');
            putenv('PATH=/usr/local/bin:/usr/bin:/bin:' . getenv('PATH'));

            $output = shell_exec($cmd);

            chdir($originalCwd);
            putenv('HOME=' . ($envBackup['HOME'] ?: ''));
            if ($envBackup['PATH']) {
                putenv('PATH=' . $envBackup['PATH']);
            }

            Log::info('Python script completed', [
                'output_length' => strlen($output ?? ''),
                'output_preview' => substr($output ?? '', 0, 1000),
            ]);

            $outputString = trim($output ?? '');

            $jsonOutput = null;
            $lines = preg_split("/\r\n|\r|\n/", $outputString);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                $decoded = json_decode($line, true);
                if ($decoded !== null && (isset($decoded['success']) || isset($decoded['error']))) {
                    $jsonOutput = $decoded;
                    break;
                }
            }

            if ($jsonOutput === null) {
                Log::error('Failed to parse Python output', ['output' => substr($outputString, 0, 2000)]);

                if (stripos($outputString, 'login') !== false || stripos($outputString, 'cookie') !== false) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Instagram session expired. Please try again later.',
                        'error_type' => 'login_required',
                    ], 400);
                }

                if (stripos($outputString, 'private') !== false) {
                    return response()->json([
                        'success' => false,
                        'error' => 'This content is from a private account.',
                        'error_type' => 'private_content',
                    ], 400);
                }

                return response()->json([
                    'success' => false,
                    'error' => 'Failed to process Instagram content. Please try again.',
                    'error_type' => 'parse_error',
                    'debug' => config('app.debug') ? substr($outputString, 0, 1000) : null,
                ], 500);
            }

            if (isset($jsonOutput['error'])) {
                Log::warning('Python script returned error', [
                    'error' => $jsonOutput['error'],
                    'type' => $jsonOutput['error_type'] ?? 'unknown',
                    'debug' => $jsonOutput['debug'] ?? null,
                ]);

                return response()->json([
                    'success' => false,
                    'error' => $jsonOutput['error'],
                    'error_type' => $jsonOutput['error_type'] ?? 'unknown',
                    'cookies_tried' => $jsonOutput['cookies_tried'] ?? 0,
                    'debug' => config('app.debug') ? ($jsonOutput['debug'] ?? null) : null,
                ], 400);
            }

            if (isset($jsonOutput['items']) && is_array($jsonOutput['items'])) {
                foreach ($jsonOutput['items'] as &$item) {
                    if (isset($item['path'])) {
                        $filename = basename($item['path']);
                        $item['download_url'] = route('instagram.download', [
                            'folder' => $sessionId,
                            'filename' => $filename,
                        ]);
                        unset($item['path']);
                    }

                    $thumbSource = $item['thumbnail_file'] ?? ($item['thumbnail'] ?? null);
                    if ($thumbSource) {
                        $isLocalPath = str_contains($thumbSource, ':\\') || str_starts_with($thumbSource, '/') || str_contains($thumbSource, DIRECTORY_SEPARATOR);
                        if ($isLocalPath && file_exists($thumbSource)) {
                            $thumbFilename = basename($thumbSource);
                            $item['thumbnail_url'] = route('instagram.download', [
                                'folder' => $sessionId,
                                'filename' => $thumbFilename,
                            ]);
                        } elseif (filter_var($thumbSource, FILTER_VALIDATE_URL)) {
                            $item['thumbnail_url'] = $thumbSource;
                        }
                    }
                    unset($item['thumbnail_file']);
                }
            }

            $jsonOutput['download_all_url'] = route('instagram.download.all', ['folder' => $sessionId]);
            $jsonOutput['session_id'] = $sessionId;
            $jsonOutput['success'] = true;

            Log::info('Instagram fetch successful', [
                'session_id' => $sessionId,
                'type' => $jsonOutput['type'] ?? 'unknown',
                'items_count' => count($jsonOutput['items'] ?? []),
            ]);

            return response()->json($jsonOutput);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Instagram fetch exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred. Please try again.',
                'error_type' => 'exception',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Download a single file
     */
    public function download(string $folder, string $filename)
    {
        $folder = basename($folder);
        $filename = basename($filename);
        $filePath = storage_path('app/downloads/' . $folder . '/' . $filename);

        if (! file_exists($filePath)) {
            Log::warning('Download file not found', ['path' => $filePath]);
            abort(404, 'File not found');
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimeTypes = [
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'mkv' => 'video/x-matroska',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
        ];

        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

        return response()->download($filePath, $filename, [
            'Content-Type' => $mimeType,
        ]);
    }

    /**
     * Download all files as ZIP
     */
    public function downloadAll(string $folder)
    {
        $folder = basename($folder);
        $folderPath = storage_path('app/downloads/' . $folder);

        if (! is_dir($folderPath)) {
            Log::warning('Download folder not found', ['path' => $folderPath]);
            abort(404, 'Download folder not found');
        }

        $files = glob($folderPath . '/*');
        if (empty($files)) {
            abort(404, 'No files found');
        }

        $zipFileName = 'instagram_download_' . substr($folder, 0, 8) . '.zip';
        $zipPath = storage_path('app/downloads/' . $zipFileName);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            Log::error('Could not create ZIP file', ['path' => $zipPath]);
            abort(500, 'Could not create ZIP file');
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                $zip->addFile($file, basename($file));
            }
        }

        $zip->close();

        return response()
            ->download($zipPath, $zipFileName, [
                'Content-Type' => 'application/zip',
            ])
            ->deleteFileAfterSend(false);
    }
}