<?php

namespace App\Console\Commands;

use App\Support\AppUrl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Deploy gate for the settings that fail silently.
 *
 * A wrong APP_URL does not throw — it just publishes canonical tags, og:url and
 * every asset() pointing at a host crawlers cannot reach, and nobody notices
 * until rankings do. Same for APP_DEBUG left on, or an unreachable media disk.
 * Run this after deploying; a non-zero exit should stop the release.
 */
class Preflight extends Command
{
    protected $signature = 'app:preflight {--strict : Treat warnings as failures too}';

    protected $description = 'Verify the environment is safe to serve publicly (APP_URL, debug, media disk, queue)';

    /** @var list<array{string, string, string}> */
    protected array $results = [];

    public function handle(): int
    {
        $isProduction = app()->environment('production');

        $this->checkAppUrl($isProduction);
        $this->checkDebug($isProduction);
        $this->checkRuntimeWritable($isProduction);
        $this->checkMediaDisk();
        $this->checkMediaUrl();
        $this->checkQueue($isProduction);

        $this->newLine();
        $this->table(['', 'Check', 'Detail'], $this->results);

        $failed = $this->countOf('FAIL');
        $warned = $this->countOf('WARN');

        if ($failed > 0) {
            $this->error("{$failed} check(s) failed — do not serve this build publicly.");

            return self::FAILURE;
        }

        if ($warned > 0 && $this->option('strict')) {
            $this->error("{$warned} warning(s), and --strict was given.");

            return self::FAILURE;
        }

        $this->info($warned > 0 ? "Passed with {$warned} warning(s)." : 'All checks passed.');

        return self::SUCCESS;
    }

    protected function checkAppUrl(bool $isProduction): void
    {
        $url = AppUrl::current();

        if ($url === '') {
            $this->fail_('APP_URL', 'Not set. Canonicals and og:url will be wrong.');

            return;
        }

        if (AppUrl::isLocal($url)) {
            // Local value in production poisons every canonical tag on the site.
            $this->record($isProduction ? 'FAIL' : 'WARN', 'APP_URL', "Local value ({$url}) — must be the public domain in production.");

            return;
        }

        if ($isProduction && ! AppUrl::isSecure($url)) {
            $this->fail_('APP_URL', "Not https ({$url}). Canonicals must point at the secure origin.");

            return;
        }

        $this->pass('APP_URL', $url);
    }

    protected function checkDebug(bool $isProduction): void
    {
        if ($isProduction && config('app.debug')) {
            $this->fail_('APP_DEBUG', 'Enabled in production — leaks stack traces, env values and queries.');

            return;
        }

        $this->pass('APP_DEBUG', config('app.debug') ? 'on (non-production)' : 'off');
    }

    /**
     * The two directories the runtime writes to at request time.
     *
     * Deliberately NOT an is_writable() check: preflight usually runs as root or as
     * the deploy user, for whom everything looks writable, while PHP-FPM runs as
     * someone else and gets "Permission denied" on storage/logs and
     * storage/framework/views. That combination is how a deploy reports success and
     * still 500s every page. So this resolves the web user and asks whether *that*
     * user could write, via owner/group/other bits.
     */
    protected function checkRuntimeWritable(bool $isProduction): void
    {
        $paths = [
            'storage/logs',
            'storage/framework/views',
            'storage/framework/cache',
            'storage/framework/sessions',
            'bootstrap/cache',
        ];

        // Locally, PHP is served by the same account that owns the checkout, so the
        // only meaningful question is whether the current user can write. Resolving a
        // `www` account that exists on macOS but never serves anything would fail every
        // dev machine for no reason.
        $webUser = $isProduction ? $this->webUser() : null;
        $problems = [];

        foreach ($paths as $rel) {
            $abs = base_path($rel);

            if (! is_dir($abs)) {
                $problems[] = "{$rel} missing";

                continue;
            }

            if ($webUser === null) {
                // No way to resolve the web user (non-POSIX host); the best available
                // signal is whether the current user can write.
                if (! is_writable($abs)) {
                    $problems[] = "{$rel} not writable";
                }

                continue;
            }

            if (! $this->writableBy($abs, $webUser)) {
                $problems[] = sprintf('%s owned by %s:%s (%04o)', $rel, $this->ownerName($abs), $this->groupName($abs), fileperms($abs) & 0777);
            }
        }

        if ($problems === []) {
            $this->pass('Runtime dirs', $webUser !== null
                ? "writable by {$webUser['name']}"
                : 'writable');

            return;
        }

        // The remedy printed here used to end in `chmod -R 775 storage
        // bootstrap/cache` — the one command DEPLOYMENT.md tells you never to
        // run. -R applies 775 to FILES as well as directories, which sets +x on
        // the eleven tracked .gitignore placeholders, flips them from 100644 to
        // 100755, and blocks the next git pull. Telling an operator to do that
        // while their deploy is already failing is how a small problem becomes
        // two. Split dirs from files instead, matching what Deploy actually does.
        $this->fail_('Runtime dirs', implode('; ', $problems).($webUser !== null
            ? " — fix: chown -R {$webUser['name']}:{$webUser['name']} storage bootstrap/cache"
                .' && find storage bootstrap/cache -type d -exec chmod 2775 {} +'
                .' && find storage bootstrap/cache -type f -exec chmod 664 {} +'
            : ''));
    }

