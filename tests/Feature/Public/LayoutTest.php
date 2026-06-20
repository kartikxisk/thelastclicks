<?php

use Illuminate\Support\Facades\Blade;

it('layout renders title, description, vite manifest, and nav/footer slots', function () {
    $html = Blade::render(
        '<x-layouts.app title="Test Title" description="Test desc"><p>BODY</p></x-layouts.app>'
    );
    expect($html)
        ->toContain('<title>Test Title</title>')
        ->toContain('Test desc')
        ->toContain('BODY')
        ->toContain('/build/');
});
