<?php

namespace App\Console\Commands;

use App\Models\SiteSetting;
use App\Support\LogoImage;
use GdImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Build the favicon set from a source mark.
 *
 * Separate from brand:import-logo because a favicon is not the logo scaled down.
 * The wordmark is 2.4:1 — at 32px it is an illegible smear — so the source here
 * should be the ring device alone, and the command squares it, plates it and
 * writes every size the browser asks for.
 */
class ImportFavicon extends Command
{
    protected $signature = 'brand:import-favicon
        {path : Square-ish source mark (the ring device, not the wordmark)}
        {--plate=#0a0a0a : Background behind the mark; "none" for transparent}
        {--inset=18 : Padding around the mark, as a percentage of the canvas}';

    protected $description = 'Trim a mark and write favicon.png, apple-touch-icon.png and favicon.ico';

    /** The .ico carries these; browsers pick per context. */
    private const ICO_SIZES = [16, 32, 48];

    public function handle(): int
    {
        $path = $this->argument('path');

        if (! is_file($path)) {
            $this->error("No file at {$path}");

            return self::FAILURE;
        }

        try {
            $mark = $this->trimmed($path);
        } catch (\Throwable $e) {
            $this->error('Could not read the mark: '.$e->getMessage());

            return self::FAILURE;
        }

        // 512 is the master; everything else is resampled from it rather than from
        // the source, so every size is the same composition.
        $master = $this->plate($mark, 512);

        $this->writePng($master, public_path('favicon.png'));
        $this->writePng($this->resize($master, 180), public_path('apple-touch-icon.png'));
        $this->writeIco($master, public_path('favicon.ico'));

        return $this->publish(public_path('favicon.png'));
    }

    private function trimmed(string $path): GdImage
    {
        $tmp = tempnam(sys_get_temp_dir(), 'favicon').'.png';
        LogoImage::process($path, $tmp, 512);

        $image = imagecreatefrompng($tmp);
        if (! $image instanceof GdImage) {
            throw new \RuntimeException('Could not re-read the trimmed mark');
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);
        @unlink($tmp);

        return $image;
    }

    /** Centre the mark on a square canvas, inset by a margin. */
    private function plate(GdImage $mark, int $size): GdImage
    {
        $canvas = imagecreatetruecolor($size, $size);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);

        $plate = (string) $this->option('plate');
        $background = $plate === 'none'
            ? imagecolorallocatealpha($canvas, 0, 0, 0, 127)
            : $this->allocateHex($canvas, $plate);
        imagefilledrectangle($canvas, 0, 0, $size - 1, $size - 1, $background);

        // Blending back on so the mark composites onto the plate; with it off the
        // mark's own alpha would be copied over and punch a hole through it.
        imagealphablending($canvas, true);

        $inset = max(0, min(40, (int) $this->option('inset'))) / 100;
        $box = (int) round($size * (1 - $inset * 2));
        $mw = imagesx($mark);
        $mh = imagesy($mark);
        $scale = $box / max($mw, $mh);
        $tw = (int) round($mw * $scale);
        $th = (int) round($mh * $scale);

        imagecopyresampled(
            $canvas, $mark,
            (int) round(($size - $tw) / 2), (int) round(($size - $th) / 2),
            0, 0, $tw, $th, $mw, $mh
        );

        imagealphablending($canvas, false);

        return $canvas;
    }

    private function allocateHex(GdImage $image, string $hex): int
    {
        [$r, $g, $b] = sscanf(ltrim($hex, '#'), '%2x%2x%2x') ?: [10, 10, 10];

        return imagecolorallocate($image, (int) $r, (int) $g, (int) $b);
    }

    private function resize(GdImage $source, int $size): GdImage
    {
        $out = imagecreatetruecolor($size, $size);
        imagealphablending($out, false);
        imagesavealpha($out, true);
        imagecopyresampled($out, $source, 0, 0, 0, 0, $size, $size, imagesx($source), imagesy($source));

        return $out;
    }

    private function writePng(GdImage $image, string $dest): void
    {
        imagesavealpha($image, true);
        imagepng($image, $dest, 9);
        $this->info("Wrote {$dest} (".round((int) filesize($dest) / 1024).' KB).');
    }

    /**
     * A PNG-payload .ico. The format also allows raw BMP entries, but every
     * browser still in use reads PNG-in-ICO, and writing BMP by hand means
     * bottom-up rows and a separate AND mask for no benefit.
     */
    private function writeIco(GdImage $master, string $dest): void
    {
        $entries = [];

        foreach (self::ICO_SIZES as $size) {
            ob_start();
            imagepng($this->resize($master, $size), null, 9);
            $entries[$size] = (string) ob_get_clean();
        }

        // 6-byte header, then one 16-byte directory entry per image.
        $offset = 6 + 16 * count($entries);
        $header = pack('vvv', 0, 1, count($entries));
        $directory = '';
        $payload = '';

        foreach ($entries as $size => $png) {
            // 0 in the width/height byte means 256; every size here is smaller.
            $directory .= pack('CCCCvvVV', $size, $size, 0, 0, 1, 32, strlen($png), $offset);
            $payload .= $png;
            $offset += strlen($png);
        }

        file_put_contents($dest, $header.$directory.$payload);
        $this->info("Wrote {$dest} (".round((int) filesize($dest) / 1024).' KB, '.implode('/', self::ICO_SIZES).').');
    }

    /** Put the 512 on the media disk too, and point the admin setting at it. */
    private function publish(string $master): int
    {
        $contents = (string) file_get_contents($master);
        $disk = config('filament.default_filesystem_disk', config('filesystems.default'));
        // Content-hashed for the same reason as the logo: CloudFront serves media
        // immutable for a year, so a fixed key would keep serving the old icon.
        $target = 'branding/favicon-'.substr(md5($contents), 0, 10).'.png';

        try {
            Storage::disk($disk)->put($target, $contents);
        } catch (\Throwable $e) {
            $this->warn("Upload failed ({$e->getMessage()}) — the bundled public/ files are still updated.");

            return self::SUCCESS;
        }

        SiteSetting::set('favicon', $target);
        $this->info('Set [favicon] — live at '.SiteSetting::faviconUrl());

        return self::SUCCESS;
    }
}
