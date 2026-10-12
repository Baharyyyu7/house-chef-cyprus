<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Ingredient;
use App\Models\IntegrityReport;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeIngredientGroup;
use App\Models\Unit;
use App\Services\Measurements\IngredientConversionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use JsonException;
use Throwable;

/**
 * Yayındaki tarifin malzeme listesine eksik satır ekler.
 *
 * 11 Ekim 2026 sadakat denetiminde ~30 tarifin adımında kullanılan tuz, yağ,
 * su ya da karabiber malzeme listesinde yoktu. coni:kaynak yalnız metin
 * alanlarını yazar; content:import-recipes listeyi silip kurar ve altı dilin
 * adım çevirisini de götürür. Bu komut satırı yönetim panelindeki akışla
 * aynı kurar (IngredientConversionService ile kanonik miktar), mevcut
 * satırlara dokunmaz.
 *
 * Yalnız her yabancı dilde adı çevrilmiş malzeme kabul edilir: çevrilmemiş
 * bir ad tarifin o dildeki sayfasını kapatır. Ekleme açık bir sayfayı yine
 * de kapatırsa hiçbir şey yazılmaz.
 *
 * Dosya: {"<slug>": [{"ingredient_id", "display_quantity", "display_measure_code", "scaling_behavior", "is_optional"?, "preparation_note"?, "group_name"?, "canonical_quantity"?, "canonical_unit"?, "manual_override_reason"?}]}
 * Eklenen satırlar raporda saklanır; --geri-al=<rapor id> siler.
 */
final class ConiMalzemeEkle extends Command
{
    protected $signature = 'coni:malzeme-ekle {dosya? : JSON dosyası} {--geri-al= : Geri alınacak rapor id} {--dene : Doğrular, yazmaz} {--taslak : Yayındaki değil, coni:taslak ile yazılmış taslak tarife ekler}';

    protected $description = 'Claude Coni: yayındaki tarife eksik malzeme satırı ekler, geri alınabilir.';

    private const OLCEKLEME = ['scalable', 'limited_scaling', 'non_scalable', 'taste_adjusted', 'manual_review'];

    public function handle(IngredientConversionService $donusum): int
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

        $diller = array_values(array_diff(array_keys((array) config('locales.supported', [])), [(string) config('locales.default', 'tr')]));
        $tarifler = [];
        $plan = [];
        $hatalar = [];

