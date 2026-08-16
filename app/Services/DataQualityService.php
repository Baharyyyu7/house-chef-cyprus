<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AiImageGeneration;
use App\Models\DataQualityIssue;
use App\Models\Ingredient;
use App\Models\IngredientAlias;
use App\Models\IngredientSubstitution;
use App\Models\IngredientUnitConversion;
use App\Models\NutritionFact;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeIngredientGroup;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

final class DataQualityService
{
    /**
     * Skorlar açık issue kayıtlarının cezasından türetilir. Hiç tarama
     * yapılmadığında kayıt da olmaz ve her kategori %100 görünür; yani "%100"
     * sağlıklı değil, "hiç bakılmadı" anlamına gelir. Panel bu ikisini
     * ayırabilsin diye son tarama zamanı saklanır.
     */
    private const LAST_SCAN_KEY = 'data_quality.last_scanned_at';

    public function lastScannedAt(): ?\DateTimeImmutable
    {
        $stamp = Cache::get(self::LAST_SCAN_KEY);

        if (! is_string($stamp) || $stamp === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($stamp);
        } catch (\Exception) {
            return null;
        }
    }

    public function hasEverScanned(): bool
    {
        return $this->lastScannedAt() !== null;
    }

    /** @return list<array<string,mixed>> */
    public function report(): array
    {
        $issues = [];
        foreach (Recipe::withCount(['recipeIngredients', 'steps', 'provenances'])->with('media')->lazyById((int) config('performance.quality_scan_chunk_size')) as $recipe) {
            if (! $recipe->title) {
                $issues[] = $this->issue('missing_recipe_title', 'critical', $recipe, 'Tarif adı eksik.');
            }
            if (! $recipe->description) {
                $issues[] = $this->issue('missing_description', 'warning', $recipe, 'Tarif açıklaması eksik.');
            }
            if (! $recipe->cuisine_id || ! $recipe->category_id) {
                $issues[] = $this->issue('missing_taxonomy', 'warning', $recipe, 'Mutfak veya kategori eksik.');
            }
            if (! $recipe->recipe_ingredients_count) {
                $issues[] = $this->issue('missing_ingredients', 'error', $recipe, 'Tarif malzemesiz.');
            }
            if (! $recipe->steps_count) {
                $issues[] = $this->issue('missing_steps', 'error', $recipe, 'Tarif adımsız.');
            }
            if (! $recipe->public_id) {
                $issues[] = $this->issue('missing_public_id', 'critical', $recipe, 'Public ID eksik.');
            }
            if (! preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', (string) $recipe->public_id)) {
                $issues[] = $this->issue('invalid_public_id', 'critical', $recipe, 'Public ID biçimi geçersiz.');
            }
            if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', (string) $recipe->slug)) {
                $issues[] = $this->issue('invalid_slug', 'error', $recipe, 'Tarif slug biçimi geçersiz.');
            }
            if ($recipe->recipe_ingredients_count > 0 && $recipe->media->isEmpty()) {
                $issues[] = $this->issue('missing_media', 'warning', $recipe, 'Tarif görseli yok.', 'media', 'Tarife lisanslı ve doğrulanmış bir görsel ekleyin.');
            }
            if ($recipe->media->isNotEmpty() && $recipe->media->every(fn ($media): bool => (bool) $media->is_placeholder || $media->verification_status === 'branded_fallback')) {
                $issues[] = $this->issue('recipe_uses_branded_fallback_image', 'warning', $recipe, 'Tarif yalnız markalı fallback görsel kullanıyor; bu gerçek yemek fotoğrafı değildir.', 'media', 'Lisansı ve editoryal onayı bulunan, tarifle uyumlu gerçek görsel ekleyin.');
            }
            if ($recipe->media->isNotEmpty() && $recipe->media->where('is_placeholder', false)->where('verification_status', 'verified')->isEmpty()) {
                $issues[] = $this->issue('recipe_missing_verified_media', 'warning', $recipe, 'Tarif için doğrulanmış gerçek görsel bulunmuyor.', 'media', 'Lisans ve doğrulama bilgisi olan gerçek görsel ekleyin.');
            }
            if ($recipe->content_origin === 'imported' && (! $recipe->source_name || ! $recipe->source_url)) {
                $issues[] = $this->issue('missing_provenance', 'warning', $recipe, 'İçe aktarılan tarifin kaynak bilgisi eksik.', 'provenance', 'Kaynak adı ve izinli kaynak bağlantısını ekleyin.');
            }
            if ($recipe->provenances_count === 0) {
                $issues[] = $this->issue('missing_content_source', 'error', $recipe, 'Tarif için aktif içerik kaynağı bulunmuyor.', 'provenance', 'Tarif provenance kaydını tamamlayın.');
            }
            if ($recipe->verification_status === 'needs_review') {
                $issues[] = $this->issue('recipe_needs_editorial_review', 'warning', $recipe, 'Tarif editoryal doğrulama bekliyor.', 'data_trust', 'Editoryal incelemeyi tamamlayın.');
            }
            if ($recipe->status === 'published' && $recipe->verification_status !== 'verified') {
                $issues[] = $this->issue('published_unverified_recipe', 'critical', $recipe, 'Yayındaki tarif doğrulanmış değil.', 'data_trust', 'Yayından önce editoryal doğrulamayı tamamlayın.');
            }
            if ($recipe->image_status === 'missing') {
                $issues[] = $this->issue('missing_recipe_image_status', 'info', $recipe, 'Tarifin görsel durumu eksik.');
            }
            if ($recipe->image_status !== 'missing' && ! $recipe->image_license) {
                $issues[] = $this->issue('missing_recipe_image_license', 'warning', $recipe, 'Tarif görselinin lisansı belirtilmemiş.');
            }
        }

