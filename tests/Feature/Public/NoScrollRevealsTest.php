<?php

/**
 * Nothing on the public site may hide content behind a scroll trigger.
 *
 * The site used to run three of them at once: motion.dev over `[data-anim]`,
 * an IntersectionObserver over `.reveal`/`.split`/`.clip-reveal`, and a
 * scroll-locked hero that held its own headline at opacity 0 with the page
 * unscrollable until the visitor tried to scroll. All three shared one failure
 * mode — whenever the trigger did not fire, real copy stayed invisible and the
 * page read as an overlay lying over the site. A stale cached bundle, a
 * restored scroll position or any script error was enough.
 *
 * They were removed. These tests fail if a start state comes back, because the
 * regression is silent: the markup is correct, the tests pass, and the page is
 * simply blank. `docs/motion-spec.md` -> "Nothing enters on scroll" is the
 * binding rule; this is its enforcement.
 */
$css = fn (string $file) => file_get_contents(resource_path("css/{$file}"));

it('ships no hidden start state for the reveal attributes', function () use ($css) {
    $pages = $css('pages.css');

    // Any `[data-anim...] { ... opacity: 0 ... }` rule, however it is spelled.
    expect($pages)->not->toMatch('/\[data-anim[^\]]*\][^{}]*\{[^{}]*opacity:\s*0[^.\d]/');
    // clip-path and blur hide content just as completely as opacity does.
    expect($pages)->not->toMatch('/\[data-anim[^\]]*\][^{}]*\{[^{}]*clip-path:/');
    expect($pages)->not->toMatch('/\[data-anim[^\]]*\][^{}]*\{[^{}]*filter:\s*blur/');
});

it('ships no reveal primitives keyed off .is-in', function () use ($css) {
    $core = $css('core.css');

    foreach (['.reveal', '.split-word', '.clip-reveal'] as $selector) {
        expect($core)->not->toContain($selector);
    }

    // `.curtain.is-in` is the route wipe: a full-screen overlay that is itself
    // the content, so it never hides the page. It is the one legitimate holdout.
    $selectors = [];
    preg_match_all('/^([^\s{][^{\n]*\.is-in[^{\n]*)\{/m', $core.$css('pages.css'), $selectors);

    foreach ($selectors[1] as $selector) {
        expect(trim($selector))->toStartWith('.curtain');
    }
});

it('does not lock scroll or hide the hero headline on load', function () use ($css) {
    // The declaration, not the word — the comment recording why it went is
    // meant to survive.
    expect($css('pages.css'))->not->toMatch('/--hero-reveal\s*:/');
});

it('no longer builds the reveal engine into the bundle', function () {
    expect(resource_path('js/reveals.js'))->not->toBeFile();

    $core = file_get_contents(resource_path('js/core.js'));

    expect($core)->not->toContain('initReveals')
        // The per-word splitter wrapped headings in spans that started at
        // opacity 0, so it hid content even with the CSS above cleaned up.
        ->and($core)->not->toContain('split-word');

    // Same holdout as the CSS: only the route curtain may still toggle .is-in.
    $lines = preg_grep('/is-in/', explode("\n", $core));

    foreach ($lines as $line) {
        expect(trim($line))->toStartWith('curtain.classList.');
    }
});

it('does not dim a section as it leaves the viewport', function () use ($css) {
    expect($css('pages.css'))->not->toContain('is-leaving')
        ->and(file_get_contents(resource_path('js/scene.js')))->not->toContain("'is-leaving'");
});