        foreach ((array) $harita as $slug => $satirlar) {
            $tarif = Recipe::query()->where('slug', $slug)->where('verification_status', 'verified')
                ->where('status', $this->option('taslak') ? '!=' : '=', 'published')
                ->with(['ingredientGroups', 'recipeIngredients'])->first();

            if ($tarif === null || ! is_array($satirlar) || $satirlar === []) {
                $hatalar[] = $tarif === null ? ($this->option('taslak') ? 'Taslak ve doğrulanmış tarif yok: ' : 'Yayında ve doğrulanmış tarif yok: ').$slug : $slug.': eklenecek satır yok.';

                continue;
            }

            $tarifler[$slug] = $tarif;

            foreach (array_values($satirlar) as $i => $satir) {
                $etiket = sprintf('%s #%d', $slug, $i + 1);
                $malzeme = Ingredient::query()->whereKey((int) ($satir['ingredient_id'] ?? 0))->where('is_active', true)->first();

                if ($malzeme === null) {
                    $hatalar[] = $etiket.': malzeme yok ya da pasif.';

                    continue;
                }

                $cevrilmemis = array_values(array_filter($diller, fn (string $dil): bool => ! $malzeme->hasTranslation($dil)));

                if ($cevrilmemis !== []) {
                    $hatalar[] = sprintf('%s: %s adı çevrilmemiş (%s).', $etiket, $malzeme->name, implode(', ', $cevrilmemis));

                    continue;
                }

                $miktar = $satir['display_quantity'] ?? null;
                $kod = (string) ($satir['display_measure_code'] ?? '');
                $olcekleme = (string) ($satir['scaling_behavior'] ?? '');
                $not = trim((string) ($satir['preparation_note'] ?? ''));

                if (! is_numeric($miktar) || (float) $miktar <= 0 || (float) $miktar > 100000) {
                    $hatalar[] = $etiket.': geçersiz miktar.';

                    continue;
                }

                if (! Unit::query()->where('code', $kod)->where('is_active', true)->where('is_user_visible', true)->exists()) {
                    $hatalar[] = $etiket.': pasif ya da geçersiz ölçü '.$kod.'.';

                    continue;
                }

                if (! in_array($olcekleme, self::OLCEKLEME, true) || mb_strlen($not) > 255) {
                    $hatalar[] = $etiket.': geçersiz scaling_behavior ya da çok uzun not.';

                    continue;
                }

                // Panelin elle girişiyle aynı: dönüşüm tablosu seyrek (Tuz'un
                // yalnız adet dönüşümü var), kanonik değer nedeniyle verilir.
                $elle = array_key_exists('canonical_quantity', $satir);
                $neden = trim((string) ($satir['manual_override_reason'] ?? ''));

                if ($elle) {
                    $kanonik = $satir['canonical_quantity'];
                    $kanonikBirim = (string) ($satir['canonical_unit'] ?? '');

                    if (! is_numeric($kanonik) || (float) $kanonik <= 0 || (float) $kanonik > 1000000 || ! in_array($kanonikBirim, ['g', 'ml', 'adet'], true) || $neden === '' || mb_strlen($neden) > 255) {
                        $hatalar[] = $etiket.': elle kanonik değer için canonical_quantity, canonical_unit (g, ml, adet) ve manual_override_reason gerekli.';

                        continue;
                    }

                    $sonuc = ['quantity' => (string) $kanonik, 'unit' => $kanonikBirim, 'confidence' => 'medium', 'method' => 'manual_override', 'conversion' => null];
                } else {
                    $sonuc = $donusum->resolve($malzeme, $kod, (string) $miktar);

                    if ($sonuc['quantity'] === null) {
                        $hatalar[] = sprintf('%s: %s için %s dönüşümü yok; canonical_quantity, canonical_unit ve manual_override_reason verin.', $etiket, $malzeme->name, $kod);

                        continue;
                    }
                }

                // Birden fazla grup varken başlıklar sayfada görünür; yeni bir
                // "Malzemeler" grubu açmak yabancı dilde Türkçe başlık basar.
                $grupAdi = trim((string) ($satir['group_name'] ?? ''));
                $gruplar = $tarif->ingredientGroups;

                if ($grupAdi === '' && $gruplar->count() > 1) {
                    $hatalar[] = sprintf('%s: tarifte %d grup var, group_name gerekli (%s).', $etiket, $gruplar->count(), $gruplar->pluck('name')->implode(', '));

                    continue;
                }

                if ($grupAdi !== '' && $gruplar->firstWhere('name', $grupAdi) === null) {
                    $hatalar[] = $etiket.': tarifte grup yok: '.$grupAdi;

                    continue;
                }

                $grupAdi = $grupAdi !== '' ? $grupAdi : ($gruplar->first()->name ?? 'Malzemeler');
                $grupId = $gruplar->firstWhere('name', $grupAdi)?->id;
                $ayniSatir = $tarif->recipeIngredients->contains(fn (RecipeIngredient $mevcut): bool => $mevcut->ingredient_id === $malzeme->id && $mevcut->recipe_ingredient_group_id === $grupId)
                    || collect($plan[$slug] ?? [])->contains(fn (array $p): bool => $p['ingredient_id'] === $malzeme->id && $p['group_name'] === $grupAdi);

                if ($ayniSatir) {
                    $hatalar[] = sprintf('%s: %s zaten listede.', $etiket, $malzeme->name);

                    continue;
                }

                $plan[$slug][] = [
                    'ingredient_id' => $malzeme->id, 'quantity' => (string) $miktar, 'unit' => $kod, 'display_quantity' => (string) $miktar, 'display_measure_code' => $kod, 'canonical_quantity' => $sonuc['quantity'], 'canonical_unit' => $sonuc['unit'], 'grams' => $sonuc['unit'] === 'g' ? $sonuc['quantity'] : null, 'ingredient_unit_conversion_id' => $sonuc['conversion']?->id, 'conversion_confidence' => $sonuc['confidence'], 'conversion_method' => $sonuc['method'], 'manual_override_reason' => $elle ? $neden : null, 'preparation_note' => $not === '' ? null : $not, 'is_optional' => (bool) ($satir['is_optional'] ?? false), 'is_required' => ! (bool) ($satir['is_optional'] ?? false), 'group_name' => $grupAdi, 'scaling_behavior' => $olcekleme,
                    'ad' => $malzeme->name,
                ];
            }
        }

