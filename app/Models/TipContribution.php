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
        'provider_payment_id',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
