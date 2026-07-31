<x-layouts.site title="AI Görsel Stüdyosu">
    <section class="hc-container hc-admin-crud-page py-8 sm:py-10">
        <x-admin.page-header eyebrow="Kontrollü pilot" title="AI Görsel Stüdyosu" description="10 tariflik pilot planı. Maliyet onayı ve canlı capability testi olmadan üretim yapılmaz." :back="['url'=>route('admin.dashboard'),'label'=>'Yönetim']" />
        <x-ui.alert class="mt-6" type="warning">Gerçek üretim kapalıdır. Günlük bütçe limiti: {{ number_format($cost['budget_cents'] / 100, 2, ',', '.') }}. Planlanan {{ $cost['variation_count'] }} varyasyonun tahmini maliyeti: {{ $cost['cost_estimate_configured'] ? number_format($cost['estimated_cost_cents'] / 100, 2, ',', '.') : 'belirlenmedi' }}.</x-ui.alert>
        <section class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-3" aria-label="Provider durumları">
            @foreach($providers as $provider)<article class="hc-admin-detail-card"><span>{{ strtoupper($provider['name']) }}</span><strong>{{ $provider['image_capability'] }}</strong><small>{{ $provider['model'] ?? 'Model seçilmedi' }} · {{ $provider['safe_message'] }}</small></article>@endforeach
        </section>
        <section class="hc-admin-detail-card mt-7" aria-labelledby="ranked-review-title">
            <div class="flex flex-wrap items-start justify-between gap-3"><div><span>Hızlı inceleme · yalnız metadata</span><h2 id="ranked-review-title" class="mt-1 text-xl font-semibold">Pexels Editoryal Review Batch {{ $candidateBatch }}</h2><p class="mt-1 text-sm text-muted">Her tarif için en güçlü üç aday görünür. Uzak CDN görseli public render edilmez; seçim yalnız private import için işaretler.</p></div><span class="hc-badge">No auto-publish</span></div>
            <nav class="mt-5 flex flex-wrap gap-2" aria-label="Review batch seçimi">@for($batch = 1; $batch <= 5; $batch++)<a class="hc-v3-btn {{ $candidateBatch === $batch ? 'hc-v3-btn--primary' : 'hc-v3-btn--ghost' }}" href="{{ route('admin.ai-image-studio.index', ['review_batch' => $batch]) }}">Batch {{ $batch }}</a>@endfor</nav>
            @if($quickRecipe && $quickCandidates)
                @php($quickRecipes = $rankedCandidates->map(fn ($candidates) => $candidates->first()?->recipe)->filter()->values())
                @php($quickIndex = $quickRecipes->search(fn ($recipe) => $recipe->is($quickRecipe)))
                <section class="mt-5 rounded-2xl border border-[color:var(--hc-gold)] bg-[color:var(--hc-surface-muted)] p-4" data-rapid-review>
                    <div class="flex flex-wrap items-start justify-between gap-3"><div><span class="text-xs font-bold uppercase tracking-[.15em] text-muted">Hızlı inceleme</span><h3 class="mt-1 text-lg font-semibold">{{ $quickRecipe->title }}</h3><p class="mt-1 text-sm text-muted">Seçilen {{ $reviewCounts?->selected ?? 0 }} · Reddedilen {{ $reviewCounts?->rejected ?? 0 }} · Kalan {{ $reviewCounts?->remaining ?? 0 }}</p></div><p class="text-xs text-muted">Kısayollar: 1–3 seç · R tümünü reddet · F fallback · ←/→ gezin</p></div>
                    <div class="mt-4 grid gap-3 md:grid-cols-3">@foreach($quickCandidates->take(3) as $candidate)<article class="rounded-xl border border-[color:var(--hc-line)] p-3"><strong>{{ $candidate->rank }}. aday · {{ $candidate->review_score }}/100</strong><p class="mt-2 text-xs text-muted">{{ $candidate->photographer ?: 'Pexels' }} · {{ $candidate->width }}×{{ $candidate->height }}</p><p class="mt-2 text-xs text-muted">{{ $candidate->alt_text }}</p>@if($candidate->pexels_page_url)<a class="mt-3 inline-flex text-sm underline" target="_blank" rel="noopener noreferrer" href="{{ $candidate->pexels_page_url }}">Pexels sayfası</a>@endif@if($candidate->status === 'pending_review')<form id="rapid-select-{{ $candidate->rank }}" class="mt-3" method="post" action="{{ route('admin.ai-image-studio.pexels-candidates.select', $candidate) }}">@csrf<button class="hc-v3-btn hc-v3-btn--primary w-full" type="submit">{{ $candidate->rank }} · Seç</button></form>@else<p class="mt-3 text-xs font-semibold">{{ $candidate->status }}</p>@endif</article>@endforeach</div>
                    <div class="mt-4 flex flex-wrap gap-2"><form id="rapid-reject-all" method="post" action="{{ route('admin.ai-image-studio.pexels-candidates.reject-all', $quickRecipe) }}">@csrf<button class="hc-v3-btn hc-v3-btn--danger" type="submit">R · Tümünü reddet</button></form><form id="rapid-fallback" method="post" action="{{ route('admin.ai-image-studio.pexels-candidates.keep-fallback', $quickRecipe) }}">@csrf<button class="hc-v3-btn hc-v3-btn--ghost" type="submit">F · Fallback’i koru</button></form>@if($quickIndex !== false && $quickIndex > 0)<a class="hc-v3-btn hc-v3-btn--ghost" href="{{ route('admin.ai-image-studio.index', ['review_batch' => $candidateBatch, 'review_recipe' => $quickRecipes[$quickIndex - 1]->public_id]) }}">← Önceki</a>@endif@if($quickIndex !== false && $quickIndex < $quickRecipes->count() - 1)<a class="hc-v3-btn hc-v3-btn--ghost" href="{{ route('admin.ai-image-studio.index', ['review_batch' => $candidateBatch, 'review_recipe' => $quickRecipes[$quickIndex + 1]->public_id]) }}">Sonraki →</a>@endif</div>
                </section>
            @endif
            <div class="mt-5 grid gap-5">
                @forelse($rankedCandidates as $recipeCandidates)
                    @php($reviewRecipe = $recipeCandidates->first()->recipe)
                    <article class="rounded-2xl border border-[color:var(--hc-line)] p-4"><div class="flex flex-wrap items-start justify-between gap-4"><div><h3 class="text-lg font-semibold">{{ $reviewRecipe?->title ?? 'Silinmiş tarif' }}</h3><p class="mt-1 text-sm text-muted">{{ $reviewRecipe?->cuisine?->name ?? '—' }} · {{ $reviewRecipe?->category?->name ?? '—' }}</p><p class="mt-1 text-xs text-muted">Ana malzemeler: {{ $reviewRecipe?->ingredients->take(4)->pluck('name')->implode(', ') ?: '—' }}</p></div><form method="post" action="{{ route('admin.ai-image-studio.pexels-candidates.keep-fallback', $reviewRecipe) }}">@csrf<button class="hc-v3-btn hc-v3-btn--ghost" type="submit">Fallback'i koru</button></form></div><div class="mt-4 grid gap-3 md:grid-cols-3">@foreach($recipeCandidates->take(3) as $candidate)<article class="rounded-xl bg-[color:var(--hc-surface-muted)] p-3"><div class="flex items-center justify-between gap-2"><strong>Aday {{ $candidate->rank }}</strong><span class="hc-badge">{{ $candidate->review_score }}/100</span></div><p class="mt-2 text-xs text-muted">{{ $candidate->width }}×{{ $candidate->height }} · {{ $candidate->photographer ?: 'Pexels' }}</p><p class="mt-2 text-xs text-muted">{{ $candidate->alt_text }}</p>@if($candidate->review_warnings)<p class="mt-2 text-xs text-danger">{{ implode(' ', $candidate->review_warnings) }}</p>@endif<p class="mt-2 text-xs text-muted">Başlık {{ data_get($candidate->score_breakdown, 'title', 0) }} · Malzeme {{ data_get($candidate->score_breakdown, 'ingredients', 0) }} · Kalite {{ data_get($candidate->score_breakdown, 'quality', 0) }}</p><div class="mt-3 flex flex-wrap gap-2">@if($candidate->pexels_page_url)<a class="hc-v3-btn hc-v3-btn--ghost" target="_blank" rel="noopener noreferrer" href="{{ $candidate->pexels_page_url }}">Pexels sayfası</a>@endif@if($candidate->status === 'pending_review')<form method="post" action="{{ route('admin.ai-image-studio.pexels-candidates.select', $candidate) }}">@csrf<button class="hc-v3-btn hc-v3-btn--primary" type="submit">Seç ve private review'a al</button></form><form method="post" action="{{ route('admin.ai-image-studio.pexels-candidates.reject', $candidate) }}">@csrf<button class="hc-v3-btn hc-v3-btn--danger" type="submit">Reddet</button></form>@elseif($candidate->status === 'auto_rejected')<span class="text-xs font-semibold text-danger">Otomatik red: {{ $candidate->rejection_code }}</span>@else<span class="text-xs font-semibold">{{ $candidate->status }}</span>@endif</div></article>@endforeach</div></article>
                    @foreach($recipeCandidates->where('status', 'selected_for_review') as $selectedCandidate)
                        <form class="mt-3" method="post" action="{{ route('admin.ai-image-studio.pexels-candidates.import', $selectedCandidate) }}">
                            @csrf
                            <button class="hc-v3-btn hc-v3-btn--primary" type="submit">Seçilen adayı private import et</button>
                        </form>
                    @endforeach
                @empty
                    <div class="hc-admin-empty">Bu batch için taranmış aday yok. Yalnız güvenli aday taraması çalıştırıldığında metadata burada görünür.</div>
                @endforelse
            </div>
        </section>
        <script>
            document.addEventListener('keydown', (event) => {
                if (event.target instanceof HTMLInputElement || event.target instanceof HTMLTextAreaElement || event.target instanceof HTMLSelectElement) return;
                const key = event.key.toLowerCase();
                const form = key.match(/^[1-3]$/) ? document.getElementById(`rapid-select-${key}`) : key === 'r' ? document.getElementById('rapid-reject-all') : key === 'f' ? document.getElementById('rapid-fallback') : null;
                if (form) { event.preventDefault(); form.requestSubmit(); return; }
                if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') { const links = [...document.querySelectorAll('[data-rapid-review] a')]; const link = links.find((item) => event.key === 'ArrowRight' ? item.textContent.includes('Sonraki') : item.textContent.includes('Önceki')); if (link) { event.preventDefault(); link.click(); } }
            });
        </script>
        <section class="hc-admin-detail-card mt-7" aria-labelledby="pexels-candidates-title">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div><span>Private review zinciri</span><h2 id="pexels-candidates-title" class="mt-1 text-xl font-semibold">Pexels Adayları</h2><p class="mt-1 text-sm text-muted">Adaylar uzak CDN’den public gösterilmez. Yönetici seçimiyle private indirilir, doğrulanır ve yalnız manuel onaydan sonra yayınlanır.</p></div>
                <span class="hc-badge">No auto-publish</span>
            </div>
            @if(session('status'))<x-ui.alert class="mt-5" type="success">{{ session('status') }}</x-ui.alert>@endif
            @if($errors->any())<x-ui.alert class="mt-5" type="warning">{{ $errors->first() }}</x-ui.alert>@endif
            <form method="get" action="{{ route('admin.ai-image-studio.index') }}" class="mt-5 grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto]">
                <label class="text-sm font-bold">Tarif<select name="recipe" class="hc-input mt-1 w-full"><option value="">Tarif seçin</option>@foreach($recipes as $recipe)<option value="{{ $recipe->public_id }}" @selected($selectedRecipe?->public_id === $recipe->public_id)>{{ $recipe->title }}</option>@endforeach</select></label>
                <button class="hc-v3-btn hc-v3-btn--ghost self-end" type="submit">Güvenli aday ara</button>
            </form>
            @if($selectedRecipe)
                @if($pexelsSearch !== null)<p class="mt-4 text-sm text-muted">{{ $pexelsSearch['safe_message'] }}@if($pexelsSearch['error_code']) <code>{{ $pexelsSearch['error_code'] }}</code>@endif</p>@endif
                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @forelse($pexelsCandidates as $candidate)
                        <article class="rounded-2xl border border-[color:var(--hc-line)] p-4"><div class="flex aspect-[4/3] items-center justify-center rounded-xl bg-[color:var(--hc-surface-muted)] p-3 text-center text-xs text-muted">Pexels görseli yalnız private indirme sonrası görünür.</div><div class="mt-3"><strong>{{ $candidate['photographer'] }}</strong><p class="mt-1 text-xs text-muted">{{ $candidate['width'] }}×{{ $candidate['height'] }} · Uyum puanı {{ $candidate['score'] }}</p></div><form class="mt-4" method="post" action="{{ route('admin.ai-image-studio.pexels.import') }}">@csrf<input type="hidden" name="recipe_public_id" value="{{ $selectedRecipe->public_id }}"><input type="hidden" name="external_photo_id" value="{{ $candidate['external_photo_id'] }}"><button class="hc-v3-btn hc-v3-btn--primary w-full" type="submit">Private review alanına al</button></form><p class="mt-3 text-xs text-muted">{{ $candidate['attribution_text'] }}</p></article>
                    @empty
                        <div class="hc-admin-empty md:col-span-2 xl:col-span-3">Bu tarif için gösterilecek güvenli Pexels adayı yok. Sağlayıcı kapalı, anahtar eksik veya sonuç bulunamadıysa branded fallback korunur.</div>
                    @endforelse
                </div>
            @endif
        </section>
        <section class="hc-admin-detail-card mt-7" aria-labelledby="unsplash-candidates-title">
            <div class="flex flex-wrap items-start justify-between gap-3"><div><span>İkincil editoryal kaynak · hotlink</span><h2 id="unsplash-candidates-title" class="mt-1 text-xl font-semibold">Unsplash Adayları</h2><p class="mt-1 text-sm text-muted">Yalnız yönetici aramasıyla metadata alınır. Seçim ve final insan onayı olmadan public kullanım, indirme veya tracking yapılmaz.</p></div><span class="hc-badge">No auto-publish</span></div>
            @if($selectedRecipe)
                <div class="mt-5 flex flex-wrap items-center gap-3"><form method="post" action="{{ route('admin.ai-image-studio.unsplash.search', $selectedRecipe) }}">@csrf<button class="hc-v3-btn hc-v3-btn--ghost" type="submit">Unsplash'ta Ara</button></form><p class="text-sm text-muted">{{ $unsplashSearch['safe_message'] ?? 'Tarif seçildiğinde Unsplash durumu görünür.' }}</p></div>
                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @forelse($unsplashCandidates as $candidate)
                        <article class="rounded-2xl border border-[color:var(--hc-line)] p-4"><div class="aspect-[4/3] overflow-hidden rounded-xl bg-[color:var(--hc-surface-muted)]">@if($candidate->status !== 'auto_rejected' && $candidate->thumb_url)<img class="h-full w-full object-cover" src="{{ $candidate->thumb_url }}" alt="" loading="lazy" referrerpolicy="no-referrer">@endif</div><div class="mt-3 flex items-center justify-between gap-2"><strong>{{ $candidate->rank }}. aday</strong><span class="hc-badge">{{ $candidate->review_score }}/100</span></div><p class="mt-2 text-xs text-muted">{{ $candidate->width }}×{{ $candidate->height }} · {{ $candidate->orientation ?: '—' }}</p><p class="mt-2 text-sm">{{ $candidate->alt_text }}</p><p class="mt-2 text-xs text-muted">{{ $candidate->attribution_text ?? ('Fotoğraf: '.$candidate->photographer.' / Unsplash') }}</p><div class="mt-3 flex flex-wrap gap-2">@if($candidate->external_page_url)<a class="hc-v3-btn hc-v3-btn--ghost" target="_blank" rel="noopener noreferrer" href="{{ $candidate->external_page_url }}">Kaynağa git</a>@endif@if($candidate->status === 'pending_review')<form method="post" action="{{ route('admin.ai-image-studio.unsplash-candidates.select', $candidate) }}">@csrf<button class="hc-v3-btn hc-v3-btn--primary" type="submit">Adayı seç</button></form><form method="post" action="{{ route('admin.ai-image-studio.unsplash-candidates.reject', $candidate) }}">@csrf<button class="hc-v3-btn hc-v3-btn--danger" type="submit">Reddet</button></form>@elseif($candidate->status === 'selected_for_review')<form class="grid w-full gap-2" method="post" action="{{ route('admin.ai-image-studio.unsplash-candidates.approve', $candidate) }}">@csrf<label class="text-xs font-bold">Alt metin<input class="hc-input mt-1 w-full" name="alt_text" value="{{ $candidate->alt_text }}" required></label><label class="text-xs font-bold">Onay notu<textarea class="hc-input mt-1 min-h-20 w-full" name="review_notes" required minlength="10"></textarea></label><button class="hc-v3-btn hc-v3-btn--primary" type="submit">Final onay ve hotlink bağla</button></form>@else<span class="text-xs font-semibold">{{ $candidate->status }}</span>@endif</div></article>
                    @empty
                        <div class="hc-admin-empty md:col-span-2 xl:col-span-3">Unsplash adayı yok. Sağlayıcı kapalıysa veya anahtar eksikse branded fallback korunur.</div>
                    @endforelse
                </div>
            @else
                <div class="hc-admin-empty mt-5">Unsplash adaylarını görmek için önce bir tarif seçin.</div>
            @endif
        </section>
        <section class="hc-admin-detail-card mt-7" aria-labelledby="pexels-review-title">
            <div><span>İnsan onayı zorunlu</span><h2 id="pexels-review-title" class="mt-1 text-xl font-semibold">Pexels Review Bekleyenler</h2></div>
            <div class="mt-5 grid gap-4 lg:grid-cols-2">
                @forelse($pexelsReviewMedia as $media)
                    <article class="rounded-2xl border border-[color:var(--hc-line)] p-4"><strong>{{ $media->mediable?->title ?? 'Silinmiş tarif' }}</strong><p class="mt-1 text-xs text-muted">{{ $media->verification_status }} · {{ data_get($media->provider_metadata, 'photographer', 'Pexels') }}</p><p class="mt-3 text-sm text-muted">{{ $media->alt_text }}</p>@if($media->verification_status === 'pending_review')<form class="mt-4 grid gap-3" method="post" action="{{ route('admin.ai-image-studio.pexels.approve', $media) }}">@csrf<label class="text-xs font-bold">Alt metin<input class="hc-input mt-1 w-full" name="alt_text" value="{{ $media->alt_text }}" required></label><label class="text-xs font-bold">Onay notu<textarea class="hc-input mt-1 min-h-20 w-full" name="review_notes" required minlength="10"></textarea></label><button class="hc-v3-btn hc-v3-btn--primary" type="submit">Onayla ve public aktar</button></form><form class="mt-3" method="post" action="{{ route('admin.ai-image-studio.pexels.reject', $media) }}">@csrf<button class="hc-v3-btn hc-v3-btn--danger" type="submit">Adayı reddet</button></form>@else<p class="mt-3 text-sm font-semibold">{{ $media->verification_status === 'verified' ? 'Public onaylandı' : 'Reddedildi' }}</p>@endif</article>
                @empty
                    <div class="hc-admin-empty lg:col-span-2">Private review bekleyen Pexels adayı yok.</div>
                @endforelse
            </div><div class="mt-5">{{ $pexelsReviewMedia->withQueryString()->links() }}</div>
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
