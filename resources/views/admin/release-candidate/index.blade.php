<x-layouts.site title="RC1 Yayın Öncesi Kontrol">
    @php
        $labels = ['blocker' => 'Blocker', 'critical' => 'Kritik', 'high' => 'Yüksek', 'medium' => 'Orta', 'low' => 'Düşük'];
        $status = $report['state'] === 'ready_for_prelaunch_human_qa' ? 'Teknik ön kontrol geçti' : 'Açık teknik bulgu var';
    @endphp
    <section class="hc-admin-dashboard hc-v3-section-reveal">
        <div class="hc-admin-page-header">
            <div>
                <p class="hc-kicker">RELEASE CANDIDATE 1</p>
                <h1 class="hc-heading-1 mt-2">Yayın öncesi kontrol merkezi</h1>
                <p class="hc-body-sm mt-3 max-w-2xl" style="color:var(--hc-text-secondary)">Bu sayfa yalnız gerçek katalog, statik denetim ve operasyon sinyallerini özetler. İnsan onayı, canlı hosting sağlığı veya kullanıcı ölçümü yerine geçmez.</p>
            </div>
            <a href="{{ route('admin.production-content.index') }}" class="hc-v3-btn hc-v3-btn--outline">Production içerik merkezine dön</a>
        </div>

        <div @class(['hc-v3-alert mt-7', 'hc-v3-alert--warning' => $report['state'] !== 'ready_for_prelaunch_human_qa']) role="status">
            <span aria-hidden="true">ⓘ</span><div><strong>{{ $status }}</strong><p>Son doğrulama: {{ \Illuminate\Support\Carbon::parse($report['generated_at'])->translatedFormat('d M Y, H:i') }} · {{ $report['measurement_note'] }}</p></div>
        </div>

        <section class="hc-admin-metrics mt-7" aria-label="RC1 bulguları">
            @foreach ($report['findings'] as $severity => $count)
                <article class="hc-admin-metric hc-v3-card"><p class="hc-label-md" style="color:var(--hc-text-muted)">{{ $labels[$severity] }}</p><strong class="hc-admin-metric__value">{{ $count }}</strong><p class="hc-caption mt-auto" style="color:var(--hc-text-muted)">Açık RC1 bulgusu</p></article>
            @endforeach
        </section>

        <section class="mt-8 grid gap-6 xl:grid-cols-2">
            <article class="hc-v3-card p-5 sm:p-6"><p class="hc-kicker">KATALOG</p><h2 class="hc-heading-3 mt-2">Public ve taslak ayrımı</h2><dl class="mt-5 grid gap-3 sm:grid-cols-2">@foreach ($report['catalogue'] as $key => $value)<div class="rounded-xl border p-3" style="border-color:var(--hc-border-subtle)"><dt class="hc-caption" style="color:var(--hc-text-muted)">{{ str($key)->replace('_', ' ')->headline() }}</dt><dd class="mt-1 text-xl font-black">{{ $value }}</dd></div>@endforeach</dl></article>
            <article class="hc-v3-card p-5 sm:p-6"><p class="hc-kicker">SAĞLIK SİNYALLERİ</p><h2 class="hc-heading-3 mt-2">Doğrulanan kaynaklar</h2><dl class="mt-5 space-y-3">@foreach ($report['health'] as $key => $value)@php($summary = is_array($value) ? collect($value)->map(fn ($item, $itemKey): string => is_scalar($item) || $item === null ? str($itemKey)->replace('_', ' ')->headline().': '.($item ?? '—') : str($itemKey)->replace('_', ' ')->headline().' ayrıntısı mevcut')->implode(' · ') : $value)<div class="flex min-w-0 items-start justify-between gap-5 border-b pb-3" style="border-color:var(--hc-border-subtle)"><dt class="hc-body-sm shrink-0">{{ str($key)->replace('_', ' ')->headline() }}</dt><dd class="max-w-[60%] break-words text-right text-sm" style="color:var(--hc-text-secondary)">{{ $summary }}</dd></div>@endforeach</dl></article>
        </section>

        <section class="hc-v3-card mt-8 p-5 sm:p-6"><p class="hc-kicker">İnsan ve Production Kapanışı</p><h2 class="hc-heading-3 mt-2">RC1 öncesi hâlâ doğrulanması gerekenler</h2><ul class="mt-5 grid gap-3 md:grid-cols-2">@foreach ($report['manual_prelaunch_requirements'] as $item)<li class="hc-v3-alert"><span aria-hidden="true">•</span><span>{{ str($item)->replace('_', ' ')->headline() }}</span></li>@endforeach</ul></section>
    </section>
</x-layouts.site>
