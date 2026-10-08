<?php

declare(strict_types=1);

namespace App\Services;

use Cron\CronExpression;
use Illuminate\Console\Scheduling\Event as ScheduledEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Zamanlanmış görevlerin sağlığı.
 *
 * Önceden her görev aynı `scheduler.health` anahtarına yazıyordu: 00:20'de
 * yedek "failed" yazarsa 00:21'de blog görevi "healthy" yazıp hatayı
 * siliyordu. Artık her görevin kendi kaydı var
 * (`scheduler.health.task.{ad}`); özet anahtar yalnız "zamanlayıcı en son ne
 * zaman dürtüldü" sorusunu yanıtlıyor.
 */
final class SchedulerHealthService
{
    /**
     * Bayatlığı /health'i "degraded"e düşüren görevler ve kayıt yokken
     * kullanılan beklenen sıklık (dakika). Yedek görevleri yalnız zamanlanmış
     * yedek açıkken kritik sayılır; kapalıyken hiç koşmazlar.
     */
    public const CRITICAL_TASKS = [
        'backup_daily' => 1440,
        'backup_verify' => 1440,
        'blog_publish_scheduled' => 1,
        'queue_drain' => 1,
    ];

    private const BACKUP_TASKS = ['backup_daily', 'backup_verify'];

    private const INDEX_KEY = 'scheduler.health.tasks';

    /**
     * Dakikalık görevlerde 2 dakika, tetikleyicinin (Cloudflare Worker,
     * dakikada bir) olağan gecikmesini arıza saymaya yetiyordu; taban bunu
     * engeller.
     */
    private const MIN_STALE_MINUTES = 5;

    /** @param callable():mixed $task */
    public function run(string $name, callable $task): void
    {
        $started = now();
        $this->summary(['last_started_at' => $started->toIso8601String(), 'last_task_name' => $name, 'status' => 'healthy']);
        $this->write($name, ['last_started_at' => $started->toIso8601String(), 'status' => 'running']);

        try {
            $sonuc = $task();

            // Artisan::call çıkış kodu döndürür. Komut hata vermeden 1
            // dönerse görev eskiden "healthy" sayılıyordu.
            if (is_int($sonuc) && $sonuc !== 0) {
                throw new RuntimeException("Görev {$name} çıkış kodu {$sonuc} ile bitti.");
            }

            $this->markSuccess($name, $started);
            $this->summary(['last_started_at' => $started->toIso8601String(), 'last_completed_at' => now()->toIso8601String(), 'last_task_name' => $name, 'last_duration_ms' => (int) $started->diffInMilliseconds(now()), 'status' => 'healthy']);
        } catch (Throwable $exception) {
            report($exception);
            $this->markFailure($name, $exception->getMessage(), $started);
            $this->summary(['last_started_at' => $started->toIso8601String(), 'last_failed_at' => now()->toIso8601String(), 'last_task_name' => $name, 'last_error_code' => 'SCHEDULER_TASK_FAILED', 'status' => 'failed']);

            throw $exception;
        }
    }

    public function markSuccess(string $name, ?Carbon $started = null): void
    {
        $this->write($name, [
            'last_success_at' => now()->toIso8601String(),
            'last_duration_ms' => $started ? (int) $started->diffInMilliseconds(now()) : null,
            'status' => 'healthy',
        ]);
    }

    public function markFailure(string $name, string $error, ?Carbon $started = null): void
    {
        $this->write($name, [
            'last_failed_at' => now()->toIso8601String(),
            'last_duration_ms' => $started ? (int) $started->diffInMilliseconds(now()) : null,
            // Hata metni yalnız yönetim paneline ve özet e-postasına gider;
            // yine de sınırlı tutulur ve bilinen sır kalıpları maskelenir.
            'last_error' => self::safeError($error),
            'last_error_code' => 'SCHEDULER_TASK_FAILED',
            'status' => 'failed',
        ]);
    }

    /**
     * Zamanlayıcı görevi başlatırken beklenen sıklığı kaydeder; bayatlık
     * kararı bu değere dayanır.
     */
    public function noteSchedule(ScheduledEvent $event): void
    {
        $name = (string) $event->description;

        if ($name === '') {
            return;
        }

        // Ayrı anahtar: telafi koşuları (backup_daily_catchup) saatlik
        // zamanlanmış olsa da asıl görevin günlük sıklığını ezmemeli, ve
        // hiç koşmamış bir görev için kayıt açmamalı.
        Cache::forever(self::intervalKey($name), self::intervalMinutes($event->expression));
    }

    /**
     * Telafi koşusu gerekli mi: bugün başarılı koşu yok ve şu an koşan da
     * yok (yedek uzun sürebilir; iki yedeğin üst üste binmesi diski doldurur).
     */
    public function needsCatchUp(string $name): bool
    {
        $kayit = Cache::get(self::taskKey($name));
        $kayit = is_array($kayit) ? $kayit : [];
        $basari = $kayit['last_success_at'] ?? null;

        if (is_string($basari) && Carbon::parse($basari)->isSameDay(now())) {
            return false;
        }

        $baslangic = $kayit['last_started_at'] ?? null;

        return ! (($kayit['status'] ?? null) === 'running' && is_string($baslangic) && abs(now()->diffInMinutes($baslangic)) < 120);
    }

    /** @return array<string, mixed>|null */
    public function task(string $name): ?array
    {
        $kayit = Cache::get(self::taskKey($name));

        return is_array($kayit) ? $this->withStaleness($name, $kayit) : null;
    }

