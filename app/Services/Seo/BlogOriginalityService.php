<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use Illuminate\Support\Collection;

/**
 * Yazılar arasında birebir tekrar eden paragrafları ölçer.
 *
 * Neden var: `originality_status` alanı bir kapıydı ama hiçbir şey onu
 * ölçmüyordu. Otomatik yayın kapısı yazıyı yayına alırken alanı kendisi
 * "passed" yapıyordu. 14 Eylül 2026'da ölçüldü: 13 Eylül'de tek günde
 * yayınlanan 100 `geo-2026-*` yazısının hepsi "özgün" işaretliydi; 800 uzun
 * paragrafın 675'i başka yazılarda birebir geçiyordu, 80 yazının özeti aynı
 * cümleyle başlıyordu. Google bunu ölçekli içerik kötüye kullanımı sayar ve
 * cezası yalnız o sayfalara değil siteye iner. Bu site 16 Ağustos 2026'da
 * aynı sebeple bir kez zarar gördü.
 *
 * Ölçüt kasten basit ve açıklanabilir: normalize edilmiş, 120 karakterden uzun
 * paragrafın başka bir yayındaki yazıda da geçip geçmediği. Anlamsal benzerlik
 * tahmini değil, birebir tekrar. Yanlış pozitif üretmesi zor; ürettiği sonuç
 * editörün elle doğrulayabileceği bir gerçek.
 */
final class BlogOriginalityService
{
    /** Bu uzunluğun altındaki paragraflar (başlık, tek cümle) sayılmaz. */
    public const ASGARI_PARAGRAF = 120;

    /** Uzun paragraflarının bu oranı başka yazıda geçen yazı tekrar sayılır. */
    public const TEKRAR_ESIGI = 0.4;

    /** Bu kadar uzun paragrafı olmayan yazı ölçülmez; oran anlamsız olur. */
    public const ASGARI_PARAGRAF_SAYISI = 3;

    /**
     * Her yayındaki yazı için tekrar oranı.
     *
     * @return array<int, array{paragraphs: int, repeated: int, share: float}>
     */
    public function olc(?Collection $yazilar = null): array
    {
        $yazilar ??= BlogPost::query()->publiclyVisible()->with('seoMetadata')->get(['id', 'slug', 'body', 'published_at', 'canonical_url']);

        // Paragraf ilk yayınlanan yazınındır; tekrar sayılan yalnız sonrakilerdir.
        // 14 Eylül 2026'da ölçüldü: 20 yazının "-XXXXX" son ekli birebir kopyası
        // vardı ve eski kural kopyayla birlikte orijinali de indeksten düşürüyordu.
        //
        // Kanoniği başka bir yazıyı gösteren yazı sahipliği önce ona bırakır:
        // son ekli kopyalar tarihçe daha eskiydi ama kanonikleri temiz adresi
        // gösteriyordu. Tarihe bakılsa indeksten düşen temiz adres olurdu.
        // Yayında olmayan aday (published_at boş) en sona konur.
        // Kanonik iki yerde tutulabiliyor: yazının kendi alanında ve SEO
        // meta kaydında. 20 Eylül 2026'da yineleme birleştirmesi ikincisini
        // yazdı; servis yalnız birincisine baktığı için sitenin en çok
        // gösterim alan iki sayfası kendi eski kopyalarının tekrarı sayıldı
        // ve gece taraması onları indeksten düşürecekti.
        $yazilar = $yazilar->sortBy(fn (BlogPost $y): array => [
            $this->sahipligiBirakir($y) ? 1 : 0,
            $y->published_at?->getTimestamp() ?? PHP_INT_MAX,
            $y->getKey() ?? PHP_INT_MAX,
        ])->values();

        $sahip = [];
        $yaziParagraflari = [];

        foreach ($yazilar as $yazi) {
            $izler = array_values(array_unique($this->parmakIzleri((string) $yazi->getRawOriginal('body'))));
            $yaziParagraflari[$yazi->id] = $izler;

            foreach ($izler as $iz) {
                $sahip[$iz] ??= $yazi->id;
            }
        }

        $sonuc = [];

        foreach ($yaziParagraflari as $id => $izler) {
            $tekrar = count(array_filter($izler, static fn (string $iz): bool => $sahip[$iz] !== $id));
            $sonuc[$id] = [
                'paragraphs' => count($izler),
                'repeated' => $tekrar,
                'share' => $izler === [] ? 0.0 : round($tekrar / count($izler), 3),
            ];
        }

        return $sonuc;
    }

    /** @param array{paragraphs: int, repeated: int, share: float} $olcum */
    public function tekrarMi(array $olcum): bool
    {
        return $olcum['paragraphs'] >= self::ASGARI_PARAGRAF_SAYISI && $olcum['share'] >= self::TEKRAR_ESIGI;
    }

    /**
     * Henüz yayında olmayan bir yazının yayındaki yazılarla tekrar oranı.
     *
     * Yayın kapısı bunu kullanır: taslağı yayına almadan önce korpusa
     * eklenmiş gibi ölçer.
     *
     * @return array{paragraphs: int, repeated: int, share: float}
     */
    public function adayiOlc(BlogPost $aday): array
    {
        $korpus = BlogPost::query()->publiclyVisible()->whereKeyNot($aday->getKey())->get(['id', 'slug', 'body', 'published_at', 'canonical_url']);

        // Aday yayındaki her yazıdan sonra gelir; kendi yayın tarihi ne olursa olsun.
        $olcumAdayi = (clone $aday)->setAttribute('published_at', null);

        return $this->olc($korpus->push($olcumAdayi))[$aday->getKey()] ?? ['paragraphs' => 0, 'repeated' => 0, 'share' => 0.0];
    }

    /** @return list<string> */
    private function parmakIzleri(string $govde): array
    {
        $metin = preg_replace('/<\/(p|li|h[1-6]|blockquote|div)>/i', "\n\n", $govde) ?? $govde;
        $metin = html_entity_decode(strip_tags($metin), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $izler = [];

        foreach (preg_split('/\n\s*\n/u', $metin) ?: [] as $paragraf) {
            $sade = mb_strtolower(trim($paragraf));
            $sade = preg_replace('/[^\p{L}\p{N} ]+/u', ' ', $sade) ?? $sade;
            $sade = trim(preg_replace('/\s+/u', ' ', $sade) ?? $sade);

            if (mb_strlen($sade) >= self::ASGARI_PARAGRAF) {
                $izler[] = hash('sha256', $sade);
            }
        }

        return $izler;
    }

    /**
     * Bu yazı paragrafların sahipliğini başkasına bırakır mı?
     *
     * Kanoniği başka bir yazıyı gösteren ya da indekse kapalı nüsha
     * sahiplik iddia edemez: arama sonucunda görünen o değil, gösterdiği
     * adrestir.
     */
    private function sahipligiBirakir(BlogPost $yazi): bool
    {
        foreach ([$yazi->canonical_url, $yazi->seoMetadata?->canonical_override] as $kanonik) {
            if (filled($kanonik) && ! str_ends_with((string) $kanonik, '/blog/'.$yazi->slug)) {
                return true;
            }
        }

        return str_contains((string) $yazi->seoMetadata?->robots, 'noindex');
    }
}
