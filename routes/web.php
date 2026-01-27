<?php

use App\Http\Controllers\InstagramDownloaderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| IGReelDownloader.net - Instagram Downloader Routes
|
*/

// Sitemap
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Home Page - Landing Page
Route::get('/', [InstagramDownloaderController::class, 'home'])->name('home');

// Instagram Downloader Pages
Route::get('/instagram-reels-downloader', [InstagramDownloaderController::class, 'reels'])->name('instagram.reels');
Route::get('/instagram-video-downloader', [InstagramDownloaderController::class, 'video'])->name('instagram.video');
Route::get('/instagram-photo-downloader', [InstagramDownloaderController::class, 'photo'])->name('instagram.photo');
Route::get('/instagram-story-downloader', [InstagramDownloaderController::class, 'story'])->name('instagram.story');
Route::get('/instagram-carousel-downloader', [InstagramDownloaderController::class, 'carousel'])->name('instagram.carousel');

// Static Pages
Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/terms-of-service', [PageController::class, 'terms'])->name('terms');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');

// API Endpoints
Route::post('/api/instagram/fetch', [InstagramDownloaderController::class, 'fetch'])->name('instagram.fetch');
Route::get('/api/instagram/download/{folder}/{filename}', [InstagramDownloaderController::class, 'download'])
    ->name('instagram.download')
    ->where('filename', '.*');
Route::get('/api/instagram/download-all/{folder}', [InstagramDownloaderController::class, 'downloadAll'])->name('instagram.download.all');

// Contact Form Submission
Route::post('/api/contact', [PageController::class, 'submitContact'])->name('contact.submit');

// Cookie Status Check (for debugging)
Route::get('/api/instagram/cookie-status', [InstagramDownloaderController::class, 'cookieStatus'])->name('instagram.cookie.status');

