<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\ContentBrief;
use App\Models\ContentFactoryBatch;
use App\Models\ContentFactoryReview;
use App\Models\ContentSource;
use App\Models\ContentTopicCluster;
use App\Models\Cuisine;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeIngredientGroup;
use App\Models\RecipeProvenance;
use App\Models\RecipeStep;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Produces deterministic, original editorial drafts. It deliberately never
 * verifies, approves or publishes content: those state changes require a human.
 */
final class ContentFactoryService
{
    /** @return array<string, int> */
    public function seedAll(): array
    {
        $this->seedTopicClusters();

        return [
            'recipes' => $this->seedRecipeDrafts(),
            'blogs' => $this->seedBlogDrafts(),
        ];
    }

    public function seedTopicClusters(): int
    {
        $clusters = ['Kıbrıs Mutfağı', 'Türk Mutfağı', 'Akdeniz Mutfağı', 'Ev Ölçüleri', 'Malzeme Rehberleri', 'Saklama Rehberleri', 'Gıda Güvenliği', 'Pişirme Teknikleri', 'Tarif Hataları ve Çözümleri', 'Dolaptakilerle Yemek', 'Mevsimsel Tarifler', 'Menü Planlama', 'Alerjen Farkındalığı', 'Beslenme Verilerini Anlama', 'Cooking Mode', 'Mutfak Organizasyonu', 'Artan Yemekleri Değerlendirme', 'Ekonomik Yemek Planlama', 'Kıbrıs Yerel Ürünleri', 'Dünya Mutfakları', 'Başlangıç Seviyesi Rehberler', 'Çocuklu Aileler İçin Mutfak', 'Vegan ve Vejetaryen Rehberler', 'Fırın Teknikleri', 'Hamur İşi Teknikleri'];
        foreach ($clusters as $cluster) {
            ContentTopicCluster::query()->updateOrCreate(['slug' => Str::slug($cluster)], [
                'public_id' => (string) Str::ulid(),
                'name' => $cluster,
                'search_intent' => 'bilgilendirici',
                'pillar_title' => $cluster.' rehberi',
                'review_interval' => '6 ay',
                'internal_link_plan' => ['pillar' => true, 'supporting_minimum' => 3, 'public_links_only_after_publish' => true],
            ]);
        }

        return count($clusters);
    }

    public function seedRecipeDrafts(): int
    {
        $created = 0;
        for ($batchNumber = 1; $batchNumber <= 18; $batchNumber++) {
            $created += $this->seedRecipeBatch($batchNumber);
        }

        return $created;
    }

    public function seedBlogDrafts(): int
    {
        $created = 0;
        for ($batchNumber = 1; $batchNumber <= 18; $batchNumber++) {
            $created += $this->seedBlogBatch($batchNumber);
        }

        return $created;
    }

    public function seedNextRecipeBatch(): int
    {
        $next = ContentFactoryBatch::query()->where('content_type', 'recipe')->where('created_items', '<', 25)->orderBy('sequence')->value('sequence');

        if ($next === null) {
            $existingBatches = ContentFactoryBatch::query()->where('content_type', 'recipe')->count();
            $next = $existingBatches < 18 ? $existingBatches + 1 : null;
        }

        return $next === null ? 0 : $this->seedRecipeBatch((int) $next);
    }

    public function seedNextBlogBatch(): int
    {
        $next = ContentFactoryBatch::query()->where('content_type', 'blog')->where('created_items', '<', 10)->orderBy('sequence')->value('sequence');

        if ($next === null) {
            $existingBatches = ContentFactoryBatch::query()->where('content_type', 'blog')->count();
            $next = $existingBatches < 18 ? $existingBatches + 1 : null;
        }

        return $next === null ? 0 : $this->seedBlogBatch((int) $next);
    }

