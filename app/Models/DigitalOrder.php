<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_uuid',
        'user_id',
        'digital_product_id',
        'buyer_email',
        'buyer_name',
        'amount',
        'currency',
        'payment_provider',
        'checkout_session_id',
        'checkout_expires_at',
        'provider_payment_id',
        'status',
        'dispute_id',
        'disputed_at',
        'download_token',
        'download_count',
        'max_downloads',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'download_count' => 'integer',
        'max_downloads' => 'integer',
        'checkout_expires_at' => 'datetime',
        'disputed_at' => 'datetime',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<DigitalProduct, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(DigitalProduct::class, 'digital_product_id');
    }

    public function canDownload(): bool
    {
        return $this->status === 'completed' && $this->download_count < $this->max_downloads;
    }
}