// Quick test endpoint - runs Python directly to verify execution
Route::get('/api/instagram/quick-test', function () {
    clearstatcache();

    $python     = env('PYTHON_PATH', '/usr/bin/python3');
    $script     = realpath(base_path('python_worker/instagram_fetch.py'));
    $scriptDir  = dirname($script);
    $cookiesDir = realpath(base_path('python_worker/cookies'));

    $results = [
        'timestamp' => now()->toIso8601String(),
        'paths'     => [
            'python'      => $python,
            'script'      => $script,
            'script_dir'  => $scriptDir,
            'cookies_dir' => $cookiesDir,
        ],
        'tests'     => [],
    ];

    // Test 1: Python works
    $cmd                        = escapeshellarg($python) . " --version 2>&1";
    $results['tests']['python'] = [
        'command' => $cmd,
        'output'  => trim(shell_exec($cmd)),
    ];

    // Test 2: yt-dlp command (with proper PATH)
    $cmd                       = "cd " . escapeshellarg($scriptDir) . " && HOME=/tmp PATH=/usr/local/bin:/usr/bin:\$PATH yt-dlp --version 2>&1";
    $ytdlpOutput               = trim(shell_exec($cmd));
    $results['tests']['ytdlp'] = [
        'command' => $cmd,
        'output'  => $ytdlpOutput,
        'works'   => preg_match('/^\d{4}\.\d{2}\.\d{2}/', $ytdlpOutput) === 1,
    ];

    // Test 3: Cookie files
    $cookies = [];
    if ($cookiesDir && is_dir($cookiesDir)) {
        foreach (glob($cookiesDir . '/*.txt') as $file) {
            clearstatcache(true, $file);
            $cookies[] = [
                'name'     => basename($file),
                'path'     => realpath($file),
                'size'     => filesize($file),
                'readable' => is_readable($file),
            ];
        }
    }
    $results['tests']['cookies'] = $cookies;

    // Test 4: Run the Python script with a test URL (exactly like terminal)
    $testUrl          = "https://www.instagram.com/reel/DTqYuzMkvNO/";
    $testDownloadPath = storage_path('app/downloads/quick-test-' . time());
    @mkdir($testDownloadPath, 0755, true);

    $cookieFiles = array_map(function ($c) {return $c['path'];}, array_filter($cookies, function ($c) {return $c['readable'] && $c['size'] > 50;}));
    $cookiesJson = json_encode(array_values($cookieFiles));

    // Build command exactly like terminal - with PATH set
    $cmd = sprintf(
        'cd %s && HOME=/tmp PATH=/usr/local/bin:/usr/bin:$PATH %s %s %s %s %s 2>&1',
        escapeshellarg($scriptDir),
        escapeshellarg($python),
        escapeshellarg($script),
        escapeshellarg($testUrl),
        escapeshellarg($testDownloadPath),
        escapeshellarg($cookiesJson)
    );

    $output = shell_exec($cmd);

    $results['tests']['script_execution'] = [
        'command'       => $cmd,
        'output_length' => strlen($output ?? ''),
        'output'        => substr($output ?? '', 0, 3000),
    ];

    // Parse JSON from output
    $jsonOutput = null;
    foreach (preg_split("/\r\n|\r|\n/", $output ?? '') as $line) {
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

// Permission check endpoint
Route::get('/api/instagram/check-permissions', function () {
    clearstatcache();

    $results = [
        'timestamp' => now()->toIso8601String(),
        'user'      => [
            'php_user'     => get_current_user(),
            'process_uid'  => function_exists('posix_geteuid') ? posix_geteuid() : 'N/A',
            'process_user' => function_exists('posix_getpwuid') ? (posix_getpwuid(posix_geteuid())['name'] ?? 'unknown') : 'N/A',
        ],
        'paths'     => [],
    ];

    // Check various paths
    $pathsToCheck = [
        'base_path'      => base_path(),
        'storage_path'   => storage_path(),
        'downloads_path' => storage_path('app/downloads'),
        'python_worker'  => base_path('python_worker'),
        'cookies_dir'    => base_path('python_worker/cookies'),
        'script'         => base_path('python_worker/instagram_fetch.py'),
    ];

    foreach ($pathsToCheck as $name => $path) {
        $exists   = file_exists($path);
        $readable = is_readable($path);
        $writable = is_writable($path);
        $realpath = realpath($path);

        $results['paths'][$name] = [
            'path'     => $path,
            'realpath' => $realpath ?: 'N/A',
            'exists'   => $exists,
            'readable' => $readable,
            'writable' => $writable,
            'type'     => $exists ? (is_dir($path) ? 'directory' : 'file') : 'N/A',
        ];

        if ($exists && is_file($path)) {
            $results['paths'][$name]['size']        = filesize($path);
            $results['paths'][$name]['permissions'] = substr(sprintf('%o', fileperms($path)), -4);
        }
    }

    // Check cookie files
    $cookiesDir              = base_path('python_worker/cookies');
    $results['cookie_files'] = [];
    if (is_dir($cookiesDir)) {
        foreach (glob($cookiesDir . '/*.txt') as $file) {
            $results['cookie_files'][] = [
                'name'        => basename($file),
                'path'        => realpath($file),
                'size'        => filesize($file),
                'readable'    => is_readable($file),
                'permissions' => substr(sprintf('%o', fileperms($file)), -4),
                'owner'       => function_exists('posix_getpwuid') ? (posix_getpwuid(fileowner($file))['name'] ?? fileowner($file)) : fileowner($file),
            ];
        }
    }

    // Try to create a test file in downloads
    $testFile = storage_path('app/downloads/test_write_' . time() . '.txt');
    $canWrite = @file_put_contents($testFile, 'test') !== false;
    if ($canWrite) {
        @unlink($testFile);
    }
    $results['can_write_downloads'] = $canWrite;

    // Check yt-dlp binary
    $ytdlpPaths = [
        '/usr/local/bin/yt-dlp',
        '/usr/bin/yt-dlp',
        '/home/ubuntu/.local/bin/yt-dlp',
    ];
    $results['ytdlp_binaries'] = [];
    foreach ($ytdlpPaths as $path) {
        if (file_exists($path)) {
            $results['ytdlp_binaries'][] = [
                'path'        => $path,
                'is_file'     => is_file($path),
                'is_dir'      => is_dir($path),
                'executable'  => is_executable($path),
                'permissions' => substr(sprintf('%o', fileperms($path)), -4),
            ];
        }
    }

    // Try running yt-dlp as www-data
    $cmd                   = 'cd ' . escapeshellarg(base_path('python_worker')) . ' && HOME=/tmp PATH=/usr/local/bin:/usr/bin:$PATH yt-dlp --version 2>&1';
    $ytdlpOutput           = shell_exec($cmd);
    $results['ytdlp_test'] = [
        'command' => $cmd,
        'output'  => trim($ytdlpOutput ?? ''),
        'works'   => preg_match('/^\d{4}\.\d{2}\.\d{2}/', trim($ytdlpOutput ?? '')) === 1,
    ];

    // Try a simple Python test
    $python                 = env('PYTHON_PATH', '/usr/bin/python3');
    $cmd                    = escapeshellarg($python) . ' -c "import sys, json; print(json.dumps({\'success\': True, \'version\': sys.version}))" 2>&1';
    $pythonOutput           = shell_exec($cmd);
    $results['python_test'] = [
        'command' => $cmd,
        'output'  => trim($pythonOutput ?? ''),
    ];

    // Try importing requests
    $cmd                      = escapeshellarg($python) . ' -c "import requests; print(requests.__version__)" 2>&1';
    $requestsOutput           = shell_exec($cmd);
    $results['requests_test'] = [
        'command'   => $cmd,
        'output'    => trim($requestsOutput ?? ''),
        'installed' => ! str_contains($requestsOutput ?? '', 'ModuleNotFoundError'),
    ];

    return response()->json($results, 200, [], JSON_PRETTY_PRINT);
});
