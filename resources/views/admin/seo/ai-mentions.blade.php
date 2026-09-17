<x-layouts.site :title="'AI Mention Takibi'">
    <section class="hc-container py-8 sm:py-10">
        <x-admin.page-header :eyebrow="'SEO'" :title="'AI Mention Takibi'" :back="['url' => route('admin.seo.index'), 'label' => 'SEO']" />

        <dl class="mt-6 grid grid-cols-2 gap-5 border-y py-5 xl:grid-cols-4" style="border-color:var(--hc-border-subtle)">
            <div class="min-w-0"><dt class="text-sm text-muted">Toplam kontrol</dt><dd class="mt-1 text-2xl font-bold">{{ $snapshot['total'] }}</dd></div>
            <div class="min-w-0"><dt class="text-sm text-muted">Marka geçme</dt><dd class="mt-1 text-2xl font-bold">{{ $snapshot['mentioned'] }}</dd></div>
            <div class="min-w-0"><dt class="text-sm text-muted">Linksiz mention</dt><dd class="mt-1 text-2xl font-bold">{{ $snapshot['unlinked'] }}</dd></div>
            <div class="min-w-0"><dt class="text-sm text-muted">Motor dağılımı</dt><dd class="mt-1 text-xl font-bold">{{ collect($snapshot['byEngine'])->implode(', ') ?: '-' }}</dd></div>
        </dl>

        <section class="border-b py-6" style="border-color:var(--hc-border-subtle)">
            <h2 class="text-lg font-bold">Önerilen sorgular</h2>
            <p class="mt-1 text-sm text-muted">Bu sorguları AI motorlarında test edin ve sonucu kaydedin.</p>
            <ul class="mt-3 divide-y">
                @forelse($suggestedPrompts as $item)
                    <li class="flex items-center justify-between gap-3 py-3">
                        <div>
                            <span class="font-semibold">{{ $item['prompt'] }}</span>
                            <span class="ml-2 text-sm text-muted">{{ $item['engine'] }}</span>
                        </div>
                        <span class="text-xs text-muted">{{ $item['checked_at'] }}</span>
                    </li>
                @empty
                    <li class="py-3 text-muted">Önerilen sorgu yok.</li>
                @endforelse
            </ul>
        </section>

        <section class="py-6">
            <h2 class="text-lg font-bold">AI Mention Geçmişi</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b" style="border-color:var(--hc-border-subtle)">
                            <th class="pb-3 text-left font-semibold">Tarih</th>
                            <th class="pb-3 text-left font-semibold">Motor</th>
                            <th class="pb-3 text-left font-semibold">Sorgu</th>
                            <th class="pb-3 text-left font-semibold">Marka</th>
                            <th class="pb-3 text-left font-semibold">Link</th>
                            <th class="pb-3 text-left font-semibold">Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mentions as $mention)
                            <tr class="border-b" style="border-color:var(--hc-border-subtle)">
                                <td class="py-3">{{ $mention->checked_at?->format('d.m.Y H:i') ?? '-' }}</td>
                                <td class="py-3">{{ $mention->engine }}</td>
                                <td class="py-3 max-w-xs truncate">{{ $mention->prompt }}</td>
                                <td class="py-3">@if($mention->mentioned) <span class="text-green-600">Evet</span> @else <span class="text-muted">Hayir</span> @endif</td>
                                <td class="py-3">@if($mention->linked) <span class="text-green-600">Evet</span> @else <span class="text-muted">Hayir</span> @endif</td>
                                <td class="py-3">{{ $mention->status }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-6 text-center text-muted">Henuz AI mention kontrolu yapilmadi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $mentions->links() }}</div>
        </section>
    </section>
</x-layouts.site>
