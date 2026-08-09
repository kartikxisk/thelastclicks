<?php

use App\Support\LogoImage;

/**
 * Both export styles have to trim to the same box, because the studio supplies
 * both: a light mark on transparency and the same mark black on white.
 */

/** A $size square with an opaque bar of $ink drawn at the given rect. */
function logoFixture(int $size, int $left, int $top, int $right, int $bottom, bool $whiteBackground, array $inkRgb = [10, 10, 10]): string
{
    $im = imagecreatetruecolor($size, $size);
    imagealphablending($im, false);
    imagesavealpha($im, true);

    $bg = $whiteBackground
        ? imagecolorallocate($im, 255, 255, 255)
        : imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefilledrectangle($im, 0, 0, $size - 1, $size - 1, $bg);

    $ink = imagecolorallocate($im, ...$inkRgb);
    imagefilledrectangle($im, $left, $top, $right, $bottom, $ink);

    $path = tempnam(sys_get_temp_dir(), 'logo').'.png';
    imagepng($im, $path);

    return $path;
}

it('crops a transparent export down to the mark', function () {
    $src = logoFixture(400, 100, 180, 299, 219, whiteBackground: false);
    $out = tempnam(sys_get_temp_dir(), 'out').'.png';

    $result = LogoImage::process($src, $out, maxEdge: 1200);

    expect($result['trimmed'])->toBe(['left' => 100, 'top' => 180, 'right' => 299, 'bottom' => 219])
        ->and($result['width'])->toBe(200)
        ->and($result['height'])->toBe(40);

    @unlink($src);
    @unlink($out);
});

/**
 * The regression that matters: the light variant is a WHITE mark on transparency.
 * A hardcoded "near-white counts as empty" rule read the mark as background,
 * found nothing, and trimmed the full canvas — so the logo shipped with all its
 * padding intact and came out tiny wherever it was sized by height.
 */
it('crops a white mark on transparency without erasing it', function () {
    $src = logoFixture(400, 100, 180, 299, 219, whiteBackground: false, inkRgb: [255, 255, 255]);
    $out = tempnam(sys_get_temp_dir(), 'out').'.png';

    $result = LogoImage::process($src, $out, maxEdge: 1200);

    expect($result['trimmed'])->toBe(['left' => 100, 'top' => 180, 'right' => 299, 'bottom' => 219])
        ->and($result['width'])->toBe(200)
        ->and($result['height'])->toBe(40);

    @unlink($src);
    @unlink($out);
});

it('crops a black-on-white export to the same box', function () {
    $src = logoFixture(400, 100, 180, 299, 219, whiteBackground: true);
    $out = tempnam(sys_get_temp_dir(), 'out').'.png';

    $result = LogoImage::process($src, $out, maxEdge: 1200);

    expect($result['width'])->toBe(200)
        ->and($result['height'])->toBe(40);

    @unlink($src);
    @unlink($out);
});

it('scales the cropped mark down to the max edge', function () {
    // 2000px of mark inside a 3000px canvas — the shape the supplied files are in.
    $src = logoFixture(3000, 500, 1400, 2499, 1599, whiteBackground: false);
    $out = tempnam(sys_get_temp_dir(), 'out').'.png';

    $result = LogoImage::process($src, $out, maxEdge: 600);

    expect($result['width'])->toBe(600)
        ->and($result['height'])->toBe(60);

    @unlink($src);
    @unlink($out);
});

it('leaves an image alone when every pixel reads as empty', function () {
    $im = imagecreatetruecolor(50, 50);
    imagealphablending($im, false);
    imagesavealpha($im, true);
    imagefilledrectangle($im, 0, 0, 49, 49, imagecolorallocatealpha($im, 0, 0, 0, 127));
    $src = tempnam(sys_get_temp_dir(), 'blank').'.png';
    imagepng($im, $src);
    $out = tempnam(sys_get_temp_dir(), 'out').'.png';

    // Rather than a zero-size crop that reads as a GD bug.
    $result = LogoImage::process($src, $out, maxEdge: 1200);

    expect($result['width'])->toBe(50)->and($result['height'])->toBe(50);

    @unlink($src);
    @unlink($out);
});
