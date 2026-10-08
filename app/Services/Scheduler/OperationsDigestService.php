<?php

declare(strict_types=1);

namespace App\Services\Scheduler;

use App\Mail\OperationsDigestMail;
use App\Models\SystemEvent;
use App\Services\SchedulerHealthService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Günlük tek operasyon özeti: başarısız ve bayat zamanlanmış görevler ile
 * son 24 saatte SystemEvent'e düşen hatalar.
 *
 * Sorun yoksa e-posta gönderilmez; her gün gelen "her şey yolunda" postası
 * bir hafta sonra okunmaz olur ve asıl uyarıyı da gömer.
 */
final class OperationsDigestService
{
    public function __construct(private readonly SchedulerHealthService $scheduler) {}

    /**
     * @return array{failed_tasks: array<string, array<string, mixed>>, stale_tasks: array<string, array<string, mixed>>, errors: list<array{key: string, message: string, count: int, last_seen_at: string}>}
     */
    public function build(): array
    {
        $tasks = $this->scheduler->tasks();

        $hatalar = SystemEvent::query()
            ->whereIn('severity', ['error', 'critical'])
            ->where('updated_at', '>=', now()->subDay())
            ->orderByDesc('updated_at')
            ->limit(500)
            ->get()
            ->groupBy(fn (SystemEvent $olay): string => (string) ($olay->metadata['fingerprint'] ?? $olay->event_type.'|'.($olay->metadata['exception'] ?? '')))
            ->map(fn ($grup, string $anahtar): array => [
                'key' => $anahtar,
                'message' => (string) ($grup->first()->metadata['summary'] ?? $grup->first()->message),
                'count' => (int) $grup->sum(fn (SystemEvent $olay): int => (int) ($olay->metadata['occurrences'] ?? 1)),
                'last_seen_at' => (string) $grup->first()->updated_at?->toIso8601String(),
            ])
            ->sortByDesc('count')
            ->values()
            ->take(20)
            ->all();

        return [
            'failed_tasks' => array_filter($tasks, fn (array $t): bool => $t['status'] === 'failed'),
            // Hiç koşmamış görev yalnız kritikse bildirilir; aksi halde yeni
            // eklenen her haftalık görev ilk haftasında "bayat" görünürdü.
            'stale_tasks' => array_filter($tasks, fn (array $t, string $ad): bool => $t['stale'] && $t['status'] !== 'failed' && ($t['status'] !== 'never_run' || in_array($ad, $this->scheduler->staleCriticalTasks(), true)), ARRAY_FILTER_USE_BOTH),
            'errors' => $hatalar,
        ];
    }

    /** Gönderildiyse true; raporlanacak bir şey yoksa ya da alıcı tanımsızsa false. */
    public function send(): bool
    {
        $ozet = $this->build();

        if ($ozet['failed_tasks'] === [] && $ozet['stale_tasks'] === [] && $ozet['errors'] === []) {
            return false;
        }

        // Yönetici bildirimleri için kodda tanımlı tek adres bu (yeni üye
        // bildirimi de buraya gidiyor).
        $alici = (string) config('mail.registration_admin_recipient');

        if ($alici === '') {
            Log::warning('operasyon_ozeti.alici_yok');

            return false;
        }

        Mail::to($alici)->send(new OperationsDigestMail($ozet));

        return true;
    }
}
