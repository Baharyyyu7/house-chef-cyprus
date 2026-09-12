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

        return redirect(app(LocaleUrlService::class)->urlFor($locale, $returnTo))
            ->with('status', __('language.changed', ['language' => config("locales.supported.{$locale}.native_label", $locale)], $locale))
            ->withCookie(cookie('hc_locale', $locale, 60 * 24 * 365, '/', null, $request->isSecure(), true, false, 'lax'));
    }

    private function safeReturnPath(string $returnTo): string
    {
        return str_starts_with($returnTo, '/') && ! str_starts_with($returnTo, '//') && ! preg_match('/[\\\\\x00-\x1f]/', rawurldecode($returnTo))
            ? $returnTo
            : '/';
    }
}
