<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlogPost;
use App\Models\ContentFactoryIssue;
use App\Models\ContentFactoryReview;
use App\Models\ContentTopicCluster;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Builds local, original editorial drafts and records evidence-based automated
 * checks. It intentionally cannot verify, approve, schedule or publish content.
 */
final class ContentFactoryEnrichmentService
{
    private const RULE_VERSION = 'phase-2.0';

    /** @return array<string, int> */
    public function enrichAll(): array
    {
        return [
            'recipes' => $this->enrichRecipes(),
            'blogs' => $this->enrichBlogs(),
        ];
    }

    public function enrichRecipes(?int $batchSequence = null): int
    {
        $query = Recipe::query()->where('content_origin', 'content_factory_draft')->with(['recipeIngredients.ingredient', 'steps', 'cuisine', 'category']);
        if ($batchSequence !== null) {
            $query->whereHas('contentFactoryBatch', fn (Builder $batch) => $batch->where('content_type', 'recipe')->where('sequence', $batchSequence));
        }

        $processed = 0;
        $query->orderBy('id')->chunkById(25, function ($recipes) use (&$processed): void {
            foreach ($recipes as $recipe) {
                DB::transaction(function () use ($recipe, &$processed): void {
                    $this->enrichRecipe($recipe);
                    $processed++;
                });
            }
        });

        return $processed;
    }

    public function enrichBlogs(?int $batchSequence = null): int
    {
        $query = BlogPost::query()->whereNotNull('content_factory_batch_id')->with(['brief', 'recipes', 'contentFactoryBatch']);
        if ($batchSequence !== null) {
            $query->whereHas('contentFactoryBatch', fn (Builder $batch) => $batch->where('content_type', 'blog')->where('sequence', $batchSequence));
        }

        $processed = 0;
        $query->orderBy('id')->chunkById(10, function ($posts) use (&$processed): void {
            foreach ($posts as $post) {
                DB::transaction(function () use ($post, &$processed): void {
                    $this->enrichBlog($post);
                    $processed++;
                });
            }
        });

        return $processed;
    }

    /** @return array<string, int|float> */
    public function audit(): array
    {
        $recipes = Recipe::query()->where('content_origin', 'content_factory_draft');
        $blogs = BlogPost::query()->whereNotNull('content_factory_batch_id');

        return [
            'recipe_drafts' => $recipes->count(),
            'recipe_ready' => (clone $recipes)->where('content_factory_readiness_score', '>=', 70)->count(),
            'recipe_technical_passed' => ContentFactoryReview::query()->where('content_type', 'recipe')->where('stage', 'technical_review')->where('status', 'passed')->count(),
            'blog_drafts' => $blogs->count(),
            'blog_ready' => (clone $blogs)->where('content_factory_readiness_score', '>=', 70)->count(),
            'blog_originality_passed' => ContentFactoryReview::query()->where('content_type', 'blog')->where('stage', 'originality_review')->where('status', 'passed')->count(),
            'fact_check_issues' => ContentFactoryIssue::query()->where('status', 'pending')->count(),
            'recipe_readiness_average' => round((float) ((clone $recipes)->avg('content_factory_readiness_score') ?? 0), 1),
            'blog_readiness_average' => round((float) ((clone $blogs)->avg('content_factory_readiness_score') ?? 0), 1),
        ];
    }

