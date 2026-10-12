        @php($siteFeatures = app(\App\Services\SiteFeatureService::class))
        <header :class="{ 'is-scrolled': scrolled }" @class([
            'hc-public-header sticky top-0 z-40 transition-all duration-300',
            {{-- Bu kip çubuğu koyu bir hero'nun üstünde okunur kılmak için
                 metni beyaza çeviriyor. Ana sayfa hero'su artık açık zeminli;
                 kip açık kalsaydı menü krem üstünde beyaz kalırdı. --}}
            'hc-header-over-hero' => request()->routeIs('recipes.show'),
        ])>
            <nav class="mx-auto flex h-[4.75rem] w-full max-w-[92rem] items-center justify-between gap-2 px-4 transition-colors duration-300 sm:px-6 lg:px-8 xl:gap-4" aria-label="{{ __('ux.menu') }}">
                <x-brand-logo class="scale-100 sm:scale-105 shrink-0" />
                {{-- Menüde sekiz üst düzey bağlantı vardı ve sığmadığı için
                     "Ana / Sayfa", "Ne / Pişirebilirim?", "AI / Mutfak" diye
                     iki satıra kırılıyordu. Herkesin gittiği yerler üstte
                     kaldı; üyeye özel araçlar tek bir başlık altına indi.
                     "Ana Sayfa" kalktı çünkü logo zaten oraya götürüyor. --}}
                <div class="hidden items-center gap-3.5 text-[13px] font-bold xl:flex 2xl:gap-6 2xl:text-sm">
                    <a @class(['nav-link whitespace-nowrap', 'nav-link-active' => request()->routeIs('recipes.*')]) href="{{ localized_route('recipes.index') }}" @if(request()->routeIs('recipes.*')) aria-current="page" @endif>{{ __('nav.recipes') }}</a>
                    {{-- Sitenin ana değer önerisi misafir menüsünde yoktu; yalnız
                         giriş yapmış üyenin "Mutfağım" açılırında duruyordu. Sayfa
                         herkese açık, bağlantı da herkese. --}}
                    @if($siteFeatures->enabled('explore'))
                        <a @class(['nav-link whitespace-nowrap', 'nav-link-active' => request()->routeIs('ingredients.explore')]) href="{{ localized_route('ingredients.explore') }}" @if(request()->routeIs('ingredients.explore')) aria-current="page" @endif>{{ __('nav.explore') }}</a>
                    @endif
                    <a @class(['nav-link whitespace-nowrap', 'nav-link-active' => request()->routeIs('blog.*')]) href="{{ localized_route('blog.index') }}" @if(request()->routeIs('blog.*')) aria-current="page" @endif>{{ __('nav.blog') }}</a>
                    @if (Route::has('weekly-menu.index'))
                        <a @class(['nav-link whitespace-nowrap', 'nav-link-active' => request()->routeIs('weekly-menu.*')]) href="{{ localized_route('weekly-menu.index') }}" @if(request()->routeIs('weekly-menu.*')) aria-current="page" @endif>{{ __('nav.weekly_menu') }}</a>
                    @endif

                    @if(turkce_sayfa() && $siteFeatures->enabled('services'))
                    <div class="relative" x-data="{ acik: false }" @keydown.escape.window="acik = false">
                        <button type="button" @click="acik = !acik" :aria-expanded="acik" aria-haspopup="menu"
                                @class(['nav-link inline-flex items-center gap-1 whitespace-nowrap', 'nav-link-active' => request()->routeIs('private-chef.*', 'catering.*', 'cooking-classes.*', 'meal-plans.*')])>
                            <x-ui.icon name="sparkles" size="15" /> Hizmetler
                            <span class="transition-transform duration-300" :class="acik ? 'rotate-180' : ''" aria-hidden="true"><x-ui.icon name="chevron-down" size="14" /></span>
                        </button>
                        <div x-cloak x-show="acik" @click="acik = false" class="fixed inset-0 z-40" aria-hidden="true"></div>
                        <div x-cloak x-show="acik"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-1 scale-[.97]"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 -translate-y-1 scale-[.97]"
                             class="hc-user-menu absolute left-1/2 z-50 mt-3 w-[20rem] -translate-x-1/2 origin-top overflow-hidden rounded-3xl p-2 shadow-2xl"
                             role="menu" aria-label="Özel Hizmetler">
                            <a href="{{ route('private-chef.index') }}" class="hc-user-menu__item !items-start" role="menuitem">
                                <span class="min-w-0">
                                    <span class="block font-bold">👨‍🍳 Private Chef Cyprus</span>
                                    <span class="hc-caption block font-normal" style="color: var(--hc-text-secondary)">Villanızda özel şef & gurme Akdeniz akşamı</span>
                                </span>
                            </a>
                            <a href="{{ route('catering.index') }}" class="hc-user-menu__item !items-start" role="menuitem">
                                <span class="min-w-0">
                                    <span class="block font-bold">🍽️ Catering Cyprus</span>
                                    <span class="hc-caption block font-normal" style="color: var(--hc-text-secondary)">Düğün, ziyafet ve kurumsal davet catering</span>
                                </span>
                            </a>
                            <a href="{{ route('cooking-classes.index') }}" class="hc-user-menu__item !items-start" role="menuitem">
                                <span class="min-w-0">
                                    <span class="block font-bold">🧀 Yemek Kursları & Atölye</span>
                                    <span class="hc-caption block font-normal" style="color: var(--hc-text-secondary)">Hellim yapımı ve Kıbrıs meze masterclass</span>
                                </span>
                            </a>
                            <a href="{{ route('meal-plans.index') }}" class="hc-user-menu__item !items-start" role="menuitem">
                                <span class="min-w-0">
                                    <span class="block font-bold">🥗 Akdeniz Yemek Planları</span>
                                    <span class="hc-caption block font-normal" style="color: var(--hc-text-secondary)">7 günlük dengeli menüler & pazar listesi</span>
                                </span>
                            </a>
                        </div>
                    </div>
                    @endif


                    @auth
                    @if($siteFeatures->enabled('member_kitchen'))
                        @php($mutfakRotalari = collect([
                            ['ai-kitchen.index', __('nav.ai_kitchen'), __('ux.plan')],
                            ['pantry.index', __('nav.pantry'), __('ux.pantry')],
                            ['favorites.index', __('nav.favorites'), __('ux.favorites')],
                            ['collections.index', __('ux.collections'), __('ux.organize')],
                        ])->filter(fn ($r) => Route::has($r[0])))

                        <div class="relative" x-data="{ acik: false }" @keydown.escape.window="acik = false">
                            <button type="button" @click="acik = !acik" :aria-expanded="acik" aria-haspopup="menu"
                                    @class(['nav-link inline-flex items-center gap-1 whitespace-nowrap', 'nav-link-active' => request()->routeIs('ai-kitchen.*', 'pantry.*', 'favorites.*', 'collections.*')])>
                                {{ __('nav.my_kitchen') }}
                                <span class="transition-transform duration-300" :class="acik ? 'rotate-180' : ''" aria-hidden="true"><x-ui.icon name="chevron-down" size="14" /></span>
                            </button>

                            <div x-cloak x-show="acik" @click="acik = false" class="fixed inset-0 z-40" aria-hidden="true"></div>

                            <div x-cloak x-show="acik"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-1 scale-[.97]"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 -translate-y-1 scale-[.97]"
                                 class="hc-user-menu absolute left-1/2 z-50 mt-3 w-[20rem] -translate-x-1/2 origin-top overflow-hidden rounded-3xl p-1.5"
                                 role="menu" aria-label="{{ __('nav.my_kitchen') }}">
                                @foreach ($mutfakRotalari as [$rota, $etiket, $aciklama])
                                    <a href="{{ localized_route($rota) }}" class="hc-user-menu__item !items-start" role="menuitem">
                                        <span class="min-w-0">
                                            <span class="block">{{ $etiket }}</span>
                                            <span class="hc-caption block font-normal" style="color: var(--hc-text-secondary)">{{ $aciklama }}</span>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @endauth

                    @if (Route::has('pricing') && $siteFeatures->enabled('premium'))
                        <a @class(['nav-link whitespace-nowrap text-amber-600 dark:text-amber-400', 'nav-link-active' => request()->routeIs('pricing')]) href="{{ localized_route('pricing') }}" @if(request()->routeIs('pricing')) aria-current="page" @endif>{{ __('nav.premium') }}</a>
                    @endif
                    @if (Route::has('shop.index') && $siteFeatures->enabled('shop'))
                        <a @class(['nav-link whitespace-nowrap', 'nav-link-active' => request()->routeIs('shop.*')]) href="{{ localized_route('shop.index') }}" @if(request()->routeIs('shop.*')) aria-current="page" @endif>{{ __('nav.shop') }}</a>
                    @endif
                </div>

                <div class="hc-nav-actions hc-nav-actions--separate hc-nav-actions--unified hidden items-center xl:flex shrink-0">
                    <x-locale-switcher class="hc-nav-control" />
                    <x-theme-switch class="hc-nav-control shrink-0" />
                    @auth
                        @if (auth()->user()->isAdmin())<a href="{{ route('admin.dashboard') }}" class="hc-btn hc-btn-outline hc-nav-control whitespace-nowrap shrink-0 !px-3">{{ __('nav.admin') }}</a>@endif
                        <x-user-menu />
                    @else
                        <a href="{{ route('login') }}" class="hc-btn hc-btn-outline hc-nav-control whitespace-nowrap shrink-0">{{ __('nav.login') }}</a>
                        <a href="{{ route('register') }}" class="hc-btn hc-btn-gold hc-nav-control whitespace-nowrap shrink-0">{{ __('nav.register') }}</a>
                    @endauth
                </div>
                <div class="flex items-center gap-2 xl:hidden shrink-0">
                    {{-- 375px'te logo + dil + tema + menu toplami 404px ediyordu:
                         menu dugmesi ekranin disinda kaliyor ve sayfa yatay
                         kaymadigi icin hata gorunmuyordu. Telefondan hicbir
                         gezinme baglantisina ulasilamiyordu. Dar ekranda dil
                         secici cubuktan cikip cekmeceye giriyor. --}}
                    <div class="hidden sm:block"><x-locale-switcher /></div>
                    <x-theme-switch class="hc-nav-control hc-theme-switch--ikon shrink-0" />
                    <button type="button" @click="menuOpen = !menuOpen" class="hc-menu-button transition duration-200" :aria-expanded="menuOpen" aria-controls="mobile-nav" aria-label="{{ __('ux.open_menu') }}"><x-ui.icon name="menu" size="20" /></button>
                </div>
            </nav>
            {{-- Çekmece kendini modal ilan ediyor; o hâlde arkası gerçekten
                 kararmalı ve sayfa altında kaymamalı. Perdeye dokunmak kapatır. --}}
            <div x-cloak x-show="menuOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="menuOpen = false" class="hc-drawer-scrim fixed inset-0 z-30 xl:hidden" aria-hidden="true"></div>
            {{-- Giriş aşağı kayarak olur; çıkış aynı yolu geri izler. --}}
            <div id="mobile-nav" x-cloak x-show="menuOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-y-3 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="-translate-y-3 opacity-0" @keydown.escape.window="menuOpen = false" x-init="$watch('menuOpen', open => { if (open) { $nextTick(() => $refs.publicDrawer.querySelector('[data-public-drawer-close]')?.focus()) } })" @keydown.tab.prevent="const items = [...$el.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex=\'-1\'])')].filter(item => !item.hidden); const current = items.indexOf(document.activeElement); const next = $event.shiftKey ? (current <= 0 ? items.length - 1 : current - 1) : (current === items.length - 1 ? 0 : current + 1); items[next]?.focus()" class="hc-mobile-drawer relative z-40 border-t px-5 py-5 shadow-xl xl:hidden" role="dialog" aria-modal="true" aria-label="{{ __('ux.menu') }}">
                <div x-ref="publicDrawer" class="mx-auto max-w-7xl">
                    <div class="mb-3 flex justify-end"><button type="button" data-public-drawer-close @click="menuOpen = false" class="hc-icon-button !h-10 !w-10 !rounded-xl" aria-label="{{ __('ux.close_menu') }}"><x-ui.icon name="close" size="18" /></button></div>
                <div class="grid gap-2 text-sm font-semibold">
                    <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('home')]) href="{{ localized_route('home') }}" @click="menuOpen = false" @if(request()->routeIs('home')) aria-current="page" @endif>{{ __('nav.home') }}</a>
                    <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('recipes.*')]) href="{{ localized_route('recipes.index') }}" @click="menuOpen = false" @if(request()->routeIs('recipes.*')) aria-current="page" @endif>{{ __('nav.recipes') }}</a>
                    @if($siteFeatures->enabled('explore'))
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('ingredients.explore')]) href="{{ localized_route('ingredients.explore') }}" @click="menuOpen = false" @if(request()->routeIs('ingredients.explore')) aria-current="page" @endif>{{ __('nav.explore') }}</a>
                    @endif
                    <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('blog.*')]) href="{{ localized_route('blog.index') }}" @click="menuOpen = false" @if(request()->routeIs('blog.*')) aria-current="page" @endif>{{ __('nav.blog') }}</a>
                    @if (Route::has('weekly-menu.index'))
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('weekly-menu.*')]) href="{{ localized_route('weekly-menu.index') }}" @click="menuOpen = false" @if(request()->routeIs('weekly-menu.*')) aria-current="page" @endif>🗓️ {{ __('nav.weekly_menu') }}</a>
                    @endif
                    @if (Route::has('pricing') && $siteFeatures->enabled('premium'))
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('pricing'), 'text-amber-600 dark:text-amber-400']) href="{{ localized_route('pricing') }}" @click="menuOpen = false" @if(request()->routeIs('pricing')) aria-current="page" @endif>👑 {{ __('nav.premium') }}</a>
                    @endif
                    {{-- Mağaza masaüstü menüsünde vardı, çekmecede yoktu: telefondan
                         gelen ziyaretçi dijital ürünleri hiç göremiyordu. --}}
                    @if (Route::has('shop.index') && $siteFeatures->enabled('shop'))
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('shop.*')]) href="{{ localized_route('shop.index') }}" @click="menuOpen = false" @if(request()->routeIs('shop.*')) aria-current="page" @endif>{{ __('nav.shop') }}</a>
                    @endif
                    @if(turkce_sayfa() && $siteFeatures->enabled('services'))
                    <div class="my-2 border-t border-stone-200/60 pt-2 dark:border-stone-800">
                        <p class="px-3 text-xs font-bold uppercase tracking-wider text-orange-600 dark:text-orange-400">✨ Özel Hizmetler</p>
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('private-chef.*')]) href="{{ route('private-chef.index') }}" @click="menuOpen = false">👨‍🍳 Private Chef Cyprus</a>
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('catering.*')]) href="{{ route('catering.index') }}" @click="menuOpen = false">🍽️ Catering & Davet</a>
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('cooking-classes.*')]) href="{{ route('cooking-classes.index') }}" @click="menuOpen = false">🧀 Yemek Kursları & Atölye</a>
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('meal-plans.*')]) href="{{ route('meal-plans.index') }}" @click="menuOpen = false">🥗 Akdeniz Yemek Planları</a>
                    </div>
                    @endif

                    @auth
                    @if($siteFeatures->enabled('member_kitchen'))
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('ai-kitchen.*')]) href="{{ localized_route('ai-kitchen.index') }}" @click="menuOpen = false" @if(request()->routeIs('ai-kitchen.*')) aria-current="page" @endif>{{ __('nav.ai_kitchen') }}</a>
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('pantry.*')]) href="{{ localized_route('pantry.index') }}" @click="menuOpen = false" @if(request()->routeIs('pantry.*')) aria-current="page" @endif>{{ __('nav.pantry') }}</a>
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('favorites.*')]) href="{{ localized_route('favorites.index') }}" @click="menuOpen = false" @if(request()->routeIs('favorites.*')) aria-current="page" @endif>{{ __('nav.favorites') }}</a>
                    @endif
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('dashboard', 'profile')]) href="{{ localized_route('dashboard') }}" @click="menuOpen = false" @if(request()->routeIs('dashboard', 'profile')) aria-current="page" @endif>{{ __('nav.dashboard') }}</a>
                        @if(auth()->user()->isAdmin())<a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('admin.*')]) href="{{ route('admin.dashboard') }}" @click="menuOpen = false" @if(request()->routeIs('admin.*')) aria-current="page" @endif>{{ __('nav.admin') }}</a>@endif
                        <form method="post" action="{{ route('logout') }}">@csrf<button class="mobile-nav-link w-full text-left" type="submit">{{ __('nav.logout') }}</button></form>
                    @else
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('login')]) href="{{ localized_route('login') }}" @click="menuOpen = false" @if(request()->routeIs('login')) aria-current="page" @endif>{{ __('nav.login') }}</a>
                        <a @class(['mobile-nav-link', 'mobile-nav-link-active' => request()->routeIs('register'), 'text-gold-700 dark:text-gold-400']) href="{{ localized_route('register') }}" @click="menuOpen = false" @if(request()->routeIs('register')) aria-current="page" @endif>{{ __('nav.register') }}</a>
                    @endauth
                </div>

                {{-- Cubuktan cikan dil secicinin karsiligi: dar ekranda tek
                     dil kontrolu burasi. --}}
                <div class="mt-5 border-t pt-5 sm:hidden">
                    <p class="hc-caption mb-3 text-charcoal-700 dark:text-charcoal-300">{{ __('ux.language') }}</p>
                    <x-locale-switcher />
                </div>
                </div>
            </div>
        </header>
