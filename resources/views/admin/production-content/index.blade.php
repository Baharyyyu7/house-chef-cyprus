<x-admin-layout title="Production İçerik Merkezi">
    @php
        $statusLabel = static fn (string $status): string => match ($status) {
            'passed_automated_checks' => 'Otomatik kontroller geçti',
            'needs_real_media' => 'Gerçek medya bekliyor',
            'branded_fallback' => 'Markalı fallback',
            'approved_real_media' => 'Onaylı gerçek medya',
            'ready_for_human_review' => 'İnsan onayı bekliyor',
            'human_approved' => 'İnsan onaylı',
            'in_review' => 'İncelemede',
            'needs_revision' => 'Düzeltme gerekli',
            'source_required' => 'Kaynak gerekli',
            'published' => 'Yayınlandı',
            'blocked' => 'Bloklu',
            'not_started' => 'Başlatılmadı',
            'incomplete' => 'Eksik',
            default => str($status)->replace('_', ' ')->title()->toString(),
        };
    @endphp
    <div class="hc-admin-crud-page space-y-6">
        <section class="hc-admin-hero">
            <p class="hc-eyebrow">İçerik operasyonları</p>
            <h1 class="hc-heading-1">Production İçerik Merkezi</h1>
            <p class="mt-2 text-muted">Bu görünüm gerçek kalite profillerini gösterir. Readiness skoru doğrulama veya yayın garantisi değildir; insan onayı olmadan yayın yapılmaz.</p>
            @can('rescan-production-content')<form class="mt-4" method="post" action="{{ route('admin.production-content.rescan') }}">@csrf<button class="hc-v3-btn hc-v3-btn--secondary">Kalite taramasını yenile</button></form>@endcan
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                'Toplam tarif' => $metrics['recipes'], 'Toplam blog' => $metrics['blogs'], 'Yayınlanan tarif' => $metrics['published_recipes'], 'Yayınlanan blog' => $metrics['published_blogs'],
                'Bloklu içerik' => $metrics['profiles']['blocked'], 'Kaynak bekleyen' => $metrics['profiles']['source_required'], 'Medya bekleyen' => $metrics['profiles']['media_required'], 'Final onay bekleyen' => $metrics['profiles']['final_approval_waiting'],
                'Ortalama readiness' => $metrics['profiles']['readiness_average'].'/100', 'Kritik açık konu' => $metrics['issues']['critical'], 'Yüksek riskli açık konu' => $metrics['issues']['high'], 'İnsan onaylı' => $metrics['profiles']['human_approved'],
            ] as $label => $value)
                <article class="hc-admin-form-card"><p class="hc-eyebrow">{{ $label }}</p><p class="mt-2 text-3xl font-black">{{ $value }}</p></article>
            @endforeach
        </section>

        <section class="hc-admin-form-card">
            <div class="flex flex-wrap items-center justify-between gap-4"><div><h2 class="hc-heading-3">Release durumu</h2><p class="mt-1 text-sm text-muted">{{ $metrics['release'] ? str($metrics['release']->state)->replace('_', ' ')->title() : 'Henüz kayıt yok' }} · İzleme sağlayıcısı bağlanana kadar yalnız bu ortamda doğrulanabilen kontrol zamanları gösterilir.</p></div><span class="hc-v3-badge">{{ $metrics['release']?->last_content_health_scan_at?->diffForHumans() ?? 'Tarama bekliyor' }}</span></div>
        </section>

        <form class="hc-admin-form-card grid gap-3 md:grid-cols-4" method="get">
            <label><span class="hc-form-label">İçerik türü</span><select name="type" class="hc-form-control"><option value="">Tümü</option><option value="recipe" @selected(request('type') === 'recipe')>Tarif</option><option value="blog" @selected(request('type') === 'blog')>Blog</option></select></label>
            <label><span class="hc-form-label">Yayın durumu</span><select name="status" class="hc-form-control"><option value="">Tümü</option><option value="blocked">Bloklu</option><option value="ready_for_human_review">İnsan onayı bekliyor</option><option value="published">Yayınlandı</option></select></label>
            <label><span class="hc-form-label">Medya</span><select name="media" class="hc-form-control"><option value="">Tümü</option><option value="approved_real_media">Onaylı gerçek medya</option><option value="branded_fallback">Markalı fallback</option><option value="needs_real_media">Gerçek medya bekliyor</option></select></label>
            <div class="flex items-end gap-2"><input class="hc-form-control" name="q" value="{{ request('q') }}" placeholder="Public ID ara"><button class="hc-v3-btn hc-v3-btn--primary">Filtrele</button></div>
        </form>

        <section class="hc-admin-table-card overflow-x-auto">
            <table class="hc-admin-table min-w-[1120px]">
                <thead><tr><th>İçerik</th><th>Readiness</th><th>Fact-check</th><th>Kaynak</th><th>Medya</th><th>Final onay</th><th>Yayın</th><th>Son tarama</th><th>İşlem</th></tr></thead>
                <tbody>
                    @forelse ($profiles as $profile)
                        <tr>
                            <td><span class="font-semibold">{{ $profile->content_type === 'recipe' ? 'Tarif' : 'Blog' }}</span><p class="mt-1 font-mono text-xs text-muted">{{ $profile->entity_public_id }}</p></td>
                            <td>{{ $profile->readiness_score }}/{{ $profile->readiness_cap }}</td>
                            <td>{{ $statusLabel($profile->fact_check_status) }}</td>
                            <td>{{ $statusLabel($profile->source_coverage_status) }}</td>
                            <td>{{ $statusLabel($profile->media_status) }}</td>
                            <td>{{ $statusLabel($profile->final_approval_status) }}</td>
                            <td>{{ $statusLabel($profile->publication_status) }}</td>
                            <td>{{ $profile->last_scanned_at?->diffForHumans() }}</td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    @can ('advance-production-graduation')
                                        <form method="post" action="{{ route('admin.production-content.advance', $profile) }}">@csrf<input type="hidden" name="stage" value="technical_review"><button class="hc-v3-btn hc-v3-btn--ghost">İncelemeye al</button></form>
                                    @endcan
                                    @if ($profile->publication_status !== 'blocked' && $profile->readiness_score === 100 && $profile->readiness_cap === 100)
                                        @can ('final-production-content-approval')
                                            <form method="post" action="{{ route('admin.production-content.approve', $profile) }}">@csrf<button class="hc-v3-btn hc-v3-btn--ghost">İnsan onayı</button></form>
                                        @endcan
                                    @endif
                                    @if ($profile->publication_status === 'ready_for_human_review' && $profile->final_approval_status === 'human_approved')
                                        @can ('publish-production-content')
                                            <form method="post" action="{{ route('admin.production-content.publish', $profile) }}">@csrf<button class="hc-v3-btn hc-v3-btn--primary">Yayınla</button></form>
                                        @endcan
                                    @endif
                                    @if ($profile->publication_status === 'blocked')
                                        <span class="hc-caption text-muted">Önce blokları giderin</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9"><x-ui.empty-state title="Gösterilecek kalite profili yok" description="Filtreleri değiştirin veya güvenli kalite taramasını çalıştırın." /></td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">{{ $profiles->links() }}</div>
        </section>
    </div>
</x-admin-layout>
