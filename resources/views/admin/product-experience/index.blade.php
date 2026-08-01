<x-layouts.site title="Ürün Deneyimi Sağlığı">
    @php
        $status = $report['status'] === 'attention_required' ? 'İnceleme gerekli' : 'Tarayıcı QA bekliyor';
        $labels = ['overall' => 'Genel', 'public' => 'Public', 'member' => 'Üye', 'admin' => 'Yönetim'];
    @endphp
    <section class="hc-admin-dashboard hc-v3-section-reveal">
        <div class="hc-admin-page-header">
            <div>
                <p class="hc-kicker">ÜRÜN DENEYİMİ</p>
                <h1 class="hc-heading-1 mt-2">UX sağlık özeti</h1>
                <p class="hc-body-sm mt-3 max-w-2xl" style="color:var(--hc-text-secondary)">Bu ekran, tasarım sistemi ve kullanıcı durumları için otomatik ön kontrolü gösterir. Gerçek tarayıcı, ekran okuyucu ve Core Web Vitals doğrulaması yerine geçmez.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}#system-status" class="hc-v3-btn hc-v3-btn--outline">Sistem durumuna dön</a>
        </div>

        <div class="hc-v3-alert hc-v3-alert--warning mt-7" role="status"><span aria-hidden="true">ⓘ</span><div><strong>{{ $status }}</strong><p>Son statik tarama: {{ \Illuminate\Support\Carbon::parse($report['generated_at'])->translatedFormat('d M Y, H:i') }} · Kural sürümü {{ $report['rule_version'] }}</p></div></div>

        <section class="hc-admin-metrics mt-7" aria-label="Ürün deneyimi ön kontrol skorları">
            @foreach ($report['scores'] as $key => $score)
                <article class="hc-admin-metric hc-v3-card">
                    <p class="hc-label-md" style="color:var(--hc-text-muted)">{{ $labels[$key] ?? $key }}</p>
                    <strong class="hc-admin-metric__value">{{ $score }}/100</strong>
                    <p class="hc-caption mt-auto" style="color:var(--hc-text-muted)">Otomatik sözleşme ön kontrolü; canlı kullanım veya erişilebilirlik sertifikası değildir.</p>
                </article>
            @endforeach
        </section>

        <section class="mt-8 grid gap-6 xl:grid-cols-2">
            <article class="hc-v3-card p-5 sm:p-6">
                <p class="hc-kicker">OTOMATİK BULGULAR</p><h2 class="hc-heading-3 mt-2">Açık kurallar</h2>
                <dl class="mt-5 divide-y" style="border-color:var(--hc-border-subtle)">
                    @foreach ($report['findings'] as $key => $value)
                        <div class="flex items-center justify-between gap-4 py-3"><dt class="hc-body-sm">{{ str($key)->replace('_', ' ')->headline() }}</dt><dd><span class="hc-v3-badge {{ $value === true ? 'hc-v3-badge--danger' : 'hc-v3-badge--success' }}">{{ is_bool($value) ? ($value ? 'Kontrol gerekli' : 'Sözleşme bulundu') : $value }}</span></dd></div>
                    @endforeach
                </dl>
            </article>
            <article class="hc-v3-card p-5 sm:p-6">
                <p class="hc-kicker">MANUEL KAPANIŞ</p><h2 class="hc-heading-3 mt-2">Tarayıcıda doğrulanacaklar</h2>
                <ul class="mt-5 space-y-3" style="color:var(--hc-text-secondary)">
                    @foreach ($report['manual_qa_required'] as $item)<li class="flex gap-3"><span aria-hidden="true" style="color:var(--hc-text-accent)">•</span><span>{{ str($item)->replace('_', ' ')->headline() }}</span></li>@endforeach
                </ul>
                <p class="hc-v3-alert mt-6"><span aria-hidden="true">✓</span><span>{{ $report['measurement_note'] }}</span></p>
            </article>
        </section>
    </section>
</x-layouts.site>
