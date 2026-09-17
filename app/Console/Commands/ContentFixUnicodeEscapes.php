<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\ContentTranslation;
use App\Models\Recipe;
use App\Models\RecipeStep;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

/**
 * Metne düz yazı olarak sızmış \uXXXX kaçış dizilerini çözer.
 *
 * 17 Eylül 2026'da blog listesinde başlıklar "Kıbrıs Mutfağı"
 * diye görünüyordu: içerik JSON olarak iki kez kodlanmış, çözülmeden
 * kaydedilmişti. Ziyaretçi de arama motoru da bu metni böyle okuyor.
 *
 * Yalnız \uXXXX kalıbına dokunur; vekil çiftleri (emoji) birleştirir.
 * Başka hiçbir karakteri değiştirmez.
 */
final class ContentFixUnicodeEscapes extends Command
{
    protected $signature = 'content:fix-unicode-escapes {--apply : Değişiklikleri yaz. Varsayılan yalnız rapordur.}';

    protected $description = 'Metne sızmış \uXXXX kaçış dizilerini çözer.';

    /** @var array<class-string<Model>, list<string>> */
    private const ALANLAR = [
        BlogPost::class => ['title', 'excerpt', 'body', 'meta_title', 'meta_description'],
        Recipe::class => ['title', 'excerpt', 'description'],
        RecipeStep::class => ['title', 'instruction'],
        ContentTranslation::class => ['value'],
    ];

    public function handle(): int
    {
        $yaz = (bool) $this->option('apply');
        $toplam = 0;

        foreach (self::ALANLAR as $sinif => $alanlar) {
            $sorgu = $sinif::query();
            $sorgu->where(function ($q) use ($alanlar): void {
                foreach ($alanlar as $alan) {
                    $q->orWhere($alan, 'like', '%\\\\u0%');
                }
            });

            foreach ($sorgu->get() as $kayit) {
                $degisiklik = [];

                foreach ($alanlar as $alan) {
                    $eski = $kayit->getRawOriginal($alan);

                    if (! is_string($eski) || $eski === '') {
                        continue;
                    }

                    $yeni = $this->coz($eski);

                    if ($yeni !== $eski) {
                        $degisiklik[$alan] = $yeni;
                    }
                }

                if ($degisiklik === []) {
                    continue;
                }

                $toplam++;

                $this->line('  · '.class_basename($sinif).' #'.$kayit->getKey().' — '.implode(', ', array_keys($degisiklik)));

                if ($yaz) {
                    $kayit->forceFill($degisiklik)->save();
                }
            }
        }

        $this->components->info(($yaz ? 'Onarılan' : 'Onarılacak').' kayıt: '.$toplam);

        if (! $yaz && $toplam > 0) {
            $this->line('Yazmak için: php artisan content:fix-unicode-escapes --apply');
        }

        return self::SUCCESS;
    }

    public function coz(string $metin): string
    {
        return preg_replace_callback(
            '/\\\\u([dD][89abAB][0-9a-fA-F]{2})\\\\u([dD][c-fC-F][0-9a-fA-F]{2})|\\\\u([0-9a-fA-F]{4})/',
            static function (array $e): string {
                // Vekil çift: iki yarım kod tek karakteri gösterir (emoji).
                if (($e[1] ?? '') !== '') {
                    $yuksek = hexdec($e[1]);
                    $dusuk = hexdec($e[2]);

                    return mb_chr(0x10000 + (($yuksek - 0xD800) << 10) + ($dusuk - 0xDC00), 'UTF-8');
                }

                return mb_chr((int) hexdec($e[3]), 'UTF-8');
            },
            $metin
        ) ?? $metin;
    }
}
