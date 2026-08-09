<?php

namespace App\Support;

use GdImage;
use RuntimeException;

/**
 * Trim and compress a logo before it is published.
 *
 * A logo exported from design tools arrives as a large square with the mark
 * floating in the middle of a lot of nothing — the wordmark supplied for this
 * site was 6250x6250 with the type occupying a band across the centre. Shipped
 * as-is that is megabytes of empty pixels, and worse, every place the logo is
 * sized by height (the nav, the preloader) sizes the PADDING rather than the
 * mark, so the visible logo comes out a fraction of the intended size and cannot
 * be corrected without guessing at the padding ratio.
 *
 * So: crop to the mark's real bounding box, then scale it down to something a
 * web page needs. Both are lossless in the sense that matters — no pixel of the
 * mark is discarded, only the emptiness around it.
 */
class LogoImage
{
    /**
     * Pixels this close to fully transparent count as empty. Anti-aliased edges
     * fade to alpha 127 (GD's "invisible"); a hard `=== 127` test would keep a
     * halo of near-invisible pixels and defeat the crop.
     */
    private const ALPHA_EMPTY = 120;

    /**
     * How far an opaque pixel may drift from the background colour and still count
     * as background. Covers the soft edge of an anti-aliased export without eating
     * into the mark itself.
     */
    private const COLOUR_TOLERANCE = 10;

    /**
     * Load, crop to content, scale to fit $maxEdge, and write an optimised PNG.
     *
     * @return array{width:int,height:int,bytes:int,trimmed:array{left:int,top:int,right:int,bottom:int}}
     */
    public static function process(string $source, string $destination, int $maxEdge = 1200): array
    {
        // No imagedestroy() anywhere here: it has been a no-op since PHP 8.0 and is
        // deprecated in 8.4, which this project pins. GdImage is garbage-collected.
        $image = self::load($source);

        $box = self::contentBox($image);
        $scaled = self::fit(self::crop($image, $box), $maxEdge);

        // Max compression, alpha preserved. The mark is flat colour on
        // transparency, which is the case PNG deflates best.
        imagesavealpha($scaled, true);

        if (! imagepng($scaled, $destination, 9)) {
            throw new RuntimeException("Could not write PNG to {$destination}");
        }

        return [
            'width' => imagesx($scaled),
            'height' => imagesy($scaled),
            'bytes' => (int) filesize($destination),
            'trimmed' => $box,
        ];
    }

    private static function load(string $path): GdImage
    {
        if (! is_file($path)) {
            throw new RuntimeException("No file at {$path}");
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException("Could not read {$path}");
        }

        $image = @imagecreatefromstring($contents);
        if (! $image instanceof GdImage) {
            throw new RuntimeException("{$path} is not an image GD can read (SVG must be rasterised first)");
        }

        // On a palette image imagecolorat() returns an INDEX, not a packed RGBA —
        // so the alpha and channel shifts below would read nonsense and the crop
        // would trim to the wrong box (or to nothing). Indexed PNGs are exactly
        // what an exported two-colour logo tends to be, so this is the common path,
        // not the edge case.
        if (! imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }

    /**
     * The bounding box of everything that is not empty.
     *
     * @return array{left:int,top:int,right:int,bottom:int}
     */
    public static function contentBox(GdImage $image): array
    {
        $w = imagesx($image);
        $h = imagesy($image);

        $left = $w;
        $top = $h;
        $right = -1;
        $bottom = -1;

        // The background is whatever the corner is. Reading it rather than assuming
        // it is the fix for the case that matters most here: the LIGHT variant is a
        // white mark on transparency, and a hardcoded "near-white is empty" rule
        // erased the mark itself and trimmed nothing. Corner-sampling handles white
        // marks, black marks, transparent exports and flat-colour exports alike.
        $background = imagecolorat($image, 0, 0);

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                if (self::isEmpty($image, $x, $y, $background)) {
                    continue;
                }

                if ($x < $left) {
                    $left = $x;
                }
                if ($x > $right) {
                    $right = $x;
                }
                if ($y < $top) {
                    $top = $y;
                }
                if ($y > $bottom) {
                    $bottom = $y;
                }
            }
        }

        // Every pixel read as empty. Returning a zero-size box here would make
        // imagecrop fail in a way that looks like a GD bug; the whole image is the
        // honest answer, and the caller sees an untrimmed result rather than a crash.
        if ($right < 0 || $bottom < 0) {
            return ['left' => 0, 'top' => 0, 'right' => $w - 1, 'bottom' => $h - 1];
        }

        return ['left' => $left, 'top' => $top, 'right' => $right, 'bottom' => $bottom];
    }

    private static function isEmpty(GdImage $image, int $x, int $y, int $background): bool
    {
        $rgba = imagecolorat($image, $x, $y);

        // Anything effectively transparent is empty whatever the background is —
        // a transparent export still has a nominal RGB under the alpha, and it is
        // not necessarily the corner's.
        if ((($rgba >> 24) & 0x7F) >= self::ALPHA_EMPTY) {
            return true;
        }

        // A transparent corner means the only meaningful test was the alpha one,
        // so an opaque pixel here is mark, regardless of its colour.
        if ((($background >> 24) & 0x7F) >= self::ALPHA_EMPTY) {
            return false;
        }

        return abs((($rgba >> 16) & 0xFF) - (($background >> 16) & 0xFF)) <= self::COLOUR_TOLERANCE
            && abs((($rgba >> 8) & 0xFF) - (($background >> 8) & 0xFF)) <= self::COLOUR_TOLERANCE
            && abs(($rgba & 0xFF) - ($background & 0xFF)) <= self::COLOUR_TOLERANCE;
    }

    /** @param array{left:int,top:int,right:int,bottom:int} $box */
    private static function crop(GdImage $image, array $box): GdImage
    {
        $cropped = imagecrop($image, [
            'x' => $box['left'],
            'y' => $box['top'],
            'width' => $box['right'] - $box['left'] + 1,
            'height' => $box['bottom'] - $box['top'] + 1,
        ]);

        if (! $cropped instanceof GdImage) {
            throw new RuntimeException('Crop failed');
        }

        return $cropped;
    }

    /** Scale down to fit within $maxEdge. Never scales up — that only adds bytes. */
    private static function fit(GdImage $image, int $maxEdge): GdImage
    {
        $w = imagesx($image);
        $h = imagesy($image);
        $longest = max($w, $h);

        if ($longest <= $maxEdge) {
            return $image;
        }

        $scale = $maxEdge / $longest;
        $tw = max(1, (int) round($w * $scale));
        $th = max(1, (int) round($h * $scale));

        // imagecopyresampled onto a prepared canvas, not imagescale(): imagescale
        // returns false outright for some interpolation modes on an alpha image,
        // and its transparency handling is inconsistent across GD builds. This path
        // is explicit about both — a fully transparent canvas, blending off so the
        // source alpha is copied rather than composited against it.
        $resized = imagecreatetruecolor($tw, $th);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagefilledrectangle($resized, 0, 0, $tw - 1, $th - 1, imagecolorallocatealpha($resized, 0, 0, 0, 127));

        if (! imagecopyresampled($resized, $image, 0, 0, 0, 0, $tw, $th, $w, $h)) {
            throw new RuntimeException('Resize failed');
        }

        return $resized;
    }
}
