<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'description',
        'image_url',
        'target_url',
        'provider',
        'price',
        'currency',
        'click_count',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'click_count' => 'integer',
    ];

    public function recordClick(): void
    {
        $this->increment('click_count');
    }
}
