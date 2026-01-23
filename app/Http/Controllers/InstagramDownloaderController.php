<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;

class InstagramDownloaderController extends Controller
{
    /**
     * Python executable path
     */
    private function getPythonPath(): string
    {
        return config('services.python.path', 'python3');
    }

    /**
     * Show downloader page
     */
    public function index()
    {
        return view('instagram-downloader');
    }

    /**
     * Fetch Instagram media via Python worker
     */
    public function fetch(Request $request)
    {
        $request->validate([
            'url' => [
                'required',
                'url',
                'regex:/^https?:\/\/(www\.)?instagram\.com\/(p|reel|reels|tv|stories)\/[\w\-]+/i'
            ]
        ]);

        $url = $request->input('url');

        try {
            $sessionId = (string) Str::uuid();
            $downloadPath = storage_path("app/downloads/{$sessionId}");

            if (!is_dir($downloadPath)) {
                mkdir($downloadPath, 0755, true);
            }

            $pythonScript = base_path('python_worker/instagram_fetch.py');
            $cookiesPath  = base_path('python_worker/cookies/instagram.txt');

            if (!file_exists($cookiesPath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Instagram cookies not configured.',
                    'error_type' => 'cookies_missing'
                ], 400);
            }

            $python = escapeshellcmd($this->getPythonPath());

            $ytDlp = config('services.ytdlp.path')
                ?: env('YTDLP_PATH', 'yt-dlp');

            $command = sprintf(
                '%s %s %s %s %s %s 2>&1',
                $python,
                escapeshellarg($pythonScript),
                escapeshellarg($url),
                escapeshellarg($downloadPath),
                escapeshellarg($cookiesPath),
                escapeshellarg($ytDlp)
            );

            Log::info('Executing yt-dlp command', ['cmd' => $command]);

            $output = [];
            $code = 0;
            exec($command, $output, $code);

            $rawOutput = implode("\n", $output);

            Log::info('Python output', [
                'return_code' => $code,
                'output' => $rawOutput
            ]);

            $json = null;
            foreach ($output as $line) {
                $decoded = json_decode($line, true);
                if (is_array($decoded)) {
                    $json = $decoded;
                    break;
                }
            }

            if (!$json) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to parse response from downloader.',
                    'debug' => config('app.debug') ? $rawOutput : null
                ], 500);
            }

            if (!empty($json['error'])) {
                return response()->json([
                    'success' => false,
                    'error' => $json['error'],
                    'error_type' => $json['error_type'] ?? 'unknown'
                ], 400);
            }

            if (!empty($json['items'])) {
                foreach ($json['items'] as &$item) {

                    if (!empty($item['path'])) {
                        $filename = basename($item['path']);
                        $item['download_url'] = route('instagram.download', [
                            'folder' => $sessionId,
                            'filename' => $filename
                        ]);
                        unset($item['path']);
                    }

                    if (!empty($item['thumbnail'])) {
                        $thumb = $item['thumbnail'];

                        $isLocal =
                            str_contains($thumb, ':\\') ||     // Windows
                            str_starts_with($thumb, '/') ||    // Linux
                            str_contains($thumb, DIRECTORY_SEPARATOR);

                        if ($isLocal) {
                            $item['thumbnail_url'] = route('instagram.download', [
                                'folder' => $sessionId,
                                'filename' => basename($thumb)
                            ]);
                        } else {
                            $item['thumbnail_url'] = $thumb;
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'session_id' => $sessionId,
                'items' => $json['items'] ?? [],
                'download_all_url' => route('instagram.download.all', [
                    'folder' => $sessionId
                ])
            ]);

        } catch (\Throwable $e) {
            Log::error('Downloader exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Unexpected server error.',
                'debug' => config('app.debug') ? $e->getMessage() : null
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

        $path = storage_path("app/downloads/{$folder}/{$filename}");

        abort_if(!file_exists($path), 404);

        return response()->download($path, $filename);
    }

    /**
     * Download all files as ZIP
     */
    public function downloadAll(string $folder)
    {
        $folder = basename($folder);
        $folderPath = storage_path("app/downloads/{$folder}");

        abort_if(!is_dir($folderPath), 404);

        $zipName = "instagram_download_{$folder}.zip";
        $zipPath = storage_path("app/downloads/{$zipName}");

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach (glob($folderPath . '/*') as $file) {
            if (is_file($file)) {
                $zip->addFile($file, basename($file));
            }
        }

        $zip->close();

        return response()->download($zipPath, $zipName);
    }
}
