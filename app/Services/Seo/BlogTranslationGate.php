<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\ContentTranslation;

/**
 * Yabancı dildeki yazı ancak gerçekten çevrilmişse o dilde vardır.
 *
 * 28 Eylül 2026'da ölçüldü: Almancada 113 yazının 110'u, Farsçada 140'ın
 * 138'i kalıptı. Başlık Türkçe bırakılıp arkasına kalıp bir ek konmuştu
 * ("Zeytinyağlı Yaprak Sarma Püf Noktaları – Umfassender Ratgeber &
 * Küchenpraxis"); gövde her yazıda aynı genel cümlelerdi, yalnız konu adı
 * değişiyordu ("Entdecken Sie fundierte kulinarische Einblicke ... zu
 * <konu>"). AdSense siteyi "düşük değerli içerik" diye reddetmişti ve bu
 * sayfalar herkese açıktı.
 *
 * İki kural:
 * - Çevrilmiş başlık Türkçe başlığı olduğu gibi taşıyorsa başlık çevrilmemiştir.
 * - Gövdenin cümleleri, konu adı çıkarıldıktan sonra aynı dildeki başka
 *   yazılarda da geçiyorsa gövde kalıptır.
 */
final class BlogTranslationGate
{
    /** Cümlenin kalıp sayılması için geçtiği asgari yazı sayısı (kendisi hariç). */
    public const TEKRAR_YAZI = 3;

    /** Gövde cümlelerinin bu oranı kalıpsa gövde kalıptır. */
    public const KALIP_ORANI = 0.5;

    public const ASGARI_CUMLE = 40;

    /** @var array<string, list<int>> */
    private array $sonuclar = [];

    /** @return list<int> Bu dilde kalıp çevirisi olan yazıların kimlikleri. */
    public function kalipYazilar(string $dil): array
    {
        if ($dil === (string) config('locales.default', 'tr')) {
            return [];
        }

        return $this->sonuclar[$dil] ??= cache()->remember('blog-kalip-ceviri:'.$dil, now()->addMinutes(10), fn (): array => $this->hesapla($dil));
    }

    public function kalipMi(BlogPost $yazi, string $dil): bool
    {
        return in_array((int) $yazi->getKey(), $this->kalipYazilar($dil), true);
    }

    /** @return list<int> */
    private function hesapla(string $dil): array
    {
        $tur = (new BlogPost)->getMorphClass();
        $satirlar = ContentTranslation::query()
            ->where('translatable_type', $tur)
            ->where('locale', $dil)
            ->whereIn('field', ['title', 'body'])
            ->get(['translatable_id', 'field', 'value'])
            ->groupBy('translatable_id');

        $kaynakBasliklar = BlogPost::query()->whereIn('id', $satirlar->keys())->pluck('title', 'id');

        $kalip = [];
        $cumleler = [];

        foreach ($satirlar as $id => $alanlar) {
            $kaynak = trim((string) ($kaynakBasliklar[$id] ?? ''));
            $baslik = trim((string) $alanlar->firstWhere('field', 'title')?->value);

            if ($kaynak !== '' && mb_strlen($kaynak) >= 12 && str_contains($baslik, $kaynak)) {
                $kalip[] = (int) $id;
            }

            $cumleler[$id] = $this->cumleler((string) $alanlar->firstWhere('field', 'body')?->value, [$kaynak, $baslik]);
        }

        $sahipler = [];

        foreach ($cumleler as $id => $izler) {
            foreach (array_unique($izler) as $iz) {
                $sahipler[$iz][] = (int) $id;
            }
        }

        foreach ($cumleler as $id => $izler) {
            if ($izler === []) {
                continue;
            }

            $tekrar = count(array_filter($izler, static fn (string $iz): bool => count($sahipler[$iz]) > self::TEKRAR_YAZI));

            if ($tekrar / count($izler) >= self::KALIP_ORANI) {
                $kalip[] = (int) $id;
            }
        }

        return array_values(array_unique($kalip));
    }

    /**
     * @param  list<string>  $cikarilacak  Konu adları; kalıpta her yazıda farklı olan tek şey.
     * @return list<string>
     */
    private function cumleler(string $govde, array $cikarilacak): array
    {
        $metin = strip_tags($govde);

        foreach (array_filter($cikarilacak) as $ad) {
            $metin = str_replace($ad, ' ', $metin);
        }

        $izler = [];

        foreach (preg_split('/(?<=[.!?؟])\s+|\n+/u', $metin) ?: [] as $cumle) {
            $sade = mb_strtolower(trim((string) preg_replace('/[^\p{L}\p{N} ]+/u', ' ', $cumle)));
            $sade = trim((string) preg_replace('/\s+/u', ' ', $sade));

            if (mb_strlen($sade) >= self::ASGARI_CUMLE) {
                $izler[] = hash('xxh64', $sade);
            }
        }

        return $izler;
    }
}
