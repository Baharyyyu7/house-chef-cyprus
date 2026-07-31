<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property array<string, mixed> $result
 * @property Carbon $expires_at
 * @property Carbon|null $applied_at
 */
final class ContentFactoryBulkRun extends Model
{
    use HasPublicId;

    protected $fillable = ['group_key', 'fingerprint', 'result', 'created_by_user_id', 'expires_at', 'applied_at'];

    protected function casts(): array
    {
        return ['result' => 'array', 'expires_at' => 'datetime', 'applied_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
}
