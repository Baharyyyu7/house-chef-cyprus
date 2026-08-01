<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlogPost;
use App\Models\ContentFactoryIssue;
use App\Models\ContentQualityProfile;
use App\Models\EditorialReviewPack;
use App\Models\ProductionReleaseState;
use App\Models\Recipe;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class ProductionContentReadinessService
{
    /** @return array{recipes:int,blogs:int,blocked:int,ready_for_human_review:int} */
    public function scan(): array
    {
        $issues = ContentFactoryIssue::query()
            ->whereNotIn('status', ['approved_correction', 'rejected', 'not_applicable'])
            ->get()
            ->groupBy(fn (ContentFactoryIssue $issue): string => $issue->entity_type.'|'.$issue->entity_public_id);
        $packs = EditorialReviewPack::query()->get()->keyBy(fn (EditorialReviewPack $pack): string => $pack->entity_type.'|'.$pack->entity_public_id);
        $counts = ['recipes' => 0, 'blogs' => 0, 'blocked' => 0, 'ready_for_human_review' => 0];

        Recipe::query()->with(['recipeIngredients', 'steps', 'provenances'])->orderBy('id')->chunkById(100, function (Collection $recipes) use ($issues, $packs, &$counts): void {
            foreach ($recipes as $recipe) {
                $profile = $this->profileRecipe($recipe, $issues->get(Recipe::class.'|'.$recipe->public_id, collect()), $packs->get(Recipe::class.'|'.$recipe->public_id));
                $this->persist($profile);
                $counts['recipes']++;
                $this->countPendingPublication($counts, $profile['publication_status']);
            }
        });

        BlogPost::query()->with(['recipes', 'heroMedia'])->orderBy('id')->chunkById(100, function (Collection $posts) use ($issues, $packs, &$counts): void {
            foreach ($posts as $post) {
                $profile = $this->profileBlog($post, $issues->get(BlogPost::class.'|'.$post->public_id, collect()), $packs->get(BlogPost::class.'|'.$post->public_id));
                $this->persist($profile);
                $counts['blogs']++;
                $this->countPendingPublication($counts, $profile['publication_status']);
            }
        });

        $this->releaseState($counts);

        return $counts;
    }

    /** @return array<string, mixed> */
    public function dashboard(): array
    {
        $profiles = ContentQualityProfile::query();

        return [
            'recipes' => Recipe::query()->count(),
            'blogs' => BlogPost::query()->count(),
            'published_recipes' => Recipe::query()->where('status', 'published')->count(),
            'published_blogs' => BlogPost::query()->where('status', 'published')->count(),
            'profiles' => [
                'blocked' => (clone $profiles)->where('publication_status', 'blocked')->count(),
                'source_required' => (clone $profiles)->where('source_coverage_status', 'source_required')->count(),
                'media_required' => (clone $profiles)->whereIn('media_status', ['needs_real_media', 'branded_fallback'])->count(),
                'final_approval_waiting' => (clone $profiles)->whereNotIn('final_approval_status', ['human_approved', 'published'])->count(),
                'human_approved' => (clone $profiles)->where('final_approval_status', 'human_approved')->count(),
                'scheduled' => (clone $profiles)->where('publication_status', 'scheduled')->count(),
                'readiness_average' => (int) round((float) ((clone $profiles)->avg('readiness_score') ?? 0)),
            ],
            'issues' => [
                'critical' => ContentFactoryIssue::query()->where('risk_level', 'critical')->whereNotIn('status', ['approved_correction', 'rejected', 'not_applicable'])->count(),
                'high' => ContentFactoryIssue::query()->where('risk_level', 'high')->whereNotIn('status', ['approved_correction', 'rejected', 'not_applicable'])->count(),
            ],
            'release' => ProductionReleaseState::query()->latest('id')->first(),
        ];
    }

    /** @param Collection<int, ContentFactoryIssue> $issues @return array<string,mixed> */
    private function profileRecipe(Recipe $recipe, Collection $issues, ?EditorialReviewPack $pack): array
    {
        $hasCritical = $issues->contains('risk_level', 'critical');
        $hasHigh = $issues->contains('risk_level', 'high');
        $measurementComplete = $recipe->recipeIngredients->isNotEmpty() && $recipe->recipeIngredients->every(fn ($item): bool => $item->canonical_quantity !== null && $item->canonical_unit !== null);
        $technicalComplete = filled($recipe->title) && filled($recipe->slug) && $recipe->steps->isNotEmpty() && $recipe->servings > 0;
        $sourceComplete = $recipe->provenances->isNotEmpty() || filled($recipe->source_url);
        $approvedMedia = $recipe->image_status === 'approved_real_media';
        $fallbackMedia = in_array($recipe->image_status, ['branded_fallback', 'placeholder'], true);
        $humanApproved = $recipe->human_approved_at !== null;
        $reasons = $this->reasons($hasCritical, $hasHigh, ! $sourceComplete, ! $approvedMedia, ! $humanApproved, $pack);

        return $this->makeProfile(Recipe::class, $recipe->public_id, 'recipe', [
            'content_quality_status' => $technicalComplete ? 'passed_automated_checks' : 'incomplete',
            'technical_quality_status' => $technicalComplete ? 'passed_automated_checks' : 'incomplete',
            'editorial_quality_status' => $recipe->editorial_reviewed_at ? 'in_review' : 'needs_revision',
            'fact_check_status' => $this->factCheckStatus($issues),
            'source_coverage_status' => $sourceComplete ? 'passed_automated_checks' : 'source_required',
            'measurement_status' => $measurementComplete ? 'passed_automated_checks' : 'needs_revision',
            'nutrition_status' => filled($recipe->nutrition) ? 'passed_automated_checks' : 'incomplete',
            'allergen_status' => filled($recipe->allergens) ? 'passed_automated_checks' : 'incomplete',
            'dietary_status' => filled($recipe->dietary_tags) ? 'passed_automated_checks' : 'incomplete',
            'media_status' => $approvedMedia ? 'approved_real_media' : ($fallbackMedia ? 'branded_fallback' : 'needs_real_media'),
            'seo_status' => $technicalComplete ? 'passed_automated_checks' : 'incomplete',
            'accessibility_status' => $approvedMedia ? 'passed_automated_checks' : 'needs_revision',
            'final_approval_status' => $humanApproved ? 'human_approved' : 'ready_for_human_review',
            'publication_status' => $this->publicationStatus($recipe->status, $hasCritical || $hasHigh || ! $sourceComplete),
            'metadata' => ['open_issue_count' => $issues->count(), 'pack_status' => $pack?->status, 'human_approval_required' => true, 'auto_publish' => false],
            'blocking_reasons' => $reasons,
        ], $hasCritical, $hasHigh, $fallbackMedia, $humanApproved);
    }

    /** @param Collection<int, ContentFactoryIssue> $issues @return array<string,mixed> */
    private function profileBlog(BlogPost $post, Collection $issues, ?EditorialReviewPack $pack): array
    {
        $hasCritical = $issues->contains('risk_level', 'critical');
        $hasHigh = $issues->contains('risk_level', 'high');
        $technicalComplete = filled($post->title) && filled($post->slug) && filled($post->excerpt) && filled($post->body);
        $sourceComplete = filled($post->source_notes);
        $approvedMedia = $post->heroMedia?->verification_status === 'approved_real_media';
        $fallbackMedia = $post->heroMedia?->verification_status === 'branded_fallback';
        $humanApproved = $post->human_approved_at !== null;
        $reasons = $this->reasons($hasCritical, $hasHigh, ! $sourceComplete, ! $approvedMedia, ! $humanApproved, $pack);

        return $this->makeProfile(BlogPost::class, $post->public_id, 'blog', [
            'content_quality_status' => $technicalComplete ? 'passed_automated_checks' : 'incomplete',
            'technical_quality_status' => $technicalComplete ? 'passed_automated_checks' : 'incomplete',
            'editorial_quality_status' => $post->editorial_status === 'passed' ? 'passed_automated_checks' : 'needs_revision',
            'fact_check_status' => $this->factCheckStatus($issues),
            'source_coverage_status' => $sourceComplete ? 'passed_automated_checks' : 'source_required',
            'measurement_status' => 'not_started',
            'nutrition_status' => 'not_started',
            'allergen_status' => 'not_started',
            'dietary_status' => 'not_started',
            'media_status' => $approvedMedia ? 'approved_real_media' : ($fallbackMedia ? 'branded_fallback' : 'needs_real_media'),
            'seo_status' => filled($post->meta_title) && filled($post->meta_description) ? 'passed_automated_checks' : 'incomplete',
            'accessibility_status' => $approvedMedia ? 'passed_automated_checks' : 'needs_revision',
            'final_approval_status' => $humanApproved ? 'human_approved' : 'ready_for_human_review',
            'publication_status' => $this->publicationStatus($post->status, $hasCritical || $hasHigh || ! $sourceComplete),
            'metadata' => ['open_issue_count' => $issues->count(), 'pack_status' => $pack?->status, 'human_approval_required' => true, 'auto_publish' => false],
            'blocking_reasons' => $reasons,
        ], $hasCritical, $hasHigh, $fallbackMedia, $humanApproved);
    }

    /** @param array<string,mixed> $attributes @return array<string,mixed> */
    private function makeProfile(string $entityType, string $publicId, string $contentType, array $attributes, bool $hasCritical, bool $hasHigh, bool $fallbackMedia, bool $humanApproved): array
    {
        $cap = $hasCritical ? 49 : ($hasHigh ? 74 : ($fallbackMedia ? 89 : ($humanApproved ? 100 : 95)));
        $base = collect($attributes)->only(['content_quality_status', 'technical_quality_status', 'editorial_quality_status', 'fact_check_status', 'source_coverage_status', 'measurement_status', 'nutrition_status', 'allergen_status', 'dietary_status', 'media_status', 'seo_status', 'accessibility_status'])->filter(fn ($status): bool => in_array($status, ['passed_automated_checks', 'approved_real_media'], true))->count();
        $score = min($cap, (int) round(($base / 12) * 100));

        return [...$attributes, 'entity_type' => $entityType, 'entity_public_id' => $publicId, 'content_type' => $contentType, 'readiness_score' => $score, 'readiness_cap' => $cap, 'rule_version' => ContentQualityProfile::RULE_VERSION, 'last_scanned_at' => now()];
    }

    /** @param array<string,mixed> $attributes */
    private function persist(array $attributes): void
    {
        $profile = ContentQualityProfile::query()->firstOrNew(['entity_type' => $attributes['entity_type'], 'entity_public_id' => $attributes['entity_public_id']]);
        if (! $profile->exists) {
            $profile->public_id = (string) Str::ulid();
        }
        $profile->fill($attributes);
        $profile->save();
    }

    /** @param Collection<int, ContentFactoryIssue> $issues */
    private function factCheckStatus(Collection $issues): string
    {
        return $issues->isEmpty() ? 'passed_automated_checks' : ($issues->contains('risk_level', 'critical') ? 'blocked' : 'in_review');
    }

    /** @return list<string> */
    private function reasons(bool $critical, bool $high, bool $sourceRequired, bool $mediaRequired, bool $humanRequired, ?EditorialReviewPack $pack): array
    {
        return array_values(array_filter([
            $critical ? 'Açık kritik fact-check konusu var.' : null,
            $high ? 'Açık yüksek riskli fact-check konusu var.' : null,
            $sourceRequired ? 'Kaynak kapsamı insan incelemesi gerektiriyor.' : null,
            $mediaRequired ? 'Onaylı gerçek medya yok; fallback gerçek fotoğraf değildir.' : null,
            $humanRequired ? 'Final insan onayı bekleniyor.' : null,
            $pack?->status === 'blocked' ? 'Editoryal review paketi bloklu.' : null,
        ]));
    }

    private function publicationStatus(string $status, bool $blocked): string
    {
        if ($status === 'published') {
            return 'published';
        }

        return $blocked ? 'blocked' : 'ready_for_human_review';
    }

    /** @param array{recipes:int,blogs:int,blocked:int,ready_for_human_review:int} $counts */
    private function countPendingPublication(array &$counts, string $publicationStatus): void
    {
        if ($publicationStatus === 'blocked') {
            $counts['blocked']++;
        }

        if ($publicationStatus === 'ready_for_human_review') {
            $counts['ready_for_human_review']++;
        }
    }

    /** @param array{recipes:int,blogs:int,blocked:int,ready_for_human_review:int} $counts */
    private function releaseState(array $counts): void
    {
        $state = ProductionReleaseState::query()->firstOrCreate([], ['state' => 'local_qa']);
        $state->update(['last_content_health_scan_at' => now(), 'blockers' => ['content_profiles_blocked' => $counts['blocked'], 'human_review_required' => $counts['ready_for_human_review']]]);
    }
}
