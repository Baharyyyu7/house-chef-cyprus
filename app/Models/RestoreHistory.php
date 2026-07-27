<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;

final class RestoreHistory extends Model
{
    use HasPublicId;

    protected $fillable = ['backup_id', 'target', 'triggered_by_user_id', 'triggered_by_type', 'started_at', 'finished_at', 'status', 'checksum_verified', 'database_opened', 'migration_table_verified', 'user_count_before', 'user_count_after', 'recipe_count_before', 'recipe_count_after', 'ingredient_count_before', 'ingredient_count_after', 'relations_verified', 'health_check_status', 'safe_error_code', 'safe_error_message'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'finished_at' => 'datetime', 'checksum_verified' => 'boolean', 'database_opened' => 'boolean', 'migration_table_verified' => 'boolean', 'relations_verified' => 'boolean'];
    }
}
