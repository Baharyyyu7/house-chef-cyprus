<?php

declare(strict_types=1);

namespace App\Services\Observability;

use App\Contracts\Observability\ErrorReporterInterface;
use App\Models\SystemEvent;
use App\Services\SchedulerHealthService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Hataları SystemEvent'e yazar; yönetim panelindeki sistem olayları ve
 * günlük operasyon özeti buradan okur.
 *
 * Önceden yalnız API hataları buraya düşüyordu; web, kuyruk ve zamanlayıcı
 * hataları yalnız log dosyasında kalıyor ve kimse görmüyordu. Artık bütün
 * raporlanan istisnalar geliyor; aynı hata (parmak izi) 24 saat içinde
 * tekrarlarsa yeni satır açılmaz, mevcut satırın sayacı artar - bir döngüde
 * patlayan görev tabloyu doldurmasın.
 */
final class LocalLogErrorReporter implements ErrorReporterInterface
{
    private const DEDUPE_HOURS = 24;

    public function report(Throwable $exception, array $context = []): void
    {
        $context = array_filter($context, fn (mixed $value, string $key): bool => ! in_array(strtolower($key), ['password', 'token', 'authorization', 'cookie', 'session'], true), ARRAY_FILTER_USE_BOTH);
        $parmakIzi = self::fingerprint($exception);
        Log::error('application.error', $context + ['error_code' => 'INTERNAL_ERROR', 'exception_class' => class_basename($exception), 'fingerprint' => $parmakIzi]);

        $anahtar = 'system-event.fingerprint.'.$parmakIzi;
        $mevcutId = Cache::get($anahtar);
        $mevcut = is_int($mevcutId) ? SystemEvent::query()->whereKey($mevcutId)->whereNull('resolved_at')->first() : null;

        if ($mevcut !== null) {
            $metadata = $mevcut->metadata ?? [];
            $metadata['occurrences'] = (int) ($metadata['occurrences'] ?? 1) + 1;
            $mevcut->forceFill(['metadata' => $metadata, 'correlation_id' => $context['request_id'] ?? $mevcut->correlation_id])->save();

            return;
        }

        $olay = SystemEvent::create([
            'event_type' => 'application_error',
            'severity' => 'error',
            'message' => 'Uygulama hatası güvenli olarak kaydedildi.',
            'correlation_id' => $context['request_id'] ?? null,
            'metadata' => [
                'error_code' => 'INTERNAL_ERROR',
                'exception' => class_basename($exception),
                'context' => $context['context'] ?? null,
                'fingerprint' => $parmakIzi,
                'occurrences' => 1,
                // Yalnız yöneticinin gördüğü özet; bilinen sır kalıpları maskelenir.
                'summary' => class_basename($exception).': '.SchedulerHealthService::safeError($exception->getMessage()),
                'location' => basename($exception->getFile()).':'.$exception->getLine(),
            ],
        ]);

        Cache::put($anahtar, (int) $olay->getKey(), now()->addHours(self::DEDUPE_HOURS));
    }

    /**
     * Aynı hatanın her tekrarında aynı kalan iz: sınıf, dosya, satır ve
     * sayılardan arındırılmış mesaj (kimlikler ve süreler her seferinde farklı).
     */
    public static function fingerprint(Throwable $exception): string
    {
        $mesaj = (string) preg_replace('/\d+/', '#', $exception->getMessage());

        return substr(sha1($exception::class.'|'.$exception->getFile().'|'.$exception->getLine().'|'.$mesaj), 0, 16);
    }
}
