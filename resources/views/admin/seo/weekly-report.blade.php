<x-layouts.site :title="'Haftalik SEO Raporu'">
    <section class="hc-container py-8 sm:py-10">
        <x-admin.page-header :eyebrow="'SEO'" :title="'Haftalik SEO Raporu'" :back="['url' => route('admin.seo.index'), 'label' => 'SEO']" />

        <p class="mt-2 text-sm text-muted">{{ $period['start']->format('d.m.Y') }} - {{ $period['end']->format('d.m.Y') }}</p>

        <dl class="mt-6 grid grid-cols-2 gap-5 border-y py-5 xl:grid-cols-4" style="border-color:var(--hc-border-subtle)">
            <div class="min-w-0"><dt class="text-sm text-muted">Yeni tarif</dt><dd class="mt-1 text-2xl font-bold">{{ $content['new_recipes'] }}</dd></div>
            <div class="min-w-0"><dt class="text-sm text-muted">Yeni blog</dt><dd class="mt-1 text-2xl font-bold">{{ $content['new_posts'] }}</dd></div>
            <div class="min-w-0"><dt class="text-sm text-muted">Toplam tarif</dt><dd class="mt-1 text-2xl font-bold">{{ $content['total_recipes'] }}</dd></div>
            <div class="min-w-0"><dt class="text-sm text-muted">Toplam blog</dt><dd class="mt-1 text-2xl font-bold">{{ $content['total_posts'] }}</dd></div>
        </dl>

        <section class="border-b py-6" style="border-color:var(--hc-border-subtle)">
            <h2 class="text-lg font-bold">AI Gorunurluk</h2>
            <dl class="mt-4 grid grid-cols-2 gap-5 xl:grid-cols-4">
                <div class="min-w-0"><dt class="text-sm text-muted">Toplam kontrol</dt><dd class="mt-1 text-xl font-bold">{{ $ai_visibility['total_checks'] }}</dd></div>
                <div class="min-w-0"><dt class="text-sm text-muted">Marka gecme</dt><dd class="mt-1 text-xl font-bold">{{ $ai_visibility['mentions'] }}</dd></div>
                <div class="min-w-0"><dt class="text-sm text-muted">Linksiz mention</dt><dd class="mt-1 text-xl font-bold">{{ $ai_visibility['unlinked'] }}</dd></div>
                <div class="min-w-0">
                    <dt class="text-sm text-muted">Motor dagilimi</dt>
                    <dd class="mt-1 text-sm">
                        @forelse($ai_visibility['by_engine'] as $engine => $data)
                            <span class="mr-3">{{ $engine }}: {{ $data['mentioned'] }}/{{ $data['total'] }}</span>
                        @empty
                            -
                        @endforelse
                    </dd>
                </div>
            </dl>
        </section>

        <section class="border-b py-6" style="border-color:var(--hc-border-subtle)">
            <h2 class="text-lg font-bold">Teknik Sorunlar</h2>
            <dl class="mt-4 grid grid-cols-3 gap-5">
                <div class="min-w-0"><dt class="text-sm text-muted">Acik sorun</dt><dd class="mt-1 text-xl font-bold">{{ $issues['open'] }}</dd></div>
                <div class="min-w-0"><dt class="text-sm text-muted">Kritik</dt><dd class="mt-1 text-xl font-bold {{ $issues['critical'] > 0 ? 'text-red-600' : '' }}">{{ $issues['critical'] }}</dd></div>
                <div class="min-w-0"><dt class="text-sm text-muted">Cozulen</dt><dd class="mt-1 text-xl font-bold">{{ $issues['resolved'] }}</dd></div>
            </dl>
        </section>

        <section class="border-b py-6" style="border-color:var(--hc-border-subtle)">
            <h2 class="text-lg font-bold">Firsatlar</h2>
            <dl class="mt-4 grid grid-cols-2 gap-5">
                <div class="min-w-0"><dt class="text-sm text-muted">Acik firsat</dt><dd class="mt-1 text-xl font-bold">{{ $opportunities['open'] }}</dd></div>
                <div class="min-w-0"><dt class="text-sm text-muted">Yuksek oncelik</dt><dd class="mt-1 text-xl font-bold">{{ $opportunities['highPriority'] }}</dd></div>
            </dl>
        </section>

        <section class="py-6">
            <h2 class="text-lg font-bold">Onerilen Aksiyonlar</h2>
            <ul class="mt-3 space-y-3">
                @forelse($next_actions as $action)
                    <li class="flex items-start gap-3 border rounded-lg p-4" style="border-color:var(--hc-border-subtle)">
                        <span class="mt-1 inline-flex h-2 w-2 shrink-0 rounded-full
                            @if($action['priority'] >= 80) bg-red-500
                            @elseif($action['priority'] >= 60) bg-amber-500
                            @else bg-green-500
                            @endif
                        "></span>
                        <div>
                            <p class="font-semibold">{{ $action['action'] }}</p>
                            <p class="mt-0.5 text-sm text-muted">{{ $action['reason'] }}</p>
                        </div>
                    </li>
                @empty
                    <li class="text-muted">Aksiyon onerisi yok.</li>
                @endforelse
            </ul>
        </section>
    </section>
</x-layouts.site>
