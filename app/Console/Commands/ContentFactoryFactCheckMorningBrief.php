<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ContentFactoryIssue;
use App\Models\ContentFactoryRevision;
use App\Services\ContentFactoryBatchReadinessService;
use App\Services\ContentFactoryFactCheckService;
use Illuminate\Console\Command;

final class ContentFactoryFactCheckMorningBrief extends Command
{
    protected $signature = 'content:fact-check-morning-brief {--json : Machine-readable output}';

    protected $description = 'Provide a safe, non-publishing morning fact-check briefing.';

    public function handle(ContentFactoryFactCheckService $factChecks, ContentFactoryBatchReadinessService $readiness): int
    {
        $inventory = $factChecks->inventory();
        $batches = $readiness->summary();
        $result = ['total_issues' => $inventory['total'], 'critical' => $inventory['critical'], 'high' => $inventory['high'], 'medium' => $inventory['medium'], 'low' => $inventory['low'], 'new_last_24h' => ContentFactoryIssue::query()->where('created_at', '>=', now()->subDay())->count(), 'duplicate_groups' => $inventory['groups'], 'source_required' => ContentFactoryIssue::query()->whereIn('status', ['pending', 'source_required'])->count(), 'insufficient_evidence' => ContentFactoryIssue::query()->where('status', 'source_required')->count(), 'evidence_ready' => $inventory['evidence_ready'], 'human_review_pending' => $inventory['human_required'], 'approved_corrections' => ContentFactoryIssue::query()->where('status', 'approved_correction')->count(), 'rejected_corrections' => ContentFactoryIssue::query()->where('status', 'rejected')->count(), 'deferred' => ContentFactoryIssue::query()->where('status', 'deferred')->count(), 'reopened' => ContentFactoryIssue::query()->where('status', 'reopened')->count(), 'revisions_proposed' => ContentFactoryRevision::query()->where('status', 'proposed')->count(), 'revisions_approved' => ContentFactoryRevision::query()->where('status', 'approved')->count(), 'revisions_applied' => ContentFactoryRevision::query()->where('status', 'applied')->count(), 'revision_conflicts' => ContentFactoryRevision::query()->where('status', 'conflict')->count(), 'revisions_rolled_back' => ContentFactoryRevision::query()->where('status', 'rolled_back')->count(), 'recipe_batches_blocked' => $batches['recipe']['blocked'], 'recipe_batches_ready' => $batches['recipe']['ready'], 'blog_batches_blocked' => $batches['blog']['blocked'], 'blog_batches_ready' => $batches['blog']['ready'], 'top_20_priority_issues' => ContentFactoryIssue::query()->orderByDesc('priority_score')->limit(20)->get(['public_id', 'risk_level', 'risk_domain', 'priority_score'])->toArray(), 'queue_status' => config('queue.default'), 'scheduler_status' => 'registered', 'last_failed_job' => null, 'generated_at' => now()->toIso8601String(), 'auto_publish' => false, 'human_approval_required' => true];
        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }
        foreach ($result as $key => $value) {
            $this->line($key.': '.(is_array($value) ? json_encode($value) : $value));
        }

        return self::SUCCESS;
    }
}