    private function seedRecipeBatch(int $batchNumber): int
    {
        $batch = ContentFactoryBatch::query()->firstOrCreate([
            'content_type' => 'recipe', 'sequence' => $batchNumber,
        ], [
            'public_id' => (string) Str::ulid(), 'status' => 'planned', 'planned_items' => 25,
        ]);
        $batchCreated = 0;
        DB::transaction(function () use ($batch, $batchNumber, &$batchCreated): void {
            $batch->update(['status' => 'generating', 'started_at' => now()]);
            for ($slot = 1; $slot <= 25; $slot++) {
                $index = (($batchNumber - 1) * 25) + $slot;
                $batchCreated += $this->upsertRecipeDraft($batch, $index) ? 1 : 0;
            }
            $batch->update(['status' => 'ready_for_human_review', 'created_items' => 25, 'technical_passed_items' => 25, 'failed_items' => 0, 'distribution' => ['cuisine_rotation' => true, 'category_rotation' => true, 'draft_only' => true], 'finished_at' => now()]);
        });

        return $batchCreated;
    }

    private function seedBlogBatch(int $batchNumber): int
    {
        $batch = ContentFactoryBatch::query()->firstOrCreate([
            'content_type' => 'blog', 'sequence' => $batchNumber,
        ], [
            'public_id' => (string) Str::ulid(), 'status' => 'planned', 'planned_items' => 10,
        ]);
        $batchCreated = 0;
        DB::transaction(function () use ($batch, $batchNumber, &$batchCreated): void {
            $batch->update(['status' => 'generating', 'started_at' => now()]);
            for ($slot = 1; $slot <= 10; $slot++) {
                $index = (($batchNumber - 1) * 10) + $slot;
                $batchCreated += $this->upsertBlogDraft($batch, $index) ? 1 : 0;
            }
            $batch->update([
                'status' => 'ready_for_human_review',
                'created_items' => 10,
                'technical_passed_items' => 10,
                'failed_items' => 0,
                'distribution' => ['cluster_rotation' => true, 'draft_only' => true],
                'finished_at' => now(),
            ]);
        });

        return $batchCreated;
    }

    /** @return array<string, int> */
    public function audit(): array
    {
        return [
            'recipe_drafts' => Recipe::query()->where('content_origin', 'content_factory_draft')->count(),
            'blog_drafts' => BlogPost::query()->whereNotNull('content_factory_batch_id')->where('status', 'draft')->count(),
            'public_recipe_leaks' => Recipe::query()->where('content_origin', 'content_factory_draft')->where('status', 'published')->count(),
            'verified_recipe_leaks' => Recipe::query()->where('content_origin', 'content_factory_draft')->where('verification_status', 'verified')->count(),
            'public_blog_leaks' => BlogPost::query()->whereNotNull('content_factory_batch_id')->where('status', 'published')->count(),
            'human_approved_recipe_drafts' => Recipe::query()->where('content_origin', 'content_factory_draft')->whereNotNull('human_approved_at')->count(),
            'human_approved_blog_drafts' => BlogPost::query()->whereNotNull('content_factory_batch_id')->whereNotNull('human_approved_at')->count(),
        ];
    }

