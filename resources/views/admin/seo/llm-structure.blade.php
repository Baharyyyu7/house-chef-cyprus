<x-layouts.site :title="'LLM Yapi Optimizasyonu'">
    <section class="hc-container py-8 sm:py-10">
        <x-admin.page-header :eyebrow="'SEO'" :title="'LLM Yapi Optimizasyonu'" :back="['url' => route('admin.seo.index'), 'label' => 'SEO']" />

        <dl class="mt-6 grid grid-cols-2 gap-5 border-y py-5 xl:grid-cols-3" style="border-color:var(--hc-border-subtle)">
            <div class="min-w-0"><dt class="text-sm text-muted">AI okunabilirlik skoru</dt><dd class="mt-1 text-2xl font-bold">{{ $score }}/100</dd></div>
            <div class="min-w-0"><dt class="text-sm text-muted">Kontrol edilen</dt><dd class="mt-1 text-2xl font-bold">{{ $checked }}</dd></div>
            <div class="min-w-0"><dt class="text-sm text-muted">Sorun sayisi</dt><dd class="mt-1 text-2xl font-bold">{{ count($issues) }}</dd></div>
        </dl>

        <section class="py-6">
            <h2 class="text-lg font-bold">Tespit Edilen Sorunlar</h2>
            <p class="mt-1 text-sm text-muted">AI motorlari iceriginizi okurken karsilastigi problemler.</p>

            @forelse($issues as $issue)
                <div class="mt-3 flex items-start gap-3 border-b pb-3" style="border-color:var(--hc-border-subtle)">
                    <span class="mt-1 inline-flex h-2 w-2 shrink-0 rounded-full
                        @if($issue['type'] === 'thin_content') bg-red-500
                        @elseif($issue['type'] === 'no_headings') bg-amber-500
                        @elseif($issue['type'] === 'missing_sections') bg-amber-500
                        @elseif($issue['type'] === 'no_sources') bg-blue-500
                        @else bg-gray-400
                        @endif
                    "></span>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold">{{ $issue['entity'] }}</p>
                        <p class="mt-0.5 text-sm text-muted">{{ $issue['message'] }}</p>
                    </div>
                    <span class="shrink-0 text-xs text-muted">{{ $issue['type'] }}</span>
                </div>
            @empty
                <p class="mt-4 text-muted">Sorun bulunamadi. Tum icerikler AI icin optimize gorunuyor.</p>
            @endforelse
        </section>
    </section>
</x-layouts.site>
