<?php

use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\IndustryController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\PortfolioController;
use App\Http\Controllers\Public\ServiceController;
use Illuminate\Support\Facades\Route;

Route::middleware('cacheResponse')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/about', [PageController::class, 'about'])->name('about');
    Route::get('/our-process', [PageController::class, 'process'])->name('our-process');
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
    // Retired industry slugs — work is now grouped into 7 real industries.
    Route::redirect('/industries/corporate-conferences', '/industries/corporate-events', 301);
    Route::redirect('/industries/brand-launches', '/industries/brands-products', 301);
    Route::redirect('/industries/automobile-showcases', '/industries/brands-products', 301);
    Route::redirect('/industries/lifestyle-beverage', '/industries/brands-products', 301);
    Route::redirect('/industries/destination-weddings', '/industries/weddings-celebrations', 301);
    Route::redirect('/industries/commercial-productions', '/industries/motion-post-production', 301);
    Route::get('/industries/{slug}', [IndustryController::class, 'show'])->name('industry.show');
    Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio');
    Route::get('/portfolio/{slug}', [PortfolioController::class, 'show'])->name('portfolio.show');
    Route::get('/blog', [BlogController::class, 'index'])->name('blog');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
    // Talent/crew pages retired — permanent redirects preserve inbound links
    Route::redirect('/crew', '/about', 301);
    Route::redirect('/crew/{slug}', '/about', 301);
    Route::get('/contact', [ContactController::class, 'show'])->name('contact');
});

// Mutations NOT cached:
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
