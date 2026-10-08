<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Exceptions\PublicationGuardException;
use Illuminate\Database\Eloquent\Model;

/**
 * Yayına geçişin son savunma hattı.
 *
 * Kapılar servislerdeydi; bir komut onları atlayıp alanı doğrudan
 * "published" yapabiliyordu. 16 Ağustos 2026'da ~3200 makine sayfası,
 * 13 Eylül'de tek günde 100 yazı bu yoldan yayına çıktı. 8 Ekim'de dört
 * komut insan onayı alanını kendisi dolduruyordu.
 *
 * Model düzeyinde iki kural: günlük yayın tavanı ve (blogda) kayıtlı insan
 * onayı. Yalnız taslaktan yayına geçişte çalışır; yayındaki kaydı
 * güncellemek etkilenmez. Üretimde açık, testte config ile açılır.
 */
trait GuardsPublication
{
    public static function bootGuardsPublication(): void
    {
        static::saving(static function (Model $model): void {
            if (! (config('production.editorial.publication_guard') ?? app()->isProduction())) {
                return;
            }

            if ($model->getAttribute('status') !== 'published') {
                return;
            }

            if ($model->exists && $model->getOriginal('status') === 'published') {
                return;
            }

            if ($model->yayinIcinInsanOnayiGerekir() && (blank($model->getAttribute('human_approved_at')) || blank($model->getAttribute('human_approved_by_user_id')))) {
                throw new PublicationGuardException(class_basename($model).' kayıtlı insan onayı olmadan yayına alınamaz.');
            }

            $tavan = (int) config('production.editorial.daily_publication_cap', 10);
            $bugun = static::query()->where('status', 'published')->where('published_at', '>=', now()->startOfDay())->count();

            if ($bugun >= $tavan) {
                throw new PublicationGuardException(class_basename($model).' için günlük yayın tavanı ('.$tavan.') doldu.');
            }
        });
    }

    protected function yayinIcinInsanOnayiGerekir(): bool
    {
        return false;
    }
}