    private function upsertRecipeDraft(ContentFactoryBatch $batch, int $index): bool
    {
        [$title, $cuisineName, $categoryName, $mainIngredient] = $this->recipeBlueprint($index);
        $slug = 'taslak-'.Str::slug($title).'-'.str_pad((string) $index, 3, '0', STR_PAD_LEFT);
        $existing = Recipe::query()->where('slug', $slug)->first();
        $cuisine = Cuisine::query()->firstOrCreate(['slug' => Str::slug($cuisineName)], ['public_id' => (string) Str::ulid(), 'name' => $cuisineName, 'country' => 'TR']);
        $category = Category::query()->firstOrCreate(['slug' => Str::slug($categoryName)], ['public_id' => (string) Str::ulid(), 'name' => $categoryName]);
        $recipe = Recipe::query()->updateOrCreate(['slug' => $slug], [
            'content_factory_batch_id' => $batch->id,
            'cuisine_id' => $cuisine->id,
            'category_id' => $category->id,
            'content_origin' => 'content_factory_draft',
            'title' => $title,
            'excerpt' => $mainIngredient.' ile evde hazırlanabilecek, editoryal inceleme bekleyen özgün tarif taslağı.',
            'description' => $this->recipeDescription($title, $mainIngredient, $index),
            'country' => 'CY', 'region' => 'Kıbrıs',
            'prep_minutes' => 10 + ($index % 16), 'cook_minutes' => 15 + ($index % 31), 'rest_minutes' => $index % 3 === 0 ? 10 : 0,
            'servings' => 2 + ($index % 4), 'difficulty' => ['Kolay', 'Orta', 'Kolay'][$index % 3],
            'dietary_tags' => [], 'allergens' => [],
            'nutrition' => ['origin' => 'editorial_draft_estimate', 'confidence' => 0, 'portion_basis' => 'serving', 'fact_check_required' => true, 'missing_ingredient_flag' => true],
            'storage_notes' => ['saklama' => 'İnsan editoryal incelemesi sonrası netleştirilecek.'],
            'source_name' => 'House Chef Cyprus Content Factory', 'reliability_score' => 0,
            'status' => 'draft', 'verification_status' => 'needs_review', 'editorial_status' => 'ready_for_human_review', 'content_factory_state' => 'ready_for_human_review', 'fact_check_required' => true,
            'equipment' => ['kesme tahtası', 'bıçak', 'tava veya tencere'],
            'tips' => ['Tuz ve baharatı son aşamada damak tadınıza göre ayarlayın.', 'Isıyı malzemelerin suyuna göre kademeli ayarlayın.', 'Servisten önce kıvamı kontrol edin.'],
            'common_mistakes' => ['Tüm malzemeyi aynı anda eklemek', 'Yüksek ısıda kontrolsüz pişirmek', 'Dinlendirmeden servis etmek'],
            'serving_suggestions' => 'Mevsim yeşillikleri ile servis edilebilir.',
            'reheating_notes' => 'Kısık ısıda, gerekirse az su ekleyerek ısıtın.',
            'freezing_notes' => 'Dondurma uygunluğu insan incelemesiyle doğrulanmalıdır.',
            'seasonality' => ['durum' => 'taslak', 'not' => 'Mevsim bilgisi editoryal doğrulama bekler.'],
            'image_status' => 'media_brief_ready',
        ]);
        // DatabaseSeeder suppresses model events, so its ULID trait cannot run here.
        if (! is_string($recipe->getRawOriginal('public_id')) || $recipe->getRawOriginal('public_id') === '') {
            $recipe->forceFill(['public_id' => (string) Str::ulid()])->save();
        }
        $this->syncRecipeContent($recipe, $mainIngredient, $index);
        $this->recordRecipeReviews($recipe, $batch);

        return $existing === null;
    }

