<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstagramDownloaderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Phase 1: Instagram Downloader Routes
|
*/

// Instagram Downloader Routes
Route::get('/', [InstagramDownloaderController::class, 'index'])
    ->name('instagram.downloader');

Route::post('/instagram-downloader/fetch', [InstagramDownloaderController::class, 'fetch'])
    ->name('instagram.fetch');

Route::get('/instagram-downloader/download/{folder}/{filename}', [InstagramDownloaderController::class, 'download'])
    ->name('instagram.download')
    ->where('filename', '.*');

Route::get('/instagram-downloader/preview/{folder}/{filename}', [InstagramDownloaderController::class, 'preview'])
    ->name('instagram.preview')
    ->where('filename', '.*');

Route::get('/instagram-downloader/thumbnail/{folder}/{filename}', [InstagramDownloaderController::class, 'thumbnail'])
    ->name('instagram.thumbnail')
    ->where('filename', '.*');

Route::get('/instagram-downloader/download-all/{folder}', [InstagramDownloaderController::class, 'downloadAll'])
    ->name('instagram.download.all');
