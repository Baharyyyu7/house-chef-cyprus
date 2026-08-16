@php
    /**
     * Menüdeki 27 bölümün 21'i tek bir "Gelişmiş araçlar" listesindeydi ve
     * yedi ayrı işlev aynı varsayılan ikonu paylaşıyordu; aranan bölümü
     * bulmak listeyi baştan sona okumayı gerektiriyordu. Bölümler artık
     * yapılan işe göre ayrılır ve en kalabalık grup dokuz maddedir.
     */
    $groups = [
        'İçerik' => [
            ['route' => 'admin.recipes.index', 'label' => 'Tarifler', 'icon' => 'book'],
            ['route' => 'admin.blog.index', 'label' => 'Blog Yazıları', 'icon' => 'pen'],
            ['route' => 'admin.recipe-verification.index', 'label' => 'İçerik Doğrulama', 'icon' => 'shield'],
            ['route' => 'admin.editorial-operations.index', 'label' => 'Editoryal Operasyonlar', 'icon' => 'check'],
            ['route' => 'admin.content-factory.index', 'label' => 'İçerik Fabrikası', 'icon' => 'flask'],
            ['route' => 'admin.production-content.index', 'label' => 'Production İçerik Merkezi', 'icon' => 'archive'],
        ],
        'Medya' => [
            ['route' => 'admin.media.index', 'label' => 'Medya Kitaplığı', 'icon' => 'image'],
            ['route' => 'admin.media-operations.index', 'label' => 'Medya Operasyonları', 'icon' => 'sliders'],
            ['route' => 'admin.ai-image-studio.index', 'label' => 'Görsel Stüdyosu', 'icon' => 'flask'],
        ],
        'Yemek verisi' => [
            ['route' => 'admin.ingredients.index', 'label' => 'Malzemeler', 'icon' => 'tag'],
            ['route' => 'admin.measurements.units', 'label' => 'Ölçü Birimleri', 'icon' => 'scale'],
            ['route' => 'admin.measurements.conversions', 'label' => 'Dönüşümler', 'icon' => 'refresh'],
            ['route' => 'admin.nutrition.index', 'label' => 'Beslenme', 'icon' => 'heart'],
        ],
        'Büyüme' => [
            ['route' => 'admin.seo.index', 'label' => 'SEO Operasyonları', 'icon' => 'search'],
            ['route' => 'admin.seo.issues', 'label' => 'SEO Sağlığı', 'icon' => 'warning'],
            ['route' => 'admin.product-experience.index', 'label' => 'Ürün Deneyimi', 'icon' => 'gauge'],
            ['route' => 'admin.personalization.index', 'label' => 'Kişiselleştirme', 'icon' => 'sliders'],
        ],
        'Topluluk' => [
            ['route' => 'admin.users.index', 'label' => 'Kullanıcılar', 'icon' => 'users'],
            ['route' => 'admin.contact-requests.index', 'label' => 'İletişim Talepleri', 'icon' => 'mail'],
        ],
        'Sistem' => [
            ['route' => 'admin.data-quality.index', 'label' => 'Veri Kalitesi', 'icon' => 'database'],
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
@endphp

<nav aria-label="Yönetim menüsü" class="admin-sidebar flex h-full flex-col">
    <div class="border-b border-white/10 px-4 py-5">
        <div class="flex items-center justify-between gap-2">
            <x-brand-logo variant="dark" context="admin" size="sm" x-show="!adminSidebarCollapsed" />
            <x-brand-logo variant="dark" context="admin" size="xs" compact x-show="adminSidebarCollapsed" />
            <button type="button" @click="adminSidebarCollapsed = !adminSidebarCollapsed" class="hidden rounded-xl p-2 text-orange-200 transition hover:bg-white/10 focus-visible:ring-white lg:inline-flex" :aria-label="adminSidebarCollapsed ? 'Menüyü genişlet' : 'Menüyü daralt'"><x-ui.icon name="menu" size="18" /></button>
        </div>
        <p x-show="!adminSidebarCollapsed" class="mt-4 text-[10px] font-bold uppercase tracking-[.18em] text-orange-300">Yönetim alanı</p>
    </div>

    <div class="border-b border-white/10 px-3 py-3" x-show="!adminSidebarCollapsed">
        <label class="sr-only" for="admin-sidebar-search">Yönetim menüsünde ara</label>
        <div class="relative"><x-ui.icon name="search" size="16" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-stone-400" /><input id="admin-sidebar-search" x-model="adminSidebarQuery" type="search" class="w-full rounded-xl border border-white/10 bg-white/5 py-2 pl-9 pr-3 text-xs font-semibold text-white placeholder:text-stone-400 focus:border-orange-300 focus:ring-orange-200" placeholder="Menüde ara" /></div>
    </div>

    <div class="flex-1 space-y-4 overflow-y-auto px-3 py-5">
        {{-- Genel Bakış her oturumun başladığı yer; bir grubun içine gömülmez. --}}
        @php($dashboardActive = request()->routeIs('admin.dashboard'))
        <a href="{{ route('admin.dashboard') }}" :title="adminSidebarCollapsed ? 'Genel Bakış' : null"
           x-show="!adminSidebarQuery || 'genel bakış'.includes(adminSidebarQuery.toLocaleLowerCase('tr-TR'))" @class([
            'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition focus-visible:ring-orange-200',
            'bg-orange-400/18 text-white shadow-sm ring-1 ring-orange-300/20' => $dashboardActive,
            'text-stone-300 hover:bg-white/8 hover:text-white' => ! $dashboardActive,
        ])>
            <span class="grid h-6 w-6 shrink-0 place-items-center text-orange-300"><x-ui.icon name="gauge" size="18" /></span>
            <span data-admin-menu-label="genel bakış" x-show="!adminSidebarCollapsed" x-transition.opacity>Genel Bakış</span>
        </a>

        @foreach ($groups as $group => $items)
            @php($groupActive = collect($items)->contains(fn ($item) => request()->routeIs(str_replace('.index', '.*', $item['route'])) || request()->routeIs($item['route'])))
            @php($groupSearch = mb_strtolower($group.' '.collect($items)->pluck('label')->join(' '), 'UTF-8'))
            {{-- "Sistem" günlük işin parçası değil; açık dururken menüyü uzatıyor. --}}
            <section aria-label="{{ $group }}" x-data="{ open: {{ $groupActive || $group !== 'Sistem' ? 'true' : 'false' }} }" x-show="!adminSidebarQuery || '{{ $groupSearch }}'.includes(adminSidebarQuery.toLocaleLowerCase('tr-TR'))">
                <button type="button" @click="open = !open" x-show="!adminSidebarCollapsed" class="mb-2 flex w-full items-center justify-between gap-2 rounded-lg px-2 py-1 text-left text-[10px] font-extrabold uppercase tracking-[.16em] text-white/55 hover:bg-white/5 hover:text-white" :aria-expanded="open">
                    <span>{{ $group }}</span><span class="text-sm leading-none" x-text="open ? '−' : '+'"></span>
                </button>
                <div class="space-y-1" x-show="open || adminSidebarCollapsed || adminSidebarQuery" x-transition.opacity>
                    @foreach ($items as $item)
                        @php($active = request()->routeIs(str_replace('.index', '.*', $item['route'])) || request()->routeIs($item['route']))
                        @php($itemSearch = mb_strtolower($item['label'], 'UTF-8'))
                        <a href="{{ route($item['route']) }}" :title="adminSidebarCollapsed ? '{{ $item['label'] }}' : null" x-show="!adminSidebarQuery || '{{ $itemSearch }}'.includes(adminSidebarQuery.toLocaleLowerCase('tr-TR'))" @class([
                            'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition focus-visible:ring-orange-200',
                            'bg-orange-400/18 text-white shadow-sm ring-1 ring-orange-300/20' => $active,
                            'text-stone-300 hover:bg-white/8 hover:text-white' => ! $active,
                        ])>
                            <span class="grid h-6 w-6 shrink-0 place-items-center text-orange-300"><x-ui.icon :name="$item['icon']" size="18" /></span>
                            <span data-admin-menu-label="{{ $itemSearch }}" x-show="!adminSidebarCollapsed" x-transition.opacity>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    <div class="border-t border-white/10 p-4">
        <div class="flex items-center gap-3">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-orange-400 font-bold text-stone-950">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span>
            <div x-show="!adminSidebarCollapsed" class="min-w-0 flex-1"><p class="truncate text-xs font-semibold text-white">{{ auth()->user()->name }}</p><p class="text-[11px] text-stone-400">Yönetici</p></div>
            <form x-show="!adminSidebarCollapsed" method="post" action="{{ route('logout') }}">@csrf<button class="rounded-lg p-2 text-stone-300 hover:bg-white/10 hover:text-white" aria-label="Çıkış yap"><x-ui.icon name="close" size="16" /></button></form>
        </div>
        <a x-show="!adminSidebarCollapsed" class="mt-3 inline-flex text-xs font-bold text-orange-300 hover:text-orange-200" href="{{ route('home') }}">Siteye dön →</a>
    </div>
</nav>
