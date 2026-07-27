@php
    $items = [
        ['route' => 'admin.dashboard', 'label' => 'Genel Bakış', 'icon' => '⌂'],
        ['route' => 'admin.recipes.index', 'label' => 'Tarifler', 'icon' => '◫'],
        ['route' => 'admin.ingredients.index', 'label' => 'Malzemeler', 'icon' => '⌘'],
        ['route' => 'admin.nutrition.index', 'label' => 'Beslenme', 'icon' => '✦'],
        ['route' => 'admin.media.index', 'label' => 'Medya', 'icon' => '▣'],
        ['route' => 'admin.data-quality.index', 'label' => 'Veri Kalitesi', 'icon' => '◌'],
        ['route' => 'admin.failed-jobs.index', 'label' => 'Başarısız İşler', 'icon' => '⚠'],
        ['route' => 'admin.system-events.index', 'label' => 'Sistem Olayları', 'icon' => '◉'],
        ['route' => 'admin.backups.index', 'label' => 'Yedekler', 'icon' => '▤'],
        ['route' => 'admin.restore-history.index', 'label' => 'Restore Geçmişi', 'icon' => '↶'],
    ];
@endphp

<nav aria-label="Yönetim menüsü" class="flex h-full flex-col">
    <div class="border-b border-white/10 px-5 py-5">
        <x-brand-logo variant="dark" />
        <p class="mt-4 text-[10px] font-bold uppercase tracking-[.18em] text-gold-300">Yönetim alanı</p>
    </div>

    <div class="flex-1 space-y-1 overflow-y-auto px-3 py-5">
        @foreach ($items as $item)
            @php($active = request()->routeIs(str_replace('.index', '.*', $item['route'])) || request()->routeIs($item['route']))
            <a href="{{ route($item['route']) }}" @class([
                'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition',
                'bg-white/12 text-white shadow-sm' => $active,
                'text-[#d9e5d9] hover:bg-white/8 hover:text-white' => ! $active,
            ])>
                <span class="grid h-6 w-6 place-items-center text-base text-gold-300" aria-hidden="true">{{ $item['icon'] }}</span>
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>

    <div class="border-t border-white/10 p-4 text-xs text-[#c8d7ca]">
        <p class="font-semibold text-white">{{ auth()->user()->name }}</p>
        <a class="mt-2 inline-flex text-gold-300 hover:text-gold-200" href="{{ route('home') }}">Siteye dön →</a>
    </div>
</nav>