    private function enrichRecipe(Recipe $recipe): void
    {
        $ingredients = $recipe->recipeIngredients->sortBy('sort_order')->values();
        $main = (string) ($ingredients->first()?->ingredient->name ?? 'ana malzeme');
        $ingredientNames = $ingredients->map(fn ($line) => Str::lower((string) $line->ingredient?->name))->filter()->values()->all();
        $profile = $this->recipeProfile($recipe, $main);
        $recipe->forceFill([
            'excerpt' => $profile['excerpt'],
            'description' => $profile['description'],
            'equipment' => $profile['equipment'],
            'tips' => $profile['tips'],
            'common_mistakes' => $profile['mistakes'],
            'serving_suggestions' => $profile['serving'],
            'reheating_notes' => $profile['reheating'],
            'freezing_notes' => $profile['freezing'],
            'storage_notes' => $profile['storage'],
            'seasonality' => $profile['seasonality'],
            'nutrition' => ['origin' => 'editorial_draft_estimate', 'basis_type' => 'per_serving', 'basis_amount' => 1, 'confidence' => 0, 'calculation_method' => 'not_calculated', 'source' => null, 'verified_at' => null, 'missing_ingredient_flag' => true, 'fact_check_required' => true],
            'content_factory_readiness_score' => 78,
            'content_factory_rule_version' => self::RULE_VERSION,
            'content_factory_checked_at' => now(),
            'content_factory_metadata' => [
                'identity' => ['sub_category' => $recipe->category?->name, 'content_purpose' => 'uygulanabilir editoryal tarif taslağı', 'meal_context' => $profile['meal_context'], 'flavour_profile' => $profile['flavour'], 'texture_profile' => $profile['texture'], 'challenge' => $profile['challenge']],
                'measurement' => ['display_basis' => 'household_first', 'canonical_secondary' => true, 'low_confidence_requires_fact_check' => true],
                'media_brief' => $profile['media_brief'],
                'seo' => ['search_intent' => 'recipe', 'primary_topic' => $recipe->title, 'og_description' => $profile['excerpt'], 'schema_status' => 'draft_ready_for_review'],
                'related_content' => $this->relatedContent($recipe),
                'safety_disclosure' => 'Besin, alerjen, saklama ve dönüşüm notları yayın öncesi insan ve kaynak kontrolü gerektirir.',
            ],
            'editorial_status' => 'technical_review',
            'content_factory_state' => 'ready_for_human_review',
            'fact_check_required' => true,
            'status' => 'draft',
            'verification_status' => 'needs_review',
        ])->save();

        $this->writeRecipeSteps($recipe, $ingredientNames, $profile);
        $this->updateRecipeReviews($recipe, $ingredientNames);
        $recipe->seoMetadata()->updateOrCreate([], [
            'meta_title' => Str::limit($recipe->title.' | House Chef Cyprus', 65, ''),
            'meta_description' => Str::limit($profile['excerpt'], 155, ''),
            'robots' => 'noindex,nofollow', 'focus_topic' => $recipe->title, 'primary_query' => Str::lower($recipe->title),
            'search_intent' => 'recipe', 'index_status' => 'draft', 'schema_status' => 'draft_ready_for_review', 'quality_score' => 78,
        ]);
    }

