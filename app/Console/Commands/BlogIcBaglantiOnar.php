<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Services\Integrity\IntegrityReportLog;
use App\Services\Seo\LocaleUrlService;
use App\Services\Seo\YaziIcBaglantilari;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Yayındaki yazıların gövde bağlantılarını o dildeki canlı adrese çevirir.
 *
 * Hedefi yayındaysa yönlendirmesiz adrese, çevirisi varsa gövdenin diline
 * yazılır. Açık sayfası olmayan bağlantı kaldırılır, çapa metni kalır.
 * Aynı yemeğin başka adreste yayında olduğu durum otomatik bilinemez:
 * --harita ile verilir (eski adres => yeni Türkçe adres, null = kaldır).
 */
final class BlogIcBaglantiOnar extends Command
{
    protected $signature = 'blog:ic-baglanti-onar
        {--harita= : JSON dosyası: {"/tarifler/eski": "/tarifler/yeni", "/en/private-chef": null}}
        {--apply : Yaz (yoksa yalnız rapor)}';

    protected $description = 'Yazı gövdelerindeki tarif ve yazı bağlantılarını o dildeki canlı adrese çevirir, ölü olanı kaldırır.';

    public function handle(YaziIcBaglantilari $baglantilar, LocaleUrlService $diller, IntegrityReportLog $gunluk): int
    {
        $harita = [];
        if (filled($this->option('harita'))) {
            $harita = json_decode((string) file_get_contents((string) $this->option('harita')), true, flags: JSON_THROW_ON_ERROR);
        }

        $varsayilan = $diller->defaultLocale();
        $satirlar = [];
        $yazilacak = [];

        foreach (BlogPost::query()->publiclyVisible()->orderBy('id')->get() as $yazi) {
            $govdeler = [$varsayilan => [null, (string) $yazi->getRawOriginal('body')]];
            foreach ($yazi->translations()->where('field', 'body')->get() as $ceviri) {
                if (isset($diller->enabledLocales()[$ceviri->locale])) {
                    $govdeler[$ceviri->locale] = [$ceviri, (string) $ceviri->value];
                }
            }

            foreach ($govdeler as $dil => [$ceviri, $metin]) {
                $yeni = (string) preg_replace_callback(YaziIcBaglantilari::DESEN, function (array $e) use ($baglantilar, $harita, $dil, $yazi, &$satirlar): string {
                    $adres = (string) preg_replace('#^https?://(?:www\.)?housechefcyprus\.com#', '', $e[2]);
                    $hedef = array_key_exists($adres, $harita)
                        ? ($harita[$adres] === null ? null : $baglantilar->hedef((string) $harita[$adres], $dil))
                        : $baglantilar->hedef($adres, $dil);

                    if ($hedef === $adres) {
                        return $e[0];
                    }

                    $satirlar[] = [$yazi->id, $dil, $adres, $hedef ?? '(kaldırıldı)'];

                    return $hedef === null ? $e[1] : '['.$e[1].']('.$hedef.($e[3] ?? '').')';
                }, $metin);

                if ($yeni !== $metin) {
                    $yazilacak[] = ['yazi' => $yazi, 'ceviri' => $ceviri, 'dil' => $dil, 'once' => $metin, 'sonra' => $yeni];
                }
            }
        }

        $this->table(['yazı', 'dil', 'adres', 'yeni'], $satirlar);
        $kaldirilan = count(array_filter($satirlar, static fn (array $s): bool => $s[3] === '(kaldırıldı)'));
        $this->info(count($satirlar).' bağlantı ('.$kaldirilan.' kaldırılacak), '.count($yazilacak).' gövde.');

        if (! $this->option('apply') || $yazilacak === []) {
            $this->line('Kuru çalıştırma. Yazmak için --apply.');

            return self::SUCCESS;
        }

        $yedek = 'yedek/ic-baglanti-onar-'.now()->format('Ymd-His').'.json';
        Storage::disk('local')->put($yedek, (string) json_encode(array_map(static fn (array $g): array => [
            'blog_post_id' => $g['yazi']->id, 'dil' => $g['dil'], 'content_translation_id' => $g['ceviri']?->id, 'once' => $g['once'],
        ], $yazilacak), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        foreach ($yazilacak as $g) {
            if ($g['ceviri'] === null) {
                $g['yazi']->forceFill(['body' => $g['sonra']])->save();
            } else {
                $g['ceviri']->forceFill(['value' => $g['sonra']])->save();
            }
        }

        $gunluk->record('blog:ic-baglanti-onar', 'ok', count($satirlar), [
            'yedek' => $yedek,
            'baglanti' => count($satirlar),
            'kaldirilan' => $kaldirilan,
            'govde' => count($yazilacak),
        ]);

        $this->info('Yazıldı. Yedek: '.Storage::disk('local')->path($yedek));

        return self::SUCCESS;
    }
}
