<x-layouts.site title="Operasyonlar ve Entegrasyonlar · House Chef Cyprus" robots="noindex,nofollow">
    <section class="hc-section hc-container">
        <x-admin.page-header eyebrow="Sistem" title="Entegrasyonlar" />
        <div class="mt-8 grid gap-4 lg:grid-cols-2">
            @foreach($integrations as $integration)
                @php($connected = $integration['status'] === 'CONNECTED_VERIFIED')
                <article class="hc-v3-card p-6">
                    <div class="flex items-start justify-between gap-4"><div><h2 class="text-lg font-extrabold">{{ $integration['name'] }}</h2><p class="mt-2 text-sm text-muted">{{ $integration['purpose'] }}</p></div><span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $connected ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900' }}">{{ $integration['status'] }}</span></div>
                    @if($integration['owner_action'])<div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950"><strong>BLOCKED_EXTERNAL_OWNER_ACTION</strong><p class="mt-1">{{ $integration['owner_action'] }}</p></div>@endif
                </article>
            @endforeach
        </div>
    </section>
</x-layouts.site>
