@props(['mobile' => false])
@php
    /**
     * Menü altı başlık ve 28 madde ile hep açık duruyordu; ekranda kalan liste
     * aranan bölümü bulmayı kolaylaştırmıyor, yalnız uzuyordu. Başlık sayısı
     * dörde indirildi ve yalnız içinde bulunulan bölüm açık durur (akordeon):
     * ekranda aynı anda bir grubun maddeleri görünür, geri kalanı dört satır.
     *
     * Gruplama işin sıklığına göredir: "Her gün" bir yöneticinin her oturumda
     * açtığı yerlerdir, "Üretim" içerik ve görsel hazırlama, "Veri ve SEO"
     * arada bakılan tablolar, "Sistem" nadiren gerekenler.
     */
    $groups = [
        'Her gün' => [
            ['route' => 'admin.recipes.index', 'label' => 'Tarifler', 'icon' => 'book'],
            ['route' => 'admin.blog.index', 'label' => 'Blog Yazıları', 'icon' => 'pen'],
            ['route' => 'admin.media.index', 'label' => 'Medya Kitaplığı', 'icon' => 'image'],
            ['route' => 'admin.recipe-verification.index', 'label' => 'İçerik Doğrulama', 'icon' => 'shield'],
            ['route' => 'admin.contact-requests.index', 'label' => 'İletişim Talepleri', 'icon' => 'mail'],
        ],
        'Üretim' => [
            ['route' => 'admin.image-requests.index', 'label' => 'Görsel Talepleri', 'icon' => 'image'],
            ['route' => 'admin.ai-image-studio.index', 'label' => 'Görsel Stüdyosu', 'icon' => 'flask'],
            ['route' => 'admin.media-operations.index', 'label' => 'Medya Operasyonları', 'icon' => 'sliders'],
            ['route' => 'admin.editorial-operations.index', 'label' => 'Editoryal Operasyonlar', 'icon' => 'check'],
            ['route' => 'admin.content-factory.index', 'label' => 'İçerik Fabrikası', 'icon' => 'server'],
            ['route' => 'admin.production-content.index', 'label' => 'Canlı İçerik Merkezi', 'icon' => 'archive'],
        ],
        'Veri ve SEO' => collect(config('admin_workspaces'))->map(fn ($area, $key) => [
            'route' => reset($area['tabs'])['route'],
            'label' => __('admin_workspaces.'.$key),
            'icon' => $area['icon'],
            'patterns' => collect($area['tabs'])->pluck('matches')->flatten()->all(),
            'search' => collect(array_keys($area['tabs']))->map(fn ($label) => __('admin_workspaces.'.$label))->join(' '),
        ])->values()->all(),
        'Sistem' => [
            ['route' => 'admin.users.index', 'label' => 'Kullanıcılar', 'icon' => 'users'],
            ['route' => 'admin.release-candidate.index', 'label' => 'RC Kontrol Merkezi', 'icon' => 'shield'],
            ['route' => 'admin.ai-kitchen.index', 'label' => 'AI Operasyonları', 'icon' => 'flask'],
            ['route' => 'admin.integrations.index', 'label' => 'Entegrasyonlar', 'icon' => 'server'],
            ['route' => 'admin.mail-operations.index', 'label' => 'E-posta Operasyonları', 'icon' => 'mail'],
            ['route' => 'admin.failed-jobs.index', 'label' => 'Başarısız İşler', 'icon' => 'warning'],
            ['route' => 'admin.backups.index', 'label' => 'Yedekler', 'icon' => 'archive'],
            ['route' => 'admin.restore-history.index', 'label' => 'Geri Yükleme Geçmişi', 'icon' => 'refresh'],
            ['route' => 'admin.system-events.index', 'label' => 'Sistem Olayları', 'icon' => 'info'],
        ],
    ];
    $isItemActive = fn ($item) => request()->routeIs(...($item['patterns'] ?? [str_replace('.index', '.*', $item['route']), $item['route']]));
    $itemTerms = fn ($item) => $item['label'].' '.($item['search'] ?? '');
@endphp

