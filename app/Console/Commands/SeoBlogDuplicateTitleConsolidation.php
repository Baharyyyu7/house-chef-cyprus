<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;

/**
 * Aynı SEO başlığını taşıyan iki yazıyı tek adreste toplar.
 *
 * 20 Eylül 2026'da ölçüldü: 26 konuda aynı başlıkla iki yazı yayındaydı.
 * Her çiftin biri "-XXXXX" son ekli ve daha uzun, diğeri temiz adresli ve
 * daha kısaydı; ikisi de indekse açıktı. Google aynı sorgu için iki
 * sayfamızı yan yana koyuyor, ikisi de zayıflıyordu.
 *
 * Yapılan: uzun gövde temiz adrese taşınır, son ekli nüsha noindex olur ve
 * kanonikle temiz adrese bağlanır, yinelenen SEO başlığı ondan alınır.
 * Hiçbir içerik silinmez, hiçbir adres kırılmaz; sinyal tek adreste
 * toplanır.
 */
final class SeoBlogDuplicateTitleConsolidation extends Command
{
    protected $signature = 'seo:blog-duplicate-title-consolidation {--apply : Değişiklikleri yaz. Varsayılan yalnız rapordur.}';

    protected $description = 'Aynı yazının birden çok nüshasını tek kanonik adreste toplar.';

    public function handle(): int
    {
        $yaz = (bool) $this->option('apply');
        $yazilar = BlogPost::query()
            ->whereIn('status', ['published', 'scheduled', 'approved'])
            ->with('seoMetadata')
            ->get();

        $birlesen = [];

        // İki ayrı yineleme biçimi var ve ikisi de aynı sorunu doğuruyor.
        // Aynı SEO başlığı: 20 Eylül 2026'da 26 konuda görüldü. Aynı adres
        // kökü: aynı gün 6 konuda, "-XXXXX" son ekli iki üç nüsha birden
        // indeksteydi ve bunların arasında sitenin en çok gösterim alan iki
        // sayfası da vardı. Başlıkları farklı olduğu için başlık kuralına
        // takılmıyorlardı.
        $gruplar = [];

        foreach ($yazilar as $yazi) {
            if (filled($yazi->meta_title)) {
                $gruplar['baslik:'.$yazi->meta_title][] = $yazi;
            }

            $gruplar['adres:'.preg_replace('/-[A-Z0-9]{5}$/', '', $yazi->slug)][] = $yazi;
        }

        $islenen = [];

        foreach ($gruplar as $baslik => $satirlar) {
            $grup = collect($satirlar);
            if ($grup->count() < 2) {
                continue;
            }

            $asil = $this->asilAdres($grup);

            foreach ($grup as $kopya) {
                if ($kopya->is($asil)) {
                    continue;
                }

                // Daha önce birleştirilmiş nüsha yeniden yazılmaz.
                if (str_contains((string) $kopya->seoMetadata?->robots, 'noindex')
                    && filled($kopya->seoMetadata?->canonical_override)) {
                    continue;
                }

                // Aynı nüsha iki gruptan da gelebilir; bir kez işlenir.
                if (isset($islenen[$kopya->getKey()])) {
                    continue;
                }

                $islenen[$kopya->getKey()] = true;
                $birlesen[] = ['kopya' => $kopya->slug, 'asil' => $asil->slug, 'baslik' => (string) $baslik];

                if (! $yaz) {
                    continue;
                }

                // Uzun olan gövde asıl adreste kalmalı: iyi metnin kötü
                // adreste kalması iki kaybı birden getirir.
                if (mb_strlen((string) $kopya->body) > mb_strlen((string) $asil->body)) {
                    $asil->forceFill(['body' => $kopya->body, 'excerpt' => $kopya->excerpt, 'last_refreshed_at' => now()])->save();
                }

                $kopya->seoMetadata()->updateOrCreate([], [
                    'robots' => 'noindex,follow',
                    'canonical_override' => route('blog.show', $asil),
                ]);

                $kopya->forceFill(['meta_title' => null])->save();
            }
        }

        $this->components->info(count($birlesen).' yinelenen başlıklı yazı tek adreste toplandı.');

        foreach (array_slice($birlesen, 0, 15) as $satir) {
            $this->line('  · '.$satir['kopya'].' → '.$satir['asil']);
        }

        if (! $yaz) {
            $this->line('Yazmak için: php artisan seo:blog-duplicate-title-consolidation --apply');
        }

        return self::SUCCESS;
    }

    /**
     * Hangi adres asıl sayılır?
     *
     * Son eki olmayan temiz adres tercih edilir; ikisi de temizse ilk
     * yayınlanan kalır. Kalıcı adres kararı gövde uzunluğuna bırakılmaz,
     * çünkü gövde zaten asıl adrese taşınıyor.
     *
     * @param  \Illuminate\Support\Collection<int, BlogPost>  $grup
     */
    private function asilAdres($grup): BlogPost
    {
        return $grup->sortBy(static fn (BlogPost $yazi): array => [
            preg_match('/-[A-Z0-9]{5}$/', $yazi->slug) === 1 ? 1 : 0,
            $yazi->published_at?->getTimestamp() ?? PHP_INT_MAX,
            $yazi->getKey(),
        ])->first();
    }
}
