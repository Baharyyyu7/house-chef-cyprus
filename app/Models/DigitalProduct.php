<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DigitalProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'features',
        'price',
        'currency',
        'cover_image',
        'file_path',
        'page_count',
        'sales_count',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'sales_count' => 'integer',
        'page_count' => 'integer',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(DigitalOrder::class);
    }
}