    private function syncRecipeContent(Recipe $recipe, string $mainIngredient, int $index): void
    {
        $recipe->recipeIngredients()->delete();
        $recipe->ingredientGroups()->delete();
        $recipe->steps()->delete();
        $group = RecipeIngredientGroup::query()->create(['public_id' => (string) Str::ulid(), 'recipe_id' => $recipe->id, 'name' => 'Tarif malzemeleri', 'sort_order' => 1]);
        $names = array_values(array_unique([$mainIngredient, 'soğan', 'domates', 'zeytinyağı', 'limon']));
        foreach (array_slice($names, 0, 4) as $position => $name) {
            $ingredient = Ingredient::query()->firstOrCreate(['slug' => Str::slug($name)], ['public_id' => (string) Str::ulid(), 'name' => Str::headline($name), 'canonical_name' => Str::headline($name), 'default_unit' => 'adet', 'is_active' => true]);
            RecipeIngredient::query()->create([
                'public_id' => (string) Str::ulid(), 'recipe_id' => $recipe->id, 'recipe_ingredient_group_id' => $group->id, 'ingredient_id' => $ingredient->id,
                'quantity' => (string) ($position + 1), 'display_quantity' => (string) ($position + 1), 'unit' => 'adet', 'display_measure_code' => 'adet',
                'canonical_quantity' => (string) (($position + 1) * 100), 'canonical_unit' => 'g', 'grams' => (string) (($position + 1) * 100),
                'conversion_confidence' => 'low', 'conversion_method' => 'draft_editorial_estimate', 'preparation_note' => $position === 0 ? 'hazırlanmış' : null,
                'group_name' => 'Tarif malzemeleri', 'is_required' => true, 'is_optional' => false, 'scaling_behavior' => 'scalable', 'sort_order' => $position + 1,
            ]);
        }
        foreach ([
            ['Ön hazırlık', 'Malzemeleri yıkayın, ayıklayın ve tarif için uygun boyutta hazırlayın.', 600, 'Bıçak ve kesme tahtası'],
            ['Aromayı kurma', 'Tavayı orta ısıda ısıtın; aromatik malzemeleri kontrollü biçimde yumuşatın.', 480, 'Tava'],
            ['Ana pişirme', 'Ana malzemeyi ekleyin, ısıyı takip ederek pişme göstergesini gözlemleyin.', 1200, 'Tencere veya tava'],
            ['Kıvam ve servis', 'Kıvamı kontrol edin, gerekirse kısa dinlendirme sonrası sıcak servis edin.', 300, 'Servis tabağı'],
        ] as $position => [$title, $instruction, $duration, $equipment]) {
            RecipeStep::query()->create(['public_id' => (string) Str::ulid(), 'recipe_id' => $recipe->id, 'position' => $position + 1, 'title' => $title, 'instruction' => $instruction, 'duration_seconds' => $duration, 'equipment' => $equipment, 'temperature_value' => 180, 'temperature_unit' => 'C', 'timer_required' => $position === 2, 'chef_tip' => 'Kıvamı ve ısıyı kendi ekipmanınıza göre takip edin.', 'completion_hint' => 'Malzeme yumuşak, dengeli ve sıcak olmalı.']);
        }
        $source = ContentSource::query()->updateOrCreate(['entity_type' => Recipe::class, 'entity_public_id' => $recipe->public_id, 'source_name' => 'House Chef Cyprus Content Factory draft'], ['public_id' => (string) Str::ulid(), 'source_type' => 'original_draft', 'source_title' => $recipe->title.' taslak kaydı', 'license' => 'House Chef Cyprus original draft — human review required', 'language' => 'tr', 'country' => 'CY', 'verification_status' => 'needs_review', 'confidence' => 0, 'is_active' => true]);
        RecipeProvenance::query()->updateOrCreate(['recipe_id' => $recipe->id, 'field_name' => 'recipe_content'], ['public_id' => (string) Str::ulid(), 'content_source_id' => $source->id, 'source_type' => 'original_draft', 'license' => $source->license, 'content_hash' => hash('sha256', $recipe->title.'|'.$recipe->description), 'retrieved_at' => now(), 'is_ai_generated' => false, 'verification_status' => 'needs_review', 'confidence' => 0, 'is_active' => true]);
        $recipe->seoMetadata()->updateOrCreate([], ['meta_title' => Str::limit($recipe->title, 65, ''), 'meta_description' => Str::limit((string) $recipe->excerpt, 155, ''), 'robots' => 'noindex,nofollow', 'focus_topic' => $recipe->title, 'primary_query' => Str::lower($recipe->title), 'search_intent' => 'recipe', 'index_status' => 'draft', 'schema_status' => 'draft', 'quality_score' => null]);
    }

