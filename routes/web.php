<?php

use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\IndustryController;
use App\Http\Controllers\Public\NewsletterController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\ServiceController;
use App\Http\Controllers\Public\WorkController;
use Illuminate\Support\Facades\Route;

Route::middleware('cacheResponse')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/about', [PageController::class, 'about'])->name('about');
    // Our-process page retired — permanent redirect preserves inbound links
    Route::redirect('/our-process', '/about', 301);
    Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('privacy');
    Route::get('/terms-of-service', [PageController::class, 'terms'])->name('terms');
    Route::get('/cookie-policy', [PageController::class, 'cookies'])->name('cookies');
    Route::get('/disclaimer', [PageController::class, 'disclaimer'])->name('disclaimer');
    Route::get('/thank-you', [PageController::class, 'thankYou'])->name('thank-you');
    // Retired service pages — the studio now offers photography, videography and
    // post-production only. Old URLs 301 to the closest remaining service.
    Route::redirect('/services/weddings', '/services/videography', 301);
    Route::redirect('/services/social-content', '/services/post-production', 301);
    Route::redirect('/services/creative-direction', '/services/post-production', 301);
    Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('service.show');
    Route::get('/industries', [IndustryController::class, 'index'])->name('industries');
    Route::get('/industries/{industry:slug}', [IndustryController::class, 'show'])->name('industry.show');
    Route::get('/our-works', [WorkController::class, 'index'])->name('works');
    // Portfolio feature retired — 301 old URLs home so inbound links don't 404
    Route::redirect('/portfolio', '/', 301);
    Route::redirect('/portfolio/{slug}', '/', 301);
    Route::get('/blog', [BlogController::class, 'index'])->name('blog');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
    // Talent/crew pages retired — permanent redirects preserve inbound links
    Route::redirect('/crew', '/about', 301);
    Route::redirect('/crew/{slug}', '/about', 301);
    Route::get('/contact', [ContactController::class, 'show'])->name('contact');
});

// Mutations NOT cached:
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::post('/newsletter', [NewsletterController::class, 'store'])->name('newsletter.store');
