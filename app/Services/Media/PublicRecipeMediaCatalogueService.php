<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\Media;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class PublicRecipeMediaCatalogueService
{
    public function __construct(private readonly PublicMediaEligibilityService $gate) {}

    /** @return array{summary:array{total:int,with_image:int,without_image:int,pending:int},recipes:LengthAwarePaginator,pending_media:Collection<int,Media>} */
    public function catalogue(User $admin, string $filter = 'all', string $search = '', int $page = 1, int $perPage = 24): array
    {
        $recipes = Recipe::query()
            ->translatedInto()
            ->with(['media.reviewedBy', 'media.approvedBy', 'cuisine:id,name', 'category:id,name'])
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->whereDoesntHave('seoMetadata', fn ($metadata) => $metadata->where('robots', 'like', '%noindex%'))
            ->when($search !== '', fn ($query) => $query->where('title', 'like', '%'.$search.'%'))
            ->orderBy('title')
            ->get();

        $pendingMedia = collect();
        $classified = $recipes->map(function (Recipe $recipe) use ($admin, $pendingMedia): Recipe {
            $publicMedia = $recipe->media->where('visibility', 'public')->values();
            $eligible = $this->gate->firstEligible($publicMedia);
            $pending = $publicMedia->first(function (Media $media) use ($admin): bool {
                if ($this->gate->reason($media) !== 'human_approval_missing') {
                    return false;
                }

                $probe = clone $media;
                $probe->forceFill([
                    'media_reviewer_user_id' => $admin->id,
                    'media_reviewed_at' => now(),
                    'approved_by_user_id' => $admin->id,
                    'approved_at' => now(),
                ]);
                $probe->setRelation('reviewedBy', $admin);
                $probe->setRelation('approvedBy', $admin);

                return $this->gate->reason($probe) === null;
            });

            if ($pending instanceof Media) {
                $pendingMedia->push($pending);
            }

            $recipe->setAttribute('media_dashboard_status', $eligible ? 'with_image' : ($pending ? 'pending' : 'without_image'));
            $recipe->setRelation('dashboardMedia', collect([$eligible ?? $pending])->filter());

            return $recipe;
        });

        $withImage = $classified->where('media_dashboard_status', 'with_image')->count();
        $pendingRecipeCount = $classified->where('media_dashboard_status', 'pending')->count();
        $summary = [
            'total' => $classified->count(),
            'with_image' => $withImage,
            'without_image' => $classified->count() - $withImage,
            'pending' => $pendingRecipeCount,
        ];

        $filtered = (match ($filter) {
            'with_image' => $classified->where('media_dashboard_status', 'with_image'),
            'without_image' => $classified->where('media_dashboard_status', 'without_image'),
            'pending' => $classified->where('media_dashboard_status', 'pending'),
            default => $classified,
        })->values();

        $paginator = new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return ['summary' => $summary, 'recipes' => $paginator, 'pending_media' => $pendingMedia->unique('id')->values()];
    }
}
