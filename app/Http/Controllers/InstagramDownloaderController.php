<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class InstagramDownloaderController extends Controller
{
    /**
     * Python executable path from .env
     */
    private function getPythonPath(): string
    {
        return config('services.python.path', 'python3');
    }

    /**
     * Display the Instagram Downloader page
     */
    public function index()
    {
        return view('instagram-downloader');
    }

    /**
     * Fetch Instagram content via Python worker
     */
    public function fetch(Request $request)
    {
        // Validate the URL
        $request->validate([
            'url' => ['required', 'url', 'regex:/^https?:\/\/(www\.)?instagram\.com\/(p|reel|reels|tv|stories)\/[\w\-]+/i']
        ], [
            'url.required' => 'Please enter an Instagram URL.',
            'url.url' => 'Please enter a valid URL.',
            'url.regex' => 'Please enter a valid Instagram URL (post, reel, video, or story).'
        ]);

        $url = $request->input('url');
        
        try {
            // Generate unique folder for this download session
            $sessionId = Str::uuid()->toString();
            $downloadPath = storage_path('app/downloads/' . $sessionId);
            
            // Create download directory
            if (!file_exists($downloadPath)) {
                mkdir($downloadPath, 0755, true);
            }

            // Path to Python script
            $pythonScript = base_path('python_worker/instagram_fetch.py');
            $cookiesPath = base_path('python_worker/cookies/instagram.txt');

            // yt-dlp binary path (optional). If not set, Python will try to use PATH.
            // IMPORTANT (Windows/Laragon): PHP-FPM/Apache may not inherit your terminal PATH, so set YTDLP_PATH in .env.
            // NOTE: In some setups config('services.ytdlp.path') can be null if config cache is stale or .env isn't loaded.
            // We therefore also fall back to env('YTDLP_PATH') to avoid silently passing "yt-dlp".
            $ytDlpPath = config('services.ytdlp.path');
            $ytDlpEnv = env('YTDLP_PATH');
            $ytDlpResolved = (string) ($ytDlpPath ?: ($ytDlpEnv ?: 'yt-dlp'));

            Log::info('Resolved binary paths', [
                'python' => $this->getPythonPath(),
                'ytdlp_config' => $ytDlpPath,
                'ytdlp_env' => $ytDlpEnv,
                'yt_dlp_resolved' => $ytDlpResolved,
            ]);

            // Check if cookies file exists
            if (!file_exists($cookiesPath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Instagram cookies not configured. Please add cookies file.',
                    'error_type' => 'cookies_missing'
                ], 400);
            }

            // Build the command
            $python = $this->getPythonPath();

            // Args: <url> <download_path> <cookies_path> <yt_dlp_path>
            // IMPORTANT (Windows): do not rely on PHP/Apache cmd.exe parsing with nested quotes.
            // Use proc_open with an argument array to avoid issues like:
            // "'C:\Users\Ashikur' is not recognized..."
            $args = [
                $python,
                $pythonScript,
                $url,
                $downloadPath,
                $cookiesPath,
                $ytDlpResolved,
            ];

            Log::info('Executing Instagram fetch (proc_open)', [
                'args' => $args,
                'python' => $python,
                'yt_dlp_resolved' => $ytDlpResolved,
            ]);

            $descriptorSpec = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];

            // Set a sane HOME on Linux so yt-dlp can write cache/config safely
            $env = null;
            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                $env = array_merge($_ENV, [
                    'HOME' => '/tmp',
                ]);
            }

            $process = proc_open($args, $descriptorSpec, $pipes, null, $env);

            if (!is_resource($process)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to start Python worker process.',
                    'error_type' => 'proc_open_failed'
                ], 500);
            }

            fclose($pipes[0]);
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);

            $returnCode = proc_close($process);

            $outputString = trim($stdout . "\n" . $stderr);
            Log::info('Python script output', ['output' => $outputString, 'return_code' => $returnCode]);

            // Parse JSON output from Python (it prints one JSON object)
            $jsonOutput = json_decode($stdout, true);
            if ($jsonOutput === null) {
                // If stdout isn't JSON, attempt to find JSON line in combined output
                foreach (preg_split("/\r\n|\r|\n/", $outputString) as $line) {
                    $decoded = json_decode(trim($line), true);
                    if ($decoded !== null) {
                        $jsonOutput = $decoded;
                        break;
                    }
                }
            }

            if ($jsonOutput === null) {
                Log::error('Failed to parse Python output', ['output' => $outputString]);
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to process Instagram content. Please try again.',
                    'error_type' => 'parse_error',
                    'debug' => config('app.debug') ? $outputString : null
                ], 500);
            }

            // Check for errors from Python script
            if (isset($jsonOutput['error'])) {
                return response()->json([
                    'success' => false,
                    'error' => $jsonOutput['error'],
                    'error_type' => $jsonOutput['error_type'] ?? 'unknown'
                ], 400);
            }

            // Transform file paths to download URLs
            if (isset($jsonOutput['items']) && is_array($jsonOutput['items'])) {
                foreach ($jsonOutput['items'] as &$item) {
                    // Main media file
                    if (isset($item['path'])) {
                        $filename = basename($item['path']);
                        $item['download_url'] = route('instagram.download', [
                            'folder' => $sessionId,
                            'filename' => $filename
                        ]);
                        unset($item['path']);
                    }

                    // Thumbnail URL
                    // Priority:
                    // 1) item['thumbnail_file'] (local file path created by yt-dlp --write-thumbnail)
                    // 2) item['thumbnail'] (remote URL from metadata)
                    $thumbSource = null;
                    if (isset($item['thumbnail_file']) && is_string($item['thumbnail_file']) && $item['thumbnail_file'] !== '') {
                        $thumbSource = $item['thumbnail_file'];
                    } elseif (isset($item['thumbnail']) && is_string($item['thumbnail']) && $item['thumbnail'] !== '') {
                        $thumbSource = $item['thumbnail'];
                    }

                    if ($thumbSource) {
                        $isLocalPath = str_contains($thumbSource, ':\\') || str_starts_with($thumbSource, '/') || str_contains($thumbSource, DIRECTORY_SEPARATOR);

                        if ($isLocalPath) {
                            $thumbFilename = basename($thumbSource);
                            $item['thumbnail_url'] = route('instagram.download', [
                                'folder' => $sessionId,
                                'filename' => $thumbFilename
                            ]);
                        } else {
                            $item['thumbnail_url'] = $thumbSource;
                        }
                    }

                    // Never expose local filesystem paths to the browser
                    unset($item['thumbnail_file']);
                }
            }

            // Add download all URL
            $jsonOutput['download_all_url'] = route('instagram.download.all', ['folder' => $sessionId]);
            $jsonOutput['session_id'] = $sessionId;
            $jsonOutput['success'] = true;

            return response()->json($jsonOutput);

        } catch (\Exception $e) {
            Log::error('Instagram fetch error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred. Please try again.',
                'error_type' => 'exception',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Download a single file
     */
    public function download(string $folder, string $filename)
    {
        // Sanitize inputs to prevent directory traversal
        $folder = basename($folder);
        $filename = basename($filename);
        
        $filePath = storage_path('app/downloads/' . $folder . '/' . $filename);

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        // Determine MIME type
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimeTypes = [
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
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
        $folder = basename($folder);
        $folderPath = storage_path('app/downloads/' . $folder);

        if (!is_dir($folderPath)) {
            abort(404, 'Download folder not found');
        }

        $files = glob($folderPath . '/*');
        
        if (empty($files)) {
            abort(404, 'No files found');
        }

        // Create ZIP file
        $zipFileName = 'instagram_download_' . $folder . '.zip';
        $zipPath = storage_path('app/downloads/' . $zipFileName);

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

        return response()->download($zipPath, $zipFileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(false);
    }
}