    /** @return array<string, array<string, mixed>> */
    public function tasks(): array
    {
        $adlar = array_unique([...$this->knownNames(), ...array_keys($this->criticalTasks())]);
        sort($adlar);
        $sonuc = [];

        foreach ($adlar as $ad) {
            $sonuc[$ad] = $this->task($ad) ?? $this->withStaleness($ad, ['status' => 'never_run']);
        }

        return $sonuc;
    }

    /**
     * Kritik görevlerden bayat ya da başarısız olanlar.
     *
     * @return list<string>
     */
    public function staleCriticalTasks(): array
    {
        $bayat = [];

        foreach (array_keys($this->criticalTasks()) as $ad) {
            $kayit = $this->task($ad) ?? $this->withStaleness($ad, ['status' => 'never_run']);

            if ($kayit['stale'] || $kayit['status'] === 'failed') {
                $bayat[] = $ad;
            }
        }

        return $bayat;
    }

    /** @return array<string, mixed> */
    public function snapshot(): array
    {
        $snapshot = Cache::get('scheduler.health', ['status' => 'never_run']);
        $lastStartedAt = $snapshot['last_started_at'] ?? null;
        $tasks = $this->tasks();

        // Bayatlık eşiği yapılandırmadan gelir. Önceden burada sabit 26 saat
        // vardı ama rapor `max_age_minutes: 30` diyordu; yani alan bir söz
        // veriyor, durum başka bir şeye bakıyordu. Zamanlayıcı 26 gün ölü
        // kaldı ve bunu yakalayacak sinyal tam da buydu.
        $azamiDakika = (int) (config('production.scheduler_max_age_minutes') ?: 30);

        if ($snapshot['status'] === 'healthy' && is_string($lastStartedAt) && abs(now()->diffInMinutes($lastStartedAt)) > $azamiDakika) {
            $snapshot['status'] = 'delayed';
        }

        $snapshot['task_count'] = count($tasks);
        $snapshot['failed_tasks'] = array_keys(array_filter($tasks, fn (array $t): bool => $t['status'] === 'failed'));

        // Özet anahtarı son koşan görevi anlatır; sonraki başarılı bir görev
        // önceki görevin hatasını silmesin diye durum görev kayıtlarından da
        // türetilir.
        if ($snapshot['status'] === 'healthy' && $snapshot['failed_tasks'] !== []) {
            $snapshot['status'] = 'failed';
            $snapshot['last_error_code'] = 'SCHEDULER_TASK_FAILED';
        }
        $snapshot['stale_tasks'] = array_keys(array_filter($tasks, fn (array $t): bool => $t['stale'] && $t['status'] !== 'never_run'));
        $snapshot['stale_critical_tasks'] = $this->staleCriticalTasks();
        $snapshot['tasks'] = $tasks;

        return $snapshot;
    }

    public static function intervalMinutes(string $expression): int
    {
        try {
            $cron = new CronExpression($expression);
            $sonraki = Carbon::instance($cron->getNextRunDate(now()));
            $birSonraki = Carbon::instance($cron->getNextRunDate($sonraki));

            return max(1, (int) round($sonraki->diffInMinutes($birSonraki)));
        } catch (Throwable) {
            return 1440;
        }
    }

    public static function safeError(string $error): string
    {
        $error = (string) preg_replace('~_zamanlayici/[^\s/"\']+~', '_zamanlayici/***', $error);
        $error = (string) preg_replace('~(sk_live_|sk-ant-|sk_test_)[A-Za-z0-9_\-]+~', '$1***', $error);
        $error = (string) preg_replace('~(password|token|secret|key)=\S+~i', '$1=***', $error);

        return Str::limit($error, 500);
    }

    /** @return array<string, int> */
    private function criticalTasks(): array
    {
        $kritik = self::CRITICAL_TASKS;

        if (! config('observability.backup_scheduled')) {
            foreach (self::BACKUP_TASKS as $ad) {
                unset($kritik[$ad]);
            }
        }

        return $kritik;
    }

    /**
     * @param  array<string, mixed>  $kayit
     * @return array<string, mixed>
     */
    private function withStaleness(string $name, array $kayit): array
    {
        $aralik = (int) (Cache::get(self::intervalKey($name)) ?? self::CRITICAL_TASKS[$name] ?? 1440);
        $esik = max(self::MIN_STALE_MINUTES, $aralik * 2);
        $basari = $kayit['last_success_at'] ?? null;

        $kayit['expected_interval_minutes'] = $aralik;
        $kayit['stale_after_minutes'] = $esik;
        $kayit['stale'] = ! is_string($basari) || abs(now()->diffInMinutes($basari)) > $esik;

        return $kayit;
    }

    /** @param array<string, mixed> $ozet */
    private function summary(array $ozet): void
    {
        Cache::forever('scheduler.health', $ozet);
    }

    /** @param array<string, mixed> $alanlar */
    private function write(string $name, array $alanlar): void
    {
        $mevcut = Cache::get(self::taskKey($name));
        Cache::forever(self::taskKey($name), array_merge(is_array($mevcut) ? $mevcut : [], $alanlar, ['name' => $name]));

        $adlar = $this->knownNames();
        if (! in_array($name, $adlar, true)) {
            $adlar[] = $name;
            Cache::forever(self::INDEX_KEY, $adlar);
        }
    }

    /** @return list<string> */
    private function knownNames(): array
    {
        $adlar = Cache::get(self::INDEX_KEY, []);

        return is_array($adlar) ? array_values(array_filter($adlar, 'is_string')) : [];
    }

    private static function intervalKey(string $name): string
    {
        return 'scheduler.health.interval.'.$name;
    }

    private static function taskKey(string $name): string
    {
        return 'scheduler.health.task.'.$name;
    }
}
