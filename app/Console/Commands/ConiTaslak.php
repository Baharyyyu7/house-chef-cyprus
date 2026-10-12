<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ContentTranslation;
use App\Models\IntegrityReport;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeIngredientGroup;
use App\Models\RecipeStep;
use App\Services\Seo\RecipeQualityGateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use JsonException;

/**
 * Taslaktaki şablon tarifi baştan yazar; yayın durumuna dokunmaz.
 *
 * Taslaktaki 400 tarifin çoğu aynı kalıptan: ekmek kadayıfı, lokma ve
 * ceviz macununun malzemesi aynı "200 g un, 150 g şeker, 2 yumurta",
 * adımları "Malzemeleri tezgaha dizin". 28 günde 140'ı aramada gösterim
 * aldı. coni:kaynak yalnız yayındaki tarifin var olan adımlarını yazar;
 * content:import-recipes malzeme satırını eski sütunlarla kurar.
 *
 * Bu komut metni, süreleri, adımları ve malzeme gruplarını yazar; eski
 * malzeme satırlarını ve kalıp çevirileri siler. Yeni satırlar
 * `coni:malzeme-ekle --taslak` ile panel akışıyla eklenir. Yayın kararı
 * insana aittir (AGENTS.md); komut status ve robots değerine dokunmaz.
 *
 * Dosya: {"<slug>": {"recipe": {"title", "excerpt", "description", "prep_minutes", "cook_minutes", "rest_minutes", "servings", "difficulty", "country"}, "groups": ["..."], "steps": [{"title", "instruction", "duration_seconds"?, "chef_tip"?}]}}
 * Eski hâl raporda saklanır; --geri-al=<rapor id> aynen geri koyar.
 */
final class ConiTaslak extends Command
{
    protected $signature = 'coni:taslak {dosya? : JSON dosyası} {--geri-al= : Geri alınacak rapor id} {--dene : Doğrular, yazmaz}';

    protected $description = 'Claude Coni: taslaktaki şablon tarifi baştan yazar, yayınlamaz, geri alınabilir.';

    private const TARIF_ALANLARI = ['title', 'excerpt', 'description', 'prep_minutes', 'cook_minutes', 'rest_minutes', 'servings', 'difficulty', 'country'];

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
            $harita = json_decode((string) file_get_contents($dosya), true, 16, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $tarifler = [];
        $hatalar = [];

        foreach ((array) $harita as $slug => $veri) {
            $tarif = Recipe::query()->where('slug', $slug)->first();

            if ($tarif === null || $tarif->status === 'published') {
                $hatalar[] = $tarif === null ? 'Tarif yok: '.$slug : $slug.': yayında; yayındaki tarif coni:kaynak ile düzeltilir.';

                continue;
            }

            $sorun = $this->dogrula((array) $veri);

            if ($sorun !== null) {
                $hatalar[] = $slug.': '.$sorun;

                continue;
            }

            $tarifler[$slug] = [$tarif, (array) $veri];
        }

        if ($hatalar !== []) {
            foreach ($hatalar as $hata) {
                $this->components->error($hata);
            }

            return self::FAILURE;
        }

        if ($this->option('dene')) {
            $this->components->info(sprintf('Deneme: %d taslak yazılabilir; yazılmadı.', count($tarifler)));

            return self::SUCCESS;
        }

        $once = [];

        DB::transaction(function () use ($tarifler, &$once): void {
            foreach ($tarifler as $slug => [$tarif, $veri]) {
                $once[$slug] = $this->anlikGoruntu($tarif);
                $this->temizle($tarif);

                $alanlar = array_intersect_key((array) $veri['recipe'], array_flip(self::TARIF_ALANLARI));
                $alanlar['difficulty'] = Recipe::zorlukKanonik((string) $alanlar['difficulty']);
                $tarif->forceFill($alanlar)->save();

                foreach (array_values($veri['groups']) as $sira => $ad) {
                    $tarif->ingredientGroups()->create(['name' => $ad, 'sort_order' => $sira + 1]);
                }

                foreach (array_values($veri['steps']) as $sira => $adim) {
                    $tarif->steps()->create([
                        'position' => $sira + 1,
                        'title' => trim((string) $adim['title']),
                        'instruction' => trim((string) $adim['instruction']),
                        'duration_seconds' => isset($adim['duration_seconds']) ? (int) $adim['duration_seconds'] : null,
                        'chef_tip' => filled($adim['chef_tip'] ?? null) ? trim((string) $adim['chef_tip']) : null,
                    ]);
                }

                $this->line(sprintf('%s: %s, %d adım, grup %s', $slug, $alanlar['title'], count($veri['steps']), implode(' / ', $veri['groups'])));
            }
        });

        $rapor = IntegrityReport::query()->create([
            'guard' => 'coni:taslak',
            'status' => 'ok',
            'headline' => count($once),
            'payload' => ['once' => $once],
        ]);

        $this->components->info(sprintf('%d taslak yazıldı, yayın durumu değişmedi. Malzeme: coni:malzeme-ekle --taslak. Geri almak için: php artisan coni:taslak --geri-al=%d', count($once), $rapor->id));

        return self::SUCCESS;
    }

