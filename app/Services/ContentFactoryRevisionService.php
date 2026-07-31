<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\BlogPost;
use App\Models\ContentFactoryRevision;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Applies a reviewer-approved, explicitly scoped correction only.  It never
 * changes publication or verification state; those remain separate human actions.
 */
final class ContentFactoryRevisionService
{
    /** @var array<class-string<Model>, array<int, string>> */
    private const EDITABLE_FIELDS = [
        Recipe::class => ['title', 'excerpt', 'description', 'storage_notes', 'nutrition', 'dietary_tags', 'allergens', 'tips', 'common_mistakes'],
        BlogPost::class => ['title', 'excerpt', 'body', 'meta_title', 'meta_description', 'source_notes', 'faq_blocks'],
    ];

    public function approve(ContentFactoryRevision $revision, User $actor): ContentFactoryRevision
    {
        $this->assertCapability($revision, $actor, 'approve-content-revision');

        return DB::transaction(function () use ($revision, $actor): ContentFactoryRevision {
            $revision = ContentFactoryRevision::query()->lockForUpdate()->findOrFail($revision->id);
            if ($revision->status === 'approved') {
                return $revision;
            }
            if ($revision->status !== 'proposed') {
                throw ValidationException::withMessages(['revision' => 'Bu revision onaylanamaz.']);
            }
            $revision->update(['status' => 'approved', 'approved_by_user_id' => $actor->id, 'approved_at' => now()]);
            $this->audit('content_factory.revision_approved', $revision, $actor);

            return $revision->refresh();
        });
    }

    public function apply(ContentFactoryRevision $revision, User $actor): ContentFactoryRevision
    {
        $this->assertCapability($revision, $actor, 'apply-content-revision');

        return DB::transaction(function () use ($revision, $actor): ContentFactoryRevision {
            $revision = ContentFactoryRevision::query()->lockForUpdate()->findOrFail($revision->id);
            if ($revision->status === 'applied') {
                return $revision;
            }
            if ($revision->status !== 'approved') {
                throw ValidationException::withMessages(['revision' => 'Revision önce insan onayı gerektirir.']);
            }
            $entity = $this->entity($revision, true);
            $this->assertField($revision, $entity);
            $current = $this->hash($entity->getAttribute($revision->field_name));
            if (! hash_equals((string) $revision->before_value_hash, $current)) {
                $revision->update(['conflict_status' => 'conflict', 'current_value_hash' => $current, 'status' => 'conflict']);
                throw ValidationException::withMessages(['revision' => 'İçerik öneriden sonra değişti; önce yeni diff oluşturulmalıdır.']);
            }
            $entity->setAttribute($revision->field_name, $this->value($revision->proposed_value, $revision->field_name));
            $entity->save();
            $revision->update(['status' => 'applied', 'conflict_status' => 'none', 'current_value_hash' => $this->hash($entity->getAttribute($revision->field_name)), 'applied_by_user_id' => $actor->id, 'applied_at' => now()]);
            $this->audit('content_factory.revision_applied', $revision, $actor);

            return $revision->refresh();
        });
    }

    public function rollback(ContentFactoryRevision $revision, User $actor): ContentFactoryRevision
    {
        $this->assertCapability($revision, $actor, 'apply-content-revision');

        return DB::transaction(function () use ($revision, $actor): ContentFactoryRevision {
            $revision = ContentFactoryRevision::query()->lockForUpdate()->findOrFail($revision->id);
            if ($revision->status === 'rolled_back') {
                return $revision;
            }
            if ($revision->status !== 'applied') {
                throw ValidationException::withMessages(['revision' => 'Yalnız uygulanmış revision geri alınabilir.']);
            }
            $entity = $this->entity($revision, true);
            $this->assertField($revision, $entity);
            $current = $this->hash($entity->getAttribute($revision->field_name));
            if (! hash_equals((string) $revision->current_value_hash, $current)) {
                $revision->update(['conflict_status' => 'conflict', 'status' => 'conflict']);
                throw ValidationException::withMessages(['revision' => 'İçerik tekrar değişti; güvenli geri alma durduruldu.']);
            }
            $entity->setAttribute($revision->field_name, $this->value($revision->before_value, $revision->field_name));
            $entity->save();
            $revision->update(['status' => 'rolled_back', 'rolled_back_by_user_id' => $actor->id, 'rolled_back_at' => now(), 'current_value_hash' => $this->hash($entity->getAttribute($revision->field_name))]);
            $this->audit('content_factory.revision_rolled_back', $revision, $actor);

            return $revision->refresh();
        });
    }

    public function reopen(ContentFactoryRevision $revision, User $actor): ContentFactoryRevision
    {
        $this->assertCapability($revision, $actor, 'approve-content-revision');

        return DB::transaction(function () use ($revision, $actor): ContentFactoryRevision {
            $revision = ContentFactoryRevision::query()->lockForUpdate()->findOrFail($revision->id);
            if ($revision->status === 'proposed') {
                return $revision;
            }
            $revision->update(['status' => 'proposed', 'conflict_status' => 'none', 'approved_by_user_id' => null, 'approved_at' => null]);
            $this->audit('content_factory.revision_reopened', $revision, $actor);

            return $revision->refresh();
        });
    }

    private function entity(ContentFactoryRevision $revision, bool $required): Recipe|BlogPost|null
    {
        $entity = match ($revision->entity_type) {
            Recipe::class => Recipe::query()->where('public_id', $revision->entity_public_id)->first(),
            BlogPost::class => BlogPost::query()->where('public_id', $revision->entity_public_id)->first(),
            default => null,
        };
        if ($required && $entity === null) {
            throw ValidationException::withMessages(['revision' => 'İlgili içerik bulunamadı; değişiklik uygulanmadı.']);
        }

        return $entity;
    }

    private function assertField(ContentFactoryRevision $revision, Recipe|BlogPost $entity): void
    {
        if ($revision->field_name === null || ! in_array($revision->field_name, self::EDITABLE_FIELDS[$entity::class], true)) {
            throw ValidationException::withMessages(['revision' => 'Bu revision güvenli, uygulanabilir bir alan içermiyor.']);
        }
    }

    private function assertCapability(ContentFactoryRevision $revision, User $actor, string $ability): void
    {
        if (! $actor->can($ability)) {
            abort(403);
        }
        if ($revision->created_by_user_id !== null && $revision->created_by_user_id === $actor->id) {
            throw ValidationException::withMessages(['revision' => 'Kendi kritik içerik önerinizi onaylayamaz veya uygulayamazsınız.']);
        }
    }

    private function value(array|string|null $payload, ?string $field): mixed
    {
        if (! is_array($payload)) {
            return $payload;
        }

        return $payload[$field] ?? $payload['value'] ?? null;
    }

    private function hash(mixed $value): string
    {
        // Keep this canonical encoding compatible with the stored revision hash.
        return hash('sha256', json_encode($value, JSON_THROW_ON_ERROR));
    }

    private function audit(string $action, ContentFactoryRevision $revision, User $actor): void
    {
        AuditLog::query()->create(['user_id' => $actor->id, 'action' => $action, 'auditable_type' => ContentFactoryRevision::class, 'auditable_id' => $revision->id, 'metadata' => ['revision_public_id' => $revision->public_id, 'issue_public_id' => $revision->issue_public_id, 'entity_public_id' => $revision->entity_public_id, 'auto_publish' => false]]);
    }
}
