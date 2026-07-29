@php
    $groups = [
        'Genel' => [
            ['route' => 'admin.dashboard', 'label' => 'Genel Bakış', 'icon' => 'sparkles'],
            ['route' => 'admin.users.index', 'label' => 'Kullanıcılar', 'icon' => 'users'],
        ],
        'İçerik' => [
            ['route' => 'admin.recipes.index', 'label' => 'Tarifler', 'icon' => 'menu'],
            ['route' => 'admin.recipe-verification.index', 'label' => 'İçerik Doğrulama', 'icon' => 'check'],
            ['route' => 'admin.media.index', 'label' => 'Medya', 'icon' => 'image'],
            ['route' => 'admin.ai-image-studio.index', 'label' => 'Görsel Stüdyosu', 'icon' => 'image'],
        ],
        'Malzeme verisi' => [
            ['route' => 'admin.ingredients.index', 'label' => 'Malzemeler', 'icon' => 'search'],
            ['route' => 'admin.measurements.units', 'label' => 'Ölçü Birimleri', 'icon' => 'info'],
            ['route' => 'admin.measurements.conversions', 'label' => 'Dönüşümler', 'icon' => 'arrow-right'],
        ],
        'Beslenme ve güven' => [
            ['route' => 'admin.nutrition.index', 'label' => 'Beslenme', 'icon' => 'info'],
            ['route' => 'admin.data-quality.index', 'label' => 'Veri Kalitesi', 'icon' => 'check'],
        ],
        'Sistem' => [
            ['route' => 'admin.failed-jobs.index', 'label' => 'Başarısız İşler', 'icon' => 'warning'],
            ['route' => 'admin.backups.index', 'label' => 'Yedekler', 'icon' => 'archive'],
            ['route' => 'admin.restore-history.index', 'label' => 'Geri Yükleme Geçmişi', 'icon' => 'arrow-right'],
            ['route' => 'admin.system-events.index', 'label' => 'Sistem Olayları', 'icon' => 'info'],
        ],
    ];
@endphp

<nav aria-label="Yönetim menüsü" class="admin-sidebar flex h-full flex-col">
    <div class="border-b border-white/10 px-4 py-5">
        <div class="flex items-center justify-between gap-2">
            <x-brand-logo variant="dark" />
            <button type="button" @click="adminSidebarCollapsed = !adminSidebarCollapsed" class="hidden rounded-xl p-2 text-orange-200 transition hover:bg-white/10 focus-visible:ring-white lg:inline-flex" :aria-label="adminSidebarCollapsed ? 'Menüyü genişlet' : 'Menüyü daralt'"><x-ui.icon name="menu" size="18" /></button>
        </div>
        <p x-show="!adminSidebarCollapsed" class="mt-4 text-[10px] font-bold uppercase tracking-[.18em] text-orange-300">Yönetim alanı</p>
    </div>

    <div class="flex-1 space-y-5 overflow-y-auto px-3 py-5">
        @foreach ($groups as $group => $items)
            <section aria-label="{{ $group }}">
                <p x-show="!adminSidebarCollapsed" class="mb-2 px-2 text-[10px] font-extrabold uppercase tracking-[.16em] text-white/45">{{ $group }}</p>
                <div class="space-y-1">
                    @foreach ($items as $item)
                        @php($active = request()->routeIs(str_replace('.index', '.*', $item['route'])) || request()->routeIs($item['route']))
                        <a href="{{ route($item['route']) }}" :title="adminSidebarCollapsed ? '{{ $item['label'] }}' : null" @class([
                            'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition focus-visible:ring-orange-200',
                            'bg-orange-400/18 text-white shadow-sm ring-1 ring-orange-300/20' => $active,
                            'text-stone-300 hover:bg-white/8 hover:text-white' => ! $active,
                        ])>
                            <span class="grid h-6 w-6 shrink-0 place-items-center text-orange-300"><x-ui.icon :name="$item['icon']" size="18" /></span>
                            <span x-show="!adminSidebarCollapsed" x-transition.opacity>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    <div class="border-t border-white/10 p-4">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 rounded-xl bg-white/5 px-3 py-2 text-xs text-stone-300 transition hover:bg-white/10">
            <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-400"></span><span x-show="!adminSidebarCollapsed">Sistem özeti kullanılabilir</span>
        </a>
        <div class="mt-4 flex items-center gap-3">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-orange-400 font-bold text-stone-950">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span>
            <div x-show="!adminSidebarCollapsed" class="min-w-0 flex-1"><p class="truncate text-xs font-semibold text-white">{{ auth()->user()->name }}</p><p class="text-[11px] text-stone-400">Yönetici</p></div>
            <form x-show="!adminSidebarCollapsed" method="post" action="{{ route('logout') }}">@csrf<button class="rounded-lg p-2 text-stone-300 hover:bg-white/10 hover:text-white" aria-label="Çıkış yap"><x-ui.icon name="close" size="16" /></button></form>
        </div>
        <a x-show="!adminSidebarCollapsed" class="mt-3 inline-flex text-xs font-bold text-orange-300 hover:text-orange-200" href="{{ route('home') }}">Siteye dön →</a>
    </div>
</nav>
