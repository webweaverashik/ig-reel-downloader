<?php

use App\Http\Controllers\InstagramDownloaderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| IGReelDownloader.net - Instagram Downloader Routes
| All routes for different content types (home, reels, video, photo, story, carousel)
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