    /**
     * Accounts that mean "this box's web server" on some distribution or panel.
     * aaPanel/BT uses `www`, Ubuntu/Debian and Forge `www-data`, RHEL `apache`
     * or `nginx`.
     *
     * @var list<string>
     */
    protected const WEB_USER_NAMES = ['www-data', 'www', 'nginx', 'apache', 'httpd'];

    /**
     * The account PHP-FPM actually serves as.
     *
     * Resolution order matters, and getting it wrong fails a deploy that had
     * nothing wrong with it. This used to return the first candidate name that
     * merely EXISTED — but Ubuntu ships a `www-data` account whether or not
     * anything runs as it, so on an aaPanel box (PHP-FPM running as `www`, and
     * storage correctly owned `www:www`) it resolved `www-data`, found the dirs
     * unwritable by that account, and aborted a perfectly healthy release while
     * printing a "fix" that would have broken the ownership that was already
     * right.
     *
     * So: ask the filesystem before guessing. The deploy chowns the runtime dirs
     * to the web user, which makes their owner the best available evidence of who
     * that is — provided the owner is a plausible web account and not `root` or a
     * deploy user, in which case there is a real problem to report.
     *
     * @return array{name: string, uid: int, gid: int}|null
     */
    protected function webUser(): ?array
    {
        if (! function_exists('posix_getpwnam')) {
            return null;
        }

        // An explicit APP_WEB_USER is a statement of fact by whoever built the
        // server; it beats any inference drawn here.
        if (($configured = env('APP_WEB_USER')) && is_string($configured)) {
            return $this->lookupUser($configured);
        }

        if (($observed = $this->ownerOfRuntimeDir()) !== null) {
            return $observed;
        }

        foreach (self::WEB_USER_NAMES as $name) {
            if (($user = $this->lookupUser($name)) !== null) {
                return $user;
            }
        }

        return null;
    }

    /**
     * The owner of storage/logs, when that owner is a recognisable web account.
     *
     * Returns null for root, a deploy user, or anything else unrecognised —
     * those are exactly the cases the check exists to catch, and inferring the
     * web user from them would make the check agree with whatever it found and
     * never fail.
     *
     * @return array{name: string, uid: int, gid: int}|null
     */
    protected function ownerOfRuntimeDir(): ?array
    {
        $path = base_path('storage/logs');

        if (! is_dir($path) || ! function_exists('posix_getpwuid')) {
            return null;
        }

        $info = posix_getpwuid(fileowner($path));

        if (! is_array($info) || ! in_array($info['name'], self::WEB_USER_NAMES, true)) {
            return null;
        }

        return ['name' => (string) $info['name'], 'uid' => (int) $info['uid'], 'gid' => (int) $info['gid']];
    }

    /** @return array{name: string, uid: int, gid: int}|null */
    protected function lookupUser(string $name): ?array
    {
        $info = posix_getpwnam($name);

        return $info === false
            ? null
            : ['name' => $name, 'uid' => (int) $info['uid'], 'gid' => (int) $info['gid']];
    }

