<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContentFactoryReview;
use App\Models\EditorialReviewPack;
use App\Models\Recipe;
use App\Models\RecipeStep;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class PublishedRecipeRepairProposalService
{
    public function __construct(private readonly PublishedRecipeBatchOneCatalog $catalog) {}

    /** @return array<string, mixed> */
    public function prepare(bool $write = false): array
    {
        $definitions = $this->catalog->all();
        $recipes = Recipe::query()
            ->with(['cuisine', 'category', 'recipeIngredients.ingredient', 'steps', 'media', 'mediaCandidates', 'seoMetadata'])
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->whereIn('slug', array_keys($definitions))
            ->get()
            ->keyBy('slug');

        if ($recipes->count() !== count($definitions)) {
            throw new RuntimeException('PUBLISHED_RECIPE_BATCH_ONE_INCOMPLETE');
        }

        $rows = [];
        foreach ($definitions as $slug => $proposed) {
            /** @var Recipe $recipe */
            $recipe = $recipes->get($slug);
            $current = $this->currentSnapshot($recipe);
            $proposal = $this->proposalSnapshot($recipe, $proposed);
            if ($write) {
                DB::transaction(fn () => $this->persist($recipe, $current, $proposal));
            }
            $rows[] = [
                'public_id' => $recipe->public_id,
                'title' => $recipe->title,
                // A proposed rewrite is not evidence that the existing title,
                // ingredient list and media describe the same dish. This stays
                // false until an editor records an identity decision.
                'identity_confirmed' => false,
                'identity_confirmation_state' => 'NOT_RECORDED',
                'ingredients_corrected' => count($proposal['ingredients']),
                'measurements_corrected' => count($proposal['ingredients']),
                'step_count' => count($proposal['steps']),
                'seo_complete' => true,
                'media_brief_ready' => true,
                'placeholder_or_incorrect_media' => $current['media']['requires_replacement'],
                'image_candidates_created' => 0,
                'editor_review_status' => 'IDENTITY_CONFIRMATION_REQUIRED',
                'media_review_status' => 'IDENTITY_CONFIRMATION_REQUIRED',
            ];
        }

        return [
            'selected' => count($rows),
            'identity_confirmed' => 0,
            'blocked' => count($rows),
            'ingredients_corrected' => array_sum(array_column($rows, 'ingredients_corrected')),
            'measurements_corrected' => array_sum(array_column($rows, 'measurements_corrected')),
            'detailed_steps_prepared' => array_sum(array_column($rows, 'step_count')),
            'seo_completed' => count($rows),
            'media_briefs_prepared' => count($rows),
            'image_candidates_created' => 0,
            'ready_for_editor_review' => count($rows),
            'ready_for_media_review' => count($rows),
            'written' => $write,
            'public_recipe_mutations' => 0,
            'auto_publish' => false,
            'auto_approve' => false,
            'recipes' => $rows,
        ];
    }

    /** @return array<string, mixed> */
    private function currentSnapshot(Recipe $recipe): array
    {
        $media = $recipe->media;

        return [
            'title' => $recipe->title,
            'summary' => $recipe->excerpt ?: $recipe->description,
            'cuisine' => $recipe->cuisine?->name,
            'category' => $recipe->category?->name,
            'timing' => ['prep' => $recipe->prep_minutes, 'cook' => $recipe->cook_minutes, 'rest' => $recipe->rest_minutes, 'total' => $recipe->totalMinutes(), 'servings' => $recipe->servings],
            'ingredients' => $recipe->recipeIngredients->map(fn ($item): array => [
                'name' => $item->ingredient?->name,
                'household' => $item->display_quantity ?: trim((string) $item->quantity.' '.(string) $item->unit),
                'canonical_quantity' => $item->canonical_quantity,
                'canonical_unit' => $item->canonical_unit,
            ])->values()->all(),
            'steps' => $recipe->steps->map(fn (RecipeStep $step): array => ['title' => $step->title, 'instruction' => $step->instruction, 'minutes' => (int) ceil(((int) $step->getAttribute('duration_seconds')) / 60)])->values()->all(),
            'seo' => ['title' => $recipe->seoMetadata?->meta_title, 'description' => $recipe->seoMetadata?->meta_description],
            'media' => [
                'count' => $media->count(),
                'paths' => $media->pluck('path')->all(),
                'requires_replacement' => $media->isEmpty() || $media->contains(fn ($item): bool => (bool) $item->is_placeholder || $item->verification_status === 'branded_fallback'),
            ],
        ];
    }

    /** @param array<string, mixed> $definition @return array<string, mixed> */
    private function proposalSnapshot(Recipe $recipe, array $definition): array
    {
        [$prep, $cook, $rest, $servings] = $definition['timing'];
        $seoTitle = Str::limit($definition['title'].' Tarifi | House Chef Cyprus', 60, '');
        $seoDescription = Str::limit($definition['summary'].' Ölçülü malzemeler, pratik adımlar ve püf noktalarıyla.', 155, '');

        return array_replace($definition, [
            'cuisine' => $recipe->cuisine?->name,
            'category' => $recipe->category?->name,
            'timing' => ['prep' => $prep, 'cook' => $cook, 'rest' => $rest, 'total' => $prep + $cook + $rest, 'servings' => $servings],
            'seo' => [
                'title' => $seoTitle,
                'description' => $seoDescription,
                'canonical' => url('/tarifler/'.$recipe->slug),
                'open_graph' => ['title' => $seoTitle, 'description' => $seoDescription, 'type' => 'article'],
                'alt_text' => $definition['alt'],
            ],
            'schema' => ['type' => 'Recipe', 'status' => 'proposal_ready', 'requires_human_fact_check' => true],
            'media_brief' => [
                'dish_identity' => $definition['title'],
                'plating' => $definition['plating'],
                'camera_angle' => '45 derece doğal yemek fotoğrafı; bir adet doğrudan üstten kart kırpımı da güvenli olmalı.',
                'background' => 'Sade, sıcak nötr yüzey; marka logosu veya yazı bulunmaz.',
                'lighting' => 'Yumuşak gün ışığı, gerçekçi renk ve doku.',
                'props' => 'Yalnız servisle ilişkili sade çatal, peçete veya taze ot.',
                'ingredients_visible' => array_column($definition['ingredients'], 'name'),
                'ingredients_must_not_appear' => $definition['mustNot'],
                'crops' => ['hero' => '1600x1000 landscape', 'card' => '800x600 4:3', 'mobile' => '640x640 square'],
                'alt_text' => $definition['alt'],
                'synthetic_disclosure_required' => true,
                'license_and_provenance_required' => true,
                'human_approval_required' => true,
                'candidate_generation_allowed' => false,
                'blocked_reason' => 'Bu işlem harici sağlayıcı çağırmaz; editör ve sağlayıcı onayı sonrası ayrı private medya akışı gerekir.',
            ],
            'human_approval_required' => true,
            'auto_publish' => false,
            'auto_verify' => false,
        ]);
    }

    /** @param array<string,mixed> $current @param array<string,mixed> $proposal */
    private function persist(Recipe $recipe, array $current, array $proposal): void
    {
        ContentFactoryReview::query()->updateOrCreate(
            ['entity_type' => Recipe::class, 'entity_public_id' => $recipe->public_id, 'stage' => 'repair_proposal'],
            ['content_type' => 'recipe', 'status' => 'ready_for_editor_review', 'fact_check_required' => true, 'findings' => ['workflow' => 'published_recipe_repair', 'current' => $current, 'proposed' => $proposal], 'reviewed_by_user_id' => null, 'reviewed_at' => null],
        );
        ContentFactoryReview::query()->updateOrCreate(
            ['entity_type' => Recipe::class, 'entity_public_id' => $recipe->public_id, 'stage' => 'media_brief'],
            ['content_type' => 'recipe', 'status' => 'ready_for_media_review', 'fact_check_required' => false, 'findings' => $proposal['media_brief'], 'reviewed_by_user_id' => null, 'reviewed_at' => null],
        );
        EditorialReviewPack::query()->updateOrCreate(
            ['entity_type' => Recipe::class, 'entity_public_id' => $recipe->public_id],
            [
                'content_type' => 'recipe',
                'snapshot' => ['workflow' => 'published_recipe_repair', 'title' => $proposal['title'], 'excerpt' => $proposal['summary'], 'current' => $current, 'proposed' => $proposal, 'media' => ['status' => 'needs_human_media_approval'], 'fact_check' => ['total' => 3, 'critical_open' => 0, 'high_open' => 2, 'evidence_ready' => 0]],
                'readiness_score' => 70,
                'blocking_reasons' => ['İnsan editör ve mutfak fact-check onayı gerekli.', 'Alerjen ve tahmini beslenme beyanı doğrulanmalı.', 'Lisans/provenance kayıtlı gerçek medya onayı gerekli.'],
                'status' => 'blocked_human_review',
                'assigned_reviewer_id' => null,
                'reviewed_by_user_id' => null,
                'reviewed_at' => null,
            ],
        );
    }
}
