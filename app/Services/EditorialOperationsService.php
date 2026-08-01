<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\BlogPost;
use App\Models\ContentFactoryIssue;
use App\Models\EditorialReviewBatch;
use App\Models\EditorialReviewPack;
use App\Models\EditorialReviewSection;
use App\Models\Recipe;
use App\Models\RecipeMediaCandidate;
use App\Models\SourceResearchQueueItem;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Builds review operations from existing Phase 3 records.  It only creates
 * queues, snapshots and proposals; it never changes publication, verification
 * or scheduling state.
 */
final class EditorialOperationsService
{
    public const MAX_BATCH_SIZE = 50;

    /** @return array{batches:int,issues:int,source_queue:int,recipe_packs:int,blog_packs:int} */
    public function refresh(): array
    {
        return DB::transaction(function (): array {
            $planned = $this->planBatches();
            $sources = $this->syncSourceQueue();
            $packs = $this->syncReviewPacks();

            return ['batches' => $planned['batches'], 'issues' => $planned['issues'], 'source_queue' => $sources, ...$packs];
        });
    }

    /** @return array{batches:int,issues:int} */
    public function planBatches(): array
    {
        $issues = ContentFactoryIssue::query()
            ->whereNotIn('status', ['approved_correction', 'rejected', 'not_applicable'])
            ->orderByRaw("case risk_level when 'critical' then 4 when 'high' then 3 when 'medium' then 2 else 1 end desc")
            ->orderByDesc('priority_score')
            ->orderBy('id')
            ->get();

        $units = $issues->groupBy(function (ContentFactoryIssue $issue): string {
            return $this->categoryFor($issue).'|'.($issue->group_key ?: 'issue-'.$issue->public_id);
        })->sortByDesc(function (Collection $items): int {
            return $items->max('priority_score') ?? 0;
        });

        $plans = [];
        foreach ($units as $key => $unit) {
            foreach ($unit->chunk(self::MAX_BATCH_SIZE) as $chunkIndex => $chunk) {
                $category = explode('|', (string) $key, 2)[0];
                $plans[] = ['key' => $key.'-'.$chunkIndex, 'category' => $category, 'issues' => $chunk->values()];
            }
        }

        EditorialReviewBatch::query()->each(function (EditorialReviewBatch $batch): void {
            $batch->issues()->detach();
        });

        $activePlanningKeys = [];
        foreach ($plans as $sequence => $plan) {
            /** @var Collection<int, ContentFactoryIssue> $batchIssues */
            $batchIssues = $plan['issues'];
            $planningKey = 'phase4-'.Str::slug($plan['category']).'-'.str_pad((string) ($sequence + 1), 3, '0', STR_PAD_LEFT);
            $activePlanningKeys[] = $planningKey;
            $open = $batchIssues->whereNotIn('status', ['approved_correction', 'rejected', 'not_applicable']);
            $sourceRequired = $open->whereIn('status', ['pending', 'source_required', 'reopened'])->count();
            $evidenceReady = $open->filter($this->hasSourceReference(...))->count();
            $critical = $open->where('risk_level', 'critical')->count();
            $high = $open->where('risk_level', 'high')->count();
            $reasons = [];
            if ($critical > 0) {
                $reasons[] = 'Kritik riskli insan doğrulaması bekleniyor.';
            }
            if ($sourceRequired > 0) {
                $reasons[] = 'Kaynak/evidence hazırlığı eksik.';
            }
            $batch = EditorialReviewBatch::query()->firstOrNew(['planning_key' => $planningKey]);
            if (! $batch->exists) {
                $batch->public_id = (string) Str::ulid();
            }
            $batch->fill([
                'title' => $this->batchTitle($plan['category'], $sequence + 1),
                'category' => $plan['category'],
                'severity_mix' => $this->mix($batchIssues, 'risk_level'),
                'risk_mix' => $this->mix($batchIssues, 'risk_domain'),
                'total_issues' => $batchIssues->count(),
                'source_required_count' => $sourceRequired,
                'evidence_ready_count' => $evidenceReady,
                'human_review_count' => $open->count(),
                'duplicate_group_count' => $batchIssues->pluck('group_key')->filter()->unique()->count(),
                'estimated_review_minutes' => $batchIssues->sum(fn (ContentFactoryIssue $issue): int => $this->effortMinutes($issue)),
                'priority_score' => min(100, (int) ($batchIssues->max('priority_score') ?? 0)),
                'status' => $evidenceReady >= $open->count() && $open->isNotEmpty() ? 'ready_for_review' : 'evidence_preparation',
                'progress' => $open->isEmpty() ? 100 : (int) round((($batchIssues->count() - $open->count()) / max(1, $batchIssues->count())) * 100),
                'blocking_reasons' => $reasons,
            ]);
            $batch->save();
            $batch->issues()->sync($batchIssues->pluck('id')->all());
        }

        EditorialReviewBatch::query()->whereNotIn('planning_key', $activePlanningKeys)->whereDoesntHave('issues')->delete();

        return ['batches' => count($plans), 'issues' => $issues->count()];
    }

