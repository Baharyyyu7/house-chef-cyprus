<x-layouts.site :title="__('admin_workspaces.search')">
    <section class="hc-container py-8 sm:py-10">
        <x-admin.page-header :eyebrow="__('admin_workspaces.search')" :title="__('admin_workspaces.overview')" :back="['url' => route('admin.dashboard'), 'label' => 'Yönetim']">
            <x-slot:actions>
                <form method="post" action="{{ route('admin.seo.audit') }}">@csrf<button class="hc-v3-btn hc-v3-btn--primary"><x-ui.icon name="search" :size="16" /> SEO denetimi</button></form>
            </x-slot:actions>
        </x-admin.page-header>
        <details class="border-b py-4">
            <summary class="cursor-pointer text-sm font-semibold">{{ __('growth.title') }}</summary>
            <ul class="mt-3 divide-y">
                @forelse($systemRuns as $run)
                    <li class="flex flex-wrap justify-between gap-3 py-3">
                        <span>{{ __('growth.stages.'.($run->payload['stage'] ?? 'seo')) }}</span>
                        <span>{{ __('growth.states.'.$run->status) }} · {{ $run->updated_at?->format('d.m.Y H:i') }}</span>
                    </li>
                @empty
                    <li class="py-3 text-muted">{{ __('growth.empty') }}</li>
                @endforelse
            </ul>
        </details>
        <dl class="mt-6 grid grid-cols-2 gap-5 border-y py-5 xl:grid-cols-4" style="border-color:var(--hc-border-subtle)">
            @foreach(['SEO kalite skoru' => $score.'/100', 'Indexlenebilir tarif' => $authority['indexable']['recipes'], 'Indexlenebilir blog' => $authority['indexable']['articles'], 'Açık sorun' => $growth['open_issues']] as $label => $value)
                <div class="min-w-0"><dt class="text-sm text-muted">{{ $label }}</dt><dd class="mt-1 text-2xl font-bold">{{ $value }}</dd></div>
            @endforeach
        </dl>
        <section class="border-b py-6" style="border-color:var(--hc-border-subtle)">
            <div class="flex flex-wrap items-center justify-between gap-3"><h2 class="text-lg font-bold">Otorite ve kapsama sinyalleri</h2><p class="text-sm text-muted">Son denetim: {{ $authority['last_audit_at'] ? \Illuminate\Support\Carbon::parse($authority['last_audit_at'])->format('d.m.Y H:i') : 'henüz çalışmadı' }}</p></div>
            <dl class="mt-4 grid grid-cols-2 gap-5 xl:grid-cols-4">
                @foreach(['Public görselli tarif' => $authority['coverage']['recipes_with_public_media'].'/'.$authority['indexable']['recipes'], 'Tarif bağlantılı blog' => $authority['coverage']['articles_with_related_recipes'].'/'.$authority['indexable']['articles'], 'Onaylı konu' => $authority['topics']['approved_topics'].'/'.$authority['topics']['topics'], 'Açık critical / error' => $authority['issues']['critical'].' / '.$authority['issues']['error']] as $label => $value)
                    <div class="min-w-0"><dt class="text-sm text-muted">{{ $label }}</dt><dd class="mt-1 text-xl font-bold">{{ $value }}</dd></div>
                @endforeach
            </dl>
        </section>
        <section class="border-b py-6" style="border-color:var(--hc-border-subtle)">
            <div class="flex flex-wrap items-center justify-between gap-3"><h2 class="text-lg font-bold">Organik büyüme işleri</h2><a class="hc-v3-btn hc-v3-btn--ghost" href="{{ route('admin.seo.growth') }}">Araştırma ve inceleme <x-ui.icon name="arrow-right" :size="16" /></a></div>
            <dl class="mt-4 grid grid-cols-2 gap-5 xl:grid-cols-4">
                <div><dt class="text-sm text-muted">Açık inceleme işi</dt><dd class="mt-1 text-xl font-bold"><a href="{{ route('admin.seo.opportunities') }}">{{ $growth['open_opportunities'] }}</a></dd></div>
                <div><dt class="text-sm text-muted">Dış bağlantı araştırma adayı</dt><dd class="mt-1 text-xl font-bold">{{ $growth['research_candidates'] }}</dd></div>
                <div><dt class="text-sm text-muted">Doğrulanmış bağlantı</dt><dd class="mt-1 text-xl font-bold">{{ $growth['machine_verified_links'] }}</dd></div>
                <div><dt class="text-sm text-muted">Google tıklamaları</dt><dd class="mt-1 text-xl font-bold">{{ $growth['organic_metrics']['google_clicks'] ?? '-' }}</dd></div>
            </dl>
            <p class="mt-4 text-sm text-muted">{{ $growth['organic_metrics']['note'] }}</p>
            <p class="mt-2 text-sm">Son otomasyon: {{ $growth['latest_run']?->created_at?->format('d.m.Y H:i') ?? 'henüz çalışmadı' }}@if($growth['latest_run']) · {{ $growth['latest_run']->status === 'success' ? 'Tamamlandı' : 'Başarısız' }}@endif</p>
        </section>
        <div class="grid gap-6 py-6 xl:grid-cols-2">
            <section class="min-w-0">
                <div class="flex flex-wrap items-center justify-between gap-3"><h2 class="text-lg font-bold">Teknik SEO sorunları</h2><a class="hc-v3-btn hc-v3-btn--ghost" href="{{ route('admin.seo.issues') }}">Tümünü gör</a></div>
                <ul class="mt-3 divide-y">
                    @forelse($issues as $issue)<li class="py-3"><a class="font-semibold break-words" href="{{ route('admin.seo.issues', ['fingerprint' => $issue->fingerprint]) }}">{{ $issue->message }}</a><p class="mt-1 text-sm text-muted">{{ $issue->severity }} · {{ $issue->category }}</p></li>
                    @empty<li class="py-3 text-muted">Açık teknik sorun yok.</li>@endforelse
                </ul>
            </section>
            <section class="min-w-0">
                <div class="flex flex-wrap items-center justify-between gap-3"><h2 class="text-lg font-bold">İçerik ve teknik inceleme</h2><a class="hc-v3-btn hc-v3-btn--ghost" href="{{ route('admin.seo.opportunities') }}">Tüm işleri gör</a></div>
                <ul class="mt-3 divide-y">
                    @forelse($opportunities as $opportunity)<li class="py-3"><p class="font-semibold break-words">{{ $opportunity->title }}</p><p class="mt-1 text-sm text-muted">{{ $opportunity->topic_cluster }} · Öncelik {{ $opportunity->priority }}</p></li>
                    @empty<li class="py-3 text-muted">Henüz açık inceleme işi yok.</li>@endforelse
                </ul>
            </section>
        </div>
        <section class="border-t py-6" style="border-color:var(--hc-border-subtle)">
            <h2 class="text-lg font-bold">Sağlayıcı durumu</h2>
            <dl class="mt-4 grid gap-5 md:grid-cols-3">@foreach($providerHealth as $name => $provider)<div class="min-w-0"><dt class="font-semibold">{{ str($name)->replace('_', ' ')->title() }}</dt><dd class="mt-1 text-sm text-muted">{{ $provider['safe_message'] }}</dd></div>@endforeach</dl>
        </section>
    </section>
</x-layouts.site>
