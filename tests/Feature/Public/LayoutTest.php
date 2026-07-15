<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;

it('layout renders title, description, vite manifest, and nav/footer slots', function () {
    // Ignore a dev server's public/hot file so @vite always renders /build/ URLs
    Vite::useHotFile(storage_path('framework/testing/never.hot'));

    $html = Blade::render(
        '<x-layouts.app title="Test Title" description="Test desc"><p>BODY</p></x-layouts.app>'
    );
    expect($html)
        ->toContain('<title>Test Title</title>')
        ->toContain('Test desc')
        ->toContain('BODY')
        ->toContain('/build/');
});