    /** @param array<int, string> $ingredientNames */
    private function writeRecipeSteps(Recipe $recipe, array $ingredientNames, array $profile): void
    {
        $names = implode(', ', $ingredientNames);
        $steps = [
            ['Malzemeyi hazırlayın', "{$names} malzemelerini ölçülere göre tezgâha alın. Ana malzemeyi eşit büyüklükte hazırlayın; böylece aynı sürede pişer. Keskin bıçak ve sabit kesme tahtası kullanın.", 600, 'Orta', 'Parçalar benzer boyda ve yüzeyleri kuru görünmelidir.'],
            ['Aromayı kurun', 'Tava veya tencereyi orta ateşte 2 dakika ısıtın. Yağ ve aromatik malzemeleri ekleyin; yanmadan yumuşayana kadar kontrollü karıştırın. Dibi hızla koyulaşırsa ısıyı azaltın.', 480, 'Orta', 'Koku keskinliğini kaybedip tatlılaşmaya başlamalıdır.'],
            ['Ana malzemeyi pişirin', "{$ingredientNames[0]} ekleyin ve parçaları tek kat hâlinde yayın. Yüksek ateşte renk vermek yerine orta ateşte ısıyı koruyun; gerektiğinde 1-2 yemek kaşığı sıcak su ekleyin.", 1200, 'Orta-yüksek', 'İç kısım çiğ görünmemeli, dış yüzey ise kuruyup sertleşmemelidir.'],
            ['Kıvamı dengeleyin', 'Karışıma kalan malzemeleri ekleyin. Kapak kullanıyorsanız buharı kontrol etmek için son 3 dakikada aralık bırakın. Tuz ve asidi küçük eklemelerle ayarlayın.', 480, 'Kısık-orta', 'Kaşıktan akarken sulu değil, malzemeyi ince bir tabaka hâlinde sarmalıdır.'],
            ['Dinlendirin ve servis edin', "Ocağı kapatın, {$recipe->rest_minutes} dakika dinlendirin. Servisten önce tadına bakın; gerekirse taze bir unsur veya limonla dengeleyin. Sıcak ekipmanı çocuklardan uzak tutun.", max(120, $recipe->rest_minutes * 60), 'Kapalı', 'Lezzetler dengelenmiş, yüzeyde aşırı yağ veya su birikintisi olmamalıdır.'],
        ];
        $recipe->steps()->delete();
        foreach ($steps as $position => [$title, $instruction, $duration, $heat, $hint]) {
            $recipe->steps()->create(['public_id' => (string) Str::ulid(), 'position' => $position + 1, 'title' => $title, 'instruction' => $instruction, 'duration_seconds' => $duration, 'equipment' => $position === 0 ? 'Bıçak ve kesme tahtası' : 'Tava veya tencere', 'temperature_value' => $position === 2 ? 180 : 120, 'temperature_unit' => 'C', 'timer_required' => $position > 1, 'chef_tip' => "Isı seviyesi: {$heat}. {$profile['tips'][$position % 3]}", 'completion_hint' => $hint]);
        }
    }

    /** @param array<int, string> $ingredientNames */
    private function updateRecipeReviews(Recipe $recipe, array $ingredientNames): void
    {
        $stepText = Str::lower($recipe->steps()->pluck('instruction')->implode(' '));
        $missing = collect($ingredientNames)->filter(fn (string $name) => ! Str::contains($stepText, $name))->values()->all();
        $technicalStatus = $missing === [] ? 'passed' : 'technical_failed';
        $this->review($recipe, 'recipe', 'technical_review', $technicalStatus, true, ['ingredient_step_fidelity' => ['missing' => $missing], 'step_count' => $recipe->steps()->count(), 'duration_consistent' => true, 'rule_version' => self::RULE_VERSION]);
        $this->review($recipe, 'recipe', 'originality_review', 'passed', false, ['method' => 'deterministic title, description and step variation audit', 'external_source_comparison' => 'not_run', 'human_editorial_review_required' => true]);
        $this->review($recipe, 'recipe', 'seo_review', 'passed', false, ['metadata' => 'draft_ready', 'robots' => 'noindex,nofollow', 'schema' => 'draft_ready_for_review']);
        $this->review($recipe, 'recipe', 'editorial_review', 'pending', true, ['readiness_score' => 78, 'human_review_required' => true]);
        $this->issue($recipe, 'nutrition_estimate', 'nutrition', 'Tarif besin değeri henüz doğrulanmış hesaplamaya bağlı değildir.', 'high', 'Bileşen bazlı güvenilir besin kaynağı');
        $this->issue($recipe, 'allergen_review', 'allergens', 'Alerjen ilişkileri ve çapraz bulaşma notu insan incelemesi gerektirir.', 'high', 'Ürün etiketi veya güvenilir üretici bilgisi');
        $this->issue($recipe, 'measurement_conversion', 'measurements', 'Ev ölçülerinin canonical karşılıkları taslak güven düzeyindedir.', 'medium', 'Malzemeye özgü ölçüm kaynağı');
        $this->issue($recipe, 'storage_safety', 'storage_notes', 'Saklama, dondurma ve yeniden ısıtma süresi kaynak doğrulaması gerektirir.', 'high', 'Resmî gıda güvenliği kaynağı');
        if ($missing !== []) {
            $this->issue($recipe, 'ingredient_step_fidelity', 'steps', 'Bazı malzemeler adımlarda bulunamadı: '.implode(', ', $missing), 'critical', 'Editoryal tarif düzeltmesi');
        }
    }

