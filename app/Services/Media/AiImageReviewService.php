<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\AiImageGeneration;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

final class AiImageReviewService
{
    public function approve(AiImageGeneration $generation, User $reviewer, ?string $notes = null): void
    {
        $media = $generation->media;
        abort_unless($generation->status === 'ready_for_review' && $media !== null && $media->visibility === 'private', 422, 'Bu görsel onay için uygun değil.');
        abort_unless(is_string($media->path) && Storage::disk('local')->exists($media->path), 422, 'Private görsel dosyası bulunamadı.');

        $extension = pathinfo($media->path, PATHINFO_EXTENSION) ?: 'png';
        $publicPath = 'media/ai-approved/'.$generation->recipe->public_id.'/'.$generation->public_id.'.'.$extension;
        Storage::disk('public')->put($publicPath, Storage::disk('local')->get($media->path));
        $generation->recipe->media()->where('is_placeholder', true)->update(['sort_order' => 99]);
        $media->update([
            'disk' => 'public', 'path' => $publicPath, 'visibility' => 'public', 'sort_order' => 0,
            'verification_status' => 'verified', 'confidence' => 90, 'reviewed_at' => now(),
            // AI output can be reviewed and published, but must never be represented as a licensed real photograph.
            'media_status' => 'approved_ai_media', 'fidelity_status' => 'strong_match', 'fidelity_confidence' => 90,
            'license_review_status' => 'approved', 'attribution_review_status' => 'approved', 'optimization_status' => 'pending',
            'media_reviewer_user_id' => $reviewer->id, 'media_reviewed_at' => now(), 'license_reviewed_at' => now(), 'attribution_reviewed_at' => now(),
            'health_scanned_at' => now(), 'health_rule_version' => config('media_excellence.health_rule_version'),
            'ai_review_status' => 'approved', 'approved_by_user_id' => $reviewer->id, 'approved_at' => now(),
            'provider_metadata' => array_merge((array) $media->provider_metadata, ['storage' => 'public_after_manual_review']),
        ]);
        $history = (array) $generation->status_transitions;
        $history[] = ['status' => 'approved', 'at' => now()->toIso8601String()];
        $generation->update(['status' => 'approved', 'review_notes' => $notes, 'reviewed_at' => now(), 'reviewed_by_user_id' => $reviewer->id, 'status_transitions' => $history]);
        AuditLog::query()->create(['user_id' => $reviewer->id, 'action' => 'ai_image_generation.approve', 'auditable_type' => $generation->getMorphClass(), 'auditable_id' => $generation->id, 'metadata' => ['generation_public_id' => $generation->public_id, 'media_public_id' => $media->public_id]]);
    }
}
