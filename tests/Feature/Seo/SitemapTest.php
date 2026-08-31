<?php

use App\Models\Industry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('generates a sitemap.xml file with public urls', function () {
    Artisan::call('sitemap:generate', ['--force' => true]);

    $path = public_path('sitemap.xml');
    expect(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);
    expect($content)
        ->toContain('<urlset')
        ->toContain('<loc>'.url('/').'</loc>')
        ->toContain('<loc>'.url('/contact').'</loc>')
        ->toContain('<loc>'.url('/services/photography').'</loc>')
        // Industry detail pages are live again, so they belong in the sitemap.
        ->toContain('<loc>'.url('/industries/'.Industry::first()->slug).'</loc>')
        ->toContain('<loc>'.url('/blog').'</loc>');

    @unlink($path);
});
