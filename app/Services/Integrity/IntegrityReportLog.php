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
                'sebep' => $this->sebep($guard, (array) $son->payload),
            ];
        }

        return $satirlar;
    }

    /**
     * Sayının arkasındaki tek cümle.
     *
     * Panel yalnız manşet sayıyı basıyordu ve bu iki ayrı iş yanılsaması
     * üretiyordu: "47 çeviri engeli" ile "45 görsel eksiği" yan yana
     * duruyor, oysa 47 engelin 47'si de görsel eksiği. Sebep payload'da
     * zaten vardı, ekrana çıkmıyordu.
     */
    private function sebep(string $guard, array $payload): ?string
    {
        if ($guard === 'media:gap-queue') {
            $bekleyen = (int) ($payload['awaiting_approval'] ?? 0);

            return $bekleyen > 0
                ? $bekleyen." tanesinin görseli hazır, yalnız onay bekliyor"
                : null;
        }

        if ($guard === 'search:recheck-zero-results') {
            $esikAlti = (int) ($payload['below_threshold'] ?? 0);

            return $esikAlti > 0
                ? $esikAlti." tekil terim daha var; tekrarlanmadıkça talep sayılmıyor"
                : null;
        }

        $engeller = $payload['blockers'] ?? null;

        if (! is_array($engeller) || $engeller === []) {
            return null;
        }

        $adlar = [
            'onayli_gorsel_yok' => 'onaylı görseli yok',
            'aciklama_ince' => 'açıklaması ince',
            'malzeme_yok' => 'malzemesi yok',
            'yer_tutucu_malzeme' => 'yer tutucu malzeme',
            'adim_yok' => 'adımı yok',
        ];

        $anahtar = array_key_first($engeller);
        $sayi = (int) $engeller[$anahtar];
        $ad = $adlar[$anahtar] ?? $anahtar;

        // Tek sebep her şeyi açıklıyorsa bunu söylemek, listelemekten iyi.
        return count($engeller) === 1
            ? "hepsinin sebebi aynı: {$ad}"
            : "en büyüğü: {$sayi} tanesinin {$ad}";
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
