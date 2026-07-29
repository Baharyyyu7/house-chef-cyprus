<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

final class AppEnvironmentCheck extends Command
{
    protected $signature = 'app:environment-check {--json : Makine okunabilir çıktı verir}';

    protected $description = 'Uygulama ortamını gizli değerleri göstermeden doğrular.';

    public function handle(): int
    {
        $checks = [
            'app_key' => filled(config('app.key')),
            'app_url' => filter_var(config('app.url'), FILTER_VALIDATE_URL) !== false,
            'database' => filled(config('database.default')),
            'cache' => filled(config('cache.default')),
            'queue' => filled(config('queue.default')),
            'session' => filled(config('session.driver')),
            'filesystem' => filled(config('filesystems.default')),
            'storage_writable' => is_writable(storage_path()),
            'cors_no_wildcard' => ! in_array('*', config('security.cors_origins', []), true),
        ];
        if (app()->environment('production')) {
            $checks['debug_disabled'] = ! config('app.debug');
            $checks['https_url'] = str_starts_with((string) config('app.url'), 'https://');
            $checks['secure_cookie'] = (bool) config('session.secure');
            $checks['queue_not_sync'] = config('queue.default') !== 'sync';
            $checks['cache_not_array'] = config('cache.default') !== 'array';
            $checks['non_local_storage'] = config('filesystems.default') !== 'local';
            $checks['demo_admin_absent'] = ! Schema::hasTable('users') || ! User::query()->where('email', 'admin@housechef.local')->exists();
            $checks['image_budget_guard'] = ! config('ai_images.generation_enabled') || filled(config('ai_images.production_budget.daily_usd'));
        }
        $result = ['valid' => ! in_array(false, $checks, true), 'environment' => app()->environment(), 'checks' => $checks];
        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(['Kontrol', 'Durum'], collect($checks)->map(fn (bool $ok, string $name) => [$name, $ok ? 'OK' : 'HATA'])->all());
        }

        return $result['valid'] ? self::SUCCESS : self::FAILURE;
    }
}
