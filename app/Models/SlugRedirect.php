<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Bir kaydın eski adresi.
 *
 * 19 Eylül 2026'da ölçüldü: Google'da gösterim alan 4 tarif ve yazının
 * adresi değiştirilmiş (cig-kofte-A96HF → etsiz-cig-kofte-A96HF), eskisinden
 * yenisine yönlendirme konmamıştı. Sıralanan adresler 404'e düştü.
 */
final class SlugRedirect extends Model
{
    protected $fillable = ['model_type', 'old_slug', 'model_id'];
}
