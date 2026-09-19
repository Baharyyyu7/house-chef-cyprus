<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Services\Seo\RecipeQualityGateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Teslim edilen gerçek tarif içeriğini içeri alır.
 *
 * 17 Eylül 2026'da ölçüldü: yayındaki 790 tarifin 678'i şablondu. "Menemen"in
 * malzemesi süt, yoğurt, peynir ve tavuktu; yumurta yoktu. Bu komut o
 * tarifleri tek tek gerçek içerikle değiştirmenin yolu.
 *
 * Yalnız JSON okur; bu dizine ajanlar yazıyor ve oradan PHP çalıştırmak
 * doğrudan uzaktan kod çalıştırma olurdu.
 *
 * Kabul ölçütü tek cümle: yazılan içerik kalite kapısını geçmeli. Kapıyı
 * geçmeyen dosya reddedilir, çünkü şablonu şablonla değiştirmenin anlamı yok.
 * Görsellere dokunmaz: medya ayrı bir hattın işi.
 */
final class ImportRecipeContent extends Command
{
    protected $signature = 'content:import-recipes
        {--apply : Değişiklikleri yaz. Varsayılan yalnız rapordur.}
        {--slug= : Yalnız tek bir tarifi işle}';

    protected $description = 'Teslim edilen gerçek tarif içeriğini denetler ve içeri alır.';

    public function handle(RecipeQualityGateService $kapi): int
    {
        $yaz = (bool) $this->option('apply');
        $kok = storage_path('app/content-import/recipes');

        if (! is_dir($kok)) {
            $this->components->info('Teslim klasörü yok: '.$kok);

            return self::SUCCESS;
        }

        $alinan = 0;
        $atlanan = [];

        foreach (glob($kok.'/*.json') ?: [] as $dosya) {
            $ad = basename($dosya);
            $veri = json_decode((string) file_get_contents($dosya), true);

            if (! is_array($veri)) {
                $atlanan['okunamayan JSON'][] = $ad;

                continue;
            }

            $slug = (string) ($veri['slug'] ?? '');

            if ($this->option('slug') && $slug !== (string) $this->option('slug')) {
                continue;
            }

            $tarif = Recipe::query()->where('slug', $slug)->with(['steps', 'ingredients'])->first();

            if ($tarif === null) {
                $atlanan['tarif bulunamadı'][] = $ad;

                continue;
            }

            $sorun = $this->dogrula($veri);

            if ($sorun !== null) {
                $atlanan[$sorun][] = $ad;

                continue;
            }

            // Yazmadan önce: bu içerik kapıyı geçiyor mu? Kapı korpusu
            // yayındaki bütün tariflerden kurulur, yani başka bir tarifin
            // metnini kopyalayan dosya burada takılır.
            $sorun = $this->kapidanGecerMi($kapi, $tarif, $veri);

            if ($sorun !== null) {
                $atlanan[$sorun][] = $ad;

                continue;
            }

            if (! $yaz) {
                $alinan++;

                continue;
            }

            // İçerik değişince eski adımlara bağlı çeviriler geçersiz kalır ve
            // o dildeki sayfa 404'e düşer. 19 Eylül 2026'da Google'ın sıraladığı
            // Rusça çoban salata ve Almanca ıspanaklı gözleme sayfaları böyle
            // kapandı. Hangi dillerin yeniden çevrilmesi gerektiği bildirilir.
            $oncekiDiller = collect(array_keys((array) config('locales.supported', [])))
                ->reject(fn (string $dil): bool => $dil === (string) config('locales.default', 'tr'))
                ->filter(fn (string $dil): bool => $tarif->hasTranslation($dil))
                ->values()
                ->all();

            DB::transaction(function () use ($tarif, $veri): void {
                $tarif->forceFill([
                    'title' => (string) ($veri['title'] ?? $tarif->title),
                    'excerpt' => (string) $veri['excerpt'],
                    'description' => (string) $veri['description'],
                ])->save();

                $tarif->steps()->delete();

                foreach ($veri['steps'] as $sira => $adim) {
                    $tarif->steps()->create([
                        'position' => $sira + 1,
                        'title' => (string) $adim['title'],
                        'instruction' => (string) $adim['instruction'],
                    ]);
                }

                $tarif->recipeIngredients()->delete();

                foreach ($veri['ingredients'] as $sira => $malzeme) {
                    $kayit = Ingredient::query()->firstOrCreate(
                        ['slug' => Str::slug((string) $malzeme['name'])],
                        ['name' => (string) $malzeme['name']],
                    );

                    $tarif->recipeIngredients()->create([
                        'ingredient_id' => $kayit->id,
                        'quantity' => $malzeme['quantity'] ?? null,
                        'unit' => $malzeme['unit'] ?? null,
                        'preparation' => $malzeme['preparation'] ?? null,
                        'sort_order' => $sira + 1,
                    ]);
                }
            });

            $tarif->refresh()->load(['steps', 'ingredients']);
            $kaybolan = array_values(array_filter($oncekiDiller, fn (string $dil): bool => ! $tarif->hasTranslation($dil)));

            if ($kaybolan !== []) {
                $this->components->warn($tarif->slug.': '.implode(', ', $kaybolan).' çevirisi artık eksik; bu dillerdeki sayfa yeniden çevrilene kadar 404 verir.');
            }

            // Kapıyı geçen içerik yazıldıysa, taramanın koyduğu makine
            // damgası kalkar: sayfa artık gerçek bir tarif. İnsanın elle
            // koyduğu başka bir robots değeri varsa ona dokunulmaz.
            $tarif->refresh()->load('seoMetadata');

            if ((string) $tarif->seoMetadata?->robots === 'noindex,follow') {
                $tarif->seoMetadata->forceFill(['robots' => 'index,follow'])->save();
            }

            $alinan++;
        }

        $this->components->info(($yaz ? 'İçeri alınan' : 'Alınmaya hazır').' tarif: '.$alinan);

        foreach ($atlanan as $sebep => $dosyalar) {
            $this->line('  · '.$sebep.': '.implode(', ', array_slice($dosyalar, 0, 5)));
        }

        if (! $yaz) {
            $this->line('Yazmak için: php artisan content:import-recipes --apply');
        }

        return self::SUCCESS;
    }

