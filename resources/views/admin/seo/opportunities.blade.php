<x-layouts.site title="İçerik fırsatları">
    <section class="hc-container py-8 sm:py-10">
        <x-admin.page-header eyebrow="SEO ve İçerik" title="İçerik ve teknik inceleme işleri" :back="['url' => route('admin.seo.index'), 'label' => 'SEO merkezi']">
            <x-slot:actions><form method="post" action="{{ route('admin.seo.growth.refresh') }}">@csrf<button class="hc-v3-btn hc-v3-btn--primary"><x-ui.icon name="refresh" :size="16" /> İşleri yenile</button></form></x-slot:actions>
        </x-admin.page-header>
        @if($errors->any())<div role="alert" class="mt-4 text-sm">{{ $errors->first() }}</div>@endif
        <form class="mt-5 flex flex-wrap items-center gap-3">
            <label for="opportunity-status" class="sr-only">İnceleme durumu</label><select id="opportunity-status" name="status" class="hc-input">@foreach(['open' => 'Açık', 'reviewing' => 'İnceleniyor', 'completed' => 'Tamamlandı', 'dismissed' => 'Reddedildi', 'all' => 'Tüm durumlar'] as $value => $label)<option value="{{ $value }}" @selected((request('status') ?: 'open') === $value)>{{ $label }}</option>@endforeach</select>
            <button class="hc-v3-btn hc-v3-btn--ghost" title="Duruma göre filtrele" aria-label="Duruma göre filtrele"><x-ui.icon name="sliders" :size="18" /></button><span class="text-sm text-muted">{{ $opportunities->total() }} iş</span>
        </form>
        <div class="hc-table-wrap mt-5 max-w-full overflow-x-auto">
            <table class="hc-table min-w-[900px]">
                <thead><tr><th>İnceleme işi</th><th>Kanıt ve kaynak</th><th>Öncelik / risk</th><th>Karar</th></tr></thead>
                <tbody>
                    @forelse($opportunities as $opportunity)
                        <tr>
                            <td class="max-w-sm whitespace-normal [overflow-wrap:anywhere]"><strong>{{ $opportunity->title }}</strong><p class="mt-1 text-sm text-muted">{{ $opportunity->topic_cluster }}</p>@if($opportunity->evidence['issue_url'] ?? null)<p class="mt-1 text-xs text-muted">{{ $opportunity->evidence['issue_url'] }}</p>@endif</td>
                            <td class="max-w-xs whitespace-normal [overflow-wrap:anywhere]">
                                @if($opportunity->evidence['issue_fingerprint'] ?? null)<a class="text-sm underline" href="{{ route('admin.seo.issues', ['fingerprint' => $opportunity->evidence['issue_fingerprint']]) }}">Kaynak SEO sorunu</a>@endif
                                @if($opportunity->evidence['query'] ?? null)<p class="text-sm">Arama: {{ $opportunity->evidence['query'] }}</p>@endif
                                @if(isset($opportunity->evidence['anonymous_search_count']))<p class="text-sm">{{ $opportunity->evidence['anonymous_search_count'] }} sonuçsuz arama</p>@endif
                                @if(isset($opportunity->evidence['recipe_count']))<p class="text-sm">Katalog: {{ $opportunity->evidence['recipe_count'] }} tarif</p>@endif
                                @if($opportunity->evidence['source'] ?? null)<p class="mt-1 text-xs text-muted">{{ $opportunity->evidence['source'] }}</p>@endif
                            </td>
                            <td>{{ $opportunity->priority }} / {{ ['high' => 'Yüksek', 'medium' => 'Orta', 'low' => 'Düşük'][$opportunity->risk_level] ?? $opportunity->risk_level }}</td>
                            <td><form method="post" action="{{ route('admin.seo.opportunities.review', ['opportunity' => $opportunity->public_id]) }}" class="flex flex-wrap items-center gap-2">@csrf<label for="review-{{ $opportunity->public_id }}" class="sr-only">İş durumu</label><select id="review-{{ $opportunity->public_id }}" name="status" class="hc-input">@foreach(['open' => 'Açık', 'reviewing' => 'İnceleniyor', 'completed' => 'Tamamlandı', 'dismissed' => 'Reddedildi'] as $value => $label)<option value="{{ $value }}" @selected($opportunity->status === $value)>{{ $label }}</option>@endforeach</select><button class="hc-v3-btn hc-v3-btn--ghost" title="Kararı kaydet" aria-label="Kararı kaydet"><x-ui.icon name="check" :size="18" /></button></form>@if($opportunity->reviewed_at)<p class="mt-1 text-xs text-muted">{{ $opportunity->reviewed_at->format('d.m.Y H:i') }}</p>@endif</td>
                        </tr>
                    @empty<tr><td colspan="4" class="py-6 text-muted">Bu durumda inceleme işi yok.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $opportunities->links() }}</div>
    </section>
</x-layouts.site>
