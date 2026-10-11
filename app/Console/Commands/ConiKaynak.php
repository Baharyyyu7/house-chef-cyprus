<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\IntegrityReport;
use App\Models\Recipe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use JsonException;

/**
 * Yayındaki tarifin Türkçe metnindeki tek tek alanları düzeltir.
 *
 * 11 Ekim 2026 çeviri turunda Türkçe asılda sayfada görünen çelişkiler
 * bulundu: açıklama "krema yok" deyip son adımda krema eklemek, başlıkta
 * taze nane yazıp malzemede kuru nane kullanmak. content:import-recipes
 * adımları silip yeniden kurduğu için altı dildeki çeviriyi de siliyor;
 * bu komut yalnız verilen alanları yazar, adım kimlikleri ve çeviriler
 * yerinde kalır.
 *
 * Dosya: {"<slug>": {"title"?, "excerpt"?, "description"?, "steps"?: {"<sıra>": {"title"?, "instruction"?, "chef_tip"?}}}}
 * Eski değerler raporda saklanır; --geri-al=<rapor id> aynen geri koyar.
 */
final class ConiKaynak extends Command
{
    protected $signature = 'coni:kaynak {dosya? : JSON dosyası} {--geri-al= : Geri alınacak rapor id}';

    protected $description = 'Claude Coni: yayındaki tarifin Türkçe alanlarını düzeltir, eskisini saklar.';

    private const TARIF_ALANLARI = ['title', 'excerpt', 'description'];

    private const ADIM_ALANLARI = ['title', 'instruction', 'chef_tip'];

    public function handle(): int
    {
        if ($this->option('geri-al') !== null) {
            return $this->geriAl((int) $this->option('geri-al'));
        }

        $dosya = (string) $this->argument('dosya');

        if (! is_file($dosya)) {
            $this->components->error('Dosya yok: '.$dosya);

            return self::FAILURE;
        }

        try {
            $harita = json_decode((string) file_get_contents($dosya), true, 8, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $tarifler = [];

        foreach ((array) $harita as $slug => $alanlar) {
            $tarif = Recipe::query()->where('slug', $slug)->where('status', 'published')->where('verification_status', 'verified')->with('steps')->first();

            if ($tarif === null) {
                $this->components->error('Yayında ve doğrulanmış tarif yok: '.$slug);

                return self::FAILURE;
            }

            foreach ((array) ($alanlar['steps'] ?? []) as $sira => $adimAlanlari) {
                if ($tarif->steps->firstWhere('position', (int) $sira) === null) {
                    $this->components->error(sprintf('%s: %d. adım yok.', $slug, $sira));

                    return self::FAILURE;
                }

                foreach ((array) $adimAlanlari as $alan => $deger) {
                    if (! in_array($alan, self::ADIM_ALANLARI, true) || ($alan !== 'chef_tip' && trim((string) $deger) === '')) {
                        $this->components->error(sprintf('%s %d. adım: geçersiz ya da boş alan %s.', $slug, $sira, $alan));

                        return self::FAILURE;
                    }
                }
            }

            foreach (array_diff_key((array) $alanlar, ['steps' => true]) as $alan => $deger) {
                if (! in_array($alan, self::TARIF_ALANLARI, true) || trim((string) $deger) === '') {
                    $this->components->error(sprintf('%s: geçersiz ya da boş alan %s.', $slug, $alan));

                    return self::FAILURE;
                }
            }

            $tarifler[$slug] = $tarif;
        }

        $diller = array_values(array_diff(array_keys((array) config('locales.supported', [])), [(string) config('locales.default', 'tr')]));
        $acikOnce = [];
        $once = [];
        $yazilan = 0;

        foreach ($tarifler as $slug => $tarif) {
            $acikOnce[$slug] = array_values(array_filter($diller, fn (string $dil): bool => $tarif->hasTranslation($dil)));
        }

        DB::transaction(function () use ($harita, $tarifler, &$once, &$yazilan): void {
            foreach ($tarifler as $slug => $tarif) {
                $alanlar = (array) $harita[$slug];

                foreach (array_diff_key($alanlar, ['steps' => true]) as $alan => $deger) {
                    $once[$slug]['recipe'][$alan] = $tarif->getRawOriginal($alan);
                    $tarif->forceFill([$alan => trim((string) $deger)]);
                    $yazilan++;
                }

                $tarif->save();

                foreach ((array) ($alanlar['steps'] ?? []) as $sira => $adimAlanlari) {
                    $adim = $tarif->steps->firstWhere('position', (int) $sira);

                    foreach ((array) $adimAlanlari as $alan => $deger) {
                        $once[$slug]['steps'][(string) $sira][$alan] = $adim->getRawOriginal($alan);
                        $adim->forceFill([$alan => trim((string) $deger) === '' ? null : trim((string) $deger)]);
                        $yazilan++;
                    }

                    $adim->save();
                }
            }
        });

        $rapor = IntegrityReport::query()->create([
            'guard' => 'coni:kaynak',
            'status' => 'ok',
            'headline' => $yazilan,
            'payload' => ['yazilan' => $yazilan, 'once' => $once],
        ]);

        foreach ($tarifler as $slug => $tarif) {
            $tarif->refresh()->load('steps');
            $kapanan = array_values(array_filter($acikOnce[$slug], fn (string $dil): bool => ! $tarif->hasTranslation($dil)));

            if ($kapanan !== []) {
                $this->components->warn($slug.': '.implode(', ', $kapanan).' sayfası kapandı; yeni Türkçe alanın çevirisi eksik.');
            }
        }

        $this->components->info(sprintf('%d tarifte %d alan yazıldı. Geri almak için: php artisan coni:kaynak --geri-al=%d', count($tarifler), $yazilan, $rapor->id));

        return self::SUCCESS;
    }

    private function geriAl(int $raporId): int
    {
        $rapor = IntegrityReport::query()->where('guard', 'coni:kaynak')->findOrFail($raporId);

        DB::transaction(function () use ($rapor): void {
            foreach ((array) ($rapor->payload['once'] ?? []) as $slug => $alanlar) {
                $tarif = Recipe::query()->where('slug', $slug)->with('steps')->firstOrFail();
                $tarif->forceFill((array) ($alanlar['recipe'] ?? []))->save();

                foreach ((array) ($alanlar['steps'] ?? []) as $sira => $adimAlanlari) {
                    $tarif->steps->firstWhere('position', (int) $sira)?->forceFill((array) $adimAlanlari)->save();
                }
            }
        });

        $rapor->update(['status' => 'geri_alindi']);
        $this->components->info('Geri alındı: rapor #'.$raporId);

        return self::SUCCESS;
    }
}
