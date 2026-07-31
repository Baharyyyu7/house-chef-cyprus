<x-layouts.site title="İçerik Fabrikası">
    <section class="hc-container py-8 sm:py-10">
        <x-admin.page-header eyebrow="Editoryal güvenlik" title="İçerik Fabrikası" description="Taslaklar teknik kapılardan geçer; yayın ve doğrulama yalnız gerçek insan aksiyonu ile yapılır." :back="['url'=>route('admin.dashboard'),'label'=>'Yönetim']" />
        @if(session('status'))<x-ui.alert class="mt-5" type="success">{{ session('status') }}</x-ui.alert>@endif
        @if(session('error'))<x-ui.alert class="mt-5" type="warning">{{ session('error') }}</x-ui.alert>@endif
        <div class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div class="hc-admin-stat"><span>Doğrulanmış tarif</span><strong>{{ $verifiedRecipes }}</strong></div>
            <div class="hc-admin-stat"><span>Tarif taslağı</span><strong>{{ $recipeDrafts }}/450</strong></div>
            <div class="hc-admin-stat"><span>Yayımlı blog</span><strong>{{ $publishedBlogs }}</strong></div>
            <div class="hc-admin-stat"><span>Blog taslağı</span><strong>{{ $blogDrafts }}/180</strong></div>
            <div class="hc-admin-stat"><span>Batch</span><strong>{{ $recipeBatches }} + {{ $blogBatches }}</strong></div>
            <div class="hc-admin-stat"><span>İnceleme / hata</span><strong>{{ $waiting }} / {{ $failed }}</strong></div>
        </div>
        <div class="mt-5 grid gap-4 md:grid-cols-3">
            <article class="hc-admin-form-card"><h2 class="hc-heading-4">Topic cluster</h2><p class="mt-2 text-3xl font-black">{{ $clusters }}</p><p class="mt-2 text-sm text-muted">Pillar ve iç bağlantı planı taslakta hazırlanır.</p></article>
            <article class="hc-admin-form-card"><h2 class="hc-heading-4">Otomatik yayın</h2><p class="mt-2 font-bold text-emerald-700 dark:text-emerald-300">Kapalı</p><p class="mt-2 text-sm text-muted">Hiçbir taslak public alana veya site haritasına eklenmez.</p></article>
            <article class="hc-admin-form-card"><h2 class="hc-heading-4">Güvenlik notu</h2><p class="mt-2 text-sm text-muted">Fact-check, özgünlük, medya ve SEO notları insan onayı yerine geçmez.</p></article>
        </div>
        <form class="mt-7 grid gap-3 sm:grid-cols-4" method="get">
            <select class="hc-input" name="type"><option value="">Tüm içerikler</option><option value="recipe" @selected(request('type')==='recipe')>Tarif</option><option value="blog" @selected(request('type')==='blog')>Blog</option></select>
            <select class="hc-input" name="stage"><option value="">Tüm aşamalar</option><option value="technical_review">Teknik QA</option><option value="originality_review">Özgünlük</option><option value="fact_check_required">Fact-check</option><option value="human_review">İnsan review</option></select>
            <select class="hc-input" name="status"><option value="">Tüm durumlar</option><option value="pending">Bekliyor</option><option value="passed">Geçti</option><option value="human_approved">İnsan onaylı</option><option value="rejected">Reddedildi</option></select>
            <button class="hc-v3-btn hc-v3-btn--secondary">Filtrele</button>
        </form>
        <section class="hc-admin-table-shell mt-5"><div class="hc-table-wrap"><table class="hc-table min-w-[980px]"><thead><tr><th>Tür</th><th>Batch</th><th>Aşama</th><th>Durum</th><th>Public ID</th><th>Fact-check</th><th>İnceleme</th><th>İşlem</th></tr></thead><tbody>@forelse($reviews as $review)<tr><td>{{ $review->content_type === 'recipe' ? 'Tarif' : 'Blog' }}</td><td>{{ $review->batch?->sequence ?? '—' }}</td><td>{{ str($review->stage)->replace('_',' ')->title() }}</td><td>{{ str($review->status)->replace('_',' ')->title() }}</td><td><code>{{ $review->entity_public_id }}</code></td><td>{{ $review->fact_check_required ? 'Gerekli' : 'Hayır' }}</td><td>{{ $review->reviewed_at?->format('d.m.Y H:i') ?? 'Bekliyor' }}</td><td><div class="flex gap-2">@if($review->status === 'pending' && $review->stage === 'human_review')<form method="post" action="{{ route('admin.content-factory.reviews.approve',$review) }}">@csrf<button class="hc-v3-btn hc-v3-btn--ghost">İnsan onayı</button></form><form method="post" action="{{ route('admin.content-factory.reviews.reject',$review) }}">@csrf<button class="hc-v3-btn hc-v3-btn--ghost">Reddet</button></form>@else<span class="text-sm text-muted">{{ $review->status === 'pending' ? 'QA bekliyor' : 'İşlendi' }}</span>@endif</div></td></tr>@empty<tr><td colspan="8"><div class="hc-admin-empty">Bu filtre için inceleme kaydı yok.</div></td></tr>@endforelse</tbody></table></div><div class="mt-5">{{ $reviews->links() }}</div></section>
    </section>
</x-layouts.site>