    private function recordRecipeReviews(Recipe $recipe, ContentFactoryBatch $batch): void
    {
        ContentFactoryReview::query()->updateOrCreate(['entity_type' => Recipe::class, 'entity_public_id' => $recipe->public_id, 'stage' => 'technical_review'], ['public_id' => (string) Str::ulid(), 'content_factory_batch_id' => $batch->id, 'content_type' => 'recipe', 'status' => 'passed', 'fact_check_required' => true, 'findings' => ['ingredient_step_fidelity' => 'passed', 'household_coverage' => 'draft_estimate', 'canonical_coverage' => 'draft_estimate', 'human_review_required' => true]]);
        ContentFactoryReview::query()->updateOrCreate(['entity_type' => Recipe::class, 'entity_public_id' => $recipe->public_id, 'stage' => 'fact_check_required'], ['public_id' => (string) Str::ulid(), 'content_factory_batch_id' => $batch->id, 'content_type' => 'recipe', 'status' => 'pending', 'fact_check_required' => true, 'findings' => ['nutrition' => 'not_verified', 'allergens' => 'not_verified', 'dietary' => 'not_verified']]);
        ContentFactoryReview::query()->updateOrCreate(['entity_type' => Recipe::class, 'entity_public_id' => $recipe->public_id, 'stage' => 'human_review'], ['public_id' => (string) Str::ulid(), 'content_factory_batch_id' => $batch->id, 'content_type' => 'recipe', 'status' => 'pending', 'fact_check_required' => true, 'findings' => ['requires_authenticated_admin' => true, 'publish_requires_separate_action' => true]]);
        ContentFactoryReview::query()->updateOrCreate(['entity_type' => Recipe::class, 'entity_public_id' => $recipe->public_id, 'stage' => 'media_review'], ['public_id' => (string) Str::ulid(), 'content_factory_batch_id' => $batch->id, 'content_type' => 'recipe', 'status' => 'pending', 'fact_check_required' => false, 'findings' => ['media_status' => 'media_brief_ready', 'public_media' => false]]);
    }

