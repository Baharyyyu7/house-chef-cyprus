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

        if (($field ?? $this->getRouteKeyName()) !== 'slug') {
            return $kayit;
        }

        // Kayıt bulundu ama yayında değilse yönlendirmeye bakılır. 22 Eylül
        // 2026'da ölçüldü: Google'ın sıraladığı 14 adres, aynı adresi tutan
        // taslak bir nüsha yüzünden 404 veriyordu; yönlendirme kaydı vardı
        // ama hiç çalışmıyordu, çünkü kayıt bulunduğu için buraya hiç
        // gelinmiyordu. Taslak nüsha adresi gölgeliyordu.
        if ($kayit !== null && $this->herkeseAcik($kayit)) {
            return $kayit;
        }

        $valueStr = (string) $value;
        $yonlendirme = SlugRedirect::query()
            ->where('model_type', $this->getMorphClass())
            ->where(function ($q) use ($valueStr) {
                $q->where('old_slug', $valueStr)
                    ->orWhere('old_slug', rawurldecode($valueStr));
            })
            ->first();

        $hedef = $yonlendirme ? static::query()->find($yonlendirme->model_id) : null;

        // Hedef de yayında değilse ya da kaydın kendisiyse yönlendirme
        // yapılmaz: biri 404 zinciri, diğeri sonsuz döngü olurdu.
        if ($hedef === null || ! $this->herkeseAcik($hedef) || ($kayit !== null && $hedef->is($kayit))) {
            return $kayit;
        }

        $currentLocale = app()->getLocale();
        $targetLocale = method_exists($hedef, 'hasTranslation') && ! $hedef->hasTranslation($currentLocale)
            ? (string) config('locales.default', 'tr')
            : $currentLocale;

        $adres = app(LocaleUrlService::class)->urlFor($targetLocale, route($this->showRouteName(), $hedef));

        throw new HttpResponseException(redirect()->to($adres, 301));
    }

    /**
     * Kayıt herkese açık mı?
     *
     * Blog yazısında bunun bir kapsamı var; tarifte yayın ve doğrulama
     * durumu birlikte ölçülüyor. Ölçüt modelin kendi kuralı olmalı, yoksa
     * yönlendirme yayındaki bir sayfayı da ezebilir.
     */
    private function herkeseAcik(self $kayit): bool
    {
        $sorgu = static::query()->whereKey($kayit->getKey());

        if (method_exists($this, 'scopePubliclyVisible')) {
            return $sorgu->publiclyVisible()->exists();
        }

        return $sorgu->where('status', 'published')->where('verification_status', 'verified')->exists();
    }
}
