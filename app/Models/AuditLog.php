<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class AuditLog extends Model
{
    protected $fillable = ['user_id', 'action', 'auditable_type', 'auditable_id', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