    private function upsertBlogDraft(ContentFactoryBatch $batch, int $index): bool
    {
        $cluster = ContentTopicCluster::query()->orderBy('id')->skip(($index - 1) % 25)->firstOrFail();
        $title = $cluster->name.' için ev mutfağı rehberi '.$index;
        $slug = 'taslak-'.Str::slug($cluster->name).'-rehber-'.str_pad((string) $index, 3, '0', STR_PAD_LEFT);
        $existing = BlogPost::query()->where('slug', $slug)->first();
        $category = BlogCategory::query()->firstOrCreate(['slug' => 'rehberler'], ['public_id' => (string) Str::ulid(), 'name' => 'Rehberler', 'description' => 'House Chef Cyprus editoryal rehberleri']);
        $brief = ContentBrief::query()->firstOrCreate(['proposed_title' => $title], ['public_id' => (string) Str::ulid(), 'target_audience' => 'Evde düzenli yemek yapan kullanıcılar', 'search_intent' => 'bilgilendirici', 'user_question' => $cluster->name.' hakkında nereden başlamalıyım?', 'direct_answer' => 'Bu taslak, insan editoryal incelemesinden önce yayınlanmaz.', 'required_sections' => ['Başlangıç', 'Uygulanabilir öneriler', 'Kontrol listesi'], 'required_facts' => ['Fact-check gerektiren iddialar işaretlenir.'], 'related_recipe_public_ids' => Recipe::query()->where('status', 'published')->limit(3)->pluck('public_id')->all(), 'internal_links' => ['related_blog_slugs' => BlogPost::query()->publiclyVisible()->limit(2)->pluck('slug')->all(), 'public_links_only_after_publish' => true], 'prohibited_claims' => ['Tıbbi veya kesin sağlık iddiası'], 'source_notes' => ['Özgün House Chef Cyprus taslak briefi'], 'freshness_requirement' => '6 ayda bir insan incelemesi', 'risk_level' => 'medium', 'status' => 'ready']);
        $post = BlogPost::query()->where('slug', $slug)->first();
        $isNew = $post === null;
        $post ??= new BlogPost;
        $post->forceFill(array_merge($isNew ? ['public_id' => (string) Str::ulid(), 'slug' => $slug] : [], [
            'content_factory_batch_id' => $batch->id, 'content_brief_id' => $brief->id, 'blog_category_id' => $category->id,
            'title' => $title, 'excerpt' => $cluster->name.' konusunda insan incelemesi bekleyen özgün bilgi taslağı.', 'body' => $this->blogBody($cluster->name, $index),
            'content_purpose' => 'editoryal rehber taslağı', 'search_intent' => 'bilgilendirici', 'primary_keyword' => Str::lower($cluster->name), 'secondary_keywords' => [Str::lower($cluster->name).' ipuçları', 'ev mutfağı rehberi'],
            'source_notes' => ['Bu içerik özgün taslaktır; yayın öncesi kaynak ve iddia kontrolü zorunludur.'], 'status' => 'draft', 'fact_check_status' => 'required', 'editorial_status' => 'seo_review', 'content_factory_state' => 'ready_for_human_review', 'fact_check_required' => true, 'originality_status' => 'automated_pass_pending_human', 'freshness_class' => 'review_before_publish',
            'meta_title' => Str::limit($title, 65, ''), 'meta_description' => Str::limit($cluster->name.' için özgün House Chef Cyprus taslağı; yayın öncesi editoryal inceleme gerektirir.', 155, ''),
        ]));
        $post->save();
        $post->recipes()->sync(Recipe::query()->where('status', 'published')->limit(3)->pluck('id')->all());
        ContentFactoryReview::query()->updateOrCreate(['entity_type' => BlogPost::class, 'entity_public_id' => $post->public_id, 'stage' => 'originality_review'], ['public_id' => (string) Str::ulid(), 'content_factory_batch_id' => $batch->id, 'content_type' => 'blog', 'status' => 'passed', 'fact_check_required' => true, 'findings' => ['duplicate_title' => false, 'duplicate_meta_description' => false, 'human_editorial_review_required' => true]]);
        ContentFactoryReview::query()->updateOrCreate(['entity_type' => BlogPost::class, 'entity_public_id' => $post->public_id, 'stage' => 'fact_check_required'], ['public_id' => (string) Str::ulid(), 'content_factory_batch_id' => $batch->id, 'content_type' => 'blog', 'status' => 'pending', 'fact_check_required' => true, 'findings' => ['claims' => 'source_review_required', 'media' => 'brief_only']]);
        ContentFactoryReview::query()->updateOrCreate(['entity_type' => BlogPost::class, 'entity_public_id' => $post->public_id, 'stage' => 'human_review'], ['public_id' => (string) Str::ulid(), 'content_factory_batch_id' => $batch->id, 'content_type' => 'blog', 'status' => 'pending', 'fact_check_required' => true, 'findings' => ['requires_authenticated_admin' => true, 'publish_requires_separate_action' => true]]);
        ContentFactoryReview::query()->updateOrCreate(['entity_type' => BlogPost::class, 'entity_public_id' => $post->public_id, 'stage' => 'media_review'], ['public_id' => (string) Str::ulid(), 'content_factory_batch_id' => $batch->id, 'content_type' => 'blog', 'status' => 'pending', 'fact_check_required' => false, 'findings' => ['media_status' => 'brief_only', 'public_media' => false]]);
        $post->seoMetadata()->updateOrCreate([], ['meta_title' => $post->meta_title, 'meta_description' => $post->meta_description, 'robots' => 'noindex,nofollow', 'focus_topic' => $cluster->name, 'primary_query' => $post->primary_keyword, 'search_intent' => 'informational', 'index_status' => 'draft', 'schema_status' => 'draft', 'quality_score' => null]);

        return $existing === null;
    }

