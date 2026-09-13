<?php

namespace App\Http\Controllers;

use App\Services\Seo\LocaleUrlService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $enabledLocales = collect(config('locales.supported', []))
            ->filter(fn (array $language): bool => ($language['enabled'] ?? false) === true)
            ->keys()
            ->all();

        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in($enabledLocales)],
            'return_to' => ['nullable', 'string', 'max:2048'],
        ]);

        $locale = $validated['locale'];
        $request->session()->put('hc_locale', $locale);

        if (($user = $request->user()) !== null && $user->preferred_language !== $locale) {
            $user->forceFill(['preferred_language' => $locale])->save();
        }

        $returnTo = $this->safeReturnPath($validated['return_to'] ?? '/');

        // Onay mesajı HEDEF dilde yazılır.
        //
        // Kullanıcı İngilizceye geçtiyse Türkçe bir onay okumak, geçişin
        // çalışmadığı izlenimi verir: kişi zaten Türkçe okuyamadığı için
        // dili değiştirmiştir. Dil adı da o dilin kendi yazımıyla geçer
        // ("Türkçe", "Deutsch"), çünkü diller kendi adlarını her dilde aynı
        // taşımaz ve insanlar kendi dillerini kendi adıyla tanır.
        $dilAdi = (string) (config('locales.supported.'.$locale.'.native_label')
            ?: config('locales.supported.'.$locale.'.label')
            ?: $locale);

        return redirect(app(LocaleUrlService::class)->urlFor($locale, $returnTo))
            ->with('status', trans('ux.locale_switched', ['language' => $dilAdi], $locale))
            ->withCookie(cookie('hc_locale', $locale, 60 * 24 * 365, '/', null, $request->isSecure(), true, false, 'lax'));
    }

    private function safeReturnPath(string $returnTo): string
    {
        return str_starts_with($returnTo, '/') && ! str_starts_with($returnTo, '//') && ! preg_match('/[\\\\\x00-\x1f]/', rawurldecode($returnTo))
            ? $returnTo
            : '/';
    }
}
