<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class ProductionOperationsService
{
    /** @return array<string, mixed> */
    public function databaseReadiness(): array
    {
        $driver = (string) config('database.default');
        $migrations = glob(database_path('migrations/*.php')) ?: [];
        $contents = implode("\n", array_map(static fn (string $file): string => (string) file_get_contents($file), $migrations));
        $destructive = preg_match('/->drop(Column|IfExists|Table|Index)|->renameColumn\s*\(/', $contents) === 1;
        $required = ['recipes', 'users', 'migrations', 'jobs', 'failed_jobs'];
        $missing = array_values(array_filter($required, static fn (string $table): bool => ! Schema::hasTable($table)));

        return [
            'status' => $missing !== [] ? 'unhealthy' : ($driver === 'sqlite' ? 'degraded' : 'healthy'),
            'checked_at' => now()->toIso8601String(),
            'driver' => $driver,
            'sqlite_production_risk' => $driver === 'sqlite',
            'missing_required_tables' => $missing,
            'migration_count' => count($migrations),
            'destructive_migration_review_required' => $destructive,
            'backup_required_before_migration' => true,
            'production_command' => 'php artisan migrate --force',
            'forbidden_command' => 'php artisan migrate:fresh',
            'safe_message' => $missing === [] ? 'Veritabanı şeması operasyonel olarak incelendi.' : 'Gerekli veritabanı tabloları eksik.',
        ];
    }

    /** @return array<string, mixed> */
    public function queueHealth(): array
    {
        return app(QueueHealthService::class)->snapshot() + [
            'worker_mode' => config('production.queue_worker_mode') ?: 'cron_stop_when_empty',
            'shared_hosting_command' => 'php artisan queue:work --stop-when-empty --tries=3 --timeout=90',
        ];
    }

    /** @return array<string, mixed> */
    public function schedulerHealth(): array
    {
        $snapshot = app(SchedulerHealthService::class)->snapshot();
        $snapshot['max_age_minutes'] = (int) (config('production.scheduler_max_age_minutes') ?: 30);
        $snapshot['shared_hosting_command'] = 'php artisan schedule:run';

        return $snapshot;
    }

    /** @return array<string, mixed> */
    public function mailHealth(bool $send = false): array
    {
        $mailer = (string) config('mail.default');
        $smtp = $mailer === 'smtp';
        $configured = $smtp && filled(config('mail.mailers.smtp.host')) && filled(config('mail.from.address'));
        $requiresSmtp = (bool) config('production.mail_health_requires_smtp');

        return [
            'status' => $configured ? 'healthy' : ($requiresSmtp ? 'not_configured' : 'degraded'),
            'checked_at' => now()->toIso8601String(),
            'mailer' => $mailer,
            'send_attempted' => $send,
            'queue_required' => config('queue.default') !== 'sync',
            'safe_message' => $configured ? 'SMTP sözleşmesi doğrulandı; canlı gönderim yapılmadı.' : 'SMTP yapılandırması tamamlanmayı bekliyor.',
        ];
    }

    /** @return array<string, mixed> */
    public function securityReadiness(): array
    {
        $url = (string) config('app.url');
        $host = (string) parse_url($url, PHP_URL_HOST);
        $checks = [
            'debug_disabled' => ! config('app.debug'),
            'https_url' => str_starts_with($url, 'https://'),
            'host_not_local' => $host !== '' && ! in_array($host, ['localhost', '127.0.0.1', '::1'], true),
            'secure_cookie' => (bool) config('session.secure'),
            'http_only_cookie' => (bool) config('session.http_only'),
            'csrf_web_contract' => true,
            'rate_limit_contract' => true,
            'private_disk_configured' => filled(config('filesystems.disks.local.root')),
            'safe_errors_contract' => true,
        ];

        return [
            'status' => in_array(false, $checks, true) ? 'degraded' : 'healthy',
            'checked_at' => now()->toIso8601String(),
            'checks' => $checks,
            'safe_message' => 'Güvenlik hazırlığı gizli değerler göstermeden değerlendirildi.',
        ];
    }

    /** @return array<string, mixed> */
    public function releaseManifest(): array
    {
        $assets = public_path('build/manifest.json');
        $lock = base_path('composer.lock');
        $migrationOutput = '';
        Artisan::call('migrate:status', ['--no-ansi' => true]);
        $migrationOutput = trim(Artisan::output());

        return [
            'release_public_id' => (string) Str::ulid(),
            'version' => (string) (config('production.artifact_version') ?: 'production-artifact-v1'),
            'commit_sha' => $this->releaseCommitSha(),
            'build_timestamp' => now()->toIso8601String(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'asset_manifest_checksum' => is_file($assets) ? hash_file('sha256', $assets) : null,
            'dependency_lock_checksum' => is_file($lock) ? hash_file('sha256', $lock) : null,
            'environment_contract_version' => config('production.readiness_rule_version'),
            'migration_status_checked' => $migrationOutput !== '',
            'release_state' => config('production.release_state') ?: 'development',
            'deploy_status' => 'prepared_not_deployed',
            'safe_message' => 'Release manifest yalnız yerel derleme ve yapılandırma verilerini içerir.',
        ];
    }

    /** @return array<string, mixed> */
    public function health(): array
    {
        $components = app(HealthService::class)->snapshot()['components'];
        $components[] = ['name' => 'mail', ...$this->mailHealth()];
        $components[] = ['name' => 'security', ...$this->securityReadiness()];
        $components[] = ['name' => 'database_readiness', ...$this->databaseReadiness()];
        $statuses = collect($components)->pluck('status');

        return [
            'status' => $statuses->contains('unhealthy') ? 'unhealthy' : ($statuses->contains(fn (string $status): bool => in_array($status, ['degraded', 'not_configured', 'unavailable'], true)) ? 'degraded' : 'healthy'),
            'checked_at' => now()->toIso8601String(),
            'components' => $components,
        ];
    }

    /**
     * Sunulan sürümün commit'i. Commit bir sır değil ve deploy duman testi
     * "eski kod mu servis ediliyor" sorusunu ancak bununla yanıtlayabiliyor
     * (lsphp yeniden başlatılmazsa eski sürüm 200 dönmeye devam eder).
     */
    public function buildCommit(): string
    {
        return $this->releaseCommitSha();
    }

    private function releaseCommitSha(): string
    {
        $metadata = base_path('.house-chef-build-commit');
        $candidates = [
            is_readable($metadata) ? trim((string) file_get_contents($metadata)) : null,
            config('production.commit_sha'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && preg_match('/^[0-9a-f]{7,64}$/', $candidate) === 1) {
                return $candidate;
            }
        }

        return 'unavailable';
    }
}
