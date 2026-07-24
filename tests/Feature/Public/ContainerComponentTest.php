<?php

it('renders slot content inside the canonical container class', function () {
    $html = (string) $this->blade('<x-container>Hello inside</x-container>');

    expect($html)->toContain('Hello inside')
        ->and($html)->toContain('class="container"');
});

it('merges extra classes and attributes', function () {
    $html = (string) $this->blade('<x-container class="contact-grid" style="margin-bottom:32px">X</x-container>');

    expect($html)->toContain('class="container contact-grid"')
        ->and($html)->toContain('style="margin-bottom:32px;"');
});
