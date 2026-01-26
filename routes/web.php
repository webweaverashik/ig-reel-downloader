<?php

use App\Http\Controllers\InstagramDownloaderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| IGReelDownloader.net - Instagram Downloader Routes
|
*/

// Home Page - Landing Page
Route::get('/', [InstagramDownloaderController::class, 'home'])->name('home');

// Instagram Downloader Pages
Route::get('/instagram-reels-downloader', [InstagramDownloaderController::class, 'reels'])->name('instagram.reels');
Route::get('/instagram-video-downloader', [InstagramDownloaderController::class, 'video'])->name('instagram.video');
Route::get('/instagram-photo-downloader', [InstagramDownloaderController::class, 'photo'])->name('instagram.photo');
Route::get('/instagram-story-downloader', [InstagramDownloaderController::class, 'story'])->name('instagram.story');
Route::get('/instagram-carousel-downloader', [InstagramDownloaderController::class, 'carousel'])->name('instagram.carousel');

// API Endpoints
Route::post('/api/instagram/fetch', [InstagramDownloaderController::class, 'fetch'])->name('instagram.fetch');

Route::get('/api/instagram/download/{folder}/{filename}', [InstagramDownloaderController::class, 'download'])
    ->name('instagram.download')
    ->where('filename', '.*');

Route::get('/api/instagram/download-all/{folder}', [InstagramDownloaderController::class, 'downloadAll'])->name('instagram.download.all');

// Cookie Status Check (for debugging)
Route::get('/api/instagram/cookie-status', [InstagramDownloaderController::class, 'cookieStatus'])->name('instagram.cookie.status');

// Quick test endpoint - runs Python directly to verify execution
Route::get('/api/instagram/quick-test', function () {
    clearstatcache();
    
    $python = env('PYTHON_PATH', '/usr/bin/python3');
    $ytdlp = env('YTDLP_PATH', '/usr/local/bin/yt-dlp');
    $script = realpath(base_path('python_worker/instagram_fetch.py'));
    $cookiesDir = realpath(base_path('python_worker/cookies'));
    
    $results = [
        'timestamp' => now()->toIso8601String(),
        'paths' => [
            'python' => $python,
            'ytdlp' => $ytdlp,
            'ytdlp_is_dir' => is_dir($ytdlp),
            'ytdlp_is_file' => is_file($ytdlp),
            'script' => $script,
            'cookies_dir' => $cookiesDir,
        ],
        'tests' => [],
    ];
    
    // Test 1: Python works
    $cmd = escapeshellarg($python) . " --version 2>&1";
    $results['tests']['python'] = [
        'command' => $cmd,
        'output' => trim(shell_exec($cmd)),
    ];
    
    // Test 2: yt-dlp as module (recommended approach)
    $cmd = escapeshellarg($python) . " -m yt_dlp --version 2>&1";
    $results['tests']['ytdlp_module'] = [
        'command' => $cmd,
        'output' => trim(shell_exec($cmd)),
    ];
    
    // Test 3: yt-dlp binary (may fail if it's a directory)
    $cmd = escapeshellarg($ytdlp) . " --version 2>&1";
    $results['tests']['ytdlp_binary'] = [
        'command' => $cmd,
        'output' => trim(shell_exec($cmd)),
    ];
    
    // Test 4: Cookie files
    $cookies = [];
    if ($cookiesDir && is_dir($cookiesDir)) {
        foreach (glob($cookiesDir . '/*.txt') as $file) {
            clearstatcache(true, $file);
            $cookies[] = [
                'name' => basename($file),
                'path' => realpath($file),
                'size' => filesize($file),
                'readable' => is_readable($file),
            ];
        }
    }
    $results['tests']['cookies'] = $cookies;
    
    // Test 5: Run the Python script with a test URL
    $testUrl = "https://www.instagram.com/reel/DTqYuzMkvNO/";
    $testDownloadPath = storage_path('app/downloads/quick-test-' . time());
    @mkdir($testDownloadPath, 0755, true);
    
    $cookieFiles = array_map(function($c) { return $c['path']; }, array_filter($cookies, function($c) { return $c['readable'] && $c['size'] > 50; }));
    $cookiesJson = json_encode(array_values($cookieFiles));
    
    // Pass empty string for yt-dlp path - let Python figure it out
    $cmd = sprintf(
        'cd %s && HOME=/tmp %s %s %s %s %s "" 2>&1',
        escapeshellarg(dirname($script)),
        escapeshellarg($python),
        escapeshellarg($script),
        escapeshellarg($testUrl),
        escapeshellarg($testDownloadPath),
        escapeshellarg($cookiesJson)
    );
    
    $output = shell_exec($cmd);
    
    $results['tests']['script_execution'] = [
        'command' => $cmd,
        'output_length' => strlen($output ?? ''),
        'output' => substr($output ?? '', 0, 3000),
    ];
    
    // Parse JSON from output
    $jsonOutput = null;
    foreach (preg_split("/\r\n|\r|\n/", $output ?? '') as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        $decoded = json_decode($line, true);
        if ($decoded !== null && (isset($decoded['success']) || isset($decoded['error']))) {
            $jsonOutput = $decoded;
            break;
        }
    }
    
    $results['tests']['parsed_json'] = $jsonOutput;
    
    // Check if files were downloaded
    $downloadedFiles = [];
    if (is_dir($testDownloadPath)) {
        foreach (glob($testDownloadPath . '/*') as $file) {
            $downloadedFiles[] = [
                'name' => basename($file),
                'size' => filesize($file),
            ];
        }
    }
    $results['tests']['downloaded_files'] = $downloadedFiles;
    
    // Cleanup
    if (is_dir($testDownloadPath)) {
        array_map('unlink', glob($testDownloadPath . '/*'));
        @rmdir($testDownloadPath);
    }
    
    return response()->json($results, 200, [], JSON_PRETTY_PRINT);
});