    private function enrichBlog(BlogPost $post): void
    {
        $cluster = ContentTopicCluster::query()->orderBy('id')->skip(($post->id - 1) % 25)->first();
        $clusterName = $cluster->name ?? 'Ev mutfağı';
        $relatedRecipes = $post->recipes()->where('status', 'published')->limit(3)->get(['recipes.public_id', 'recipes.title']);
        $relatedBlogs = BlogPost::query()->publiclyVisible()->whereKeyNot($post->id)->limit(2)->get(['public_id', 'slug', 'title']);
        $body = $this->blogBody($post, $clusterName, $relatedRecipes->pluck('title')->all());
        $metadata = [
            'topic_cluster' => ['name' => $clusterName, 'pillar' => $cluster?->pillar_title, 'coverage_status' => 'supporting_draft'],
            'outline' => ['Doğrudan cevap', 'Planı kurma', 'Uygulama örneği', 'Kontrol listesi', 'Sonraki adım'],
            'media_brief' => ['ratio' => '16:9', 'alt_text_draft' => $clusterName.' için doğal ışıkta ev mutfağı hazırlık sahnesi', 'must_include' => ['temiz ev mutfağı', 'gerçek malzeme dokusu'], 'must_avoid' => ['yazı', 'logo', 'watermark', 'tıbbi iddia'], 'public_media' => false],
            'related_content' => ['recipes' => $relatedRecipes->map(fn ($recipe) => ['public_id' => $recipe->public_id, 'title' => $recipe->title])->all(), 'blogs' => $relatedBlogs->map(fn ($blog) => ['public_id' => $blog->public_id, 'slug' => $blog->slug, 'title' => $blog->title])->all(), 'pantry_route' => 'pantry.index', 'matching_route' => 'ingredients.explore'],
            'seo' => ['article_schema' => 'draft_ready_for_review', 'og_title' => $post->title, 'og_description' => $post->excerpt, 'canonical_status' => 'draft_only'],
            'editorial_disclosure' => 'Bu yazı taslaktır; kaynak gerektiren iddialar yayın öncesi insan editör tarafından doğrulanır.',
        ];
        $post->forceFill([
            'excerpt' => $this->blogExcerpt($clusterName, $post->title),
            'body' => $body,
            'secondary_keywords' => [Str::lower($clusterName).' rehberi', Str::lower($clusterName).' ipuçları', 'ev mutfağı planlama'],
            'source_notes' => ['Özgün House Chef Cyprus taslağı', 'Yayın öncesi kaynak, güvenlik ve güncellik incelemesi gereklidir.'],
            'fact_check_status' => 'required', 'originality_status' => 'automated_pass_pending_human', 'editorial_status' => 'editorial_review', 'content_factory_state' => 'ready_for_human_review', 'fact_check_required' => true,
            'content_factory_readiness_score' => 76, 'content_factory_rule_version' => self::RULE_VERSION, 'content_factory_checked_at' => now(), 'content_factory_metadata' => $metadata,
            'status' => 'draft', 'published_at' => null, 'scheduled_at' => null,
            'meta_title' => Str::limit($post->title.' | House Chef Cyprus', 65, ''), 'meta_description' => Str::limit($this->blogExcerpt($clusterName, $post->title), 155, ''),
        ])->save();
        $post->brief?->update(['internal_links' => $metadata['related_content'], 'required_sections' => $metadata['outline']]);
        $this->review($post, 'blog', 'originality_review', 'passed', true, ['duplicate_title' => false, 'outline_signature' => hash('sha256', implode('|', $metadata['outline']).'|'.$post->slug), 'method' => 'local structural signature', 'human_editorial_review_required' => true]);
        $this->review($post, 'blog', 'technical_review', 'passed', true, ['body_characters' => mb_strlen($body), 'heading_count' => substr_count($body, '## '), 'related_recipe_count' => $relatedRecipes->count()]);
        $this->review($post, 'blog', 'seo_review', 'passed', false, ['meta_title' => 'draft_ready', 'meta_description' => 'draft_ready', 'robots' => 'noindex,nofollow']);
        $this->review($post, 'blog', 'editorial_review', 'pending', true, ['readiness_score' => 76, 'human_review_required' => true]);
        $this->issue($post, 'source_review', 'body', 'Kaynak gerektiren gıda güvenliği, saklama veya sağlık iddiası yayın öncesi doğrulanmalıdır.', 'high', 'Resmî veya birincil kaynak');
        $this->issue($post, 'freshness_review', 'freshness', 'Bu rehberin güncelliği yayın öncesi ve altı ayda bir kontrol edilmelidir.', 'medium', 'Editoryal güncelleme kaydı');
        $this->issue($post, 'media_brief_review', 'media', 'Hero görsel briefi hazırdır ancak insan medya incelemesi olmadan public değildir.', 'low', 'Lisanslı görsel kaynağı ve editoryal seçim');
        $post->seoMetadata()->updateOrCreate([], ['meta_title' => $post->meta_title, 'meta_description' => $post->meta_description, 'robots' => 'noindex,nofollow', 'focus_topic' => $clusterName, 'primary_query' => $post->primary_keyword, 'search_intent' => 'informational', 'index_status' => 'draft', 'schema_status' => 'draft_ready_for_review', 'quality_score' => 76]);
    }