    public function syncSourceQueue(): int
    {
        ContentFactoryIssue::query()->whereNotIn('status', ['approved_correction', 'rejected', 'not_applicable'])->orderBy('id')->chunkById(100, function (Collection $issues): void {
            foreach ($issues as $issue) {
                $item = SourceResearchQueueItem::query()->firstOrNew(['content_factory_issue_id' => $issue->id]);
                if (! $item->exists) {
                    $item->public_id = (string) Str::ulid();
                }
                $item->fill([
                    'claim' => (string) $issue->claim,
                    'risk_level' => $issue->risk_level,
                    'risk_domain' => $issue->risk_domain,
                    'required_source_type' => $issue->source_requirement ?: $this->sourceType($issue),
                    'suggested_search_terms' => $this->searchTerms($issue),
                    'freshness_requirement' => $this->freshness($issue),
                    'copyright_safe_notes' => 'Yalnız kısa özgün özet ve referans kaydedin; kaynak metnini kopyalamayın.',
                    'reviewer_status' => $this->hasSourceReference($issue) ? 'evidence_ready' : 'source_required',
                ]);
                $item->save();
            }
        });

        return SourceResearchQueueItem::query()->count();
    }

    /** @return array{recipe_packs:int,blog_packs:int} */
    public function syncReviewPacks(): array
    {
        /** @var array<string, list<ContentFactoryIssue>> $issueMap */
        $issueMap = [];
        foreach (ContentFactoryIssue::query()->get() as $issue) {
            $issueMap[$issue->entity_type.'|'.$issue->entity_public_id][] = $issue;
        }
        $recipePacks = $this->syncRecipePacks($issueMap);
        $blogPacks = $this->syncBlogPacks($issueMap);

        return ['recipe_packs' => $recipePacks, 'blog_packs' => $blogPacks];
    }

