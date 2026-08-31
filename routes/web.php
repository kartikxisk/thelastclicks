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
    // editing only. Old URLs 301 to the closest remaining service.
    Route::redirect('/services/weddings', '/services/videography', 301);
    // Post Production was renamed to Editing; its slug moved with it.
    Route::redirect('/services/post-production', '/services/editing', 301);
    Route::redirect('/services/social-content', '/services/editing', 301);
    Route::redirect('/services/creative-direction', '/services/editing', 301);
    Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('service.show');
    Route::get('/industries', [IndustryController::class, 'index'])->name('industries');
    // Industry detail pages are live again — each vertical now carries its own
    // copy and the work actually filed under it, which the deck alone could not.
    // Registered after the exact '/industries' route above, which still wins.
    //
    // Slugs retired with the earlier taxonomy 301 to the index, but that is
    // handled in the controller rather than by Route::redirect entries here: a
    // redirect registered ahead of this route would shadow a live industry the
    // moment anyone reused one of those slugs, and the shadowing is invisible.
    Route::get('/industries/{slug}', [IndustryController::class, 'show'])->name('industry.show');
    Route::get('/portfolio', [WorkController::class, 'index'])->name('works');
    // Renamed from /our-works — 301 preserves inbound links and indexed URLs.
    Route::redirect('/our-works', '/portfolio', 301);
    // The old Portfolio feature's detail pages stay retired to home. Registered
    // after the exact '/portfolio' route above, which therefore still wins.
    Route::redirect('/portfolio/{slug}', '/', 301);
    Route::get('/blog', [BlogController::class, 'index'])->name('blog');
    // Earlier posts were published under auto-generated slugs built from the full
    // headline. Those URLs are consolidated onto the short canonical slugs.
    Route::redirect('/blog/how-to-brief-a-video-production-team-so-the-film-you-get-is-the-film-you-imagined', '/blog/how-to-brief-a-video-production-team', 301);
    Route::redirect('/blog/planning-your-wedding-photography-timeline-a-working-template', '/blog/wedding-photography-timeline-planning', 301);
    Route::redirect('/blog/what-post-production-actually-includes-and-why-it-is-half-the-film', '/blog/what-post-production-actually-includes', 301);
    Route::redirect('/blog/photo-video-or-both-choosing-coverage-for-your-corporate-event', '/blog/photo-vs-video-corporate-event-coverage', 301);
    Route::redirect('/blog/how-to-prepare-your-team-for-a-corporate-shoot', '/blog/preparing-your-team-for-a-corporate-shoot', 301);
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
    // Talent/crew pages retired — permanent redirects preserve inbound links
    Route::redirect('/crew', '/about', 301);
    Route::redirect('/crew/{slug}', '/about', 301);
    Route::get('/contact', [ContactController::class, 'show'])->name('contact');
});

// IndexNow ownership proof. Served from a route rather than a file in public/ so
// the key lives in .env with every other environment secret-shaped value, and
// rotating it needs no deploy. Outside the cacheResponse group deliberately: it
// is not a page, and a cached copy of a rotated key would keep failing
// validation long after the key changed.
Route::get('/{key}.txt', function (string $key) {
    $configured = (string) config('services.indexnow.key');

    abort_if($configured === '' || ! hash_equals($configured, $key), 404);

    return response($configured, 200, ['Content-Type' => 'text/plain']);
})->where('key', '[A-Fa-f0-9]{8,128}')->name('indexnow.key');

// Mutations NOT cached:
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::post('/newsletter', [NewsletterController::class, 'store'])->name('newsletter.store');
