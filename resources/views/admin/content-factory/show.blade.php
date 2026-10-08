<x-layouts.site title="İçerik Fabrikası İncelemesi">
    <section class="hc-container py-8 sm:py-10">
        <x-admin.page-header eyebrow="İnsan editoryal inceleme" title="{{ $entity->title }}" description="Bu ekrandaki teknik sonuçlar insan onayı veya yayın anlamına gelmez." :back="['url'=>route('admin.content-factory.index'),'label'=>'İçerik Fabrikası']" />
        @if(session('status'))<x-ui.alert class="mt-5" type="success">{{ session('status') }}</x-ui.alert>@endif
        @if(session('error'))<x-ui.alert class="mt-5" type="warning">{{ session('error') }}</x-ui.alert>@endif
        <div class="mt-7 grid gap-5 lg:grid-cols-[1.45fr_.8fr]">
            <article class="hc-admin-form-card prose max-w-none dark:prose-invert">
                <p class="text-sm text-muted">{{ $review->content_type === 'recipe' ? 'Tarif taslağı' : 'Blog taslağı' }} · {{ $entity->public_id }}</p>
                @if($review->content_type === 'recipe')
                    <p>{{ $entity->description }}</p>
                    <h2>Malzemeler ve ölçüler</h2><ul>@foreach($entity->recipeIngredients()->with('ingredient')->orderBy('sort_order')->get() as $line)<li>{{ $line->display_quantity }} {{ $line->display_measure_code }} {{ $line->ingredient?->name }} <span class="text-muted">(canonical: {{ $line->canonical_quantity }} {{ $line->canonical_unit }})</span></li>@endforeach</ul>
                    <h2>Adımlar</h2><ol>@foreach($entity->steps()->orderBy('position')->get() as $step)<li><strong>{{ $step->title }}</strong> — {{ $step->instruction }} <em>({{ $step->duration_seconds }} sn)</em></li>@endforeach</ol>
                @else
                    {{-- Taslak gövdesi AI ve içe aktarmadan gelir: ham HTML ve javascript: bağlantıları süzülür, yoksa yöneticinin oturumunda betik çalışır. --}}
                    <div>{!! str($entity->body)->markdown(['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
                @endif
            </article>
            <aside class="space-y-5">
                <article class="hc-admin-form-card"><h2 class="hc-heading-4">Readiness</h2><p class="mt-2 text-3xl font-black">{{ $entity->content_factory_readiness_score ?? 0 }}/100</p><p class="mt-2 text-sm text-muted">Kural: {{ $entity->content_factory_rule_version ?? '—' }} · {{ $entity->content_factory_checked_at?->format('d.m.Y H:i') ?? 'tarama bekliyor' }}</p></article>
                <article class="hc-admin-form-card"><h2 class="hc-heading-4">Teknik ve fact-check</h2><ul class="mt-3 space-y-2 text-sm">@foreach($reviews as $item)<li><strong>{{ str($item->stage)->replace('_',' ')->title() }}</strong>: {{ str($item->status)->replace('_',' ')->title() }}</li>@endforeach</ul></article>
                <article class="hc-admin-form-card"><h2 class="hc-heading-4">Fact-check sorunları</h2><ul class="mt-3 space-y-3 text-sm">@forelse($issues as $issue)<li><strong>{{ str($issue->risk_level)->title() }}</strong> · {{ $issue->claim }}<br><span class="text-muted">Kaynak ihtiyacı: {{ $issue->source_requirement }}</span></li>@empty<li class="text-muted">Açık issue yok.</li>@endforelse</ul></article>
                <article class="hc-admin-form-card"><h2 class="hc-heading-4">SEO, medya ve bağlantılar</h2><pre class="mt-3 overflow-x-auto whitespace-pre-wrap text-xs">{{ json_encode($entity->content_factory_metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre></article>
                @if($review->stage === 'human_review' && in_array($review->status, ['pending','revision_requested'], true))
                    <article class="hc-admin-form-card"><h2 class="hc-heading-4">İnsan kararı</h2><p class="mt-2 text-sm text-muted">Onay kaydedilse bile içerik taslak kalır; yayın ayrı bir yetkili işlemdir.</p><div class="mt-4 flex flex-wrap gap-2"><form method="post" action="{{ route('admin.content-factory.reviews.approve',$review) }}">@csrf<button class="hc-v3-btn hc-v3-btn--primary">İnsan onayı</button></form><form method="post" action="{{ route('admin.content-factory.reviews.reject',$review) }}">@csrf<button class="hc-v3-btn hc-v3-btn--ghost">Reddet</button></form></div><form class="mt-4" method="post" action="{{ route('admin.content-factory.reviews.request-revision',$review) }}">@csrf<label class="block text-sm font-semibold" for="comment">Düzeltme notu</label><textarea class="hc-input mt-2" id="comment" name="comment" rows="4" minlength="10" required></textarea><button class="hc-v3-btn hc-v3-btn--secondary mt-2">Düzeltme iste</button></form></article>
                @endif
            </aside>
        </div>
    </section>
</x-layouts.site>
