<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\BlogPost;
use App\Models\ContentQualityProfile;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ContentGraduationService
{
    public const RECIPE_STAGES = ['draft', 'technical_review', 'fact_check_review', 'editorial_review', 'media_review', 'seo_review', 'ready_for_final_approval', 'human_approved', 'scheduled', 'published'];

    public const BLOG_STAGES = ['draft', 'originality_review', 'fact_check_review', 'editorial_review', 'seo_review', 'media_review', 'ready_for_final_approval', 'human_approved', 'scheduled', 'published'];

    public function advance(ContentQualityProfile $profile, string $stage, User $actor): void
    {
        $allowed = $profile->content_type === 'recipe' ? self::RECIPE_STAGES : self::BLOG_STAGES;
        if (! in_array($stage, $allowed, true) || in_array($stage, ['human_approved', 'published'], true)) {
            throw ValidationException::withMessages(['stage' => 'Bu aşama yalnız ilgili insan onayı veya yayın aksiyonuyla değiştirilebilir.']);
        }
        if ($profile->publication_status === 'blocked' && ! in_array($stage, ['technical_review', 'fact_check_review', 'originality_review'], true)) {
            throw ValidationException::withMessages(['stage' => 'Açık blocker’lar çözülmeden sonraki zorunlu kapıya geçilemez.']);
        }

        DB::transaction(function () use ($profile, $stage, $actor): void {
            $profile->update(['graduation_stage' => $stage]);
            $this->audit($actor, 'production_content.graduation_advanced', $profile, ['stage' => $stage]);
        });
    }

    public function approve(ContentQualityProfile $profile, User $actor): void
    {
        if ($profile->publication_status === 'blocked' || $profile->readiness_score < 100 || $profile->readiness_cap < 100) {
            throw ValidationException::withMessages(['approval' => 'Açık blocker, onaysız medya veya eksik zorunlu kapı varken insan final onayı verilemez.']);
        }

        DB::transaction(function () use ($profile, $actor): void {
            $profile->update([
                'graduation_stage' => 'human_approved',
                'final_approval_status' => 'human_approved',
                'human_approved_by_user_id' => $actor->id,
                'human_approved_at' => now(),
            ]);
            $this->audit($actor, 'production_content.human_approved', $profile, ['auto_publish' => false]);
        });
    }

    public function publish(ContentQualityProfile $profile, User $actor): void
    {
        if ($profile->final_approval_status !== 'human_approved' || $profile->human_approved_at === null) {
            throw ValidationException::withMessages(['publication' => 'Yayın için kayıtlı insan final onayı zorunludur.']);
        }

        DB::transaction(function () use ($profile, $actor): void {
            $entity = $this->entity($profile);
            $entity->update(['status' => 'published', 'published_at' => now()]);
            $profile->update(['graduation_stage' => 'published', 'publication_status' => 'published', 'published_by_user_id' => $actor->id]);
            $this->audit($actor, 'production_content.published', $profile, ['auto_publish' => false]);
        });
    }

    private function entity(ContentQualityProfile $profile): Model
    {
        $model = $profile->entity_type === Recipe::class ? Recipe::class : BlogPost::class;

        return $model::query()->where('public_id', $profile->entity_public_id)->firstOrFail();
    }

    /** @param array<string,mixed> $metadata */
    private function audit(User $actor, string $action, ContentQualityProfile $profile, array $metadata): void
    {
        AuditLog::query()->create(['user_id' => $actor->id, 'action' => $action, 'auditable_type' => ContentQualityProfile::class, 'auditable_id' => $profile->id, 'metadata' => $metadata]);
    }
}
