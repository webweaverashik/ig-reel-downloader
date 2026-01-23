<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;

class InstagramDownloaderController extends Controller
{
    /**
     * Python executable path - adjust based on environment
     */
    private function getPythonPath(): string
    {
        // Windows (Laragon) typically uses 'python'
        // Ubuntu VPS might use 'python3'
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return 'python';
        }
        return 'python3';
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
            'url' => ['required', 'url', 'regex:/^https?:\/\/(www\.)?instagram\.com\/(p|reel|reels|tv|stories)\/[\w\-]+/i'],
        ], [
            'url.required' => 'Please enter an Instagram URL.',
            'url.url'      => 'Please enter a valid URL.',
            'url.regex'    => 'Please enter a valid Instagram URL (post, reel, video, or story).',
        ]);

        $url = $request->input('url');

        try {
            // Generate unique folder for this download session
            $sessionId    = Str::uuid()->toString();
            $downloadPath = storage_path('app/downloads/' . $sessionId);

            // Create download directory
            if (! file_exists($downloadPath)) {
                mkdir($downloadPath, 0755, true);
            }

            // Path to Python script
            $pythonScript = base_path('python_worker/instagram_fetch.py');
            $cookiesPath  = base_path('python_worker/cookies/instagram.txt');

            // Check if cookies file exists
            if (! file_exists($cookiesPath)) {
                return response()->json([
                    'success'    => false,
                    'error'      => 'Instagram cookies not configured. Please add cookies file.',
                    'error_type' => 'cookies_missing',
                ], 400);
            }

            // Build the command
            $python  = $this->getPythonPath();
            $command = sprintf(
                '%s %s %s %s %s 2>&1',
                escapeshellcmd($python),
                escapeshellarg($pythonScript),
                escapeshellarg($url),
                escapeshellarg($downloadPath),
                escapeshellarg($cookiesPath)
            );

            Log::info('Executing Instagram fetch command', ['command' => $command]);

            // Execute Python script
            $output     = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            $outputString = implode("\n", $output);
            Log::info('Python script output', ['output' => $outputString, 'return_code' => $returnCode]);

            // Parse JSON output from Python
            $jsonOutput = null;
            foreach ($output as $line) {
                $decoded = json_decode($line, true);
                if ($decoded !== null) {
                    $jsonOutput = $decoded;
                    break;
                }
            }

            if ($jsonOutput === null) {
                Log::error('Failed to parse Python output', ['output' => $outputString]);
                return response()->json([
                    'success'    => false,
                    'error'      => 'Failed to process Instagram content. Please try again.',
                    'error_type' => 'parse_error',
                    'debug'      => config('app.debug') ? $outputString : null,
                ], 500);
            }

            // Check for errors from Python script
            if (isset($jsonOutput['error'])) {
                return response()->json([
                    'success'    => false,
                    'error'      => $jsonOutput['error'],
                    'error_type' => $jsonOutput['error_type'] ?? 'unknown',
                ], 400);
            }

            // Transform file paths to download URLs
            if (isset($jsonOutput['items']) && is_array($jsonOutput['items'])) {
                foreach ($jsonOutput['items'] as &$item) {
                    if (isset($item['path'])) {
                        $filename             = basename($item['path']);
                        $item['download_url'] = route('instagram.download', [
                            'folder'   => $sessionId,
                            'filename' => $filename,
                        ]);
                        // Keep original path for reference but don't expose it
                        unset($item['path']);
                    }
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

            return response()->json([
                'success'    => false,
                'error'      => 'An unexpected error occurred. Please try again.',
                'error_type' => 'exception',
                'debug'      => config('app.debug') ? $e->getMessage() : null,
            ], 500);
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
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
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
        $zipFileName = 'instagram_download_' . $folder . '.zip';
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

        return response()->download($zipPath, $zipFileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(false);
    }
}