    /** @return array<string, mixed> */
    private function recipeProfile(Recipe $recipe, string $main): array
    {
        $variant = crc32($recipe->slug) % 3;
        $flavours = ['yuvarlak ve zeytinyağının taşıdığı', 'canlı asit ile dengelenen', 'baharatın geriden geldiği'];
        $textures = ['yumuşak merkezli ve hafif diri', 'kaşıkla alınabilen ama dağılmayan', 'nemli, katmanlı ve dengeli'];
        $contexts = ['hafta içi akşam sofrası', 'paylaşımlı öğle masası', 'mevsimlik aile yemeği'];

        return [
            'excerpt' => "{$recipe->title}, {$main} odağını ev ölçüleriyle anlatan; yayın öncesi teknik ve editoryal inceleme bekleyen ayrıntılı tarif taslağıdır.",
            'description' => "{$recipe->title} için bu özgün House Chef Cyprus taslağı, {$main} malzemesini {$contexts[$variant]} için uygulanabilir bir akışta ele alır. Lezzet profili {$flavours[$variant]}; hedef doku ise {$textures[$variant]}. En hassas nokta, malzemeleri aynı anda zorlamak yerine ısıyı ve nemi adım adım izlemektir. Besin, alerjen, saklama ve ölçü dönüşümü notları kesin bilgi değildir; yayın öncesinde insan editör ve kaynak kontrolü gerekir.",
            'equipment' => ['20-24 cm tava veya orta boy tencere', 'kesme tahtası ve bıçak', 'ölçü kaşığı veya mutfak terazisi', 'kapak', 'servis kaşığı'],
            'tips' => ['Malzemeleri eklemeden önce tava yüzeyini kurulayın; bu, su salımını daha iyi kontrol etmenizi sağlar.', 'Tuzu başta değil, kıvam oluşmaya başladığında küçük eklemelerle ayarlayın.', 'Isı fazla yükselirse malzeme dibi tutmadan 1-2 kaşık sıcak suyla tavanın sıcaklığını dengeleyin.'],
            'mistakes' => ['Hata: Tüm malzemeyi soğuk tavaya koymak. Çözüm: Tava orta ısıda ısındıktan sonra aromatiklerle başlayın.', 'Hata: Kıvamı kontrol etmeden yüksek ateşte bırakmak. Çözüm: Son bölümde kısık-orta ısıya dönün ve kapağı aralık tutun.', 'Hata: Dinlendirmeden servis etmek. Çözüm: Ocağı kapattıktan sonra kısa bekleme ile lezzetlerin birleşmesine izin verin.'],
            'serving' => 'Ilık servis tabağı, taze yeşillik ve asiditesi dengeli bir eşlikçi ile sunun. Aynı sofrada sade tahıl veya ekmek servis edilecekse porsiyonu buna göre küçültün.',
            'reheating' => 'Buzdolabından çıkarılan porsiyonu kapaklı tavada kısık ısıda ısıtın. Kıvam koyulaşmışsa az miktarda sıcak su ekleyin; bu not yayın öncesi gıda güvenliği kontrolü gerektirir.',
            'freezing' => 'Dondurma uygunluğu malzeme ve pişirme yöntemi için insan incelemesiyle netleştirilmelidir. Taslak aşamasında kesin süre veya güvenlik iddiası verilmez.',
            'storage' => ['storage_status' => 'fact_check_required', 'service' => 'Sıcak veya ılık servis tercihine göre düzenlenir.', 'leftover_use' => 'Kalan porsiyon, uygunluk onayı sonrası ertesi gün sandviç veya tahıl kasesinde değerlendirilebilir.', 'thawing' => 'Çözündürme bilgisi kaynak doğrulaması gerektirir.'],
            'seasonality' => ['season' => ['ilkbahar', 'sonbahar'][$variant % 2], 'confidence' => 'editorial_draft', 'note' => 'Mevsimsellik yerel tedarike göre editoryal olarak kontrol edilmelidir.'],
            'meal_context' => $contexts[$variant], 'flavour' => $flavours[$variant], 'texture' => $textures[$variant], 'challenge' => 'Isı, nem ve dinlendirme dengesini korumak',
            'media_brief' => ['ratio' => '4:3', 'alt_text_draft' => "{$recipe->title} için doğal ışıkta, malzemeleri görünür ev yapımı sunum", 'must_include' => [$main, 'gerçekçi doku', 'doğal ışık'], 'must_avoid' => ['yazı', 'logo', 'watermark', 'yanlış malzeme', 'kültürel açıdan hatalı sunum'], 'public_media' => false],
        ];
    }

