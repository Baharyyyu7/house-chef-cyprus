{{--
    Dil seçici.

    Bayrak, dilin kendi adı ve seçili olana bir onay işareti: listede kendi
    dilini arayan biri önce bayrağa bakar, sonra yazıya. Açılır yüzey kendi
    tetikleyicisinden doğar, ekranın ortasından değil.

    <details> yerine Alpine kullanılıyor, çünkü açılışın yaylanarak gelmesi ve
    dışarı dokununca kapanması gerekiyor; ikisi de <details> ile olmuyor.
--}}
@php
    $languages = config('locales.supported', []);
    $currentLocale = app()->getLocale();
    $currentLanguage = $languages[$currentLocale] ?? $languages[config('locales.default', 'tr')] ?? [];
    // Sayım ETKİN diller üzerinden: kapalı diller listede çıkmıyor, dolayısıyla
    // "birden çok dil var" ölçütü de onları saymamalı. Tek seçenekli bir menü
    // kullanıcıya bir şey sunmaz; bağlantısındaki hreflang niteliği ise tek dil
    // açıkken sayfada hreflang varmış görüntüsü verir.
    $enabledLanguages = array_filter($languages, fn (array $dil): bool => ($dil['enabled'] ?? false) === true);
@endphp

@if (count($enabledLanguages) > 1)
    <div x-data="{ acik: false }" @keydown.escape.window="acik = false" {{ $attributes->class(['hc-locale relative']) }}>
        <button
            type="button"
            @click="acik = !acik"
            :aria-expanded="acik"
            aria-haspopup="menu"
            aria-label="{{ __('language.switcher_label') }}"
            class="hc-locale__trigger"
        >
            <span class="hc-locale__flag" aria-hidden="true">{{ $currentLanguage['flag'] ?? '🏳️' }}</span>
            <span class="hc-locale__code">{{ strtoupper((string) ($currentLanguage['html_locale'] ?? $currentLocale)) }}</span>
            <span class="hc-locale__caret" :class="acik ? 'is-open' : ''" aria-hidden="true">
                <x-ui.icon name="chevron-down" size="14" />
            </span>
            <span class="sr-only">{{ __('language.current', ['language' => $currentLanguage['native_label'] ?? $currentLocale]) }}</span>
        </button>

        {{-- Dışarı dokunmak kapatır. --}}
        <div x-cloak x-show="acik" @click="acik = false" class="fixed inset-0 z-40" aria-hidden="true"></div>

        <div
            x-cloak
            x-show="acik"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
            class="hc-locale__panel"
            role="menu"
            aria-label="{{ __('language.switcher_label') }}"
        >
            @foreach ($languages as $locale => $language)
                @if (($language['enabled'] ?? false) === true)
                    @php($hedefUrl = app(\App\Services\Seo\LocaleUrlService::class)->urlFor($locale, request()->getRequestUri()))
                    <a href="{{ $hedefUrl }}"
                       role="menuitem"
                       lang="{{ $language['html_locale'] ?? $locale }}"
                       hreflang="{{ $language['html_locale'] ?? $locale }}"
                       aria-label="{{ $language['native_label'] }} ({{ strtoupper((string) ($language['html_locale'] ?? $locale)) }})"
                       @class(['hc-locale__option', 'is-current' => $locale === $currentLocale])
                       @if($locale === $currentLocale) aria-current="true" @endif>
                        <span class="hc-locale__flag" aria-hidden="true">{{ $language['flag'] ?? '🏳️' }}</span>
                        <span class="hc-locale__name">{{ $language['native_label'] }}</span>
                        @if($locale === $currentLocale)
                            <span class="hc-locale__check" aria-hidden="true"><x-ui.icon name="check" size="16" /></span>
                        @endif
                    </a>
                @else
                    <span class="hc-locale__option is-pending" aria-disabled="true" title="{{ __('language.coming_soon') }}">
                        <span class="hc-locale__flag" aria-hidden="true">{{ $language['flag'] ?? '🏳️' }}</span>
                        <span class="hc-locale__name">{{ $language['native_label'] }}</span>
                        <span class="hc-locale__status">{{ __('language.coming_soon') }}</span>
                    </span>
                @endif
            @endforeach
        </div>
    </div>
@endif
