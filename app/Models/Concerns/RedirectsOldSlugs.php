<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\SlugRedirect;
use App\Services\Seo\LocaleUrlService;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Adres değiştiğinde eskisi kaydedilir ve kalıcı olarak yenisine gider.
 *
 * Adresi değişen bir sayfa Google'ın gözünde yeni bir sayfadır; eski adres
 * yönlendirilmezse sıralaması, bağlantıları ve gösterimi sıfırlanır. Bu
 * sistem ajanın ya da editörün yönlendirmeyi hatırlamasına bağlı değil:
 * slug değiştiği anda eski adres kendiliğinden yönlenir.
 */
trait RedirectsOldSlugs
{
    abstract public function showRouteName(): string;

    public static function bootRedirectsOldSlugs(): void
    {
        static::updating(static function (self $kayit): void {
            if (! $kayit->isDirty('slug')) {
                return;
            }

            $eski = (string) $kayit->getOriginal('slug');

            if ($eski === '' || $eski === $kayit->slug) {
                return;
            }

            SlugRedirect::query()->updateOrCreate(
                ['model_type' => $kayit->getMorphClass(), 'old_slug' => $eski],
                ['model_id' => $kayit->getKey()],
            );

            // Yeni adres eskiden başka bir kaydın yönlendirmesiyse o kayıt
            // artık gerçek sayfa; eski yönlendirme silinir, yoksa döngü olur.
            SlugRedirect::query()
                ->where('model_type', $kayit->getMorphClass())
                ->where('old_slug', $kayit->slug)
                ->delete();
        });
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $kayit = parent::resolveRouteBinding($value, $field);

        if ($kayit !== null || ($field ?? $this->getRouteKeyName()) !== 'slug') {
            return $kayit;
        }

        $yonlendirme = SlugRedirect::query()
            ->where('model_type', $this->getMorphClass())
            ->where('old_slug', (string) $value)
            ->first();

        $hedef = $yonlendirme ? static::query()->find($yonlendirme->model_id) : null;

        if ($hedef === null) {
            return null;
        }

        $adres = app(LocaleUrlService::class)->urlFor(app()->getLocale(), route($this->showRouteName(), $hedef));

        throw new HttpResponseException(redirect()->to($adres, 301));
    }
}