<nav aria-label="Yönetim menüsü" class="admin-sidebar flex h-full flex-col" x-data="{ eslesir(metin) { return metin.toLocaleLowerCase('tr-TR').includes(adminSidebarQuery.trim().toLocaleLowerCase('tr-TR')) } }">
    <div class="border-b border-white/10 px-4 py-5">
        <div class="flex items-center justify-between gap-2">
            <x-brand-logo variant="dark" context="admin" size="sm" x-show="!adminSidebarCollapsed" />
            <x-brand-logo variant="dark" context="admin" size="xs" compact x-show="adminSidebarCollapsed" />
            <button type="button" @click="adminSidebarCollapsed = !adminSidebarCollapsed" class="hidden rounded-xl p-2 text-orange-200 transition hover:bg-white/10 focus-visible:ring-white lg:inline-flex" :aria-label="adminSidebarCollapsed ? 'Menüyü genişlet' : 'Menüyü daralt'"><x-ui.icon name="menu" size="18" /></button>
        </div>
        <p x-show="!adminSidebarCollapsed" class="mt-4 text-[10px] font-bold uppercase tracking-[.18em] text-orange-300">Yönetim alanı</p>
    </div>

    <div class="border-b border-white/10 px-3 py-3" x-show="!adminSidebarCollapsed">
        <label class="sr-only" for="admin-sidebar-search{{ $mobile ? '-mobile' : '' }}">Yönetim menüsünde ara</label>
        <div class="relative"><x-ui.icon name="search" size="16" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-stone-400" /><input id="admin-sidebar-search{{ $mobile ? '-mobile' : '' }}" x-model="adminSidebarQuery" type="search" class="w-full rounded-xl border border-white/10 bg-white/5 py-2 pl-9 pr-3 text-xs font-semibold text-white placeholder:text-stone-400 focus:border-orange-300 focus:ring-orange-200" placeholder="Menüde ara" /></div>
    </div>

    <div class="flex-1 space-y-4 overflow-y-auto px-3 py-5">
        {{-- Genel Bakış her oturumun başladığı yer; bir grubun içine gömülmez. --}}
        @php($dashboardActive = request()->routeIs('admin.dashboard'))
        <a href="{{ route('admin.dashboard') }}" :title="adminSidebarCollapsed ? 'Genel Bakış' : null"
           aria-label="Genel Bakış" @if($dashboardActive) aria-current="page" @endif
           x-show="eslesir('Genel Bakış')" @class([
            'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition focus-visible:ring-orange-200',
            'bg-orange-400/18 text-white shadow-sm ring-1 ring-orange-300/20' => $dashboardActive,
            'text-stone-300 hover:bg-white/8 hover:text-white' => ! $dashboardActive,
        ])>
            <span class="grid h-6 w-6 shrink-0 place-items-center text-orange-300"><x-ui.icon name="gauge" size="18" /></span>
            <span data-admin-menu-label="genel bakış" x-show="!adminSidebarCollapsed" x-transition.opacity>Genel Bakış</span>
        </a>

        {{-- Akordeon: aynı anda tek grup açık kalır, böylece menü 28 satır
             yerine bir grubun maddeleri kadar uzar. Açık grup sayfa yüklenirken
             bulunulan bölüme göre seçilir; hiçbiri değilse "Her gün" açılır. --}}
        @php($acikGrup = collect($groups)->search(fn ($items) => collect($items)->contains($isItemActive)))
        <div x-data="{ acik: '{{ $acikGrup === false ? 'Her gün' : $acikGrup }}' }" class="space-y-1.5">
        @foreach ($groups as $group => $items)
            @php($groupActive = collect($items)->contains($isItemActive))
            @php($groupSearch = mb_strtolower($group.' '.collect($items)->pluck('label')->join(' '), 'UTF-8'))
            @php($gunluk = $group === 'Her gün')
            <section aria-label="{{ $group }}" x-show="eslesir(@js($group.' '.collect($items)->map($itemTerms)->join(' ')))">
                {{-- "Her gün" akordeon değil: her oturumda kullanılan yerler
                     bir tıklama arkasında durmamalı. Diğer üç grup kapalı
                     kalır, derin işler bir kat aşağıda. --}}
                @unless ($gunluk)
                <button type="button" data-admin-section-toggle @click="acik = (acik === '{{ $group }}' ? '' : '{{ $group }}')" x-show="!adminSidebarCollapsed"
                        class="group flex w-full items-center justify-between gap-2 rounded-lg px-2 py-2 text-left text-[10px] font-extrabold uppercase tracking-[.16em] transition-colors duration-200 hover:bg-white/5"
                        :class="acik === '{{ $group }}' ? 'text-orange-300' : 'text-white/50 hover:text-white'"
                        :aria-expanded="acik === '{{ $group }}'">
                    <span>{{ $group }}</span>
                    <span class="transition-transform duration-300 ease-out" :class="acik === '{{ $group }}' ? 'rotate-180' : ''"><x-ui.icon name="chevron-down" size="14" /></span>
                </button>
                @else
                    <p class="px-2 pb-1 pt-2 text-[10px] font-extrabold uppercase tracking-[.16em] text-white/40" x-show="!adminSidebarCollapsed">{{ $group }}</p>
                @endunless
                <div class="space-y-1 overflow-hidden"
                     x-show="{{ $gunluk ? 'true' : "acik === '".$group."'" }} || adminSidebarCollapsed || adminSidebarQuery"
                     @unless($gunluk) x-collapse.duration.250ms @endunless>
                    @foreach ($items as $item)
                        @php($active = $isItemActive($item))
                        @php($itemSearch = mb_strtolower($item['label'], 'UTF-8'))
                        <a href="{{ route($item['route']) }}" aria-label="{{ $item['label'] }}" @if($active) aria-current="page" @endif :title="adminSidebarCollapsed ? @js($item['label']) : null" x-show="eslesir(@js($itemTerms($item))) || (adminSidebarQuery.trim() && eslesir(@js($group)))" @class([
                            'group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all duration-200 focus-visible:ring-orange-200',
                            'bg-orange-400/18 text-white shadow-sm ring-1 ring-orange-300/20' => $active,
                            'text-stone-300 hover:translate-x-0.5 hover:bg-white/8 hover:text-white' => ! $active,
                        ])>
                            @if ($active)
                                {{-- Bulunulan sayfa göz ucuyla da seçilebilsin. --}}
                                <span class="absolute inset-y-1.5 left-0 w-1 rounded-full bg-orange-400" aria-hidden="true"></span>
                            @endif
                            <span class="grid h-6 w-6 shrink-0 place-items-center text-orange-300 transition-transform duration-200 group-hover:scale-110"><x-ui.icon :name="$item['icon']" size="18" /></span>
                            <span data-admin-menu-label="{{ $itemSearch }}" x-show="!adminSidebarCollapsed" x-transition.opacity>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
        </div>
        <p x-cloak x-show="adminSidebarQuery.trim() && !eslesir(@js('Genel Bakış '.collect($groups)->flatten(1)->map($itemTerms)->join(' ').' '.implode(' ', array_keys($groups))))" role="status" class="px-3 py-4 text-xs text-stone-300">Eşleşen bölüm bulunamadı.</p>
    </div>

    <div class="border-t border-white/10 p-4">
        <div class="flex items-center gap-3">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-orange-400 font-bold text-stone-950">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span>
            <div x-show="!adminSidebarCollapsed" class="min-w-0 flex-1"><p class="truncate text-xs font-semibold text-white">{{ auth()->user()->name }}</p><p class="text-[11px] text-stone-400">Yönetici</p></div>
            <form x-show="!adminSidebarCollapsed" method="post" action="{{ route('logout') }}">@csrf<button class="rounded-lg p-2 text-stone-300 hover:bg-white/10 hover:text-white" aria-label="Çıkış yap" title="Çıkış yap"><x-ui.icon name="log-out" size="16" /></button></form>
        </div>
        <a x-show="!adminSidebarCollapsed" class="mt-3 inline-flex text-xs font-bold text-orange-300 hover:text-orange-200" href="{{ route('home') }}">Siteye dön →</a>
    </div>
</nav>
