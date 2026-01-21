<?php

use App\Http\Controllers\InstagramDownloaderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [InstagramDownloaderController::class, 'index'])->name('instagram.downloader');

Route::post('/api/instagram/fetch', [InstagramDownloaderController::class, 'fetch'])->name('instagram.fetch');
