<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ContentFactoryBulkRun;
use App\Models\ContentFactoryIssue;
use App\Models\ContentFactoryRevision;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Bulk operations are proposal-only.  Each child remains independently
 * reviewable and cannot change public or verification state by itself.
 */
final class ContentFactoryDuplicateGroupService
{
    /** @return Collection<int, ContentFactoryIssue> */
    public function groups(): Collection
    {
        return ContentFactoryIssue::query()->whereNotNull('group_key')->orderBy('group_key')->get()->groupBy('group_key')->map(fn (Collection $issues) => $issues->first())->values();
    }

    public function dryRun(string $groupKey, User $actor): ContentFactoryBulkRun
    {
        $this->authorize($actor, false);
        $issues = $this->issues($groupKey);
        abort_if($issues->isEmpty(), 404);
        $fingerprint = $this->fingerprint($issues);
        $conflicts = $issues->filter(fn (ContentFactoryIssue $issue) => (bool) data_get($issue->evidence_pack, 'context_conflict'))->count();
        $result = ['total' => $issues->count(), 'applicable' => $issues->count() - $conflicts, 'conflicts' => $conflicts, 'skipped' => 0, 'invalid' => 0, 'proposed_revisions' => 0, 'estimated_changes' => 0, 'data_fingerprint' => $fingerprint, 'issue_public_ids' => $issues->pluck('public_id')->all()];
        $run = ContentFactoryBulkRun::query()->create(['group_key' => $groupKey, 'fingerprint' => $fingerprint, 'result' => $result, 'created_by_user_id' => $actor->id, 'expires_at' => now()->addMinutes(30)]);
        $this->audit('content_factory.duplicate_dry_run', $run, $actor, ['group_key' => $groupKey]);

        return $run;
    }

    /** @param array<int, string> $issuePublicIds */
    public function proposePartial(ContentFactoryBulkRun $run, array $issuePublicIds, User $actor): array
    {
        $this->authorize($actor, true);

        return DB::transaction(function () use ($run, $issuePublicIds, $actor): array {
            $run = ContentFactoryBulkRun::query()->lockForUpdate()->findOrFail($run->id);
            if ($run->applied_at !== null || $run->expires_at->isPast()) {
                throw ValidationException::withMessages(['dry_run' => 'Dry-run süresi dolmuş veya zaten kullanılmış. Yeni dry-run oluşturun.']);
            }
            $issues = $this->issues($run->group_key);
            if (! hash_equals($run->fingerprint, $this->fingerprint($issues))) {
                throw ValidationException::withMessages(['dry_run' => 'Grup verisi değişti. Blind bulk apply engellendi.']);
            }
            $allowed = array_flip($run->result['issue_public_ids'] ?? []);
            $created = 0;
            $skipped = 0;
            foreach ($issues->whereIn('public_id', array_values(array_intersect($issuePublicIds, array_keys($allowed)))) as $issue) {
                if ((bool) data_get($issue->evidence_pack, 'context_conflict')) {
                    $skipped++;

                    continue;
                }
                ContentFactoryRevision::query()->firstOrCreate(['content_factory_issue_id' => $issue->id], ['issue_public_id' => $issue->public_id, 'entity_type' => $issue->entity_type, 'entity_public_id' => $issue->entity_public_id, 'field_name' => null, 'before_value' => [], 'proposed_value' => [], 'reason' => 'Duplicate group dry-run sonrası insan incelemesi için ayrı öneri.', 'source_evidence' => ['group_key' => $run->group_key, 'dry_run_public_id' => $run->public_id], 'evidence_reference_ids' => [], 'created_by_user_id' => $actor->id, 'status' => 'proposed', 'conflict_status' => 'none', 'audit_metadata' => ['auto_publish' => false, 'bulk_run_public_id' => $run->public_id]]);
                $this->audit('content_factory.duplicate_child_revision_proposed', $run, $actor, ['issue_public_id' => $issue->public_id]);
                $created++;
            }
            $result = [...$run->result, 'proposed_revisions' => $created, 'estimated_changes' => 0, 'skipped' => $skipped];
            $run->update(['result' => $result, 'applied_at' => now()]);

            return $result;
        });
    }

    /** @return Collection<int, ContentFactoryIssue> */
    private function issues(string $groupKey): Collection
    {
        return ContentFactoryIssue::query()->where('group_key', $groupKey)->orderBy('id')->get();
    }

    /** @param Collection<int, ContentFactoryIssue> $issues */
    private function fingerprint(Collection $issues): string
    {
        return hash('sha256', $issues->map(fn (ContentFactoryIssue $issue) => $issue->public_id.'|'.$issue->updated_at?->toIso8601String().'|'.$issue->status)->implode(';'));
    }

    private function authorize(User $actor, bool $apply): void
    {
        if ($apply ? ! $actor->can('run-duplicate-bulk') : ! $actor->can('run-duplicate-dry-run')) {
            abort(403);
        }
    }

    /** @param array<string, mixed> $metadata */
    private function audit(string $action, ContentFactoryBulkRun $run, User $actor, array $metadata): void
    {
        AuditLog::query()->create(['user_id' => $actor->id, 'action' => $action, 'auditable_type' => ContentFactoryBulkRun::class, 'auditable_id' => $run->id, 'metadata' => [...$metadata, 'bulk_run_public_id' => $run->public_id, 'auto_publish' => false]]);
    }
}