    /** @param array<string, list<ContentFactoryIssue>> $issueMap */
    private function syncRecipePacks(array $issueMap): int
    {
        $count = 0;
        Recipe::query()->where('content_origin', 'content_factory_draft')->with(['cuisine', 'category', 'recipeIngredients.ingredient', 'steps', 'ingredientGroups'])->orderBy('id')->chunkById(50, function (Collection $recipes) use ($issueMap, &$count): void {
            foreach ($recipes as $recipe) {
                $issues = collect($issueMap[Recipe::class.'|'.$recipe->public_id] ?? []);
                $media = $this->recipeMedia($recipe);
                $snapshot = [
                    'title' => $recipe->title,
                    'cuisine' => $recipe->cuisine?->name,
                    'category' => $recipe->category?->name,
                    'excerpt' => $recipe->excerpt,
                    'description_preview' => Str::limit((string) $recipe->description, 600),
                    'ingredients' => $recipe->recipeIngredients->map(fn ($item): array => ['name' => $item->ingredient?->name, 'display_quantity' => $item->display_quantity, 'unit' => $item->unit, 'canonical_quantity' => $item->canonical_quantity, 'canonical_unit' => $item->canonical_unit])->values()->all(),
                    'steps' => $recipe->steps->map(fn ($step): array => ['position' => $step->position, 'title' => $step->title, 'instruction' => Str::limit((string) $step->instruction, 300)])->values()->all(),
                    'timing' => ['prep' => $recipe->prep_minutes, 'cook' => $recipe->cook_minutes, 'servings' => $recipe->servings],
                    'nutrition' => ['status' => data_get($recipe->nutrition, 'origin'), 'confidence' => data_get($recipe->nutrition, 'confidence')],
                    'allergens' => $recipe->allergens,
                    'dietary' => $recipe->dietary_tags,
                    'seo' => ['title' => $recipe->seoMetadata?->meta_title, 'description' => $recipe->seoMetadata?->meta_description],
                    'media' => $media,
                    'fact_check' => $this->issueSnapshot($issues),
                    'human_approval_required' => true,
                    'auto_publish' => false,
                ];
                $this->upsertPack('recipe', Recipe::class, $recipe->public_id, $recipe->content_factory_batch_id, $snapshot, $issues, $media['status']);
                $count++;
            }
        });

        return $count;
    }

    /** @param array<string, list<ContentFactoryIssue>> $issueMap */
    private function syncBlogPacks(array $issueMap): int
    {
        $count = 0;
        BlogPost::query()->whereNotNull('content_factory_batch_id')->where('status', 'draft')->with(['category', 'recipes'])->orderBy('id')->chunkById(50, function (Collection $posts) use ($issueMap, &$count): void {
            foreach ($posts as $post) {
                $issues = collect($issueMap[BlogPost::class.'|'.$post->public_id] ?? []);
                $snapshot = [
                    'title' => $post->title,
                    'topic_cluster' => $post->category?->name,
                    'search_intent' => $post->search_intent,
                    'excerpt' => $post->excerpt,
                    'content_preview' => Str::limit(strip_tags((string) $post->body), 900),
                    'originality' => $post->originality_status,
                    'seo' => ['title' => $post->meta_title, 'description' => $post->meta_description, 'canonical' => null, 'article_schema' => 'draft'],
                    'related_recipes' => $post->recipes->pluck('public_id')->all(),
                    'source_coverage' => $post->source_notes,
                    'media' => ['status' => 'needs_real_media', 'human_selection_required' => true],
                    'fact_check' => $this->issueSnapshot($issues),
                    'human_approval_required' => true,
                    'auto_publish' => false,
                ];
                $this->upsertPack('blog', BlogPost::class, $post->public_id, $post->content_factory_batch_id, $snapshot, $issues, 'needs_real_media');
                $count++;
            }
        });

        return $count;
    }

    /** @param Collection<int, ContentFactoryIssue> $issues @param array<string,mixed> $snapshot */
    private function upsertPack(string $type, string $entityType, string $entityPublicId, ?int $batchId, array $snapshot, Collection $issues, string $mediaStatus): void
    {
        $openCritical = $issues->where('risk_level', 'critical')->whereNotIn('status', ['approved_correction', 'rejected', 'not_applicable'])->count();
        $openHigh = $issues->where('risk_level', 'high')->filter(fn (ContentFactoryIssue $issue): bool => ! in_array($issue->status, ['approved_correction', 'rejected', 'not_applicable', 'deferred'], true))->count();
        $reasons = [];
        if ($openCritical > 0) {
            $reasons[] = 'Açık kritik fact-check konusu var.';
        }
        if ($openHigh > 0) {
            $reasons[] = 'Açık yüksek riskli fact-check konusu var.';
        }
        if ($mediaStatus !== 'approved_real_media') {
            $reasons[] = 'Gerçek medya insan seçimi/onayı bekliyor.';
        }
        $score = max(0, 100 - ($openCritical * 25) - ($openHigh * 10) - max(0, count($reasons) - 1) * 5);
        $pack = EditorialReviewPack::query()->firstOrNew(['entity_type' => $entityType, 'entity_public_id' => $entityPublicId]);
        if (! $pack->exists) {
            $pack->public_id = (string) Str::ulid();
        }
        $pack->fill([
            'content_factory_batch_id' => $batchId, 'content_type' => $type, 'snapshot' => $snapshot,
            'readiness_score' => $score, 'blocking_reasons' => $reasons, 'status' => $reasons === [] ? 'ready_for_final_human_approval' : 'blocked',
        ]);
        $pack->save();
    }

