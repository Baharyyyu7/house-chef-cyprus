<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Services\Seo\SeoMetadataService;
use Illuminate\Console\Command;

/**
 * "Taslak İnceleme" kopyalarını arama sonuçlarından çeker.
 *
 * 17 Eylül 2026'da ölçüldü: yayında 20 yazının adresi
 * "...-taslak-inceleme-XXXXX" ile bitiyordu ve başlıklarında "(Taslak
 * İnceleme)" yazıyordu. Bunların 14'ünün temiz adresli eşi de yayındaydı:
 * aynı konu iki adreste birbiriyle yarışıyordu.
 *
 * Eşi olan kopya noindex olur ve kanonik olarak eşini gösterir; eşi olmayan
 * yazı indekste kalır, yalnız başlığındaki iç not temizlenir. Hiçbir içerik
 * silinmez, hiçbir adres kırılmaz.
 */
final class SeoReviewDraftCleanup extends Command
{
    protected $signature = 'seo:review-draft-cleanup {--apply : Değişiklikleri yaz. Varsayılan yalnız rapordur.}';

    protected $description = '"Taslak İnceleme" kopyalarını indeks dışına alır, eşi olmayanın başlığını temizler.';

    private const SON_EK = '/-taslak-inceleme-[A-Za-z0-9]{5}$/';

    private const BASLIK_NOTU = '/\s*\((?:Taslak İnceleme|Taslak Inceleme)\)\s*/u';

    public function handle(SeoMetadataService $meta): int
    {
        $yaz = (bool) $this->option('apply');
        $indeksDisi = [];
        $baslikTemizlenen = [];

        foreach (BlogPost::query()->publiclyVisible()->with('seoMetadata')->get() as $yazi) {
            if (preg_match(self::SON_EK, (string) $yazi->slug) !== 1) {
                continue;
            }

            $temizSlug = (string) preg_replace(self::SON_EK, '', (string) $yazi->slug);
            $es = BlogPost::query()->publiclyVisible()->where('slug', $temizSlug)->first();

            // Başlıktaki iç not her durumda temizlenir: indeks dışına alınan
            // kopya bile ilgili yazı kartlarında ziyaretçiye görünüyor.
            $yeniBaslik = trim((string) preg_replace(self::BASLIK_NOTU, ' ', (string) $yazi->title));

            if ($yeniBaslik !== '' && $yeniBaslik !== $yazi->title) {
                $baslikTemizlenen[] = $yazi->slug;

                if ($yaz) {
                    $yazi->forceFill(['title' => $yeniBaslik])->save();
                }
            }

            if ($es === null) {
                continue;
            }

            $indeksDisi[] = $yazi->slug;

            if ($yaz) {
                $meta->update($yazi, [
                    'robots' => 'noindex,follow',
                    'canonical_override' => route('blog.show', $es),
                ]);
            }
        }

        $this->components->info(count($indeksDisi).' kopya indeks dışına alınacak, '.count($baslikTemizlenen).' başlıktaki iç not temizlenecek.');

        foreach (array_slice($indeksDisi, 0, 5) as $slug) {
            $this->line('  · kopya: '.$slug);
        }

        foreach (array_slice($baslikTemizlenen, 0, 5) as $slug) {
            $this->line('  · başlık: '.$slug);
        }

        if (! $yaz) {
            $this->line('Yazmak için: php artisan seo:review-draft-cleanup --apply');
        }

        return self::SUCCESS;
    }
}
