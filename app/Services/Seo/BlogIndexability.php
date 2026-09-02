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

    public function indekslenebilir(BlogPost $yazi): bool
    {
        return $this->engeller($yazi) === [];
    }

    public function robots(BlogPost $yazi): string
    {
        return $this->indekslenebilir($yazi) ? 'index,follow' : 'noindex,follow';
    }

    /** @return list<string> */
    public function engeller(BlogPost $yazi): array
    {
        $engeller = [];

        if (mb_strlen(trim((string) $yazi->body)) < self::ASGARI_KARAKTER) {
            $engeller[] = 'ince';
        }

        if ($yazi->fact_check_status !== 'passed') {
            $engeller[] = 'olgu_denetimi_gecmedi';
        }

        if ($yazi->originality_status !== 'passed') {
            $engeller[] = 'ozgunluk_gecmedi';
        }

        return $engeller;
    }
}
