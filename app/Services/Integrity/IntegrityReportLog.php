<?php

declare(strict_types=1);

namespace App\Services\Integrity;

use App\Models\IntegrityReport;

/**
 * Otonom denetimlerin koşu geçmişi ve bunun okunabilir özeti.
 *
 * Muhafızlar tek başına bir sayı üretiyor: "eksik görsel 59". Bu sayı
 * yalnız bir öncekiyle yan yana konduğunda bilgi taşıyor - sitenin
 * iyileşip iyileşmediği ancak farkta görünüyor. Panelde gösterilen de bu.
 */
final class IntegrityReportLog
{
    /** Her muhafızın manşet sayısının ne anlama geldiği. Dördünde de az iyidir. */
    public const MUHAFIZLAR = [
        'content:integrity-guard' => ['baslik' => 'İçerik bütünlüğü', 'birim' => 'bulgu'],
        'assets:delivery-check' => ['baslik' => 'Varlık teslimi', 'birim' => 'hatalı varlık'],
        'media:gap-queue' => ['baslik' => 'Görsel eksiği', 'birim' => 'görselsiz tarif'],
        'content:translation-readiness' => ['baslik' => 'Çeviri hazırlığı', 'birim' => 'engelli tarif'],
        'search:recheck-zero-results' => ['baslik' => 'Karşılanmamış arama', 'birim' => 'karşılanmamış terim'],
        'content:link-integrity' => ['baslik' => 'Bağlantı bütünlüğü', 'birim' => 'kusurlu bağlantı'],
        'media:awaiting-approval' => ['baslik' => 'Onay bekleyen görsel', 'birim' => 'bitmiş ama görünmeyen'],
        'backup:completeness' => ['baslik' => 'Yedek eksiksizliği', 'birim' => 'yedekte olmayan tablo'],
        'db:schema-contract' => ['baslik' => 'Veritabanı sözleşmesi', 'birim' => 'kanıtlanmış taşma'],
    ];

    /** @param array<string, mixed> $payload */
    public function record(string $guard, string $status, ?int $headline, array $payload): IntegrityReport
    {
        return IntegrityReport::query()->create([
            'guard' => $guard,
            'status' => $status,
            'headline' => $headline,
            // Ham rapor bütünüyle saklanmıyor: bazıları yüzlerce satırlık
            // kuyruk taşıyor ve tabloyu şişirir. Panelde gereken özet.
            'payload' => $this->ozet($payload),
        ]);
    }

    /**
     * Her muhafız için son koşu, bir önceki koşu ve aradaki fark.
     *
     * @return list<array<string, mixed>>
     */
    public function summary(): array
    {
        $satirlar = [];

        foreach (self::MUHAFIZLAR as $guard => $tanim) {
            $koşular = IntegrityReport::query()
                ->where('guard', $guard)
                ->latest('id')
                ->limit(2)
                ->get();

            $son = $koşular->first();

            if ($son === null) {
                // Hiç koşmamış bir muhafızı "sorun yok" diye göstermek
                // yanlış olur: bakılmadı demek.
                $satirlar[] = ['guard' => $guard, 'baslik' => $tanim['baslik'], 'birim' => $tanim['birim'], 'durum' => 'hic_kosmadi'];

                continue;
            }

            $onceki = $koşular->get(1);

            $satirlar[] = [
                'guard' => $guard,
                'baslik' => $tanim['baslik'],
                'birim' => $tanim['birim'],
                'durum' => $son->status,
                'sayi' => $son->headline,
                'onceki_sayi' => $onceki?->headline,
                'fark' => $son->headline !== null && $onceki?->headline !== null ? $son->headline - $onceki->headline : null,
                'kosma_zamani' => $son->created_at?->toIso8601String(),
                'ozet' => $son->payload,
            ];
        }

        return $satirlar;
    }

    /** Geçmiş sonsuza kadar büyümemeli; trend için birkaç aylık pencere yeter. */
    public function prune(int $gun = 90): int
    {
        return IntegrityReport::query()->where('created_at', '<', now()->subDays($gun))->delete();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function ozet(array $payload): array
    {
        unset($payload['queue'], $payload['results'], $payload['findings']);

        return $payload;
    }
}
