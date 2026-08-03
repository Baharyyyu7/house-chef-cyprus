<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ContactRequest extends Model
{
    use HasPublicId;
    use SoftDeletes;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'subject', 'message', 'status', 'priority',
        'assigned_admin_id', 'user_id', 'source_page', 'referrer_url', 'utm_source', 'utm_medium',
        'utm_campaign', 'ip_hash', 'user_agent', 'privacy_consent_at', 'first_responded_at', 'resolved_at',
        'last_activity_at', 'submitted_at', 'anonymized_at',
    ];

    protected function casts(): array
    {
        return [
            'privacy_consent_at' => 'datetime',
            'first_responded_at' => 'datetime',
            'resolved_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'submitted_at' => 'datetime',
            'anonymized_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<ContactRequestReply, $this> */
    public function replies(): HasMany
    {
        return $this->hasMany(ContactRequestReply::class)->latest('sent_at');
    }

    /** @return HasMany<ContactRequestNote, $this> */
    public function notes(): HasMany
    {
        return $this->hasMany(ContactRequestNote::class)->latest();
    }

    /** @return HasMany<ContactRequestStatusHistory, $this> */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(ContactRequestStatusHistory::class)->latest('created_at');
    }

    public function displayName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