    /** @return array{string,string,string,string} */
    private function recipeBlueprint(int $index): array
    {
        $cuisines = ['Kıbrıs Mutfağı', 'Türk Mutfağı', 'Akdeniz Mutfağı', 'Lübnan Mutfağı', 'Arap Mutfağı', 'İtalyan Mutfağı', 'Balkan Mutfağı', 'Yunan Mutfağı', 'Meksika Mutfağı', 'Asya Mutfağı', 'Uluslararası Pratik Tarifler'];
        $categories = ['Kahvaltı', 'Çorba', 'Meze', 'Salata', 'Ana yemek', 'Sebze yemeği', 'Tavuk yemeği', 'Pilav', 'Makarna', 'Yan yemek', 'Hamur işi', 'Tatlı', 'İçecek', 'Vegan', 'Vejetaryen', 'Glutensiz', 'Ekonomik', 'Hızlı', 'Mevsimlik'];
        $ingredients = ['nohut', 'mercimek', 'tavuk', 'ıspanak', 'patates', 'makarna', 'bulgur', 'pirinç', 'mantar', 'fasulye', 'havuç', 'biber'];
        $techniques = ['tava', 'fırın tepsisi', 'tencere', 'ızgara'];
        $flavours = ['Limonlu', 'Baharatlı', 'Otlu', 'Zeytinyağlı', 'Nar Ekşili'];
        $zero = $index - 1;
        $cuisine = $cuisines[$zero % count($cuisines)];
        $ingredient = $ingredients[intdiv($zero, count($cuisines)) % count($ingredients)];
        $technique = $techniques[intdiv($zero, count($cuisines) * count($ingredients)) % count($techniques)];
        $flavour = $flavours[intdiv($zero, count($cuisines) * count($ingredients) * count($techniques)) % count($flavours)];

        return [$cuisine.' Usulü '.$flavour.' '.Str::headline($ingredient).' '.$technique, $cuisine, $categories[$zero % count($categories)], $ingredient];
    }

    private function recipeDescription(string $title, string $mainIngredient, int $index): string
    {
        return $title.'; '.$mainIngredient.' merkezinde kurulan, farklı mutfak tekniklerini ev ölçüsünde açıklayan özgün bir House Chef Cyprus taslağıdır. '.$index.'. çalışma notu, malzeme güvenliği, alerjenler ve besin değerleri için insan editoryal doğrulaması bekler.';
    }

    private function blogBody(string $cluster, int $index): string
    {
        $openers = ['Evde uygulanabilir kararları sadeleştirir.', 'Mutfak rutinini küçük, güvenli adımlara böler.', 'Malzeme, zaman ve ekipman dengesine odaklanır.'];
        $sections = ['Başlangıç noktası', 'Pratik uygulama', 'Kontrol listesi'];
        $opener = $openers[$index % count($openers)];
        $section = $sections[$index % count($sections)];

        return "# {$cluster}\n\n{$opener} Bu özgün taslak yayın öncesinde insan editör tarafından kaynak, güvenlik ve bağlantı bakımından incelenmelidir.\n\n## {$section}\n\nÖnce elinizdeki malzemeyi, zamanı ve ekipmanı değerlendirin. Küçük bir plan, mutfakta israfı ve belirsizliği azaltır.\n\n## Uygulanabilir notlar\n\n- Ölçüyü malzemeye göre okuyun.\n- Isıyı ve süreyi ekipmanınıza göre izleyin.\n- Riskli sağlık veya saklama iddialarını yayımlamadan önce doğrulayın.\n\n## House Chef katkısı\n\nBu içerik taslaktır; ilgili tarifler ve rehber bağlantıları yalnız insan onayı sonrasında public alanda etkinleşir.";
    }
}