    /** @return array<string,mixed> */
    public function metrics(): array
    {
        $batches = EditorialReviewBatch::query();
        $packs = EditorialReviewPack::query();

        return [
            'open_issues' => ContentFactoryIssue::query()->whereNotIn('status', ['approved_correction', 'rejected', 'not_applicable'])->count(),
            'critical_high' => ContentFactoryIssue::query()->whereIn('risk_level', ['critical', 'high'])->whereNotIn('status', ['approved_correction', 'rejected', 'not_applicable'])->count(),
            'evidence_ready' => SourceResearchQueueItem::query()->where('reviewer_status', 'evidence_ready')->count(),
            'source_required' => SourceResearchQueueItem::query()->where('reviewer_status', 'source_required')->count(),
            'batches' => ['total' => (clone $batches)->count(), 'in_review' => (clone $batches)->where('status', 'in_review')->count(), 'completed' => (clone $batches)->where('status', 'completed')->count()],
            'recipe_packs' => ['total' => (clone $packs)->where('content_type', 'recipe')->count(), 'blocked' => (clone $packs)->where('content_type', 'recipe')->where('status', 'blocked')->count()],
            'blog_packs' => ['total' => (clone $packs)->where('content_type', 'blog')->count(), 'blocked' => (clone $packs)->where('content_type', 'blog')->where('status', 'blocked')->count()],
            'final_waiting' => (clone $packs)->where('status', 'ready_for_final_human_approval')->count(),
            'media_waiting' => (clone $packs)->whereJsonContains('snapshot->media->human_selection_required', true)->count(),
            'blocked' => (clone $packs)->where('status', 'blocked')->count(),
            'deferred' => ContentFactoryIssue::query()->where('status', 'deferred')->count(),
        ];
    }

    /** @return Collection<int, array{user: User, assigned_batches: int<0, max>, open_issues: int<0, max>, recipe_packs: int<0, max>, blog_packs: int<0, max>, final_approvals: int<0, max>}> */
    public function workload(): Collection
    {
        return User::query()->whereIn('role', ['admin', 'fact_check_reviewer', 'content_editor', 'seo_reviewer', 'media_reviewer'])->get()->reject->isSystemReviewer()->map(function (User $user): array {
            return ['user' => $user, 'assigned_batches' => EditorialReviewBatch::query()->where('assigned_reviewer_id', $user->id)->count(), 'open_issues' => SourceResearchQueueItem::query()->where('assigned_reviewer_id', $user->id)->whereNotIn('reviewer_status', ['completed', 'not_applicable'])->count(), 'recipe_packs' => EditorialReviewPack::query()->where('assigned_reviewer_id', $user->id)->where('content_type', 'recipe')->count(), 'blog_packs' => EditorialReviewPack::query()->where('assigned_reviewer_id', $user->id)->where('content_type', 'blog')->count(), 'final_approvals' => EditorialReviewPack::query()->where('assigned_reviewer_id', $user->id)->where('status', 'ready_for_final_human_approval')->count()];
        })->values();
    }

    public function decideSection(EditorialReviewPack $pack, string $section, string $action, ?string $note, User $actor): EditorialReviewSection
    {
        abort_unless($actor->can('review-editorial-pack', [$pack, $section, $action]), 403);
        $decision = EditorialReviewSection::query()->updateOrCreate(['editorial_review_pack_id' => $pack->id, 'section_key' => $section], ['status' => $action, 'note' => $note, 'decided_by_user_id' => $actor->id, 'decided_at' => now()]);
        AuditLog::query()->create(['user_id' => $actor->id, 'action' => 'editorial_operations.section.'.$action, 'auditable_type' => EditorialReviewPack::class, 'auditable_id' => $pack->id, 'metadata' => ['pack_public_id' => $pack->public_id, 'section' => $section, 'auto_publish' => false]]);

        return $decision;
    }

