<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Http\Controllers\CategoryHubController;
use App\Http\Controllers\CuisineHubController;
use App\Http\Controllers\IngredientHubController;
use App\Http\Controllers\RecipeCollectionLandingController;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * "Bu sayfa bu dilde var mı?" sorusunun tek cevabı.
 *
 * 8 Ekim 2026 denetimi: en az 139 hreflang hedefi 404 veriyordu. Türkçe hub
 * ve statik sayfalar yedi dilin hepsine hreflang basıyordu; oysa hub'lar
 * yabancı dilde tarif eşiğine takılıp 404 dönüyor, statik sayfaların hiç
 * çevirisi yok. Site haritası, layout'taki hreflang ve controller'lar aynı
 * soruyu üç ayrı yerde farklı cevaplıyordu. Artık hepsi buraya soruyor ve
 * buradaki cevap controller'ların kullandığı sayım ve eşiğe dayanıyor.
 *
 * İçeriğe bağlı sayfalar (tarif, blog yazısı) kaydın kendi
 * hasTranslation() cevabıyla ayrıca süzülür; burada rota düzeyinde "var"
 * sayılırlar.
 */
final class LocalePageAvailability
{
    public function __construct(private readonly LocaleUrlService $adresler)
    {
    }

    /**
     * @param  string  $path  İç (Türkçe) yol ya da dil önekli/yerelleşmiş yol.
     */
    public function varMi(string $locale, string $path): bool
    {
        if ($locale === $this->adresler->defaultLocale()) {
            return true;
        }

        if (! array_key_exists($locale, $this->adresler->enabledLocales())) {
            return false;
        }

        $route = $this->rota($this->icYol($path));

        if (! $this->adresler->yerellesmisRota($route)) {
            return false;
        }

        return $this->dilde($locale, fn (): bool => $this->rotaVar($route));
    }

    /**
     * Taksonomi adının o dilde karşılığı var mı?
     *
     * Adı çevrilmemiş bir mutfak ya da kategori yabancı sayfada Türkçe
     * başlıkla ("Ana Yemek Rezepte") görünüyordu. Karşılığı yoksa sayfa o
     * dilde yoktur.
     */
    public static function taksonomiAdiVar(string $tur, string $slug, ?string $locale = null): bool
    {
        $locale ??= app()->getLocale();

        if ($locale === (string) config('locales.default', 'tr')) {
            return true;
        }

        return Lang::has("taxonomy.{$tur}.{$slug}", $locale, false);
    }

    private function rotaVar(Route $route): bool
    {
        $slug = (string) $route->parameter('slug', '');

        return match ($route->getName()) {
            'ingredients.hub.index' => IngredientHubController::sayfasiOlanlar()->isNotEmpty(),
            'ingredients.hub.show' => IngredientHubController::sayfasiVarMi($slug),
            'cuisines.index' => CuisineHubController::sayfasiOlanlar()->isNotEmpty(),
            'cuisines.show' => CuisineHubController::sayfasiVar(CuisineHubController::ceviriSayilari()[$slug] ?? 0),
            'categories.index' => CategoryHubController::sayfasiOlanlar()->isNotEmpty(),
            'categories.show' => CategoryHubController::sayfasiVar(CategoryHubController::ceviriSayilari()[$slug] ?? 0),
            'collections.sure.index' => RecipeCollectionLandingController::sayfasiOlanlar()->where('type', 'sure')->isNotEmpty(),
            'collections.zorluk.index' => RecipeCollectionLandingController::sayfasiOlanlar()->where('type', 'zorluk')->isNotEmpty(),
            'collections.sure' => RecipeCollectionLandingController::sayfasiOlanlar()->where('type', 'sure')->where('slug', $slug)->isNotEmpty(),
            'collections.zorluk' => RecipeCollectionLandingController::sayfasiOlanlar()->where('type', 'zorluk')->where('slug', $slug)->isNotEmpty(),
            default => true,
        };
    }

    private function icYol(string $path): string
    {
        $path = '/'.ltrim((string) (parse_url($path, PHP_URL_PATH) ?? '/'), '/');
        $ilk = explode('/', ltrim($path, '/'))[0];

        if (array_key_exists($ilk, (array) config('locales.supported', []))) {
            $path = substr($path, strlen('/'.$ilk)) ?: '/';
            $path = $this->adresler->icYol($ilk, $path);
        }

        return $path;
    }

    private function rota(string $path): ?Route
    {
        try {
            $route = app('router')->getRoutes()->match(Request::create($path, 'GET'));
        } catch (HttpExceptionInterface) {
            return null;
        }

        return $route->getName() !== null ? $route : null;
    }

    /**
     * @template T
     *
     * @param  callable(): T  $is
     * @return T
     */
    private function dilde(string $locale, callable $is): mixed
    {
        $onceki = app()->getLocale();
        app()->setLocale($locale);

        try {
            return $is();
        } finally {
            app()->setLocale($onceki);
        }
    }
}
