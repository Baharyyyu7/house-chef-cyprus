<x-layouts.site title="Medya Operasyonları">
    <section class="hc-container hc-admin-crud-page py-8 sm:py-10">
        <x-admin.page-header eyebrow="Media Excellence" title="Medya Operasyonları" description="Gerçek medya, lisans ve insan onayı zincirinin operasyon merkezi." :back="['url' => route('admin.dashboard'), 'label' => 'Yönetim']">
            <x-slot:actions><a class="hc-v3-btn hc-v3-btn--ghost" href="{{ route('admin.ai-image-studio.index') }}">Aday incelemeye git</a></x-slot:actions>
        </x-admin.page-header>
        <x-ui.alert class="mt-6" type="warning">Branded fallback gerçek görsel değildir. İnsan onayı, lisans ve attribution tamamlanmadan hiçbir aday public, schema veya image sitemap’e girmez.</x-ui.alert>
        <nav class="mt-6 flex gap-2 overflow-x-auto pb-2" aria-label="Medya operasyon bölümleri">
            @foreach(['' => 'Genel Bakış', 'candidates_ready' => 'Adaylar', 'fidelity_review' => 'Fidelity', 'license_review' => 'Lisans', 'attribution_review' => 'Attribution', 'optimization_pending' => 'Optimizasyon', 'ready_for_human_approval' => 'Final Onay', 'rejected' => 'Reddedilen', 'blocked' => 'Duplicate / Blokaj', 'approved_real_media' => 'Onaylı Gerçek Medya'] as $value => $label)
                <a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm whitespace-nowrap @if(request('status', '') === $value) ring-2 ring-[color:var(--hc-accent)] @endif" href="{{ route('admin.media-operations.index', array_filter(['type' => request('type'), 'status' => $value])) }}">{{ $label }}</a>
            @endforeach
        </nav>
        <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            @foreach(['score' => 'Medya sağlık skoru', 'approved_real_media' => 'Onaylı gerçek medya', 'branded_fallback' => 'Branded fallback', 'no_media' => 'Görselsiz', 'private_leakage' => 'Private sızıntı'] as $key => $label)
                <article class="hc-admin-stat"><span>{{ $label }}</span><strong>{{ $key === 'score' ? $health[$key].'/100' : $health[$key] }}</strong></article>
            @endforeach
        </div>
        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach(['candidates_ready' => 'Adaylar hazır', 'fidelity_waiting' => 'Fidelity inceleme', 'license_waiting' => 'Lisans inceleme', 'attribution_waiting' => 'Attribution inceleme', 'optimization_waiting' => 'Optimizasyon', 'final_approval_waiting' => 'Final onay', 'rejected' => 'Reddedilen', 'duplicate_groups' => 'Duplicate grup'] as $key => $label)
                <article class="hc-admin-detail-card"><span>{{ $label }}</span><strong>{{ $health[$key] }}</strong></article>
            @endforeach
        </div>
        <section class="hc-admin-form-card mt-6">
            <h2 class="hc-heading-3">Sağlayıcı sağlığı</h2>
            <p class="mt-1 text-sm text-muted">Yalnız güvenli yapılandırma durumu ve aday sayısı gösterilir; anahtar veya token gösterilmez.</p>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($health['provider_health'] as $provider)
                    <article class="hc-admin-detail-card"><span>{{ strtoupper($provider['name']) }}</span><strong>{{ $provider['status'] }}</strong><small>{{ $provider['candidates'] }} kayıtlı aday</small></article>
                @endforeach
            </div>
        </section>
        <form class="mt-8 grid gap-3 sm:grid-cols-3" method="get">
            <select class="hc-v3-input" name="type"><option value="">Tüm içerikler</option><option value="recipe" @selected(request('type') === 'recipe')>Tarif</option><option value="blog" @selected(request('type') === 'blog')>Blog</option></select>
            <select class="hc-v3-input" name="status"><option value="">Tüm durumlar</option>@foreach($statuses as $item)<option value="{{ $item }}" @selected(request('status') === $item)>{{ $item }}</option>@endforeach</select>
            <button class="hc-v3-btn hc-v3-btn--primary" type="submit">Envanteri filtrele</button>
        </form>
        <section class="hc-admin-form-card mt-6 overflow-x-auto">
            <h2 class="hc-heading-3">Tarif ve blog medya envanteri</h2>
            <table class="mt-4 w-full min-w-[1050px] text-left text-sm"><thead><tr><th>Tür</th><th>Public ID</th><th>Durum</th><th>Kaynak</th><th>Aday</th><th>Fidelity</th><th>Lisans</th><th>Attribution</th><th>Responsive</th><th>Blokaj</th></tr></thead><tbody>
            @forelse($rows as $row)<tr class="border-t" style="border-color:var(--hc-border-subtle)"><td>{{ $row['entity_type'] }}</td><td class="font-mono text-xs">{{ $row['entity_public_id'] }}</td><td>{{ $row['status'] }}</td><td>{{ $row['current_source'] ?? '—' }}</td><td>{{ $row['candidate_count'] }}</td><td>{{ $row['fidelity_status'] ?? '—' }}</td><td>{{ $row['license_complete'] ? 'Tam' : 'Bekliyor' }}</td><td>{{ $row['attribution_complete'] ? 'Tam' : 'Bekliyor' }}</td><td>{{ $row['responsive_ready'] ? 'Hazır' : '—' }}</td><td>{{ $row['blocking_reason'] ?? '—' }}</td></tr>@empty<tr><td colspan="10" class="p-8 text-center text-muted">Bu filtre için medya envanteri kaydı yok.</td></tr>@endforelse
            </tbody></table>
        </section>
        <section class="hc-admin-form-card mt-6"><h2 class="hc-heading-3">Private adaylar</h2><div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">@forelse($candidates as $candidate)<article class="rounded-xl border p-4" style="border-color:var(--hc-border-subtle)"><p class="font-bold">{{ $candidate->recipe?->title ?? 'Silinmiş tarif' }}</p><p class="mt-1 text-sm text-muted">{{ strtoupper($candidate->provider) }} · {{ $candidate->status }} · fidelity: {{ $candidate->fidelity_status ?? 'taranmadı' }}</p><p class="mt-2 text-xs text-muted">Public ID: {{ $candidate->public_id }}</p>@if($candidate->status === 'pending_review')<div class="mt-3 flex gap-2"><form method="post" action="{{ route('admin.media-operations.candidates.select', $candidate) }}">@csrf<button class="hc-v3-btn hc-v3-btn--primary hc-v3-btn--sm" type="submit">Seç</button></form><form method="post" action="{{ route('admin.media-operations.candidates.reject', $candidate) }}">@csrf<button class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" type="submit">Reddet</button></form></div>@endif</article>@empty<p class="text-muted">Private aday yok.</p>@endforelse</div></section>
    </section>
</x-layouts.site>
