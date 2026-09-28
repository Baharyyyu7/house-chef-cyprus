{{--
    Kullanıcı menüsü.

    21st.dev'deki React/shadcn bileşeninin bu projede çalışması mümkün değil:
    burada React, TypeScript, Radix ve shadcn yok — Laravel Blade, Alpine ve
    Tailwind var. O yığını kurmak, çalışan bir arayüzün yanına ikinci bir
    arayüz katmanı eklemek olurdu. Onun yerine aynı davranış ve düzen buraya
    taşındı: baş harf rozeti, gruplanmış menü, ayırıcılar, premium vurgusu.

    Menü bir açılır yüzeydir; okunması gerekir, arkasına bakılması değil.
    Bu yüzden opak. Saydamlık perdenin işi.
--}}

@auth
    <div class="relative" x-data="{ acik: false }" @keydown.escape.window="acik = false">
        <button
            type="button"
            @click="acik = !acik"
            :aria-expanded="acik"
            aria-haspopup="menu"
            aria-label="{{ __('ui.account_menu') }}"
            class="hc-user-trigger hc-nav-control inline-flex items-center gap-2 rounded-2xl bg-charcoal-900 py-1.5 pl-1.5 pr-3 text-sm font-extrabold text-white transition hover:bg-orange-600 shrink-0 whitespace-nowrap"
        >
            <span class="grid h-7 w-7 place-items-center rounded-xl bg-orange-500 text-xs shrink-0 font-black">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span>
            <span class="whitespace-nowrap font-bold">{{ __('ui.my_account') }}</span>
            <span class="transition-transform duration-300 shrink-0" :class="acik ? 'rotate-180' : ''" aria-hidden="true"><x-ui.icon name="chevron-down" size="14" /></span>
        </button>

        {{-- Perde: dışarı dokunmak kapatır. Görünmez ama tıklanabilir. --}}
        <div x-cloak x-show="acik" @click="acik = false" class="fixed inset-0 z-40" aria-hidden="true"></div>

        {{-- Menü tetikleyicisinden açılır: kaynağıyla mekânsal bağı korunur. --}}
        <div
            x-cloak
            x-show="acik"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1 scale-[.97]"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-1 scale-[.97]"
            class="hc-user-menu absolute right-0 z-50 mt-2 w-[19rem] origin-top-right overflow-hidden rounded-3xl p-1.5"
            role="menu"
            aria-label="Hesap"
        >
            <div class="flex items-center gap-3 rounded-2xl px-3 py-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-orange-500 text-sm font-extrabold text-white">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-bold" style="color: var(--hc-text-primary)">{{ auth()->user()->name }}</span>
                    <span class="hc-caption block truncate" style="color: var(--hc-text-secondary)">{{ auth()->user()->email }}</span>
                </span>
                @if(auth()->user()->isPremium())
                    <span class="hc-user-menu__badge">Premium</span>
                @endif
            </div>

            <div class="hc-user-menu__rule"></div>

            @foreach ([
                ['dashboard', __('ui.my_account'), 'gauge'],
                ['pantry.index', 'Dolabım', 'archive'],
                ['favorites.index', 'Favorilerim', 'heart'],
                ['collections.index', 'Koleksiyonlarım', 'book'],
                ['profile', 'Profil ayarları', 'sliders'],
            ] as [$rota, $etiket, $simge])
                @if (Route::has($rota))
                    <a href="{{ route($rota) }}" class="hc-user-menu__item" role="menuitem">
                        <span class="hc-user-menu__icon" aria-hidden="true"><x-ui.icon :name="$simge" size="16" /></span>{{ $etiket }}
                    </a>
                @endif
            @endforeach

            @if (! auth()->user()->isPremium() && Route::has('pricing') && app(\App\Services\SiteFeatureService::class)->enabled('premium'))
                <div class="hc-user-menu__rule"></div>
                <a href="{{ route('pricing') }}" class="hc-user-menu__item hc-user-menu__item--accent" role="menuitem">
                    <span class="hc-user-menu__icon" aria-hidden="true"><x-ui.icon name="sparkles" size="16" /></span>{{ __('ui.go_premium') }}
                    <span class="hc-user-menu__badge ml-auto">{{ __('ui.ad_free') }}</span>
                </a>
            @endif

            {{-- Yönetim bağlantısı burada da vardı; başlık çubuğunda kendi
                 düğmesi dururken aynı yere giden ikinci bir kapı, menüyü
                 uzatmaktan başka bir şey yapmıyordu. --}}

            <div class="hc-user-menu__rule"></div>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="hc-user-menu__item w-full text-left" role="menuitem">
                    <span class="hc-user-menu__icon" aria-hidden="true"><x-ui.icon name="log-out" size="16" /></span>{{ __('ui.logout') }}
                </button>
            </form>
        </div>
    </div>
@endauth
