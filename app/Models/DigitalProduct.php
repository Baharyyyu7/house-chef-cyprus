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

    /**
     * Fiyatın gösterim biçimi.
     *
     * Mağaza görünümünde para birimi simgesi "€" olarak sabit yazılmıştı:
     * ürünün kendi para birimi ne olursa olsun ekranda euro görünüyordu.
     * Simge burada, ürünün kaydettiği para biriminden türetilir.
     */
    public function formattedPrice(): string
    {
        $simgeler = ['TRY' => '₺', 'EUR' => '€', 'USD' => '$', 'GBP' => '£'];
        $kod = strtoupper((string) $this->currency);
        $simge = $simgeler[$kod] ?? ($kod.' ');

        return $simge.number_format((float) $this->price, 2, ',', '.');
    }

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
