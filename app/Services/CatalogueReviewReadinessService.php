<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContentFactoryIssue;
use App\Models\ContentFactoryReview;
use App\Models\Media;
use App\Models\Recipe;

/**
 * Read-only readiness summary. It deliberately does not infer an approval
 * from seed data, a placeholder, or an automated review finding.
 */
final class CatalogueReviewReadinessService
{
    /** @return array<string,mixed> */
    public function report(): array
    {
        return [
            'published' => $this->scope('published'),
            'draft' => $this->scope('draft'),
            'media' => $this->media(),
            'safe_to_apply' => 0,
            'auto_publish' => false,
            'auto_verify' => false,
            'human_approval_required' => true,
        ];
    }

    /** @return array<string,mixed> */
    private function scope(string $scope): array
    {
        $prefix = $scope === 'draft' ? 'draft_' : '';
        $reviews = ContentFactoryReview::query()
            ->where('content_type', 'recipe')
            ->where('stage', $prefix.'identity_review')
            ->get(['entity_public_id', 'findings']);
        $issues = ContentFactoryIssue::query()
            ->where('issue_type', 'like', $prefix.'recipe_%')
            ->get(['issue_type', 'risk_level', 'status']);

        return [
            'total' => Recipe::query()->where('status', $scope)->count(),
            'private_identity_reviews' => $reviews->count(),
            'identity_states' => $reviews->map(fn (ContentFactoryReview $review): string => (string) data_get($review->findings, 'identity_state', 'UNKNOWN'))->countBy()->all(),
            'open_issues' => $issues->where('status', 'pending')->count(),
            'high_risk_issues' => $issues->where('risk_level', 'high')->where('status', 'pending')->count(),
            'programmatic_identity_blocks' => $issues->where('issue_type', $prefix.'recipe_programmatic_identity')->where('status', 'pending')->count(),
        ];
    }

    /** @return array<string,int> */
    private function media(): array
    {
        $media = Media::query()->where('mediable_type', Recipe::class);

        return [
            // Keep this summary database-aggregated: loading the entire catalogue's
            // media collection does not scale to the planned review backlog.
            'approved_recipe_media' => (clone $media)
                ->where('visibility', 'public')
                ->where('is_placeholder', false)
                ->where('verification_status', 'verified')
                ->where('media_status', 'approved_real_media')
                ->where('license_review_status', 'approved')
                ->where('attribution_review_status', 'approved')
                ->count(),
            'placeholder_recipe_media' => (clone $media)
                ->where(function ($query): void {
                    $query->where('is_placeholder', true)
                        ->orWhere('verification_status', 'branded_fallback')
                        ->orWhere('media_status', 'branded_fallback');
                })
                ->count(),
        ];
    }
}
