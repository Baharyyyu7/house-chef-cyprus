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
        $yazilar ??= BlogPost::query()->publiclyVisible()->get(['id', 'body']);

        $parmakIzleri = [];
        $yaziParagraflari = [];

        foreach ($yazilar as $yazi) {
            $izler = array_values(array_unique($this->parmakIzleri((string) $yazi->getRawOriginal('body'))));
            $yaziParagraflari[$yazi->id] = $izler;

            foreach ($izler as $iz) {
                $parmakIzleri[$iz][$yazi->id] = true;
            }
        }

        $sonuc = [];

        foreach ($yaziParagraflari as $id => $izler) {
            $tekrar = count(array_filter($izler, static fn (string $iz): bool => count($parmakIzleri[$iz]) > 1));
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
        $korpus = BlogPost::query()->publiclyVisible()->whereKeyNot($aday->getKey())->get(['id', 'body']);

        return $this->olc($korpus->push($aday))[$aday->getKey()] ?? ['paragraphs' => 0, 'repeated' => 0, 'share' => 0.0];
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
}
