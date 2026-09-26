<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Recipe;
use App\Services\Coni\ClaudeConi;
use App\Services\Seo\RecipeTranslationGate;
use Illuminate\Console\Command;
use InvalidArgumentException;
use JsonException;

/**
 * Bir klasördeki çevirileri tarif tarif, dil dil uygular.
 *
 * Dosya adı tarifin slug'ı, içeriği {"<dil>": {title, excerpt, description,
 * step_titles, steps}}. Her çift coni:ceviri ile aynı yoldan geçer: kalıp
 * denetimi, eski değerin saklanması, defter kaydı. Adımları zaten gerçek
 * çeviri olan çift atlanır; elle yazılmış bir çeviri ezilmez.
 */
final class ConiCeviriToplu extends Command
{
    protected $signature = 'coni:ceviri-toplu {klasor} {--neden=Kalıp çeviri gerçeğiyle değiştirildi} {--zorla : Gerçek çeviriyi de üzerine yaz}';

    protected $description = 'Claude Coni: klasördeki tarif çevirilerini dil dil uygular, kalıp olmayanı ezmez.';

    public function handle(ClaudeConi $coni, RecipeTranslationGate $kapi): int
    {
        $dosyalar = glob(rtrim((string) $this->argument('klasor'), '/').'/*.json') ?: [];
        $sayac = ['yazildi' => 0, 'atlandi' => 0, 'hata' => 0, 'acildi' => 0];

        foreach ($dosyalar as $dosya) {
            $slug = basename($dosya, '.json');

            try {
                $diller = json_decode((string) file_get_contents($dosya), true, 16, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                $this->components->error($slug.': '.$e->getMessage());
                $sayac['hata']++;

                continue;
            }

            foreach ((array) $diller as $dil => $ceviri) {
                $tarif = Recipe::query()->where('slug', $slug)->first();

                if ($tarif !== null && ! $this->option('zorla') && $kapi->engel($tarif, (string) $dil) === null && $tarif->hasTranslation((string) $dil)) {
                    $sayac['atlandi']++;

                    continue;
                }

                try {
                    $sonuc = $coni->ceviri($slug, (string) $dil, (array) $ceviri, (string) $this->option('neden'));
                } catch (InvalidArgumentException $e) {
                    $this->components->error($slug.' '.$dil.': '.$e->getMessage());
                    $sayac['hata']++;

                    continue;
                }

                $sayac['yazildi']++;
                $sayac['acildi'] += (int) ($sonuc['engel_once'] !== null && $sonuc['engel_sonra'] === null);
            }
        }

        $this->components->info(sprintf('%d yazıldı, %d dizine açıldı, %d atlandı, %d hata.', $sayac['yazildi'], $sayac['acildi'], $sayac['atlandi'], $sayac['hata']));

        return $sayac['hata'] === 0 ? self::SUCCESS : self::FAILURE;
    }
}