        foreach (Ingredient::query()->lazyById((int) config('performance.quality_scan_chunk_size')) as $ingredient) {
            if (! $ingredient->public_id) {
                $issues[] = $this->modelIssue('missing_ingredient_public_id', 'critical', $ingredient, 'Malzeme public ID eksik.');
            }
            if (! $ingredient->is_active && $ingredient->recipeIngredients()->exists()) {
                $issues[] = $this->modelIssue('inactive_ingredient_in_use', 'warning', $ingredient, 'Pasif malzeme aktif tarif ilişkilerinde kullanılıyor.');
            }
        }

        foreach (IngredientAlias::query()->select('normalized_alias')->groupBy('normalized_alias')->havingRaw('COUNT(*) > 1')->get() as $alias) {
            $issues[] = $this->genericIssue('duplicate_ingredient_alias', 'warning', IngredientAlias::class, (string) $alias->normalized_alias, 'Aynı normalize malzeme takma adı birden fazla kez kullanılmış.');
        }
        foreach (IngredientSubstitution::query()->with('ingredient')->whereColumn('ingredient_id', 'substitute_ingredient_id')->get() as $substitution) {
            $issues[] = $this->genericIssue('self_reference_substitution', 'error', IngredientSubstitution::class, $substitution->ingredient?->public_id, 'Malzeme kendisine alternatif olarak tanımlanmış.');
        }
        foreach (RecipeIngredient::query()->with('recipe')->where(fn ($query) => $query->where('quantity', '<', 0)->orWhere('grams', '<', 0))->get() as $item) {
            $issues[] = $this->genericIssue('negative_recipe_ingredient_quantity', 'error', RecipeIngredient::class, $item->recipe?->public_id, 'Tarif malzemesinde negatif miktar var.');
        }
        foreach (RecipeIngredient::query()->with('recipe')->get() as $item) {
            $entity = $item->recipe?->public_id;
            if ($item->recipe_ingredient_group_id === null) {
                $issues[] = $this->genericIssue('missing_recipe_ingredient_group', 'warning', RecipeIngredient::class, $entity, 'Tarif malzemesi bir gruba bağlı değil.');
            }
            if ($item->display_quantity === null || ! $item->display_measure_code) {
                $issues[] = $this->genericIssue('missing_household_measurement', 'warning', RecipeIngredient::class, $entity, 'Tarif malzemesinde kullanıcıya gösterilecek ev ölçüsü eksik.');
            }
            if ($item->canonical_quantity === null || ! $item->canonical_unit) {
                $issues[] = $this->genericIssue('missing_canonical_measurement', 'warning', RecipeIngredient::class, $entity, 'Tarif malzemesinde canonical miktar eksik.');
            }
            if ($item->display_quantity !== null && (float) $item->display_quantity <= 0) {
                $issues[] = $this->genericIssue('invalid_household_quantity', 'error', RecipeIngredient::class, $entity, 'Tarif malzemesindeki ev ölçüsü sıfır veya negatif.');
            }
            if ($item->display_measure_code && ! Unit::query()->where('code', $item->display_measure_code)->where('is_active', true)->exists()) {
                $issues[] = $this->genericIssue('invalid_measure_code', 'error', RecipeIngredient::class, $entity, 'Tarif malzemesinde geçersiz veya pasif ölçü kodu var.');
            }
            if ($item->conversion_method === 'generic_estimate') {
                $issues[] = $this->genericIssue('generic_estimate_conversion', 'warning', RecipeIngredient::class, $entity, 'Tarif malzemesi genel tahmin dönüşümü kullanıyor.');
            }
            if (in_array($item->conversion_confidence, ['low', 'unknown'], true)) {
                $issues[] = $this->genericIssue('low_confidence_conversion', 'info', RecipeIngredient::class, $entity, 'Tarif malzemesi düşük güvenli veya doğrulanmamış dönüşüm kullanıyor.');
            }
        }
        foreach (RecipeIngredientGroup::query()->withCount('ingredients')->get() as $group) {
            if ($group->ingredients_count === 0) {
                $issues[] = $this->genericIssue('empty_recipe_ingredient_group', 'info', RecipeIngredientGroup::class, $group->public_id, 'Tarif malzeme grubu boş.');
            }
        }
        foreach (IngredientUnitConversion::query()->get() as $conversion) {
            if (! $conversion->source_name) {
                $issues[] = $this->genericIssue('conversion_missing_source', 'warning', IngredientUnitConversion::class, $conversion->public_id, 'Malzeme dönüşümünün kaynak bilgisi eksik.', 'measurements', 'Dönüşüm kaynağını ve doğrulama tarihini ekleyin.');
            }
            if ($conversion->canonical_quantity <= 0 || $conversion->household_quantity <= 0) {
                $issues[] = $this->genericIssue('invalid_conversion_quantity', 'error', IngredientUnitConversion::class, $conversion->public_id, 'Malzeme dönüşümünde geçersiz miktar var.');
            }
            if ((int) $conversion->confidence_score < (int) config('data_quality.thresholds.conversion_low_confidence') || in_array($conversion->verification_status, ['low', 'unknown'], true)) {
                $issues[] = $this->genericIssue('conversion_low_confidence', 'warning', IngredientUnitConversion::class, $conversion->public_id, 'Malzeme dönüşümünün güven skoru düşük.', 'measurements', 'Malzeme bazlı kaynağı inceleyip dönüşümü doğrulayın.');
            }
        }
        foreach (NutritionFact::query()->with(['nutritionable', 'nutrient'])->get() as $fact) {
            $owner = $fact->nutritionable;
            $ownerId = is_object($owner) && isset($owner->public_id) ? (string) $owner->public_id : 'nutrition-'.$fact->nutrient_id;
            $factKey = $ownerId.'-'.((string) optional($fact->nutrient)->code ?: 'nutrient');
            if ($fact->value < 0) {
                $issues[] = $this->genericIssue('negative_nutrition_value', 'error', NutritionFact::class, $factKey, 'Besin değerinde negatif değer var.');
            }
            if ((int) $fact->confidence_score < (int) config('data_quality.thresholds.nutrition_low_confidence') || in_array($fact->origin, ['ai_estimated', 'calculated'], true)) {
                $issues[] = $this->genericIssue('nutrition_low_confidence', 'warning', NutritionFact::class, $factKey, 'Besin değeri düşük güvenli veya tahmine dayalı.', 'nutrition', 'Besin verisini güvenilir bir kaynakla doğrulayın.');
            }
            if ($fact->origin === 'editorial') {
                $issues[] = $this->genericIssue('nutrition_editorial_estimate', 'warning', NutritionFact::class, $factKey, 'Besin değeri editoryal tahmin olarak işaretli.', 'nutrition', 'Editoryal tahmini doğrulanmış kaynakla güncelleyin.');
            }
            if (! $fact->source_name) {
                $issues[] = $this->genericIssue('nutrition_missing_source', 'warning', NutritionFact::class, $factKey, 'Besin değerinin kaynak bilgisi eksik.', 'nutrition', 'Besin kaynağını ve doğrulama tarihini ekleyin.');
            }
        }

