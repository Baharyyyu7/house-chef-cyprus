<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EditorialReviewSection extends Model
{
    protected $fillable = ['editorial_review_pack_id', 'section_key', 'status', 'note', 'decided_by_user_id', 'decided_at'];

    protected function casts(): array
    {
        return ['decided_at' => 'datetime'];
    }

    /** @return BelongsTo<EditorialReviewPack, $this> */
    public function pack(): BelongsTo
    {
        return $this->belongsTo(EditorialReviewPack::class, 'editorial_review_pack_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }
}