    /** @param array<string, mixed> $veri */
    private function dogrula(array $veri): ?string
    {
        $tarif = (array) ($veri['recipe'] ?? []);

        foreach (['title', 'excerpt', 'description'] as $alan) {
            if (mb_strlen(trim((string) ($tarif[$alan] ?? ''))) < ($alan === 'title' ? 3 : 40)) {
                return 'eksik ya da çok kısa alan: '.$alan;
            }
        }

        foreach (['prep_minutes', 'cook_minutes', 'rest_minutes', 'servings'] as $alan) {
            if (! is_int($tarif[$alan] ?? null) || $tarif[$alan] < 0 || $tarif[$alan] > 20000) {
                return 'geçersiz sayı: '.$alan;
            }
        }

        if (Recipe::zorlukKanonik((string) ($tarif['difficulty'] ?? '')) === null) {
            return 'geçersiz zorluk';
        }

        $gruplar = array_values(array_filter(array_map(static fn ($g): string => trim((string) $g), (array) ($veri['groups'] ?? []))));

        if ($gruplar === [] || count($gruplar) !== count(array_unique($gruplar))) {
            return 'grup listesi boş ya da tekrarlı';
        }

        $adimlar = (array) ($veri['steps'] ?? []);

        if (count($adimlar) < RecipeQualityGateService::ASGARI_ADIM + 1) {
            return 'yetersiz adım';
        }

        foreach ($adimlar as $i => $adim) {
            $baslik = trim((string) ($adim['title'] ?? ''));

            if ($baslik === '' || preg_match('/^adım\s*\d+$/iu', $baslik) === 1 || mb_strlen(trim((string) ($adim['instruction'] ?? ''))) < 40) {
                return sprintf('%d. adım: başlık boş ya da "Adım N", ya da yönerge 40 karakterden kısa', $i + 1);
            }
        }

        return null;
    }

    /** @return array<string, mixed> */
    private function anlikGoruntu(Recipe $tarif): array
    {
        $adimlar = $tarif->steps()->get();

        return [
            'recipe_id' => $tarif->id,
            'alanlar' => $tarif->only(self::TARIF_ALANLARI),
            'adimlar' => $adimlar->map(static fn (RecipeStep $a): array => $a->getAttributes())->values()->all(),
            'satirlar' => $tarif->recipeIngredients()->get()->map(static fn (RecipeIngredient $s): array => $s->getAttributes())->values()->all(),
            'gruplar' => $tarif->ingredientGroups()->get()->map(static fn (RecipeIngredientGroup $g): array => $g->getAttributes())->values()->all(),
            'ceviriler' => ContentTranslation::query()
                ->where(fn ($q) => $q->where('translatable_type', $tarif->getMorphClass())->where('translatable_id', $tarif->id))
                ->orWhere(fn ($q) => $q->where('translatable_type', (new RecipeStep)->getMorphClass())->whereIn('translatable_id', $adimlar->pluck('id')))
                ->get()->map(static fn (ContentTranslation $c): array => $c->getAttributes())->values()->all(),
        ];
    }

    /**
     * Tarifin adımlarını, malzeme satırlarını, gruplarını ve kendisiyle
     * adımlarının çevirilerini siler. Kalıp çeviri yeni adımlara ait değil.
     */
    private function temizle(Recipe $tarif): void
    {
        $adimIdleri = $tarif->steps()->pluck('id');

        ContentTranslation::query()
            ->where(fn ($q) => $q->where('translatable_type', $tarif->getMorphClass())->where('translatable_id', $tarif->id))
            ->orWhere(fn ($q) => $q->where('translatable_type', (new RecipeStep)->getMorphClass())->whereIn('translatable_id', $adimIdleri))
            ->get()->each->delete();

        // Model üzerinden: gözlemciler besin değeri ve çeviri önbelleğini düşürür.
        $tarif->recipeIngredients()->get()->each->delete();
        $tarif->steps()->get()->each->delete();
        $tarif->ingredientGroups()->get()->each->delete();
    }

    private function geriAl(int $raporId): int
    {
        $rapor = IntegrityReport::query()->where('guard', 'coni:taslak')->findOrFail($raporId);

        DB::transaction(function () use ($rapor): void {
            foreach ((array) data_get($rapor->payload, 'once', []) as $eski) {
                $tarif = Recipe::query()->findOrFail((int) $eski['recipe_id']);
                $this->temizle($tarif);
                $tarif->forceFill((array) $eski['alanlar'])->save();

                // Ham satır, aynı kimlikle: model üzerinden yazmak JSON
                // sütunlarını ikinci kez kodlar.
                foreach ([[RecipeIngredientGroup::class, 'gruplar'], [RecipeStep::class, 'adimlar'], [RecipeIngredient::class, 'satirlar'], [ContentTranslation::class, 'ceviriler']] as [$sinif, $anahtar]) {
                    foreach ((array) $eski[$anahtar] as $satir) {
                        DB::table((new $sinif)->getTable())->insert($satir);
                    }
                }
            }
        });

        Recipe::forgetTranslatedIds();

        $rapor->update(['status' => 'geri_alindi']);
        $this->components->info(sprintf('Geri alındı: rapor #%d.', $raporId));

        return self::SUCCESS;
    }
}
