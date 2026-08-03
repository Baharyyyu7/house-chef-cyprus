<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContentFactoryIssue;
use App\Models\ContentFactoryReview;
use App\Models\Recipe;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Queues published recipes for human-led correction without changing a public
 * recipe, its verification state, or its media. It intentionally produces no
 * content or image revision where the current record cannot support one.
 */
final class PublishedRecipeReviewQueueService
{
    /** @var array<string, list<string>> */
    private const IDENTITY_INGREDIENTS = [
        'tavuk' => ['tavuk'],
        'balık' => ['balık'],
        'nohut' => ['nohut'],
        'mercimek' => ['mercimek'],
        'yoğurt' => ['yoğurt'],
        'peynir' => ['peynir', 'hellim'],
        'patates' => ['patates'],
        'domates' => ['domates'],
        'ıspanak' => ['ıspanak'],
        'mantar' => ['mantar'],
        'bulgur' => ['bulgur'],
        'pirinç' => ['pirinç'],
        'kabak' => ['kabak'],
        'enginar' => ['enginar'],
        'fasulye' => ['fasulye'],
        'yumurta' => ['yumurta'],
    ];

    /**
     * @return array{selected:int,queued:int,identity_states:array<string,int>,media_waiting:int,recipes:list<array<string,mixed>>}
     */
    public function queue(int $limit = 10, int $page = 1, bool $write = false, string $scope = 'published'): array
    {
        $query = Recipe::query()
            ->with(['cuisine', 'category', 'recipeIngredients.ingredient', 'steps', 'media', 'nutritionSnapshots', 'provenances', 'seoMetadata'])
            ->where('status', $scope === 'draft' ? 'draft' : 'published')
            ->orderByDesc('is_featured')
            ->orderBy('published_at')
            ->orderBy('id')
            ->offset(($page - 1) * $limit)
            ->limit($limit);
        if ($scope === 'published') {
            $query->where('verification_status', 'verified');
        }
        $recipes = $query->get();

        $rows = $recipes->map(function (Recipe $recipe) use ($scope, $write): array {
            $assessment = $this->assess($recipe);
            if ($write) {
                $this->persist($recipe, $assessment, $scope);
            }

            return ['public_id' => $recipe->public_id, 'title' => $recipe->title] + $assessment;
        })->all();

        /** @var Collection<int, array<string,mixed>> $collection */
        $collection = collect($rows);

        return [
            'selected' => $recipes->count(),
            'queued' => $write ? $recipes->count() : 0,
            'identity_states' => $collection->countBy('identity_state')->all(),
            'media_waiting' => $collection->where('media_state', 'WAITING_FOR_IDENTITY_REVIEW')->count(),
            'recipes' => $rows,
        ];
    }

    /** @return array<string,mixed> */
    private function assess(Recipe $recipe): array
    {
        $ingredientNames = $recipe->recipeIngredients
            ->map(fn ($item): string => Str::lower((string) $item->ingredient->name))
            ->filter()
            ->values()
            ->all();
        $title = Str::lower($recipe->title);
        $missingIdentityIngredients = [];
        foreach (self::IDENTITY_INGREDIENTS as $titleTerm => $requiredIngredients) {
            if (! str_contains($title, $titleTerm)) {
                continue;
            }
            $present = collect($requiredIngredients)->contains(fn (string $ingredient): bool => collect($ingredientNames)->contains(fn (string $name): bool => str_contains($name, $ingredient)));
            if (! $present) {
                $missingIdentityIngredients[] = $titleTerm;
            }
        }

        $hasOvenMethod = str_contains($title, 'fırın') || str_contains($title, 'tepsi');
        $stepsMentionOven = $recipe->steps->contains(fn ($step): bool => str_contains(Str::lower((string) $step->instruction), 'fırın') || str_contains(Str::lower((string) $step->title), 'fırın'));
        $placeholderMedia = $recipe->media->contains(fn ($media): bool => (bool) $media->is_placeholder || $media->verification_status === 'branded_fallback');
        $identityState = $missingIdentityIngredients !== []
            ? 'TITLE_INGREDIENT_MISMATCH'
            : ($hasOvenMethod && ! $stepsMentionOven ? 'TITLE_METHOD_MISMATCH' : 'IDENTITY_NEEDS_REVIEW');

        return [
            'identity_state' => $identityState,
            'missing_title_ingredients' => $missingIdentityIngredients,
            'ingredient_count' => count($ingredientNames),
            'step_count' => $recipe->steps->count(),
            'placeholder_media' => $placeholderMedia,
            'media_state' => 'WAITING_FOR_IDENTITY_REVIEW',
            'nutrition_snapshot_count' => $recipe->nutritionSnapshots->count(),
            'provenance_count' => $recipe->provenances->count(),
            'seo_metadata_present' => $recipe->seoMetadata !== null,
            'review_reason' => 'Otomatik denetim yalnız eksikleri işaretler; tarif, medya veya doğrulama durumunu değiştirmez.',
        ];
    }

