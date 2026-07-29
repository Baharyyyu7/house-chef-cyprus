<x-layouts.site title="AI Görsel Stüdyosu">
    <section class="hc-container hc-admin-crud-page py-8 sm:py-10">
        <x-admin.page-header eyebrow="Kontrollü pilot" title="AI Görsel Stüdyosu" description="10 tariflik pilot planı. Maliyet onayı ve canlı capability testi olmadan üretim yapılmaz." :back="['url'=>route('admin.dashboard'),'label'=>'Yönetim']" />
        <x-ui.alert class="mt-6" type="warning">Gerçek üretim kapalıdır. Günlük bütçe limiti: {{ number_format($cost['budget_cents'] / 100, 2, ',', '.') }}. Planlanan {{ $cost['variation_count'] }} varyasyonun tahmini maliyeti: {{ $cost['cost_estimate_configured'] ? number_format($cost['estimated_cost_cents'] / 100, 2, ',', '.') : 'belirlenmedi' }}.</x-ui.alert>
        <section class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-3" aria-label="Provider durumları">
            @foreach($providers as $provider)<article class="hc-admin-detail-card"><span>{{ strtoupper($provider['name']) }}</span><strong>{{ $provider['image_capability'] }}</strong><small>{{ $provider['model'] ?? 'Model seçilmedi' }} · {{ $provider['safe_message'] }}</small></article>@endforeach
        </section>
        <section class="hc-admin-detail-card mt-7" aria-labelledby="fallback-comparison-title">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <span>Kontrollü karşılaştırma</span>
                    <h2 id="fallback-comparison-title" class="mt-1 text-xl font-semibold">Domates Çorbası · {{ strtoupper($comparison['primary']) }} / {{ strtoupper($comparison['fallback']) }}</h2>
                    <p class="mt-1 text-sm text-muted">En fazla {{ $comparison['request_limit'] }} çağrı · bütçe üst sınırı {{ number_format($comparison['budget_cents'] / 100, 2, ',', '.') }} USD · otomatik yayın kapalıdır.</p>
                </div>
                <span class="hc-badge">Private review</span>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                @foreach(['bfl', 'stability'] as $providerName)
                    @php($record = $comparison['records']->firstWhere('provider', $providerName))
                    @php($dryRun = $comparison['dry_run']['providers'][$providerName] ?? [])
                    <article class="rounded-2xl border border-[color:var(--hc-line)] p-4">
                        <div class="flex items-center justify-between gap-3"><strong>{{ strtoupper($providerName) }}</strong><span class="text-sm text-muted">{{ $record?->status ?? ($dryRun['status'] ?? 'not_configured') }}</span></div>
                        @if($record?->media)
                            <img class="mt-4 aspect-[4/3] w-full rounded-xl object-cover" src="{{ route('admin.ai-image-studio.preview', $record) }}" alt="{{ $record->recipe->title }} için inceleme taslağı">
                        @else
                            <div class="mt-4 flex aspect-[4/3] items-center justify-center rounded-xl bg-[color:var(--hc-surface-muted)] p-4 text-center text-sm text-muted">{{ $dryRun['safe_message'] ?? 'Sağlayıcı yapılandırılmadığı için üretim çağrısı yapılmadı.' }}</div>
                        @endif
                        @if($record)<a class="hc-v3-btn hc-v3-btn--ghost mt-4" href="{{ route('admin.ai-image-studio.show', $record) }}">İnceleme kaydını aç</a>@endif
                    </article>
                @endforeach
            </div>
        </section>
        <div class="hc-admin-table-shell mt-7"><div class="hc-table-wrap"><table class="hc-table min-w-[1000px]"><thead><tr><th>Tarif</th><th>Mutfak</th><th>Provider/model</th><th>Durum</th><th>Tahmin</th><th>Prompt sürümü</th><th></th></tr></thead><tbody>@forelse($generations as $generation)<tr><td><strong>{{ $generation->recipe->title }}</strong><br><code>{{ $generation->recipe->public_id }}</code></td><td>{{ $generation->recipe->cuisine?->name ?? '—' }}<br><span class="text-muted">{{ $generation->recipe->category?->name ?? '—' }}</span></td><td>{{ $generation->provider }}<br><span class="text-muted">{{ $generation->model ?? '—' }}</span></td><td>{{ $generation->status }}</td><td>{{ number_format($generation->estimated_cost_cents / 100, 2, ',', '.') }}</td><td>{{ $generation->prompt_version }}</td><td><a class="hc-v3-btn hc-v3-btn--ghost" href="{{ route('admin.ai-image-studio.show', $generation) }}">İncele</a></td></tr>@empty<tr><td colspan="7"><div class="hc-admin-empty">Pilot için taslak oluşturulmadı.</div></td></tr>@endforelse</tbody></table></div><div class="mt-5">{{ $generations->links() }}</div></div>
    </section>
</x-layouts.site>
