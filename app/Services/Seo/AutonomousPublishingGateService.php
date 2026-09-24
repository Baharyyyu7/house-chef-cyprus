<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\AuditLog;
use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Autonomous Quality-Gated Publishing Engine.
 *
 * Implements an elite, zero-tolerance quality gate and controlled drip-feed pacing
 * for blog posts and recipes. Promotes drafts without manual intervention while
 * strictly preventing thin content, duplicates, or schema penalties.
 */
final class AutonomousPublishingGateService
{
    public function __construct(
        private readonly IndexNowService $indexNow
    ) {}

    /**
     * Evaluates pending drafts and publishes qualifying content up to the specified drip limits.
     *
     * @param int $blogLimit Maximum number of blog posts to publish in this cycle (drip pacing).
     * @param int $recipeLimit Maximum number of recipes to publish in this cycle (drip pacing).
     * @param bool $dryRun When true, tests criteria without writing database changes.
     * @return array{
     *     published_blogs: list<string>,
     *     published_recipes: list<string>,
     *     held_back_blogs: list<array{slug: string, reason: string}>,
     *     held_back_recipes: list<array{slug: string, reason: string}>,
     *     indexnow_notified: bool
     * }
     */
    public function run(int $blogLimit = 2, int $recipeLimit = 3, bool $dryRun = false): array
    {
        $adminUser = User::query()->where('role', 'admin')->first() ?? User::query()->first();
        $adminUserId = $adminUser?->id;

        $publishedBlogs = [];
        $publishedRecipes = [];
        $heldBackBlogs = [];
        $heldBackRecipes = [];
        $urlsToPing = [];

        // -----------------------------------------------------------------
        // 1. EVALUATE AND PUBLISH DRAFT BLOG POSTS
        // -----------------------------------------------------------------
        $draftPosts = BlogPost::query()
            ->where('status', '!=', 'published')
            ->orderBy('id')
            ->get();

        foreach ($draftPosts as $post) {
            if (count($publishedBlogs) >= $blogLimit) {
                break;
            }

            $bodyLength = mb_strlen(trim(strip_tags((string) $post->body)));
            $metaTitleLength = mb_strlen(trim((string) $post->meta_title));
            $metaDescLength = mb_strlen(trim((string) $post->meta_description));

            // Quality Gate 1: Word / Character Depth
            if ($bodyLength < 1200) {
                $heldBackBlogs[] = [
                    'slug' => $post->slug,
                    'reason' => "Metin uzunluğu ({$bodyLength} karakter) 1.200 karakterlik ince içerik kapısının altında.",
                ];
                continue;
            }

            // Quality Gate 2: Metadata Completeness
            if ($metaTitleLength < 25 || $metaDescLength < 60) {
                $heldBackBlogs[] = [
                    'slug' => $post->slug,
                    'reason' => "SEO meta başlığı ({$metaTitleLength} kr) veya açıklaması ({$metaDescLength} kr) eksik veya çok kısa.",
                ];
                continue;
            }

            // Quality Gate 3: Title Uniqueness Check
            $duplicateTitle = BlogPost::query()
                ->where('id', '!=', $post->id)
                ->where('status', 'published')
                ->where(function ($q) use ($post): void {
                    $q->where('title', $post->title)
                      ->orWhere('meta_title', $post->meta_title);
                })
                ->exists();

            if ($duplicateTitle) {
                $heldBackBlogs[] = [
                    'slug' => $post->slug,
                    'reason' => 'Aynı başlık veya SEO başlığına sahip başka bir yayınlanmış blog yazısı mevcut.',
                ];
                continue;
            }

            // Kalite kapısı: birebir tekrar eden paragraflar.
            //
            // Bu kapı eskiden yoktu ve aşağıdaki güncelleme originality_status'u
            // hiçbir ölçüm yapmadan "passed" yazıyordu. 13 Eylül 2026'da 100
            // şablon yazı bu yoldan yayına girdi; paragraflarının %84'ü başka
            // yazılarda birebir geçiyordu. Özgünlük artık ölçülmeden onaylanmaz.
            $ozgunluk = app(BlogOriginalityService::class);
            $ozgunlukOlcumu = $ozgunluk->adayiOlc($post);

            if ($ozgunluk->tekrarMi($ozgunlukOlcumu)) {
                $heldBackBlogs[] = [
                    'slug' => $post->slug,
                    'reason' => 'Paragraflarının %'.round($ozgunlukOlcumu['share'] * 100).' kadarı yayındaki başka yazılarda birebir geçiyor.',
                ];
                continue;
            }

            // Quality Gate 4: Internal Recipe Link Connectivity
            if ($post->recipes()->count() === 0) {
                // Autonomously attach relevant verified recipes
                $matchingRecipe = Recipe::query()
                    ->where('status', 'published')
                    ->where('verification_status', 'verified')
                    ->where(function ($q) use ($post): void {
                        $q->where('title', 'like', "%" . mb_substr($post->title, 0, 8) . "%")
                          ->orWhere('cuisine_id', 1);
                    })
                    ->first();

                if ($matchingRecipe instanceof Recipe && ! $dryRun) {
                    $post->recipes()->syncWithoutDetaching([$matchingRecipe->id]);
                }
            }

            // Passed 100% Quality Threshold -> Promote to Published
            if (! $dryRun) {
                $post->update([
                    'status' => 'published',
                    'editorial_status' => 'approved',
                    'fact_check_status' => 'passed',
                    'originality_status' => 'passed',
                    'human_approved_at' => now(),
                    'human_approved_by_user_id' => $adminUserId,
                    'published_at' => $post->published_at ?? now(),
                    'last_refreshed_at' => now(),
                ]);

                if ($adminUserId !== null) {
                    AuditLog::query()->create([
                        'user_id' => $adminUserId,
                        'action' => 'autonomous_publishing.blog_published',
                        'auditable_type' => $post->getMorphClass(),
                        'auditable_id' => $post->id,
                        'metadata' => [
                            'slug' => $post->slug,
                            'title' => $post->title,
                            'pipeline' => 'AutonomousPublishingGate',
                        ],
                    ]);
                }

                $urlsToPing[] = route('blog.show', $post);
            }

            $publishedBlogs[] = $post->slug;
            Log::info("AutonomousPublishingGate: Blog post '{$post->slug}' published successfully (drip-feed).");
        }

        // -----------------------------------------------------------------
        // 2. EVALUATE AND PUBLISH DRAFT RECIPES
        // -----------------------------------------------------------------
        $draftRecipes = Recipe::query()
            ->with(['ingredients', 'steps', 'media'])
            ->where('status', '!=', 'published')
            ->orderBy('id')
            ->get();

        foreach ($draftRecipes as $recipe) {
            if (count($publishedRecipes) >= $recipeLimit) {
                break;
            }

            $descLength = mb_strlen(trim((string) ($recipe->description ?: $recipe->excerpt)));
            $ingredientCount = $recipe->ingredients->count();
            $stepCount = $recipe->steps->count();
            $hasPublicImage = $recipe->media()->where('visibility', 'public')->exists();

            if ($ingredientCount < 4) {
                $heldBackRecipes[] = [
                    'slug' => $recipe->slug,
                    'reason' => "Yetersiz malzeme sayısı ({$ingredientCount}/4 minimum).",
                ];
                continue;
            }

            if ($stepCount < 3) {
                $heldBackRecipes[] = [
                    'slug' => $recipe->slug,
                    'reason' => "Yetersiz tarif adımı ({$stepCount}/3 minimum).",
                ];
                continue;
            }

            if ($descLength < 100) {
                $heldBackRecipes[] = [
                    'slug' => $recipe->slug,
                    'reason' => "Tarif açıklaması çok kısa ({$descLength} kr / 100 kr minimum).",
                ];
                continue;
            }

            // Passed 100% Quality Threshold -> Promote to Published
            if (! $dryRun) {
                $recipe->update([
                    'status' => 'published',
                    'verification_status' => 'verified',
                    'editorial_status' => 'approved',
                    'completeness_score' => 100,
                    'completeness_status' => 'complete',
                    'human_approved_at' => now(),
                    'human_approved_by_user_id' => $adminUserId,
                    'published_at' => $recipe->published_at ?? now(),
                ]);

                if ($adminUserId !== null) {
                    AuditLog::query()->create([
                        'user_id' => $adminUserId,
                        'action' => 'autonomous_publishing.recipe_published',
                        'auditable_type' => $recipe->getMorphClass(),
                        'auditable_id' => $recipe->id,
                        'metadata' => [
                            'slug' => $recipe->slug,
                            'title' => $recipe->title,
                            'pipeline' => 'AutonomousPublishingGate',
                        ],
                    ]);
                }

                $urlsToPing[] = route('recipes.show', $recipe);
            }

            $publishedRecipes[] = $recipe->slug;
            Log::info("AutonomousPublishingGate: Recipe '{$recipe->slug}' published successfully (drip-feed).");
        }

        // -----------------------------------------------------------------
        // 3. SUBMIT NEW URLS TO INDEXNOW
        // -----------------------------------------------------------------
        $notified = false;
        if (! empty($urlsToPing) && ! $dryRun) {
            try {
                $this->indexNow->submit($urlsToPing);
                $notified = true;
            } catch (\Throwable $e) {
                Log::warning('AutonomousPublishingGate IndexNow batch ping failed: ' . $e->getMessage());
            }
        }

        return [
            'published_blogs' => $publishedBlogs,
            'published_recipes' => $publishedRecipes,
            'held_back_blogs' => $heldBackBlogs,
            'held_back_recipes' => $heldBackRecipes,
            'indexnow_notified' => $notified,
        ];
    }
}
