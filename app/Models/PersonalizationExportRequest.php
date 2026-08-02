<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PersonalizationExportRequest extends Model
{
    use HasPublicId;

    protected $fillable = ['user_id', 'status', 'requested_at', 'completed_at'];

    protected function casts(): array
    {
        return ['requested_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
