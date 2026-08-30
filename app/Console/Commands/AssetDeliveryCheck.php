<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Integrity\AssetDeliveryProbe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Yayındaki varlıkların tarayıcıya doğru içerik türüyle ulaştığını denetler.
 *
 * Örneklem sayfanın kendisinden geliyor: ziyaretçinin gördüğü sayfa
 * çekiliyor ve istediği varlık adresleri oradan çıkarılıyor. Elle tutulan
 * bir liste şablondan kayıyor ve kimsenin talep etmediği adresi ölçüyor.
 */
final class AssetDeliveryCheck extends Command
{
    protected $signature = 'assets:delivery-check {--limit=14 : Denetlenecek en fazla varlık sayısı} {--json : Makine okunabilir çıktı}';

    protected $description = 'Yayındaki varlıkların içerik türünü denetler; uzantı tanınmazsa tarayıcı görseli çözümleyemez.';

    public function handle(AssetDeliveryProbe $probe): int
    {
        $adresler = [];

        foreach ([route('home'), route('recipes.index')] as $sayfa) {
            $adresler = [...$adresler, ...$this->sayfadanAdresler($probe, $sayfa)];
        }

        $adresler = array_slice(array_values(array_unique($adresler)), 0, max(1, (int) $this->option('limit')));

        if ($adresler === []) {
            // Sayfa çekilemediyse "her şey yolunda" demek yanlış olur.
            $this->cikti(['status' => 'failed', 'checked' => 0, 'failed' => 0, 'reason' => 'NO_ASSETS_DISCOVERED', 'results' => []]);

            return self::SUCCESS;
        }

        $this->cikti($probe->probe($adresler));

        return self::SUCCESS;
    }

    /** @return list<string> */
    private function sayfadanAdresler(AssetDeliveryProbe $probe, string $sayfa): array
    {
        try {
            $cevap = Http::timeout(15)->get($sayfa);
        } catch (\Throwable) {
            return [];
        }

        return $cevap->successful() ? $probe->adresler($cevap->body(), rtrim(config('app.url'), '/')) : [];
    }

    /** @param array<string, mixed> $rapor */
    private function cikti(array $rapor): void
    {
        if ($this->option('json')) {
            $this->line((string) json_encode($rapor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return;
        }

        if (($rapor['failed'] ?? 0) === 0) {
            $this->info("{$rapor['checked']} varlık doğru içerik türüyle servis ediliyor.");

            return;
        }

        $this->warn("{$rapor['failed']}/{$rapor['checked']} varlık hatalı:");

        foreach ($rapor['results'] as $satir) {
            if ($satir['status'] === 'failed') {
                $this->line('  '.$satir['url'].' -> '.$satir['reason'].' ('.($satir['actual'] ?? '-').')');
            }
        }
    }

}