    /** @param array{name: string, uid: int, gid: int} $user */
    protected function writableBy(string $path, array $user): bool
    {
        $perms = fileperms($path) & 0777;

        if (fileowner($path) === $user['uid']) {
            return (bool) ($perms & 0200);
        }

        if ($this->inGroup($path, $user)) {
            return (bool) ($perms & 0020);
        }

        return (bool) ($perms & 0002);
    }

    /**
     * Group match covers both the user's primary group and any supplementary group —
     * a deploy that chowns to `deploy:www` is perfectly valid.
     *
     * @param  array{name: string, uid: int, gid: int}  $user
     */
    protected function inGroup(string $path, array $user): bool
    {
        $gid = filegroup($path);

        if ($gid === $user['gid']) {
            return true;
        }

        if (! function_exists('posix_getgrgid')) {
            return false;
        }

        $group = posix_getgrgid($gid);

        return is_array($group) && in_array($user['name'], $group['members'], true);
    }

    protected function ownerName(string $path): string
    {
        $uid = fileowner($path);

        if (function_exists('posix_getpwuid') && ($info = posix_getpwuid($uid)) !== false) {
            return (string) $info['name'];
        }

        return (string) $uid;
    }

    protected function groupName(string $path): string
    {
        $gid = filegroup($path);

        if (function_exists('posix_getgrgid') && ($info = posix_getgrgid($gid)) !== false) {
            return (string) $info['name'];
        }

        return (string) $gid;
    }

    protected function checkMediaDisk(): void
    {
        $disk = (string) config('media-library.disk_name', 'public');

        try {
            Storage::disk($disk)->exists('.preflight-probe');
            $this->pass('Media disk', "{$disk} reachable");
        } catch (Throwable $e) {
            // Uploads (client logos, industry media, work galleries) all land here.
            $this->fail_('Media disk', "{$disk} unreachable: ".$e->getMessage());
        }
    }

    /**
     * The media disk being reachable is not the same as media being displayable.
     *
     * Credentials alone satisfy checkMediaDisk(), so a bucket with no AWS_URL
     * passes it while every image on the site silently breaks: MediaUrl falls
     * through to Storage::disk()->url(), which returns a direct bucket URL that
     * 403s on a private bucket, and resolves the s3 driver on every page that
     * shows media — the PortableVisibilityConverter crash the disk config warns
     * about. Locally AWS_URL is usually set and prod is where it goes missing,
     * which is exactly the "works on my machine" shape this is here to name.
     */
    protected function checkMediaUrl(): void
    {
        $disk = (string) config('media-library.disk_name', 'public');

        if ($disk === 'public') {
            $this->pass('Media URL', 'public disk — served from the app origin');

            return;
        }

        $base = config("filesystems.disks.{$disk}.url");

        if (! is_string($base) || trim($base) === '') {
            $this->fail_('Media URL', "No url configured for the '{$disk}' disk (AWS_URL). Images will resolve to direct bucket URLs and 404/403.");

            return;
        }

        if (! preg_match('~^https?://~i', $base)) {
            $this->fail_('Media URL', "Disk '{$disk}' url is not absolute: {$base}");

            return;
        }

        $this->pass('Media URL', $base);
    }

    protected function checkQueue(bool $isProduction): void
    {
        if ($isProduction && config('queue.default') === 'sync') {
            // Quote notifications send inline, so slow SMTP stalls the visitor's
            // form submit and a mail failure can surface as a 500 after the lead
            // has already been captured.
            $this->record('WARN', 'Queue', 'sync in production — mail sends inside the web request.');

            return;
        }

        $this->pass('Queue', (string) config('queue.default'));
    }

    protected function pass(string $check, string $detail): void
    {
        $this->record('OK', $check, $detail);
    }

    protected function fail_(string $check, string $detail): void
    {
        $this->record('FAIL', $check, $detail);
    }

    protected function record(string $status, string $check, string $detail): void
    {
        $this->results[] = [$status, $check, $detail];
    }

    protected function countOf(string $status): int
    {
        return count(array_filter($this->results, fn (array $r): bool => $r[0] === $status));
    }
}