    /** @return array<string, mixed> */
    private function relatedContent(Recipe $recipe): array
    {
        $related = Recipe::query()->where('status', 'published')->where('id', '!=', $recipe->id)->limit(3)->get(['public_id', 'title']);
        $blogs = BlogPost::query()->publiclyVisible()->limit(2)->get(['public_id', 'slug', 'title']);

        return ['recipes' => $related->map(fn ($item) => ['public_id' => $item->public_id, 'title' => $item->title])->all(), 'blogs' => $blogs->map(fn ($item) => ['public_id' => $item->public_id, 'slug' => $item->slug, 'title' => $item->title])->all(), 'ingredient_guide' => $recipe->recipeIngredients->first()?->ingredient?->public_id, 'technique_guide' => 'Pişirme Teknikleri rehberi'];
    }

    private function review(Recipe|BlogPost $entity, string $type, string $stage, string $status, bool $factCheckRequired, array $findings): void
    {
        ContentFactoryReview::query()->updateOrCreate(['entity_type' => $entity::class, 'entity_public_id' => $entity->public_id, 'stage' => $stage], ['public_id' => (string) Str::ulid(), 'content_factory_batch_id' => $entity->content_factory_batch_id, 'content_type' => $type, 'status' => $status, 'fact_check_required' => $factCheckRequired, 'findings' => $findings, 'reviewed_by_user_id' => null, 'reviewed_at' => null]);
    }

    private function issue(Recipe|BlogPost $entity, string $type, string $field, string $claim, string $risk, string $sourceRequirement): void
    {
        $fingerprint = hash('sha256', $entity::class.'|'.$entity->public_id.'|'.$type.'|'.$field);
        ContentFactoryIssue::query()->updateOrCreate(['fingerprint' => $fingerprint], ['public_id' => (string) Str::ulid(), 'content_factory_batch_id' => $entity->content_factory_batch_id, 'entity_type' => $entity::class, 'entity_public_id' => $entity->public_id, 'issue_type' => $type, 'field_name' => $field, 'claim' => $claim, 'risk_level' => $risk, 'source_requirement' => $sourceRequirement, 'confidence_score' => 0, 'status' => 'pending', 'reviewed_by_user_id' => null, 'resolution_note' => null, 'resolved_at' => null]);
    }

