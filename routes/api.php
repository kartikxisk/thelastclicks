<?php

use App\Http\Controllers\Api\V1\IndustryController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\WorkController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API v1
|--------------------------------------------------------------------------
|
| Consumed by the Next.js frontend over localhost. Every GET here already
| serves data the Blade site renders publicly, so there is no auth layer.
|
| Do NOT apply the `cacheResponse` middleware in this file. Next.js ISR is the
| cache of record for these responses; two caches over the same data produce
| stale bugs that are very hard to reproduce.
|
*/

// `withRouting(api:)` applies the /api URL prefix but no name prefix, so the
// full `api.v1.` name lives here. Route names are referenced by tests and by
// `route()` calls; they must not drift from the URL.
Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('/health', fn () => ['status' => 'ok', 'version' => 'v1'])->name('health');

    Route::get('/settings', SettingsController::class)->name('settings');

    // Exact page routes are registered before the {slug} wildcard so they win.
    Route::get('/pages/home', [PageController::class, 'home'])->name('pages.home');
    Route::get('/pages/about', [PageController::class, 'about'])->name('pages.about');
    Route::get('/pages/contact', [PageController::class, 'contact'])->name('pages.contact');
    Route::get('/pages/{slug}', [PageController::class, 'staticPage'])
        ->whereIn('slug', PageController::STATIC_PAGES)
        ->name('pages.static');

    Route::get('/works', [WorkController::class, 'index'])->name('works.index');

    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('services.show');

    Route::get('/industries', [IndustryController::class, 'index'])->name('industries.index');
});