    /** @param array<string, mixed> $veri */
    private function dogrula(array $veri): ?string
    {
        foreach (['excerpt', 'description'] as $alan) {
            if (! is_string($veri[$alan] ?? null) || mb_strlen(trim((string) $veri[$alan])) < 40) {
                return 'eksik ya da çok kısa alan: '.$alan;
            }
        }

        $adimlar = is_array($veri['steps'] ?? null) ? $veri['steps'] : [];

        if (count($adimlar) < RecipeQualityGateService::ASGARI_ADIM + 1) {
            return 'yetersiz adım';
        }

        foreach ($adimlar as $adim) {
            if (! is_array($adim) || ! is_string($adim['instruction'] ?? null) || mb_strlen(trim($adim['instruction'])) < 40) {
                return 'adım yönergesi eksik ya da çok kısa';
            }

            if (! is_string($adim['title'] ?? null) || trim($adim['title']) === '') {
                return 'adım başlığı eksik';
            }
        }

        $malzemeler = is_array($veri['ingredients'] ?? null) ? $veri['ingredients'] : [];

        if (count($malzemeler) < RecipeQualityGateService::ASGARI_MALZEME) {
            return 'yetersiz malzeme';
        }

        foreach ($malzemeler as $malzeme) {
            if (! is_array($malzeme) || ! is_string($malzeme['name'] ?? null) || trim($malzeme['name']) === '') {
                return 'malzeme adı eksik';
            }

            // Ölçüsüz malzeme tarif değil alışveriş listesidir.
            if (! isset($malzeme['quantity']) && ! isset($malzeme['preparation'])) {
                return 'malzeme ölçüsü eksik: '.$malzeme['name'];
            }
        }

        return null;
    }

    /** @param array<string, mixed> $veri */
    private function kapidanGecerMi(RecipeQualityGateService $kapi, Recipe $tarif, array $veri): ?string
    {
        $deneme = $tarif->replicate();
        $deneme->id = $tarif->id;
        $deneme->exists = true;
        $deneme->forceFill(['excerpt' => (string) $veri['excerpt'], 'description' => (string) $veri['description']]);

        $adimlar = collect($veri['steps'])->map(function (array $adim, int $sira) use ($tarif) {
            $yeni = $tarif->steps()->make(['position' => $sira + 1, 'title' => (string) $adim['title'], 'instruction' => (string) $adim['instruction']]);
            $yeni->setRawAttributes(['position' => $sira + 1, 'title' => (string) $adim['title'], 'instruction' => (string) $adim['instruction']], true);

            return $yeni;
        });

        $deneme->setRelation('steps', $adimlar);

        $korpus = Recipe::query()
            ->where('status', 'published')
            ->whereKeyNot($tarif->getKey())
            ->with('steps:id,recipe_id,instruction')
            ->get(['id', 'title', 'description', 'excerpt', 'published_at'])
            ->push($deneme);

        $olcum = $kapi->olc($korpus)[$tarif->getKey()] ?? null;

        return $kapi->sablonMu($olcum) ? 'kalite kapısını geçmiyor: metin başka tariflerde geçiyor' : null;
    }
}
