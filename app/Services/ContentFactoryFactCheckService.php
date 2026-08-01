<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContentFactoryIssue;
use App\Models\ContentFactoryRevision;
use Illuminate\Support\Str;

/**
 * Triage never treats a source placeholder as verification and never closes
 * safety, health, allergen, cultural, or measurement claims automatically.
 */
final class ContentFactoryFactCheckService
{
    /** @return array<string, int|float> */
    public function triage(): array
    {
        $updated = 0;
        ContentFactoryIssue::query()->where('status', 'pending')->orderBy('id')->chunkById(100, function ($issues) use (&$updated): void {
            foreach ($issues as $issue) {
                $classification = $this->classify($issue);
                $issue->update([...$classification, 'triaged_at' => now()]);
                $updated++;
            }
        });

        return [...$this->inventory(), 'triaged' => $updated];
    }

    /** @return array<string, int|float> */
    public function inventory(): array
    {
        $issues = ContentFactoryIssue::query();

        return [
            'total' => $issues->count(),
            'pending' => (clone $issues)->where('status', 'pending')->count(),
            'critical' => (clone $issues)->where('risk_level', 'critical')->count(),
            'high' => (clone $issues)->where('risk_level', 'high')->count(),
            'medium' => (clone $issues)->where('risk_level', 'medium')->count(),
            'low' => (clone $issues)->where('risk_level', 'low')->count(),
            'groups' => (clone $issues)->whereNotNull('group_key')->distinct('group_key')->count('group_key'),
            'human_required' => (clone $issues)->where('resolution_mode', 'human_required')->count(),
            'deterministic_resolved' => (clone $issues)->where('resolution_mode', 'deterministic')->where('status', 'resolved')->count(),
            'evidence_ready' => (clone $issues)->whereNotNull('evidence_pack')->count(),
        ];
    }

    /** @return array<string, mixed> */
    public function evidencePack(ContentFactoryIssue $issue): array
    {
        $pack = [
            'claim' => $issue->claim,
            'risk' => $issue->risk_domain,
            'current_text' => $issue->claim,
            'recommended_change' => 'Kaynak doğrulaması tamamlanana kadar kesin iddiayı taslak/koşullu ifade olarak koruyun.',
            'confidence' => $issue->confidence_score,
            // This is a triage aid, not a cited source. Only an explicitly
            // saved source reference may mark an item evidence-ready.
            'source_reference_ready' => false,
            'human_decision_required' => true,
            'publication_impact' => 'Bu konu çözülmeden içerik verified, scheduled veya published olmaz.',
            'bulk_applicable' => $issue->group_key !== null,
            'source_priority' => $this->sourcePriority($issue->risk_domain ?? 'editorial'),
        ];
        $issue->update(['evidence_pack' => $pack]);

        return $pack;
    }

    public function proposeRevision(ContentFactoryIssue $issue): ContentFactoryRevision
    {
        $before = ['claim' => $issue->claim];

        return ContentFactoryRevision::query()->firstOrCreate(
            ['content_factory_issue_id' => $issue->id],
            ['issue_public_id' => $issue->public_id, 'entity_type' => $issue->entity_type, 'entity_public_id' => $issue->entity_public_id, 'field_name' => $issue->field_name, 'before_value' => $before, 'proposed_value' => ['claim' => 'İnsan kaynak incelemesi gerektirir.'], 'reason' => 'Fact-check kanıt paketi için güvenli revision önerisi.', 'source_evidence' => $this->evidencePack($issue), 'evidence_reference_ids' => [], 'before_value_hash' => hash('sha256', json_encode($before)), 'current_value_hash' => hash('sha256', json_encode($before)), 'audit_metadata' => ['auto_publish' => false], 'status' => 'proposed']
        );
    }

    /** @return array<string, mixed> */
    private function classify(ContentFactoryIssue $issue): array
    {
        $map = match ($issue->issue_type) {
            'nutrition_estimate' => ['nutrition', 'health', 'high'],
            'allergen_review' => ['allergen', 'allergen', 'critical'],
            'measurement_conversion' => ['canonical_conversion', 'measurement', 'high'],
            'storage_safety' => ['storage_duration', 'safety', 'critical'],
            'source_review' => ['general_editorial_wording', 'editorial', 'high'],
            'freshness_review' => ['seo_content_claim', 'SEO', 'medium'],
            'media_brief_review' => ['seo_content_claim', 'editorial', 'low'],
            default => ['general_editorial_wording', 'editorial', $issue->risk_level],
        };
        [$category, $risk, $severity] = $map;
        $frequency = max(1, ContentFactoryIssue::query()->where('issue_type', $issue->issue_type)->count());
        $impact = match ($severity) {
            'critical' => 100, 'high' => 75, 'medium' => 50, 'low' => 25, default => 10
        };
        $effort = in_array($risk, ['safety', 'allergen', 'health', 'measurement'], true) ? 90 : 35;
        $priority = min(100, (int) round(($impact * min(100, $frequency) * max(10, 100 - $issue->confidence_score)) / (100 * max(1, $effort))));

        return ['category' => $category, 'risk_domain' => $risk, 'risk_level' => $severity, 'priority_score' => max(1, $priority), 'resolution_mode' => 'human_required', 'group_key' => Str::slug($issue->issue_type.'-'.$category)];
    }

    /** @return array<int, string> */
    private function sourcePriority(string $risk): array
    {
        return match ($risk) {
            'safety', 'health', 'allergen' => ['Resmî kamu kurumu', 'üniversite/akademik yayın', 'uluslararası resmî gıda veritabanı'],
            'measurement' => ['Resmî/üretici teknik bilgi', 'kontrollü House Chef editoryal ölçümü'],
            default => ['Birincil kaynak', 'güvenilir editoryal mutfak kaynağı'],
        };
    }
}