        $placeholderPaths = Recipe::query()->with('media')->get()->flatMap(fn (Recipe $recipe) => $recipe->media->filter(fn ($media): bool => (bool) $media->is_placeholder || $media->verification_status === 'branded_fallback')->pluck('path'));
        foreach ($placeholderPaths->filter()->countBy()->filter(fn (int $count): bool => $count >= (int) config('data_quality.thresholds.placeholder_repetition'))->all() as $path => $count) {
            $issues[] = $this->genericIssue('catalogue_placeholder_repetition', 'warning', 'CatalogueMedia', hash('sha256', (string) $path), 'Aynı yer tutucu görsel katalogda '.$count.' kez kullanılıyor.', 'media', 'Öncelikli tarifler için farklı, doğrulanmış görseller hazırlayın.', ['repetition_count' => $count]);
        }
        foreach (AiImageGeneration::query()->with('recipe')->where('status', 'blocked_recipe_fidelity')->get() as $generation) {
            if ($generation->recipe) {
                $issues[] = $this->issue('ai_image_recipe_fidelity_blocked', 'error', $generation->recipe, 'AI görsel promptu, tarif adıyla uyumlu temel malzeme doğrulaması eksik olduğu için bloklandı.', 'data_trust', 'Önce tarifin temel malzemelerini editoryal olarak doğrulayın.');
            }
        }
        if (app()->environment(['local', 'testing'])) {
            $fixturesPresent = User::query()->whereIn('email', ['member-a@housechef.local', 'member-b@housechef.local'])->count() === 2;
            if (! $fixturesPresent) {
                $issues[] = $this->genericIssue('member_browser_qa_fixture_missing', 'info', 'OperationalQa', 'member-fixtures', 'Member tarayıcı QA fixture hesapları henüz oluşturulmadı.', 'operational_confidence', 'Yerel ortamda qa:seed-fixtures komutunu çalıştırın.');
            }
            if (! Cache::get('qa_browser.last_success')) {
                $issues[] = $this->genericIssue('critical_browser_flow_not_verified', 'info', 'OperationalQa', 'browser-smoke', 'Kritik üye tarayıcı akışları için güncel QA kanıtı yok.', 'operational_confidence', 'Yerel browser smoke kontrolünü çalıştırın.');
            }
        }

