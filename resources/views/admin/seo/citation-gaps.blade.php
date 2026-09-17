<x-layouts.site :title="'Citation Bosluk Analizi'">
    <section class="hc-container py-8 sm:py-10">
        <x-admin.page-header :eyebrow="'SEO'" :title="'Citation Bosluk Analizi'" :back="['url' => route('admin.seo.index'), 'label' => 'SEO']" />

        <dl class="mt-6 grid grid-cols-3 gap-5 border-y py-5" style="border-color:var(--hc-border-subtle)">
            <div class="min-w-0"><dt class="text-sm text-muted">Toplam sorgu</dt><dd class="mt-1 text-2xl font-bold">{{ $summary['total_queries'] }}</dd></div>
            <div class="min-w-0"><dt class="text-sm text-muted">Kaynakli yanit</dt><dd class="mt-1 text-2xl font-bold">{{ $summary['cited_count'] }}</dd></div>
            <div class="min-w-0"><dt class="text-sm text-muted">Bosluk (firsat)</dt><dd class="mt-1 text-2xl font-bold">{{ $summary['gap_count'] }}</dd></div>
        </dl>

        <section class="py-6">
            <h2 class="text-lg font-bold">Citation Bosluklari</h2>
            <p class="mt-1 text-sm text-muted">AI yaniTLarinda rakipler kaynak olarak gosterilmis ama siz gosterilmemissiniz.</p>

            @forelse($gaps as $gap)
                <div class="mt-4 border rounded-lg p-4" style="border-color:var(--hc-border-subtle)">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold">{{ $gap['query'] }}</p>
                            <p class="mt-1 text-sm text-muted">Kaynaklar: {{ implode(', ', $gap['cited_sources']) }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">Firsat</span>
                    </div>
                    <p class="mt-3 text-sm">{{ $gap['opportunity'] }}</p>
                </div>
            @empty
                <p class="mt-4 text-muted">Citation boslugu bulunamadi. AI mention kayitlarini kontrol edin.</p>
            @endforelse
        </section>
    </section>
</x-layouts.site>
