<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ContentTranslation;
use App\Models\Ingredient;
use App\Models\IntegrityReport;
use App\Models\RecipeIngredient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JsonException;

/**
 * Malzeme adlarının yabancı dil karşılıklarını topluca düzeltir.
 *
 * 24 Eylül 2026'da ölçüldü: yayındaki tariflerin kullandığı malzeme
 * adlarının her dilde %40-50'si kelime parçası değiştirilerek üretilmişti:
 * "Taze Fasulye" Almancada "Taze Fawasserlye" ("su" → "Wasser"), İngilizcede
 * "Kuzu Minced Meatsı", Rusçada "Haşlanmış Kuru Faводаlye". Bu adlar her
 * yabancı tarif sayfasının malzeme listesinde görünüyor.
 *
 * Dosya: {"<malzeme id>": {"en": "...", "de": "...", ...}, ...}
 * Eski değerler raporda saklanır; --geri-al=<rapor id> aynen geri koyar.
 *
 * Anahtar sayı değil Türkçe adsa ("Sumak") malzeme katalogda yoksa açılır.
 * 11 Ekim 2026'da adımda kullanılan sumak, çörek otu, marul ve kek katalogda
 * yoktu; yönetim panelinde malzeme açan bir akış yok. --geri-al açtığı
 * malzemeyi hiçbir tarif satırı kullanmıyorsa siler.
 *
 * Kayıtlar olaysız yazılır: her çeviri kaydı gözlemci üzerinden o malzemeyi
 * kullanan her tarif için bir kuyruk işi açıyor; 1.400 kayıt, dakikada 20 iş
 * boşaltan kuyruğu günlerce tıkardı.
 */
final class ConiMalzeme extends Command
{
    protected $signature = 'coni:malzeme {dosya? : JSON dosyası} {--geri-al= : Geri alınacak rapor id} {--json}';

    protected $description = 'Claude Coni: malzeme adlarının yabancı dil çevirilerini düzeltir, eskisini saklar.';

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

        $diller = array_diff(array_keys((array) config('locales.supported', [])), [(string) config('locales.default', 'tr')]);
        $tur = (new Ingredient)->getMorphClass();
        $once = [];
        $acilan = [];
        $yazilan = 0;

        DB::transaction(function () use ($harita, $diller, $tur, &$once, &$acilan, &$yazilan): void {
            ContentTranslation::withoutEvents(function () use ($harita, $diller, $tur, &$once, &$acilan, &$yazilan): void {
                foreach ((array) $harita as $id => $adlar) {
                    if (! is_int($id)) {
                        $ad = trim((string) $id);
                        $malzeme = $ad === '' || mb_strlen($ad) > 120 ? null : Ingredient::query()->firstOrCreate(['slug' => Str::slug($ad)], ['name' => $ad, 'canonical_name' => $ad, 'is_active' => true]);

                        if ($malzeme === null) {
                            continue;
                        }

                        if ($malzeme->wasRecentlyCreated) {
                            $acilan[$malzeme->id] = $ad;
                        }

                        $id = $malzeme->id;
                    }

                    if (! Ingredient::query()->whereKey((int) $id)->exists()) {
                        continue;
                    }

                    foreach ((array) $adlar as $dil => $ad) {
                        $ad = trim((string) $ad);

                        if (! in_array($dil, $diller, true) || $ad === '' || mb_strlen($ad) > 120) {
                            continue;
                        }

                        $kosul = ['translatable_type' => $tur, 'translatable_id' => (int) $id, 'locale' => $dil, 'field' => 'name'];
                        $mevcut = ContentTranslation::query()->where($kosul)->value('value');

                        if ($mevcut === $ad) {
                            continue;
                        }

                        $once[$id][$dil] = $mevcut;
                        ContentTranslation::query()->updateOrCreate($kosul, ['value' => $ad]);
                        $yazilan++;
                    }
                }
            });
        });

        $rapor = IntegrityReport::query()->create([
            'guard' => 'coni:malzeme',
            'status' => 'ok',
            'headline' => $yazilan,
            'payload' => ['yazilan' => $yazilan, 'once' => $once, 'acilan' => $acilan],
        ]);

        foreach ($acilan as $id => $ad) {
            $this->line(sprintf('Açılan malzeme: %s #%d', $ad, $id));
        }

        $this->components->info(sprintf('%d malzeme adı yazıldı. Geri almak için: php artisan coni:malzeme --geri-al=%d', $yazilan, $rapor->id));

        return self::SUCCESS;
    }

    private function geriAl(int $raporId): int
    {
        $rapor = IntegrityReport::query()->where('guard', 'coni:malzeme')->findOrFail($raporId);
        $tur = (new Ingredient)->getMorphClass();

        ContentTranslation::withoutEvents(function () use ($rapor, $tur): void {
            foreach ((array) ($rapor->payload['once'] ?? []) as $id => $adlar) {
                foreach ((array) $adlar as $dil => $ad) {
                    $kosul = ['translatable_type' => $tur, 'translatable_id' => (int) $id, 'locale' => (string) $dil, 'field' => 'name'];

                    $ad === null
                        ? ContentTranslation::query()->where($kosul)->delete()
                        : ContentTranslation::query()->updateOrCreate($kosul, ['value' => $ad]);
                }
            }
        });

        foreach (array_keys((array) ($rapor->payload['acilan'] ?? [])) as $id) {
            if (RecipeIngredient::query()->where('ingredient_id', (int) $id)->exists()) {
                $this->components->warn('Malzeme #'.$id.' tarifte kullanılıyor, silinmedi.');

                continue;
            }

            ContentTranslation::withoutEvents(fn () => ContentTranslation::query()->where(['translatable_type' => $tur, 'translatable_id' => (int) $id])->delete());
            Ingredient::query()->whereKey((int) $id)->delete();
        }

        $rapor->update(['status' => 'geri_alindi']);
        $this->components->info('Geri alındı: rapor #'.$raporId);

        return self::SUCCESS;
    }
}
