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
            'reels'    => [
                'title'            => 'Instagram Reels Downloader - Download Reels in HD | IGReelDownloader.net',
                'meta_description' => 'Download Instagram Reels in HD quality. Free, fast, and no login required. Save your favorite Reels instantly.',
                'hero_title'       => 'Download Instagram',
                'hero_highlight'   => 'Reels in HD',
                'subtitle'         => 'Download any Instagram Reels in HD quality. Fast, free, and no login required. Save your favorite Reels instantly.',
                'badge'            => 'Free & Unlimited Reels Downloads',
                'placeholder'      => 'Paste Instagram Reel URL here...',
                'formats'          => ['Reels', 'HD Quality', 'MP4 Format', 'No Watermark', 'Fast Download'],
                'faqs'             => [['q' => 'How do I download Instagram Reels?', 'a' => 'Simply copy the Reel URL from Instagram, paste it in the input field above, and click Download. Your Reel will be ready in seconds.'], ['q' => 'Is downloading Reels free?', 'a' => 'Yes, our Instagram Reels downloader is completely free with no hidden charges or subscription fees.'], ['q' => 'What quality can I download Reels in?', 'a' => 'We always provide the highest quality available, typically 1080p HD or the original upload quality.'], ['q' => 'Do I need to login to download Reels?', 'a' => 'No, you don\'t need to login or create an account. Just paste the URL and download instantly.'], ['q' => 'Can I download Reels on mobile?', 'a' => 'Yes! Our downloader works perfectly on all devices including smartphones and tablets.']],
            ],
            'video'    => [
                'title'            => 'Instagram Video Downloader - Download IG Videos in HD | IGReelDownloader.net',
                'meta_description' => 'Download Instagram Videos in HD quality. Free, fast, and works on all devices. Save IGTV and video posts instantly.',
                'hero_title'       => 'Download Instagram',
                'hero_highlight'   => 'Videos in HD',
                'subtitle'         => 'Download any Instagram video in original HD quality. Fast, free, and works on all devices. Save IGTV and video posts instantly.',
                'badge'            => 'Free HD Video Downloads',
                'placeholder'      => 'Paste Instagram Video URL here...',
                'formats'          => ['IGTV', 'Video Posts', 'HD 1080p', 'MP4 Format', 'Original Quality'],
                'faqs'             => [['q' => 'How do I download Instagram videos?', 'a' => 'Copy the video URL from Instagram, paste it above, and click Download. We support all Instagram video formats including IGTV.'], ['q' => 'What video formats are supported?', 'a' => 'We support all Instagram video types: regular video posts, IGTV, and video content from carousel posts.'], ['q' => 'Is the video quality preserved?', 'a' => 'Yes, we always download videos in the highest available quality, up to 1080p HD.'], ['q' => 'Can I download private account videos?', 'a' => 'No, only public videos can be downloaded. Private account content requires the owner\'s permission.'], ['q' => 'Are there any download limits?', 'a' => 'No limits! Download as many videos as you want, completely free.']],
            ],
            'photo'    => [
                'title'            => 'Instagram Photo Downloader - Download IG Photos in HD | IGReelDownloader.net',
                'meta_description' => 'Download Instagram photos in full resolution. Save profile pictures, posts, and images in original quality instantly.',
                'hero_title'       => 'Download Instagram',
                'hero_highlight'   => 'Photos in HD',
                'subtitle'         => 'Download Instagram photos in full resolution. Save profile pictures, posts, and images in original quality instantly.',
                'badge'            => 'Free HD Photo Downloads',
                'placeholder'      => 'Paste Instagram Photo URL here...',
                'formats'          => ['Photos', 'Profile Pictures', 'Full Resolution', 'JPG/PNG', 'Original Size'],
                'faqs'             => [['q' => 'How do I download Instagram photos?', 'a' => 'Copy the photo post URL from Instagram, paste it in the field above, and click Download to save it in full resolution.'], ['q' => 'What image quality will I get?', 'a' => 'We download photos in their original full resolution, exactly as uploaded by the creator.'], ['q' => 'Can I download multiple photos from a post?', 'a' => 'Yes! For carousel posts with multiple photos, we provide a "Download All" option to save everything at once.'], ['q' => 'What format are photos saved in?', 'a' => 'Photos are saved in their original format, typically JPG or PNG, maintaining full quality.'], ['q' => 'Can I download profile pictures?', 'a' => 'Yes, you can download profile pictures in full resolution using our tool.']],
            ],
            'story'    => [
                'title'            => 'Instagram Story Downloader - Download IG Stories | IGReelDownloader.net',
                'meta_description' => 'Download Instagram Stories before they disappear. Save photos and videos from stories in HD quality anonymously.',
                'hero_title'       => 'Download Instagram',
                'hero_highlight'   => 'Stories Anonymously',
                'subtitle'         => 'Download Instagram Stories before they disappear. Save photos and videos from stories in HD quality anonymously.',
                'badge'            => 'Anonymous Story Downloads',
                'placeholder'      => 'Paste Instagram Story URL here...',
                'formats'          => ['Stories', 'Highlights', 'Photos', 'Videos', 'Anonymous'],
                'faqs'             => [['q' => 'How do I download Instagram Stories?', 'a' => 'Copy the story URL from Instagram (or the story highlight URL), paste it above, and click Download.'], ['q' => 'Will the user know I downloaded their story?', 'a' => 'Our tool downloads stories anonymously. The user won\'t be notified that you saved their content.'], ['q' => 'Can I download story highlights?', 'a' => 'Yes! You can download both regular stories and story highlights using our tool.'], ['q' => 'What if the story has expired?', 'a' => 'Unfortunately, expired stories cannot be downloaded. You need to save them before they disappear after 24 hours.'], ['q' => 'Are story videos and photos supported?', 'a' => 'Yes, we support both photo and video stories in their original quality.']],
            ],
            'carousel' => [
                'title'            => 'Instagram Carousel Downloader - Download Multiple Photos/Videos | IGReelDownloader.net',
                'meta_description' => 'Download all photos and videos from Instagram carousel posts at once. Save multiple items in HD quality with one click.',
                'hero_title'       => 'Download Instagram',
                'hero_highlight'   => 'Carousel Posts',
                'subtitle'         => 'Download all photos and videos from Instagram carousel posts at once. Save multiple items in HD quality with one click.',
                'badge'            => 'Bulk Carousel Downloads',
                'placeholder'      => 'Paste Instagram Carousel URL here...',
                'formats'          => ['Multiple Photos', 'Multiple Videos', 'Bulk Download', 'ZIP Archive', 'HD Quality'],
                'faqs'             => [['q' => 'What is a carousel post?', 'a' => 'A carousel is an Instagram post containing multiple photos or videos that you can swipe through. We can download all items at once.'], ['q' => 'How many items can I download at once?', 'a' => 'Instagram allows up to 10 items per carousel, and we can download all of them in a single click.'], ['q' => 'Will I get all items from the carousel?', 'a' => 'Yes! We detect and download every photo and video in the carousel, providing them in a convenient ZIP file.'], ['q' => 'What if the carousel has both photos and videos?', 'a' => 'No problem! We handle mixed carousel posts and download all content types in their original quality.'], ['q' => 'Can I download individual items?', 'a' => 'Yes, you can choose to download items individually or use "Download All" to get everything as a ZIP.']],
            ],
        ];
    }

    /**
     * Python executable path from .env
     */
    private function getPythonPath(): string
    {
        return config('services.python.path', 'python3');
    }

    /**
     * Get all available cookie files
     */
    private function getCookieFiles(): array
    {
        $cookiesDir = base_path('python_worker/cookies');

        if (! is_dir($cookiesDir)) {
            return [];
        }

        $files = glob($cookiesDir . '/*.txt');

        // Sort by modification time (newest first) or by name
        usort($files, function ($a, $b) {
            // Prefer files with "active" or numbered prefixes
            $aName = basename($a);
            $bName = basename($b);

            // instagram.txt is the primary cookie
            if ($aName === 'instagram.txt') {
                return -1;
            }
            if ($bName === 'instagram.txt') {
                return 1;
            }

            // Then sort by modification time
            return filemtime($b) - filemtime($a);
        });

        return $files;
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

            $status[] = [
                'name'     => $name,
                'size'     => $size,
                'modified' => $modified,
                'valid'    => $size > 100, // Basic check
            ];
        }

        return response()->json([
            'total_cookies' => count($cookies),
            'cookies'       => $status,
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
                'url' => ['required', 'url', 'regex:/^https?:\/\/(www\.)?instagram\.com\/(p|reel|reels|tv|stories)\/[\w\-]+/i'],
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

            // Get all available cookie files
            $cookieFiles = $this->getCookieFiles();

            if (empty($cookieFiles)) {
                return response()->json(
                    [
                        'success'    => false,
                        'error'      => 'No Instagram cookies configured. Please add cookie files to python_worker/cookies/',
                        'error_type' => 'cookies_missing',
                    ],
                    400,
                );
            }

            // Path to Python script
            $pythonScript = base_path('python_worker/instagram_fetch.py');

            // yt-dlp binary path
            $ytDlpPath     = config('services.ytdlp.path');
            $ytDlpEnv      = env('YTDLP_PATH');
            $ytDlpResolved = (string) ($ytDlpPath ?: ($ytDlpEnv ?: 'yt-dlp'));

            $python = $this->getPythonPath();

            // Prepare cookies list as JSON
            $cookiesJson = json_encode($cookieFiles);

            Log::info('Starting Instagram fetch with multiple cookies', [
                'url'          => $url,
                'session_id'   => $sessionId,
                'cookie_count' => count($cookieFiles),
                'python'       => $python,
                'yt_dlp'       => $ytDlpResolved,
            ]);

            // Build command arguments
            // Args: <url> <download_path> <cookies_json> <yt_dlp_path>
            $args = [$python, $pythonScript, $url, $downloadPath, $cookiesJson, $ytDlpResolved];

            $descriptorSpec = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];

            // Set a sane HOME on Linux
            $env = null;
            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                $env = array_merge($_ENV, [
                    'HOME' => '/tmp',
                ]);
            }

            $process = proc_open($args, $descriptorSpec, $pipes, null, $env);

            if (! is_resource($process)) {
                return response()->json(
                    [
                        'success'    => false,
                        'error'      => 'Failed to start Python worker process.',
                        'error_type' => 'proc_open_failed',
                    ],
                    500,
                );
            }

            fclose($pipes[0]);
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);

            $returnCode = proc_close($process);

            $outputString = trim($stdout . "\n" . $stderr);
            Log::info('Python script output', ['output' => $outputString, 'return_code' => $returnCode]);

            // Parse JSON output from Python
            $jsonOutput = null;
            foreach (preg_split("/\r\n|\r|\n/", $outputString) as $line) {
                $decoded = json_decode(trim($line), true);
                if (($decoded !== null && isset($decoded['success'])) || isset($decoded['error'])) {
                    $jsonOutput = $decoded;
                    break;
                }
            }

            if ($jsonOutput === null) {
                Log::error('Failed to parse Python output', ['output' => $outputString]);
                return response()->json(
                    [
                        'success'    => false,
                        'error'      => 'Failed to process Instagram content. Please try again.',
                        'error_type' => 'parse_error',
                        'debug'      => config('app.debug') ? $outputString : null,
                    ],
                    500,
                );
            }

            // Check for errors from Python script
            if (isset($jsonOutput['error'])) {
                return response()->json(
                    [
                        'success'       => false,
                        'error'         => $jsonOutput['error'],
                        'error_type'    => $jsonOutput['error_type'] ?? 'unknown',
                        'cookies_tried' => $jsonOutput['cookies_tried'] ?? 0,
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
                        $isLocalPath = str_contains($thumbSource, ':\\') || str_starts_with($thumbSource, '/') || str_contains($thumbSource, DIRECTORY_SEPARATOR);

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

            return response()->json($jsonOutput);
        } catch (\Exception $e) {
            Log::error('Instagram fetch error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
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
