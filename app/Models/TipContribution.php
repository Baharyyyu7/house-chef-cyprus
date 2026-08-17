<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipContribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'supporter_name',
        'supporter_email',
        'amount',
        'currency',
        'message',
        'payment_provider',
        'checkout_session_id',
        'checkout_expires_at',
        'provider_payment_id',
        'status',
        'dispute_id',
        'disputed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'checkout_expires_at' => 'datetime',
        'disputed_at' => 'datetime',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
