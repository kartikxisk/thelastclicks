<?php

use App\Http\Controllers\Api\V1\SettingsController;
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
});