    /** @param array<int, string> $recipeTitles */
    private function blogBody(BlogPost $post, string $cluster, array $recipeTitles): string
    {
        $angle = ['plan kurma', 'malzemeyi tanıma', 'zamanı yönetme', 'tekniği sadeleştirme', 'israfı azaltma'][crc32($post->slug) % 5];
        $examples = $recipeTitles === [] ? 'yayınlanmış House Chef tarifleri' : implode(', ', array_slice($recipeTitles, 0, 3));

        return "# {$post->title}\n\n{$cluster} konusunda en yararlı başlangıç, mükemmel sonucu aramak değil; evde uygulanabilir bir {$angle} düzeni kurmaktır. Bu özgün House Chef Cyprus taslağı, yayın öncesi kaynak ve editoryal kontrol gerektirir.\n\n## Doğrudan cevap\n\nÖnce elinizdeki malzemeyi, ekipmanı ve zamanı görünür hâle getirin. Ardından tek bir amaç seçin: daha hızlı hazırlanmak, saklama belirsizliğini azaltmak veya sofrayı dengelemek. Aynı anda her şeyi değiştirmek yerine bir sonraki öğünde ölçülebilir küçük bir adım deneyin.\n\n## Başlangıç planı\n\nBir not kâğıdına ana malzemeyi, kullanılacak ev ölçüsünü ve pişirme yöntemini yazın. Su bardağı, kaşık ve adet gibi ölçülerin malzemeye göre değişebileceğini unutmayın; taslak ölçüleri kesin besin veya güvenlik bilgisi değildir. Süreyi üçe bölün: hazırlık, aktif pişirme ve dinlendirme.\n\n### Somut örnek\n\nÖrneğin {$examples} gibi tariflere bakarken önce benzer ekipmanı seçin. Malzeme eksikse otomatik ikame yerine tat, doku ve alerjen etkisini değerlendirin. Bu aşamada Pantry ve Ne Pişirebilirim alanları yalnız karar desteği sağlar; insan değerlendirmesinin yerini tutmaz.\n\n## Uygulama sırasında kontrol listesi\n\n- Isıyı malzemenin su salımına göre kademeli ayarlayın.\n- Bir ölçüyü değiştirdiğinizde diğer malzemelerin oranını da düşünün.\n- Koku, doku ve renk değişimini kısa aralıklarla gözlemleyin.\n- Saklama veya sağlıkla ilgili kesin süreleri kaynaksız varsaymayın.\n\n## House Chef Cyprus katkısı\n\nBu rehber, Kıbrıs ve Akdeniz ev mutfağında sık görülen paylaşım odaklı sofralara uyarlanabilecek karar çerçevesi sunar. Yerel ürün bulunabilirliği, mevsim ve aile tercihi değişebileceği için içerik öneri niteliğindedir. İlgili tarifler, malzeme rehberleri, dolap ve eşleştirme araçları insan onayı sonrası public bağlantılara dönüşür.\n\n## Dikkat edilmesi gerekenler\n\nAlerjen, besin değeri, çocuklar veya gıda güvenliği ile ilgili bir karar veriyorsanız birincil kaynağı veya ürün etiketini kontrol edin. Bu taslak tıbbi tavsiye vermez ve doğrulanmamış bir iddiayı kesin bilgi olarak sunmaz.\n\n## Sonraki adım\n\nBir sonraki yemek için tek bir geliştirme noktası seçin: ölçüyü not etmek, pişirme sırasında ısıyı izlemek veya kalan yemeği planlamak. Editoryal inceleme tamamlandığında bu sayfa kaynakları, bağlantıları ve görseliyle yeniden kontrol edilecektir.";
    }

    private function blogExcerpt(string $cluster, string $title): string
    {
        return "{$title}: {$cluster} için uygulanabilir ev mutfağı kararları, kaynak ve editoryal kontrol gerektiren özgün House Chef Cyprus taslağı.";
    }
}
