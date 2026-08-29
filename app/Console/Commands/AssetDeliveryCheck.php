<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\Integrity\AssetDeliveryProbe;
use Illuminate\Console\Command;

/**
 * Yayındaki varlıkların tarayıcıya doğru içerik türüyle ulaştığını denetler.
 *
 * Örneklem sabit değil: hero dosyaları her zaman, ayrıca yayındaki
 * medyadan birkaç gerçek varyant. Böylece yeni yüklenen görseller de
 * kapsama giriyor.
 */
final class AssetDeliveryCheck extends Command
{
    protected $signature = 'assets:delivery-check {--limit=6 : Medya varyantı örneklem sayısı} {--json : Makine okunabilir çıktı}';

    protected $description = 'Yayındaki varlıkların içerik türünü denetler; uzantı tanınmazsa tarayıcı görseli çözümleyemez.';

    public function handle(AssetDeliveryProbe $probe): int
    {
        $adresler = $this->heroDosyalari();

        foreach ($this->medyaOrneklemi((int) $this->option('limit')) as $adres) {
            $adresler[] = $adres;
        }

        $rapor = $probe->probe(array_values(array_unique($adresler)));

        if ($this->option('json')) {
            $this->line((string) json_encode($rapor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } elseif ($rapor['failed'] === 0) {
            $this->info("{$rapor['checked']} varlık doğru içerik türüyle servis ediliyor.");
        } else {
            $this->warn("{$rapor['failed']}/{$rapor['checked']} varlık hatalı:");

            foreach ($rapor['results'] as $satir) {
                if ($satir['status'] === 'failed') {
                    $this->line('  '.$satir['url'].' -> '.$satir['reason'].' ('.($satir['actual'] ?? '-').')');
                }
            }
        }

        // Çıkış kodu bilinçli olarak başarı: bu bir gözlem, deploy kapısı
        // değil. Kapı yapılacaksa ayrı ve açık bir karar olmalı.
        return self::SUCCESS;
    }

    /** @return list<string> */
    private function heroDosyalari(): array
    {
        return collect(['avif', 'webp'])
            ->crossJoin([900, 1440, 1717])
            ->map(fn (array $c): string => asset("images/house-chef-hero-v2-{$c[1]}.{$c[0]}"))
            ->push(asset('images/house-chef-hero-v2.png'))
            ->all();
    }

    /** @return list<string> */
    private function medyaOrneklemi(int $adet): array
    {
        return Media::query()
            ->where('visibility', 'public')
            ->whereNotNull('responsive_variants')
            ->latest('id')
            ->limit(max(1, $adet))
            ->get()
            ->flatMap(function (Media $media): array {
                $yollar = [];

                foreach ((array) $media->responsive_variants as $varyant) {
                    foreach (['avif', 'webp', 'jpeg'] as $bicim) {
                        $yol = is_array($varyant) ? ($varyant[$bicim] ?? null) : null;

                        if (is_string($yol) && $yol !== '') {
                            $yollar[] = asset('storage/'.$yol);
                            break;
                        }
                    }
                }

                return $yollar;
            })
            ->unique()
            ->take(max(1, $adet))
            ->values()
            ->all();
    }
}
