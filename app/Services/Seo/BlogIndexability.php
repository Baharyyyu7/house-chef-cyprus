<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;

/**
 * Editoryal kapılar indekslemeye bağlanır.
 *
 * Kapılar kayıtta vardı ama hiçbir etkisi yoktu: fact_check_status ve
 * originality_status'u "pending" olan 891 karakterlik bir yazı da
 * index,follow olarak site haritasında duruyordu. Muhafız bunu her gece
 * raporluyor, kimse okumadan gün geçiyordu.
 *
 * Bu site bir kez ölçekli zayıf içerik yüzünden ceza yedi (2026-08-16,
 * ~3200 makine sayfası). Bu yüzden ölçüt sıkı: yazı ya kendi kapılarından
 * geçmiştir ya da aranabilir değildir.
 *
 * Yazı yayında ve okunabilir kalıyor - hiçbir bağlantı kırılmıyor, hiçbir
 * içerik silinmiyor. Yalnız arama motoruna "bunu dizine alma" deniyor ve
 * site haritasından çıkarılıyor. Yazı düzelince kural kendiliğinden
 * gevşiyor; ayrıca bir işlem gerekmiyor.
 *
 * "follow" bilinçli: sayfadaki bağlantılar taranmaya devam etsin, yalnız
 * sayfanın kendisi dizine girmesin.
 */
final class BlogIndexability
{
    /** PublishedContentGuard ile aynı eşik; ikisi aynı şeyi ölçüyor. */
    public const ASGARI_KARAKTER = 2000;

    /**
     * Reklam taşımak için gereken asgari gövde.
     *
     * İndekslenebilir olmak ile reklam taşımaya uygun olmak aynı şey değil
     * ve eşiğin tek olması bu ikisini karıştırıyordu. 2000 karakter (~350
     * kelime) bir yazının aramada görünmesi için yeterli olabilir; AdSense
     * politikası "düşük değerli içerik" derken bundan fazlasını kastediyor.
     * Eşik dağılımdan okundu, yuvarlak sayı seçilerek değil. 2026-09-24'te
     * yayındaki 156 yazının gövde uzunluğu iki tepe yapıyor: 3000-3500
     * karakterde sıkışan kalabalık bir küme (makine üretimi parti, hepsi
     * birbirine yakın uzunlukta) ve 4000'in üstünde duran 46 gerçek
     * makale. Sınır tam bu ikisinin arasına düşüyor; ortanca 3375.
     *
     * Hesap o sırada incelemedeydi ve 110 yazı hem indekste hem reklamlı
     * hem de o kümenin içindeydi.
     *
     * Sayfa yayında ve okunabilir kalır; yalnız reklam basılmaz. Yazı
     * derinleşince kural kendiliğinden gevşer.
     */
    public const REKLAM_ASGARI_KARAKTER = 4000;

    public function reklamTasiyabilir(BlogPost $yazi): bool
    {
        return $this->indekslenebilir($yazi)
            && mb_strlen(trim(strip_tags((string) $yazi->translated('body')))) >= self::REKLAM_ASGARI_KARAKTER;
    }

    public function indekslenebilir(BlogPost $yazi): bool
    {
        return $this->engeller($yazi) === [];
    }

    public function robots(BlogPost $yazi): string
    {
        $metadata = $yazi->relationLoaded('seoMetadata') ? $yazi->getRelation('seoMetadata') : $yazi->seoMetadata()->first();

        return app(SeoMetadataService::class)->robots($metadata?->robots, $this->indekslenebilir($yazi) ? 'index,follow' : 'noindex,follow');
    }

    /** @return list<string> */
    public function engeller(BlogPost $yazi): array
    {
        $engeller = [];

        if (mb_strlen(trim(strip_tags((string) $yazi->translated('body')))) < self::ASGARI_KARAKTER) {
            $engeller[] = 'ince';
        }

        if (! in_array($yazi->fact_check_status, ['passed', 'verified'], true)) {
            $engeller[] = 'olgu_denetimi_gecmedi';
        }

        if (! in_array($yazi->originality_status, ['passed', 'clean', 'original'], true)) {
            $engeller[] = 'ozgunluk_gecmedi';
        }

        return $engeller;
    }
}