        return $issues;
    }

    /** @return array{new:int,updated:int,resolved:int,score:int} */
    public function scan(): array
    {
        $found = $this->report();
        $keys = [];
        $new = 0;
        $updated = 0;
        foreach ($found as $item) {
            $key = $item['issue_type'].'|'.$item['entity_type'].'|'.$item['entity_public_id'];
            $keys[] = $key;
            $issue = DataQualityIssue::query()->where('issue_type', $item['issue_type'])->where('entity_type', $item['entity_type'])->where('entity_public_id', $item['entity_public_id'])->where('resolution_status', 'open')->first();
            if ($issue) {
                $issue->update($item + ['last_detected_at' => now()]);
                $updated++;
            } else {
                DataQualityIssue::create($item + ['detected_at' => now(), 'first_detected_at' => now(), 'last_detected_at' => now(), 'resolution_status' => 'open']);
                $new++;
            }
        }
        $resolved = 0;
        foreach (DataQualityIssue::query()->where('resolution_status', 'open')->get() as $issue) {
            if (! in_array($issue->issue_type.'|'.$issue->entity_type.'|'.$issue->entity_public_id, $keys, true)) {
                $issue->update(['resolution_status' => 'resolved', 'resolved_at' => now()]);
                $resolved++;
            }
        }

        $previous = Cache::get('data_quality.last_score');
        $score = $this->score();
        Cache::forever('data_quality.last_score', $score);
        Cache::forever(self::LAST_SCAN_KEY, now()->toIso8601String());

        return ['new' => $new, 'updated' => $updated, 'resolved' => $resolved, 'score' => $score, 'previous_score' => is_int($previous) ? $previous : null];
    }

    public function score(): int
    {
        return $this->breakdown()['overall']['score'];
    }

    /** @return array<string, int> */
    public function categoryScores(): array
    {
        return collect($this->breakdown())->map(fn (array $item): int => $item['score'])->all();
    }

    /** @return array<string, array{label:string,weight:int,score:int,open:int,failed:int,warning:int,critical:int,reasons:list<string>}> */
    public function breakdown(): array
    {
        $issues = DataQualityIssue::query()->where('resolution_status', 'open')->get();
        $rows = [];
        $weightedScore = 0.0;
        foreach (config('data_quality.categories') as $key => $definition) {
            $categoryIssues = $issues->filter(fn (DataQualityIssue $issue): bool => ($issue->category ?: $this->categoryFor($issue->issue_type, $issue->entity_type)) === $key);
            $loss = $categoryIssues->sum(fn (DataQualityIssue $issue): int => (int) config('data_quality.penalties.'.$issue->severity, 0));
            $score = max(0, 100 - $loss);
            $weight = (int) $definition['weight'];
            $weightedScore += $score * ($weight / 100);
            $rows[$key] = [
                'label' => (string) $definition['label'], 'weight' => $weight, 'score' => $score,
                'open' => $categoryIssues->count(),
                'failed' => $categoryIssues->whereIn('severity', ['critical', 'error'])->count(),
                'warning' => $categoryIssues->where('severity', 'warning')->count(),
                'critical' => $categoryIssues->where('severity', 'critical')->count(),
                'reasons' => $categoryIssues->sortByDesc(fn (DataQualityIssue $issue): int => (int) config('data_quality.penalties.'.$issue->severity, 0))->pluck('message')->unique()->take(3)->values()->all(),
            ];
        }
        $rows['overall'] = ['label' => 'Genel skor', 'weight' => 100, 'score' => (int) round($weightedScore), 'open' => $issues->count(), 'failed' => $issues->whereIn('severity', ['critical', 'error'])->count(), 'warning' => $issues->where('severity', 'warning')->count(), 'critical' => $issues->where('severity', 'critical')->count(), 'reasons' => $issues->sortByDesc(fn (DataQualityIssue $issue): int => (int) config('data_quality.penalties.'.$issue->severity, 0))->pluck('message')->unique()->take(5)->values()->all()];

        return $rows;
    }

    private function issue(string $type, string $severity, Recipe $recipe, string $message, ?string $category = null, ?string $suggestedAction = null): array
    {
        return $this->genericIssue($type, $severity, Recipe::class, $recipe->public_id, $message, $category, $suggestedAction);
    }

    private function modelIssue(string $type, string $severity, Ingredient $ingredient, string $message): array
    {
        return $this->genericIssue($type, $severity, Ingredient::class, $ingredient->public_id, $message);
    }

    /** @return array<string, mixed> */
    private function genericIssue(string $type, string $severity, string $entityType, ?string $entityPublicId, string $message, ?string $category = null, ?string $suggestedAction = null, array $metadata = []): array
    {
        return ['issue_type' => $type, 'severity' => $severity, 'category' => $category ?? $this->categoryFor($type, $entityType), 'message' => $message, 'suggested_action' => $suggestedAction ?? 'Kayıt doğrulama kontrol listesini inceleyin.', 'entity_type' => $entityType, 'entity_public_id' => $entityPublicId, 'metadata' => $metadata];
    }

    private function categoryFor(string $type, string $entityType): string
    {
        if (str_contains($type, 'media') || str_contains($type, 'image')) {
            return 'media';
        }
        if (str_contains($type, 'conversion') || str_contains($type, 'measurement') || $entityType === IngredientUnitConversion::class) {
            return 'measurements';
        }
        if (str_contains($type, 'nutrition') || $entityType === NutritionFact::class) {
            return 'nutrition';
        }
        if (str_contains($type, 'allergen')) {
            return 'allergens';
        }
        if (str_contains($type, 'dietary')) {
            return 'dietary';
        }
        if (str_contains($type, 'provenance') || str_contains($type, 'content_source') || str_contains($type, 'verification') || str_contains($type, 'unverified')) {
            return 'provenance';
        }
        if ($entityType === Ingredient::class || $entityType === IngredientAlias::class || $entityType === IngredientSubstitution::class) {
            return 'data_trust';
        }

        return 'content_completeness';
    }
}