    /** @param array<string,mixed> $assessment */
    private function persist(Recipe $recipe, array $assessment, string $scope): void
    {
        $prefix = $scope === 'draft' ? 'draft_' : '';
        $this->review($recipe, $prefix.'identity_review', 'pending', true, [
            'identity_state' => $assessment['identity_state'],
            'missing_title_ingredients' => $assessment['missing_title_ingredients'],
            'automated_state_is_not_approval' => true,
        ]);
        $this->review($recipe, $prefix.'content_review', 'pending', true, [
            'ingredient_count' => $assessment['ingredient_count'],
            'step_count' => $assessment['step_count'],
            'minimum_meaningful_steps' => 5,
            'revision_creation_blocked_until_identity_review' => true,
        ]);
        $this->review($recipe, $prefix.'media_review', 'waiting_for_identity_review', false, [
            'media_state' => $assessment['media_state'],
            'placeholder_media' => $assessment['placeholder_media'],
            'candidate_generation_allowed' => false,
            'human_media_approval_required' => true,
        ]);
        $this->review($recipe, $prefix.'media_brief', 'blocked_identity_review', false, $this->mediaBrief($recipe, $assessment));

        $this->issue($recipe, $prefix.'recipe_identity', 'title', $assessment['identity_state'], 'high', 'Tarif kimliği, malzeme listesi ve adımlar için insan editör incelemesi');
        if ($assessment['step_count'] < 5) {
            $this->issue($recipe, $prefix.'recipe_step_completeness', 'steps', 'Tarif anlamlı en az beş hazırlık adımı içermiyor.', 'medium', 'Özgün editoryal tarif metni ve mutfak testi');
        }
        if ($assessment['placeholder_media']) {
            $this->issue($recipe, $prefix.'recipe_media_truthfulness', 'media', 'Yer tutucu medya onaylı yemek fotoğrafı değildir.', 'high', 'Lisans/provenance kaydı olan, insan onaylı yemek görseli');
        }
        if ($assessment['nutrition_snapshot_count'] === 0) {
            $this->issue($recipe, $prefix.'recipe_nutrition_review', 'nutrition', 'Aktif beslenme snapshotı bulunamadı.', 'high', 'Kaynaklı beslenme incelemesi veya görünürlüğü kapatma');
        }
    }

    /** @param array<string,mixed> $assessment @return array<string,mixed> */
    private function mediaBrief(Recipe $recipe, array $assessment): array
    {
        return [
            'dish_identity' => $recipe->title,
            'identity_confirmation_required' => true,
            'identity_state' => $assessment['identity_state'],
            'cuisine' => $recipe->cuisine?->name,
            'category' => $recipe->category?->name,
            'visible_ingredients_for_editor_confirmation' => $recipe->recipeIngredients
                ->map(fn ($item): string => (string) $item->ingredient->name)
                ->filter()
                ->values()
                ->all(),
            'hero_crop' => ['minimum_width' => 1600, 'minimum_height' => 1000, 'orientation' => 'landscape'],
            'card_crop' => ['minimum_width' => 800, 'minimum_height' => 600],
            'mobile_crop' => ['minimum_width' => 640, 'minimum_height' => 640, 'orientation' => 'square'],
            'quality_requirements' => ['No embedded text', 'No watermark', 'No third-party logo', 'No unrelated dish or ingredient'],
            'approved_media_required' => true,
            'permitted_media_classes' => ['AUTHENTIC_ORIGINAL', 'AUTHENTIC_LICENSED', 'USER_SUBMITTED_APPROVED', 'SYNTHETIC_APPROVED'],
            'synthetic_disclosure_required' => true,
            'license_and_provenance_required' => true,
            'candidate_generation_allowed' => false,
            'blocked_reason' => 'Tarif kimliği, malzemeleri ve hazırlama adımları insan editör tarafından doğrulanmadan görsel adayı oluşturulamaz.',
        ];
    }

    /** @param array<string,mixed> $findings */
    private function review(Recipe $recipe, string $stage, string $status, bool $factCheckRequired, array $findings): void
    {
        ContentFactoryReview::query()->updateOrCreate(
            ['entity_type' => Recipe::class, 'entity_public_id' => $recipe->public_id, 'stage' => $stage],
            ['public_id' => (string) Str::ulid(), 'content_type' => 'recipe', 'status' => $status, 'fact_check_required' => $factCheckRequired, 'findings' => $findings, 'reviewed_by_user_id' => null, 'reviewed_at' => null],
        );
    }

    private function issue(Recipe $recipe, string $type, string $field, string $claim, string $risk, string $sourceRequirement): void
    {
        $fingerprint = hash('sha256', Recipe::class.'|'.$recipe->public_id.'|catalogue-review|'.$type.'|'.$field);
        ContentFactoryIssue::query()->updateOrCreate(
            ['fingerprint' => $fingerprint],
            ['public_id' => (string) Str::ulid(), 'entity_type' => Recipe::class, 'entity_public_id' => $recipe->public_id, 'issue_type' => $type, 'field_name' => $field, 'claim' => $claim, 'risk_level' => $risk, 'source_requirement' => $sourceRequirement, 'confidence_score' => 0, 'status' => 'pending', 'reviewed_by_user_id' => null, 'resolution_note' => null, 'resolved_at' => null],
        );
    }
}
