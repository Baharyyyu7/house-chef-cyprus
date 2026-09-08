<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;

final class SeoBacklinkCandidate extends Model
{
    use HasPublicId;

    public const STATUSES = [
        'candidate' => 'Araştırma adayı',
        'reviewing' => 'İnceleniyor',
        'contacted' => 'İletişim kaydedildi',
        'rejected' => 'Reddedildi',
        'observed' => 'Gözlemlendi (editör kaydı)',
    ];

    public const TRANSITIONS = [
        'candidate' => ['reviewing', 'rejected'],
        'reviewing' => ['candidate', 'contacted', 'rejected', 'observed'],
        'contacted' => ['reviewing', 'rejected', 'observed'],
        'rejected' => ['reviewing'],
        'observed' => ['reviewing', 'rejected'],
    ];

    protected $fillable = ['source_url', 'source_host', 'target_url', 'deduplication_hash', 'status', 'evidence', 'review_note', 'reviewed_at', 'reviewed_by_user_id'];

    protected function casts(): array
    {
        return ['evidence' => 'array', 'reviewed_at' => 'datetime'];
    }
}
