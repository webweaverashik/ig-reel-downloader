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

// Debug endpoint - test Python execution directly
Route::get('/api/instagram/test-python', function () {
    $python     = config('services.python.path', '/usr/bin/python3');
    $ytdlp      = config('services.ytdlp.path', '/usr/local/bin/yt-dlp');
    $script     = base_path('python_worker/instagram_fetch.py');
    $cookiesDir = base_path('python_worker/cookies');

    $results = [];

    // Test 1: Python version
    $results['python']         = [];
    $results['python']['path'] = $python;
    exec("{$python} --version 2>&1", $pyOutput, $pyCode);
    $results['python']['version'] = implode("\n", $pyOutput);
    $results['python']['code']    = $pyCode;

    // Test 2: yt-dlp version
    $results['ytdlp']         = [];
    $results['ytdlp']['path'] = $ytdlp;
    exec("{$ytdlp} --version 2>&1", $ytOutput, $ytCode);
    $results['ytdlp']['version'] = implode("\n", $ytOutput);
    $results['ytdlp']['code']    = $ytCode;

    // Test 3: Script exists
    $results['script']             = [];
    $results['script']['path']     = $script;
    $results['script']['exists']   = file_exists($script);
    $results['script']['readable'] = is_readable($script);

    // Test 4: Cookies directory
    $results['cookies']           = [];
    $results['cookies']['dir']    = $cookiesDir;
    $results['cookies']['exists'] = is_dir($cookiesDir);

    if (is_dir($cookiesDir)) {
        $files                       = glob($cookiesDir . '/*.txt');
        $results['cookies']['files'] = [];
        foreach ($files as $file) {
            $results['cookies']['files'][] = [
                'name'     => basename($file),
                'size'     => filesize($file),
                'readable' => is_readable($file),
                'path'     => realpath($file),
            ];
        }
    }

    // Test 5: Simple Python test
    $testCmd                           = escapeshellarg($python) . " -c \"import sys, json; print(json.dumps({'success': True, 'python': sys.version}))\" 2>&1";
    $results['python_test']            = [];
    $results['python_test']['command'] = $testCmd;
    $results['python_test']['output']  = shell_exec($testCmd);

    // Test 6: Environment
    $results['environment'] = [
        'user'      => get_current_user(),
        'home'      => getenv('HOME'),
        'path'      => getenv('PATH'),
        'cwd'       => getcwd(),
        'base_path' => base_path(),
    ];

    // Test 7: Run the actual script with a test (no URL - should fail with usage error)
    $testScriptCmd = escapeshellarg($python) . ' ' . escapeshellarg($script) . ' 2>&1';
    putenv('HOME=/tmp');
    $results['script_test']            = [];
    $results['script_test']['command'] = $testScriptCmd;
    $results['script_test']['output']  = shell_exec($testScriptCmd);

    return response()->json($results, 200, [], JSON_PRETTY_PRINT);
});
