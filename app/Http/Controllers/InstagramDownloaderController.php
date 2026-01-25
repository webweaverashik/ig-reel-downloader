<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;

class InstagramDownloaderController extends Controller
{
    /**
     * Page configurations for different downloader types
     */
    private function getPageConfigs(): array
    {
        return [
            'home'     => [
                'title'            => 'IG Reel Downloader - Best Instagram Downloader | IGReelDownloader.net',
                'meta_description' => 'With IG Reel Downloader, download any reels, videos and photos from Instagram easily. Free, fast, and no login required.',
                'hero_title'       => 'IG Reel Downloader',
                'hero_highlight'   => 'Best Instagram Downloader',
                'subtitle'         => 'With IG Reel Downloader, download any reels, videos and photos from Instagram easily. Free, fast, and no login required.',
                'badge'            => '100% Free & Unlimited Downloads',
                'placeholder'      => 'Paste Instagram URL here (Reels, Videos, Photos)...',
                'formats'          => ['Reels', 'Videos', 'Photos', 'Stories', 'Carousel', 'HD Quality'],
                'faqs'             => [
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
            'reels'    => [
                'title'            => 'Instagram Reels Downloader - Download Reels in HD | IGReelDownloader.net',
                'meta_description' => 'Download Instagram Reels in HD quality. Free, fast, and no login required. Save your favorite Reels instantly with IG Reel Downloader.',
                'hero_title'       => 'Instagram Reels Downloader',
                'hero_highlight'   => 'Download Reels in HD',
                'subtitle'         => 'Download any Instagram Reels in HD quality. Fast, free, and no login required. Save your favorite Reels instantly.',
                'badge'            => 'Free & Unlimited Reels Downloads',
                'placeholder'      => 'Paste Instagram Reel URL here...',
                'formats'          => ['Reels', 'HD Quality', 'MP4 Format', 'No Watermark', 'Fast Download'],
                'faqs'             => [
                    ['q' => 'How do I download Instagram Reels?', 'a' => 'Simply copy the Reel URL from Instagram, paste it in the input field above, and click Download. Your Reel will be ready in seconds.'],
                    ['q' => 'Is downloading Reels free?', 'a' => 'Yes, our Instagram Reels downloader is completely free with no hidden charges or subscription fees.'],
                    ['q' => 'What quality can I download Reels in?', 'a' => 'We always provide the highest quality available, typically 1080p HD or the original upload quality.'],
                    ['q' => 'Do I need to login to download Reels?', 'a' => 'No, you don\'t need to login or create an account. Just paste the URL and download instantly.'],
                    ['q' => 'Can I download Reels on mobile?', 'a' => 'Yes! Our downloader works perfectly on all devices including smartphones and tablets.'],
                    ['q' => 'Are Reels downloaded without watermark?', 'a' => 'Yes, we download Reels in their original quality without any added watermarks.'],
                ],
            ],
            'video'    => [
                'title'            => 'Instagram Video Downloader - Download IG Videos in HD | IGReelDownloader.net',
                'meta_description' => 'Download Instagram Videos in HD quality. Free, fast, and works on all devices. Save IGTV and video posts instantly.',
                'hero_title'       => 'Instagram Video Downloader',
                'hero_highlight'   => 'Download Videos in HD',
                'subtitle'         => 'Download any Instagram video in original HD quality. Fast, free, and works on all devices. Save IGTV and video posts instantly.',
                'badge'            => 'Free HD Video Downloads',
                'placeholder'      => 'Paste Instagram Video URL here...',
                'formats'          => ['IGTV', 'Video Posts', 'HD 1080p', 'MP4 Format', 'Original Quality'],
                'faqs'             => [
                    ['q' => 'How do I download Instagram videos?', 'a' => 'Copy the video URL from Instagram, paste it above, and click Download. We support all Instagram video formats including IGTV.'],
                    ['q' => 'What video formats are supported?', 'a' => 'We support all Instagram video types: regular video posts, IGTV, and video content from carousel posts.'],
                    ['q' => 'Is the video quality preserved?', 'a' => 'Yes, we always download videos in the highest available quality, up to 1080p HD.'],
                    ['q' => 'Can I download private account videos?', 'a' => 'No, only public videos can be downloaded. Private account content requires the owner\'s permission.'],
                    ['q' => 'Are there any download limits?', 'a' => 'No limits! Download as many videos as you want, completely free.'],
                    ['q' => 'What is the video format?', 'a' => 'Videos are downloaded in MP4 format, which is compatible with all devices and media players.'],
                ],
            ],
            'photo'    => [
                'title'            => 'Instagram Photo Downloader - Download IG Photos in HD | IGReelDownloader.net',
                'meta_description' => 'Download Instagram photos in full resolution. Save profile pictures, posts, and images in original quality instantly.',
                'hero_title'       => 'Instagram Photo Downloader',
                'hero_highlight'   => 'Download Photos in HD',
                'subtitle'         => 'Download Instagram photos in full resolution. Save profile pictures, posts, and images in original quality instantly.',
                'badge'            => 'Free HD Photo Downloads',
                'placeholder'      => 'Paste Instagram Photo URL here...',
                'formats'          => ['Photos', 'Profile Pictures', 'Full Resolution', 'JPG/PNG', 'Original Size'],
                'faqs'             => [
                    ['q' => 'How do I download Instagram photos?', 'a' => 'Copy the photo post URL from Instagram, paste it in the field above, and click Download to save it in full resolution.'],
                    ['q' => 'What image quality will I get?', 'a' => 'We download photos in their original full resolution, exactly as uploaded by the creator.'],
                    ['q' => 'Can I download multiple photos from a post?', 'a' => 'Yes! For carousel posts with multiple photos, we provide a "Download All" option to save everything at once.'],
                    ['q' => 'What format are photos saved in?', 'a' => 'Photos are saved in their original format, typically JPG or PNG, maintaining full quality.'],
                    ['q' => 'Can I download profile pictures?', 'a' => 'Yes, you can download profile pictures in full resolution using our tool.'],
                    ['q' => 'Is there a size limit for photos?', 'a' => 'No, we download photos in their original size without any compression.'],
                ],
            ],
            'story'    => [
                'title'            => 'Instagram Story Downloader - Download IG Stories | IGReelDownloader.net',
                'meta_description' => 'Download Instagram Stories before they disappear. Save photos and videos from stories in HD quality anonymously.',
                'hero_title'       => 'Instagram Story Downloader',
                'hero_highlight'   => 'Download Stories Anonymously',
                'subtitle'         => 'Download Instagram Stories before they disappear. Save photos and videos from stories in HD quality anonymously.',
                'badge'            => 'Anonymous Story Downloads',
                'placeholder'      => 'Paste Instagram Story URL here...',
                'formats'          => ['Stories', 'Highlights', 'Photos', 'Videos', 'Anonymous'],
                'faqs'             => [
                    ['q' => 'How do I download Instagram Stories?', 'a' => 'Copy the story URL from Instagram (or the story highlight URL), paste it above, and click Download.'],
                    ['q' => 'Will the user know I downloaded their story?', 'a' => 'Our tool downloads stories anonymously. The user won\'t be notified that you saved their content.'],
                    ['q' => 'Can I download story highlights?', 'a' => 'Yes! You can download both regular stories and story highlights using our tool.'],
                    ['q' => 'What if the story has expired?', 'a' => 'Unfortunately, expired stories cannot be downloaded. You need to save them before they disappear after 24 hours.'],
                    ['q' => 'Are story videos and photos supported?', 'a' => 'Yes, we support both photo and video stories in their original quality.'],
                    ['q' => 'Can I download stories from private accounts?', 'a' => 'No, only stories from public accounts can be downloaded.'],
                ],
            ],
            'carousel' => [
                'title'            => 'Instagram Carousel Downloader - Download Multiple Photos/Videos | IGReelDownloader.net',
                'meta_description' => 'Download all photos and videos from Instagram carousel posts at once. Save multiple items in HD quality with one click.',
                'hero_title'       => 'Instagram Carousel Downloader',
                'hero_highlight'   => 'Download All Carousel Items',
                'subtitle'         => 'Download all photos and videos from Instagram carousel posts at once. Save multiple items in HD quality with one click.',
                'badge'            => 'Bulk Carousel Downloads',
                'placeholder'      => 'Paste Instagram Carousel URL here...',
                'formats'          => ['Multiple Photos', 'Multiple Videos', 'Bulk Download', 'ZIP Archive', 'HD Quality'],
                'faqs'             => [
                    ['q' => 'What is a carousel post?', 'a' => 'A carousel is an Instagram post containing multiple photos or videos that you can swipe through. We can download all items at once.'],
                    ['q' => 'How many items can I download at once?', 'a' => 'Instagram allows up to 10 items per carousel, and we can download all of them in a single click.'],
                    ['q' => 'Will I get all items from the carousel?', 'a' => 'Yes! We detect and download every photo and video in the carousel, providing them in a convenient ZIP file.'],
                    ['q' => 'What if the carousel has both photos and videos?', 'a' => 'No problem! We handle mixed carousel posts and download all content types in their original quality.'],
                    ['q' => 'Can I download individual items?', 'a' => 'Yes, you can choose to download items individually or use "Download All" to get everything as a ZIP.'],
                    ['q' => 'What format is the download?', 'a' => 'Individual items download in their original format. "Download All" creates a ZIP archive.'],
                ],
            ],
        ];
    }

    /**
     * Python executable path from config
     */
    private function getPythonPath(): string
    {
        return config('services.python.path', '/usr/bin/python3');
    }

    /**
     * Get yt-dlp path from config
     */
    private function getYtDlpPath(): string
    {
        return config('services.ytdlp.path', '/usr/local/bin/yt-dlp');
    }

    /**
     * Get all available cookie files with absolute paths
     */
    private function getCookieFiles(): array
    {
        $cookiesDir = base_path('python_worker/cookies');

        if (! is_dir($cookiesDir)) {
            Log::warning('Cookies directory not found', ['path' => $cookiesDir]);
            return [];
        }

        $files = glob($cookiesDir . '/*.txt');

        if (empty($files)) {
            Log::warning('No cookie files found in cookies directory', ['path' => $cookiesDir]);
            return [];
        }

        // Convert to absolute paths and verify readability
        $validFiles = [];
        foreach ($files as $file) {
            $absolutePath = realpath($file);
            if ($absolutePath && is_readable($absolutePath) && filesize($absolutePath) > 50) {
                $validFiles[] = $absolutePath;
            } else {
                Log::warning('Cookie file invalid or unreadable', [
                    'file'     => $file,
                    'exists'   => file_exists($file),
                    'readable' => is_readable($file),
                    'size'     => file_exists($file) ? filesize($file) : 0,
                ]);
            }
        }

        // Sort: instagram.txt first, then by modification time
        usort($validFiles, function ($a, $b) {
            $aName = basename($a);
            $bName = basename($b);

            if ($aName === 'instagram.txt') {
                return -1;
            }

            if ($bName === 'instagram.txt') {
                return 1;
            }

            return filemtime($b) - filemtime($a);
        });

        Log::info('Found cookie files', [
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
        $config = $this->getPageConfigs()['home'];
        return view('home', [
            'pageType' => 'home',
            'config'   => $config,
        ]);
    }

    /**
     * Display Reels Downloader page
     */
    public function reels()
    {
        $config = $this->getPageConfigs()['reels'];
        return view('instagram-downloader', [
            'pageType' => 'reels',
            'config'   => $config,
        ]);
    }

    /**
     * Display Video Downloader page
     */
    public function video()
    {
        $config = $this->getPageConfigs()['video'];
        return view('instagram-downloader', [
            'pageType' => 'video',
            'config'   => $config,
        ]);
    }

    /**
     * Display Photo Downloader page
     */
    public function photo()
    {
        $config = $this->getPageConfigs()['photo'];
        return view('instagram-downloader', [
            'pageType' => 'photo',
            'config'   => $config,
        ]);
    }

    /**
     * Display Story Downloader page
     */
    public function story()
    {
        $config = $this->getPageConfigs()['story'];
        return view('instagram-downloader', [
            'pageType' => 'story',
            'config'   => $config,
        ]);
    }

    /**
     * Display Carousel Downloader page
     */
    public function carousel()
    {
        $config = $this->getPageConfigs()['carousel'];
        return view('instagram-downloader', [
            'pageType' => 'carousel',
            'config'   => $config,
        ]);
    }

    /**
     * Check cookie status (for debugging)
     */
    public function cookieStatus()
    {
        $cookies = $this->getCookieFiles();
        $status  = [];

        foreach ($cookies as $cookieFile) {
            $name     = basename($cookieFile);
            $size     = filesize($cookieFile);
            $modified = date('Y-m-d H:i:s', filemtime($cookieFile));
            $readable = is_readable($cookieFile);

            $status[] = [
                'name'     => $name,
                'path'     => $cookieFile,
                'size'     => $size,
                'modified' => $modified,
                'readable' => $readable,
                'valid'    => $size > 50 && $readable,
            ];
        }

        // Test Python and yt-dlp
        $pythonPath = $this->getPythonPath();
        $ytdlpPath  = $this->getYtDlpPath();

        $pythonVersion = null;
        $ytdlpVersion  = null;
        $hasRequests   = false;

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

        // Check if requests library is installed
        try {
            exec($pythonPath . ' -c "import requests; print(requests.__version__)" 2>&1', $requestsOutput, $requestsCode);
            $hasRequests     = $requestsCode === 0;
            $requestsVersion = $requestsCode === 0 ? implode(' ', $requestsOutput) : 'Not installed';
        } catch (\Exception $e) {
            $requestsVersion = 'Check failed';
        }

        return response()->json([
            'success'          => true,
            'total_cookies'    => count($cookies),
            'python_path'      => $pythonPath,
            'python_version'   => $pythonVersion,
            'ytdlp_path'       => $ytdlpPath,
            'ytdlp_version'    => $ytdlpVersion,
            'requests_library' => $requestsVersion,
            'base_path'        => base_path(),
            'cookies_dir'      => base_path('python_worker/cookies'),
            'web_user'         => get_current_user(),
            'process_user'     => function_exists('posix_geteuid') ? (posix_getpwuid(posix_geteuid())['name'] ?? 'unknown') : php_uname('n'),
            'cookies'          => $status,
        ]);
    }

    /**
     * Fetch Instagram content via Python worker with multiple cookie support
     */
    public function fetch(Request $request)
    {
        // Validate the URL
        $request->validate(
            [
                'url' => ['required', 'url', 'regex:/^https?:\/\/(www\.)?instagram\.com\/(p|reel|reels|tv|stories)\/[\w\-\.]+/i'],
            ],
            [
                'url.required' => 'Please enter an Instagram URL.',
                'url.url'      => 'Please enter a valid URL.',
                'url.regex'    => 'Please enter a valid Instagram URL (post, reel, video, or story).',
            ],
        );

        $url = $request->input('url');

        try {
            // Generate unique folder for this download session
            $sessionId    = Str::uuid()->toString();
            $downloadPath = storage_path('app/downloads/' . $sessionId);

            // Create download directory
            if (! file_exists($downloadPath)) {
                mkdir($downloadPath, 0755, true);
            }

            // Get all available cookie files (absolute paths)
            $cookieFiles = $this->getCookieFiles();

            if (empty($cookieFiles)) {
                Log::error('No Instagram cookies configured');
                return response()->json(
                    [
                        'success'    => false,
                        'error'      => 'No Instagram cookies configured. Please add valid cookie files.',
                        'error_type' => 'cookies_missing',
                    ],
                    400,
                );
            }

            // Path to Python script (absolute path)
            $pythonScript = base_path('python_worker/instagram_fetch.py');

            if (! file_exists($pythonScript)) {
                Log::error('Python script not found', ['path' => $pythonScript]);
                return response()->json(
                    [
                        'success'    => false,
                        'error'      => 'Server configuration error. Python script not found.',
                        'error_type' => 'script_missing',
                    ],
                    500,
                );
            }

            $python    = $this->getPythonPath();
            $ytDlpPath = $this->getYtDlpPath();

            // Prepare cookies list as JSON (already absolute paths)
            $cookiesJson = json_encode($cookieFiles);

            Log::info('Starting Instagram fetch', [
                'url'           => $url,
                'session_id'    => $sessionId,
                'cookie_count'  => count($cookieFiles),
                'python'        => $python,
                'yt_dlp'        => $ytDlpPath,
                'script'        => $pythonScript,
                'download_path' => $downloadPath,
            ]);

            // Build command - use shell_exec with full command for better compatibility
            $escapedPython       = escapeshellarg($python);
            $escapedScript       = escapeshellarg($pythonScript);
            $escapedUrl          = escapeshellarg($url);
            $escapedDownloadPath = escapeshellarg($downloadPath);
            $escapedCookiesJson  = escapeshellarg($cookiesJson);
            $escapedYtDlpPath    = escapeshellarg($ytDlpPath);

            $cmd = "{$escapedPython} {$escapedScript} {$escapedUrl} {$escapedDownloadPath} {$escapedCookiesJson} {$escapedYtDlpPath} 2>&1";

            Log::debug('Executing command', ['cmd' => substr($cmd, 0, 300) . '...']);

            // Change to script directory and execute
            $cwd = getcwd();
            chdir(base_path('python_worker'));

            // Set environment variables
            putenv('HOME=/tmp');
            putenv('PATH=/usr/local/bin:/usr/bin:/bin:' . getenv('PATH'));

            $output = shell_exec($cmd);

            chdir($cwd);

            Log::info('Python script completed', [
                'output_length'  => strlen($output ?? ''),
                'output_preview' => substr($output ?? '', 0, 500),
            ]);

            $outputString = trim($output ?? '');

            // Parse JSON output from Python
            $jsonOutput = null;
            $lines      = preg_split("/\r\n|\r|\n/", $outputString);

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }

                $decoded = json_decode($line, true);
                if ($decoded !== null && (isset($decoded['success']) || isset($decoded['error']))) {
                    $jsonOutput = $decoded;
                    break;
                }
            }

            if ($jsonOutput === null) {
                Log::error('Failed to parse Python output', [
                    'output' => substr($outputString, 0, 1000),
                ]);

                // Try to extract meaningful error
                if (stripos($outputString, 'login') !== false || stripos($outputString, 'cookie') !== false) {
                    return response()->json([
                        'success'    => false,
                        'error'      => 'Instagram session expired. Please try again later.',
                        'error_type' => 'login_required',
                    ], 400);
                }

                if (stripos($outputString, 'private') !== false) {
                    return response()->json([
                        'success'    => false,
                        'error'      => 'This content is from a private account.',
                        'error_type' => 'private_content',
                    ], 400);
                }

                return response()->json(
                    [
                        'success'    => false,
                        'error'      => 'Failed to process Instagram content. Please try again.',
                        'error_type' => 'parse_error',
                        'debug'      => config('app.debug') ? substr($outputString, 0, 500) : null,
                    ],
                    500,
                );
            }

            // Check for errors from Python script
            if (isset($jsonOutput['error'])) {
                Log::warning('Python script returned error', [
                    'error' => $jsonOutput['error'],
                    'type'  => $jsonOutput['error_type'] ?? 'unknown',
                    'debug' => $jsonOutput['debug'] ?? null,
                ]);

                return response()->json(
                    [
                        'success'       => false,
                        'error'         => $jsonOutput['error'],
                        'error_type'    => $jsonOutput['error_type'] ?? 'unknown',
                        'cookies_tried' => $jsonOutput['cookies_tried'] ?? 0,
                        'debug'         => config('app.debug') ? ($jsonOutput['debug'] ?? null) : null,
                    ],
                    400,
                );
            }

            // Transform file paths to download URLs
            if (isset($jsonOutput['items']) && is_array($jsonOutput['items'])) {
                foreach ($jsonOutput['items'] as &$item) {
                    // Main media file
                    if (isset($item['path'])) {
                        $filename             = basename($item['path']);
                        $item['download_url'] = route('instagram.download', [
                            'folder'   => $sessionId,
                            'filename' => $filename,
                        ]);
                        unset($item['path']);
                    }

                    // Thumbnail URL handling
                    $thumbSource = $item['thumbnail_file'] ?? ($item['thumbnail'] ?? null);

                    if ($thumbSource) {
                        $isLocalPath = str_contains($thumbSource, ':\\') ||
                        str_starts_with($thumbSource, '/') ||
                        str_contains($thumbSource, DIRECTORY_SEPARATOR);

                        if ($isLocalPath && file_exists($thumbSource)) {
                            $thumbFilename         = basename($thumbSource);
                            $item['thumbnail_url'] = route('instagram.download', [
                                'folder'   => $sessionId,
                                'filename' => $thumbFilename,
                            ]);
                        } elseif (filter_var($thumbSource, FILTER_VALIDATE_URL)) {
                            $item['thumbnail_url'] = $thumbSource;
                        }
                    }

                    unset($item['thumbnail_file']);
                }
            }

            // Add download all URL
            $jsonOutput['download_all_url'] = route('instagram.download.all', ['folder' => $sessionId]);
            $jsonOutput['session_id']       = $sessionId;
            $jsonOutput['success']          = true;

            Log::info('Instagram fetch successful', [
                'session_id'  => $sessionId,
                'type'        => $jsonOutput['type'] ?? 'unknown',
                'items_count' => count($jsonOutput['items'] ?? []),
            ]);

            return response()->json($jsonOutput);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Instagram fetch exception', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json(
                [
                    'success'    => false,
                    'error'      => 'An unexpected error occurred. Please try again.',
                    'error_type' => 'exception',
                    'debug'      => config('app.debug') ? $e->getMessage() : null,
                ],
                500,
            );
        }
    }

    /**
     * Download a single file
     */
    public function download(string $folder, string $filename)
    {
        // Sanitize inputs to prevent directory traversal
        $folder   = basename($folder);
        $filename = basename($filename);

        $filePath = storage_path('app/downloads/' . $folder . '/' . $filename);

        if (! file_exists($filePath)) {
            Log::warning('Download file not found', ['path' => $filePath]);
            abort(404, 'File not found');
        }

        // Determine MIME type
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimeTypes = [
            'mp4'  => 'video/mp4',
            'webm' => 'video/webm',
            'mkv'  => 'video/x-matroska',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'webp' => 'image/webp',
            'gif'  => 'image/gif',
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
        // Sanitize folder name
        $folder     = basename($folder);
        $folderPath = storage_path('app/downloads/' . $folder);

        if (! is_dir($folderPath)) {
            Log::warning('Download folder not found', ['path' => $folderPath]);
            abort(404, 'Download folder not found');
        }

        $files = glob($folderPath . '/*');

        if (empty($files)) {
            abort(404, 'No files found');
        }

        // Create ZIP file
        $zipFileName = 'instagram_download_' . substr($folder, 0, 8) . '.zip';
        $zipPath     = storage_path('app/downloads/' . $zipFileName);

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