    private function categoryFor(ContentFactoryIssue $issue): string
    {
        return match ($issue->risk_domain) {
            'safety', 'allergen' => 'gida-guvenligi-ve-alerjen',
            'measurement' => 'olcum-ve-canonical',
            'health', 'nutrition' => 'nutrition',
            'cultural' => 'kulturel-geleneksel',
            default => in_array($issue->risk_level, ['critical', 'high'], true) ? 'yuksek-oncelik-editorial' : 'editorial-ve-seo',
        };
    }

    private function batchTitle(string $category, int $number): string
    {
        return Str::headline(str_replace('-', ' ', $category)).' · İnceleme Batch '.$number;
    }

    /** @return array<string,int> */
    private function mix(Collection $issues, string $column): array
    {
        return $issues->groupBy($column)->map(fn (Collection $items): int => $items->count())->all();
    }

    private function effortMinutes(ContentFactoryIssue $issue): int
    {
        return match ($issue->risk_level) {
            'critical' => 12, 'high' => 8, 'medium' => 5, default => 3
        };
    }

    private function sourceType(ContentFactoryIssue $issue): string
    {
        return match ($issue->risk_domain) {
            'safety', 'allergen', 'health' => 'resmi_kurum_veya_akademik_kaynak',
            'measurement' => 'teknik_uretici_veya_kontrollu_editorial_olcum',
            default => 'birincil_veya_guvenilir_editorial_kaynak',
        };
    }

    /** @return array<int,string> */
    private function searchTerms(ContentFactoryIssue $issue): array
    {
        return array_values(array_filter([$issue->claim, $issue->risk_domain, $issue->issue_type, 'site:.gov OR site:.edu']));
    }

    private function freshness(ContentFactoryIssue $issue): string
    {
        return in_array($issue->risk_domain, ['safety', 'allergen', 'health'], true) ? '12 ay veya daha güncel' : '24 ay veya editoryal yeniden kontrol';
    }

    /** @return array<string,mixed> */
    private function recipeMedia(Recipe $recipe): array
    {
        $candidate = RecipeMediaCandidate::query()->where('recipe_id', $recipe->id)->orderByDesc('updated_at')->first();
        if ($candidate === null) {
            return ['status' => $recipe->image_status === 'branded_fallback' ? 'branded_fallback' : 'needs_real_media', 'candidates' => 0, 'human_selection_required' => true, 'attribution' => null, 'alt_text' => null];
        }

        return ['status' => match ($candidate->status) {
            'approved', 'approved_real_media' => 'approved_real_media', 'selected', 'selected_private' => 'selected_private', 'rejected' => 'rejected', default => 'awaiting_candidate_review'
        }, 'candidates' => RecipeMediaCandidate::query()->where('recipe_id', $recipe->id)->count(), 'human_selection_required' => ! in_array($candidate->status, ['approved', 'approved_real_media'], true), 'attribution' => $candidate->photographer, 'alt_text' => $candidate->alt_text];
    }

    /** @param Collection<int, ContentFactoryIssue> $issues @return array<string,mixed> */
    private function issueSnapshot(Collection $issues): array
    {
        return ['total' => $issues->count(), 'critical_open' => $issues->where('risk_level', 'critical')->whereNotIn('status', ['approved_correction', 'rejected', 'not_applicable'])->count(), 'high_open' => $issues->where('risk_level', 'high')->whereNotIn('status', ['approved_correction', 'rejected', 'not_applicable', 'deferred'])->count(), 'evidence_ready' => $issues->filter($this->hasSourceReference(...))->count(), 'public_ids' => $issues->pluck('public_id')->all()];
    }

    private function hasSourceReference(ContentFactoryIssue $issue): bool
    {
        return data_get($issue->evidence_pack, 'source_reference_ready') === true;
    }
}
