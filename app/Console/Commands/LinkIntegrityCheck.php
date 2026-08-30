<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Http\Controllers\RecipeCollectionLandingController;
use App\Services\Integrity\IntegrityReportLog;
use App\Services\Integrity\LinkIntegrityProbe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

/**
 * Sitenin kendi iç bağlantılarını denetler.
 *
 * Taranan sayfalar hub'lar: buradan çıkan bağlantılar sitenin gezinme
 * omurgası. Tek tek 129 tarifi taramıyor - amaç tam kapsama değil, omurgadaki
 * kusuru yakalamak; üretimdeki iki bozuk bağlantı da anasayfadaydı.
 */
final class LinkIntegrityCheck extends Command
{
    protected $signature = 'content:link-integrity {--limit=60 : Denetlenecek en fazla bağlantı} {--json : Makine okunabilir çıktı}';

    protected $description = 'İç bağlantılar 404 veriyor mu, katalog filtreleri boş mu döndürüyor - ikisini de denetler.';

    public function handle(LinkIntegrityProbe $probe, IntegrityReportLog $gunluk): int
    {
        $koleksiyonlar = RecipeCollectionLandingController::sayfasiOlanlar()
            ->pluck('url')
            ->all();
        $rapor = $probe->probe($this->hublar(), (int) $this->option('limit'), $koleksiyonlar);

        // Koşu kaydediliyor: tek bir sayı ancak bir öncekiyle yan yana
        // konduğunda sitenin iyileşip iyileşmediğini gösteriyor.
        $gunluk->record('content:link-integrity', (string) $rapor['status'], (int) $rapor['broken'], $rapor);

        if ($this->option('json')) {
            $this->line((string) json_encode($rapor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        if ($rapor['broken'] === 0) {
            $this->info("{$rapor['checked']} bağlantının hepsi bir yere götürüyor.");

            return self::SUCCESS;
        }

        $this->warn("{$rapor['broken']}/{$rapor['checked']} bağlantı kusurlu:");

        foreach ($rapor['findings'] as $bulgu) {
            $this->line('  '.$bulgu['url'].' -> '.$bulgu['reason']);
        }

        // Çıkış kodu bilinçli olarak başarı: bu bir gözlem, deploy kapısı
        // değil.
        return self::SUCCESS;
    }

    /** @return list<string> */
    private function hublar(): array
    {
        $hublar = [route('home'), route('recipes.index'), route('blog.index')];

        foreach (['cuisines.index', 'ingredients.hub.index'] as $ad) {
            if (Route::has($ad)) {
                $hublar[] = route($ad);
            }
        }

        return $hublar;
    }
}