        if ($hatalar !== []) {
            foreach ($hatalar as $hata) {
                $this->components->error($hata);
            }

            return self::FAILURE;
        }

        $acikOnce = [];

        foreach ($tarifler as $slug => $tarif) {
            $acikOnce[$slug] = array_values(array_filter($diller, fn (string $dil): bool => $tarif->hasTranslation($dil)));
        }

        $eklenen = [];
        $yeniGruplar = [];
        $kapanan = [];
        DB::beginTransaction();

        try {
            foreach ($plan as $slug => $satirlar) {
                $tarif = $tarifler[$slug];

                foreach ($satirlar as $satir) {
                    $grup = $tarif->ingredientGroups()->firstOrCreate(['name' => $satir['group_name']], ['sort_order' => ((int) $tarif->ingredientGroups()->max('sort_order')) + 1]);

                    if ($grup->wasRecentlyCreated) {
                        $yeniGruplar[] = $grup->id;
                    }

                    $yeni = $tarif->recipeIngredients()->create(array_diff_key($satir, ['ad' => true]) + ['recipe_ingredient_group_id' => $grup->id, 'sort_order' => ((int) $tarif->recipeIngredients()->max('sort_order')) + 1]);
                    $eklenen[$slug][] = $yeni->id;
                    $this->line(sprintf('%s: + %s %s %s (%s %s)', $slug, (float) $satir['display_quantity'], $satir['display_measure_code'], $satir['ad'], (float) $satir['canonical_quantity'], $satir['canonical_unit']));
                }

                $tarif->refresh()->load(['steps', 'ingredients']);
                $kapali = array_values(array_filter($acikOnce[$slug], fn (string $dil): bool => ! $tarif->hasTranslation($dil)));

                if ($kapali !== []) {
                    $kapanan[$slug] = $kapali;
                }
            }
        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }

        if ($kapanan !== [] || $this->option('dene')) {
            DB::rollBack();

            foreach ($kapanan as $slug => $kapali) {
                $this->components->error($slug.': '.implode(', ', $kapali).' sayfası kapanırdı; hiçbir şey yazılmadı.');
            }

            if ($kapanan === []) {
                $this->components->info(sprintf('Deneme: %d tarifte %d satır eklenebilir; yazılmadı.', count($plan), array_sum(array_map('count', $plan))));
            }

            return $kapanan === [] ? self::SUCCESS : self::FAILURE;
        }

        DB::commit();

        $toplam = array_sum(array_map('count', $eklenen));
        $rapor = IntegrityReport::query()->create([
            'guard' => 'coni:malzeme-ekle',
            'status' => 'ok',
            'headline' => $toplam,
            'payload' => ['eklenen' => $eklenen, 'yeni_gruplar' => $yeniGruplar],
        ]);

        $this->components->info(sprintf('%d tarife %d satır eklendi. Geri almak için: php artisan coni:malzeme-ekle --geri-al=%d', count($eklenen), $toplam, $rapor->id));

        return self::SUCCESS;
    }

    private function geriAl(int $raporId): int
    {
        $rapor = IntegrityReport::query()->where('guard', 'coni:malzeme-ekle')->findOrFail($raporId);
        $idler = array_merge(...array_values(array_map('array_values', (array) ($rapor->payload['eklenen'] ?? []))));

        DB::transaction(function () use ($rapor, $idler): void {
            // Model üzerinden silinir: gözlemci besin değeri önbelleğini düşürür.
            RecipeIngredient::query()->whereKey($idler)->get()->each->delete();
            RecipeIngredientGroup::query()->whereKey((array) ($rapor->payload['yeni_gruplar'] ?? []))->whereDoesntHave('ingredients')->delete();
        });

        $rapor->update(['status' => 'geri_alindi']);
        $this->components->info(sprintf('Geri alındı: rapor #%d, %d satır silindi.', $raporId, count($idler)));

        return self::SUCCESS;
    }
}
