<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

final class AppEnvironmentCheck extends Command
{
    protected $signature = 'app:environment-check
                            {--json : Makine okunabilir çıktı verir}
                            {--strict : Hedef ortam için zorunlu production kapılarını uygular}
                            {--environment= : Doğrulanacak profil (local, staging veya production)}';

    protected $description = 'Uygulama ortamını gizli değerleri göstermeden doğrular.';

    public function handle(): int
    {
        $target = (string) ($this->option('environment') ?: app()->environment());
        $isProductionLike = in_array($target, ['staging', 'production'], true);
        $appUrl = (string) config('app.url');
        $host = (string) parse_url($appUrl, PHP_URL_HOST);
        $checks = [
            'app_key' => filled(config('app.key')),
            'app_url' => filter_var($appUrl, FILTER_VALIDATE_URL) !== false,
            'database' => filled(config('database.default')),
            'cache' => filled(config('cache.default')),
            'queue' => filled(config('queue.default')),
            'session' => filled(config('session.driver')),
            'filesystem' => filled(config('filesystems.default')),
            'storage_writable' => is_writable(storage_path()),
            'cors_no_wildcard' => ! in_array('*', config('security.cors_origins', []), true),
        ];
        if ($isProductionLike) {
            $checks['debug_disabled'] = ! config('app.debug');
            $checks['https_url'] = str_starts_with($appUrl, 'https://');
            $checks['public_host'] = $host !== '' && ! in_array($host, ['localhost', '127.0.0.1', '::1'], true);
            $releaseState = (string) (config('production.release_state') ?: 'development');
            $checks['release_state_valid'] = in_array($releaseState, config('production.release_states'), true);
            $checks['secure_cookie'] = (bool) config('session.secure');
            $checks['queue_not_sync'] = config('queue.default') !== 'sync';
            $checks['cache_not_array'] = config('cache.default') !== 'array';
            $checks['database_not_sqlite'] = config('database.default') !== 'sqlite' || (bool) config('production.allow_sqlite');
            $checks['session_not_array'] = config('session.driver') !== 'array';
            $checks['mail_configured'] = $target === 'staging'
                ? config('mail.default') !== 'array'
                : ! in_array(config('mail.default'), ['array', 'log'], true)
                    && filled(config('mail.mailers.'.config('mail.default').'.host'));
            $checks['backup_configured'] = filled(config('production.backup_disk'));
            $checks['trusted_proxies_configured'] = filled(config('production.trusted_proxies'));
            $checks['public_storage_configured'] = $this->publicStorageIsConfigured($appUrl);
            $checks['canonical_configured'] = filled(config('production.canonical_host')) && config('production.canonical_scheme') === 'https';
            $checks['private_storage_distinct'] = config('filesystems.default') !== 'local' || filled(config('filesystems.disks.local.root'));
            $checks['demo_admin_absent'] = ! Schema::hasTable('users') || ! User::query()->where('email', 'admin@housechef.local')->exists();
            $checks['image_budget_guard'] = ! config('ai_images.generation_enabled') || filled(config('ai_images.production_budget.daily_usd'));
        }
        $warnings = [];
        if ($isProductionLike && ! filled(config('database.redis.default.host'))) {
            $warnings[] = 'Redis bağlantısı henüz yapılandırılmadı.';
        }
        if ($target === 'staging') {
            $checks['staging_noindex'] = ! config('seo.indexing_enabled', true);
            $checks['staging_social_disabled'] = ! config('seo.x_distribution_enabled', false);
        }

        $result = [
            'valid' => ! in_array(false, $checks, true),
            'environment' => app()->environment(),
            'target_environment' => $target,
            'strict' => (bool) $this->option('strict'),
            'checks' => $checks,
            'warnings' => $warnings,
        ];
        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(['Kontrol', 'Durum'], collect($checks)->map(fn (bool $ok, string $name) => [$name, $ok ? 'OK' : 'HATA'])->all());
        }

        return $result['valid'] || ! $this->option('strict') ? self::SUCCESS : self::FAILURE;
    }

    private function publicStorageIsConfigured(string $appUrl): bool
    {
        $defaultDisk = (string) config('filesystems.default');
        if (in_array($defaultDisk, ['public', 's3'], true)) {
            return true;
        }

        // Shared hosting keeps the application's default local disk private.
        // Public media may still be served safely through a shell-created
        // public/storage -> shared/storage/app/public link.
        if ($defaultDisk !== 'local') {
            return false;
        }

        $publicDisk = config('filesystems.disks.public');
        $root = is_array($publicDisk) ? (string) ($publicDisk['root'] ?? '') : '';
        $url = (string) (config('production.public_storage_url') ?: (is_array($publicDisk) ? ($publicDisk['url'] ?? '') : ''));

        return $root !== ''
            && is_dir($root)
            && is_link(public_path('storage'))
            && filter_var($url, FILTER_VALIDATE_URL) !== false
            && str_starts_with($url, rtrim($appUrl, '/').'/storage');
    }
}
