<x-admin-layout title="Editoryal Operasyonlar">
    <div class="space-y-6">
        <section class="hc-admin-hero">
            <p class="hc-eyebrow">İnsan kontrollü içerik operasyonu</p>
            <h1 class="hc-heading-1">Editoryal Operasyonlar</h1>
            <p class="mt-2 text-muted">Bu ekran yalnız çalışma kuyrukları ve kanıt hazırlığı üretir. İnsan onayı olmadan hiçbir taslak doğrulanmaz, planlanmaz veya yayınlanmaz.</p>
            @can('assign-editorial-work')<form class="mt-4" method="post" action="{{ route('admin.editorial-operations.refresh') }}">@csrf<button class="hc-v3-btn hc-v3-btn--secondary">Operasyon kuyruğunu yenile</button></form>@endcan
        </section>
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach (['Açık fact-check' => $metrics['open_issues'], 'Critical / high' => $metrics['critical_high'], 'Kaynak bekleyen' => $metrics['source_required'], 'Evidence-ready' => $metrics['evidence_ready'], 'Review batch' => $metrics['batches']['total'], 'Tarif paketi' => $metrics['recipe_packs']['total'], 'Blog paketi' => $metrics['blog_packs']['total'], 'Medya bekleyen' => $metrics['media_waiting']] as $label => $value)
                <article class="hc-admin-form-card"><p class="hc-eyebrow">{{ $label }}</p><p class="mt-2 text-3xl font-black">{{ $value }}</p></article>
            @endforeach
        </section>
        <section class="grid gap-4 lg:grid-cols-3">
            <a class="hc-admin-form-card transition hover:-translate-y-0.5" href="{{ route('admin.editorial-operations.batches.index') }}"><h2 class="hc-heading-4">Fact-check Batch’leri</h2><p class="mt-2 text-sm text-muted">Maksimum 50 konuluk, risk öncelikli güvenli inceleme paketleri.</p></a>
            <a class="hc-admin-form-card transition hover:-translate-y-0.5" href="{{ route('admin.editorial-operations.sources.index') }}"><h2 class="hc-heading-4">Kaynak Araştırma Kuyruğu</h2><p class="mt-2 text-sm text-muted">Yalnız kısa özet ve referans saklanır; kaynak metni kopyalanmaz.</p></a>
            <a class="hc-admin-form-card transition hover:-translate-y-0.5" href="{{ route('admin.editorial-operations.packs.index') }}"><h2 class="hc-heading-4">Tarif ve Blog Paketleri</h2><p class="mt-2 text-sm text-muted">Bölüm kararları final insan onayının veya yayının yerine geçmez.</p></a>
        </section>
        <section class="hc-admin-table-card overflow-x-auto"><div class="flex items-center justify-between gap-4"><div><h2 class="hc-heading-3">Reviewer iş yükü</h2><p class="mt-1 text-sm text-muted">Yalnız gerçek yetkili kullanıcılar listelenir; sistem/seed reviewer atanamaz.</p></div><a class="hc-v3-btn hc-v3-btn--ghost" href="{{ route('admin.editorial-operations.workload') }}">Tümü</a></div><table class="hc-admin-table mt-4 min-w-[680px]"><thead><tr><th>Reviewer</th><th>Batch</th><th>Açık kaynak işi</th><th>Tarif</th><th>Blog</th><th>Final</th></tr></thead><tbody>@forelse($workload as $item)<tr><td>{{ $item['user']->name }}</td><td>{{ $item['assigned_batches'] }}</td><td>{{ $item['open_issues'] }}</td><td>{{ $item['recipe_packs'] }}</td><td>{{ $item['blog_packs'] }}</td><td>{{ $item['final_approvals'] }}</td></tr>@empty<tr><td colspan="6">Atanmış gerçek reviewer bulunmuyor.</td></tr>@endforelse</tbody></table></section>
    </div>
</x-admin-layout>
