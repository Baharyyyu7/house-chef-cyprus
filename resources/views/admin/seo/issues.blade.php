<x-layouts.site :title="__('admin_workspaces.issues')">
    <section class="hc-container py-8 sm:py-10">
        <x-admin.page-header :eyebrow="__('admin_workspaces.search')" :title="__('admin_workspaces.issues')" :back="['url' => route('admin.seo.index'), 'label' => 'SEO merkezi']" />
        @if($errors->any())<div role="alert" class="mt-4 text-sm">{{ $errors->first() }}</div>@endif
        <form class="mt-6 grid gap-3 sm:grid-cols-4">
            <label class="sr-only" for="issue-severity">Önem seviyesi</label><select id="issue-severity" class="hc-input" name="severity"><option value="">Tüm önem seviyeleri</option>@foreach(['critical', 'error', 'warning', 'info'] as $value)<option value="{{ $value }}" @selected(request('severity') === $value)>{{ $value }}</option>@endforeach</select>
            <label class="sr-only" for="issue-category">Kategori</label><select id="issue-category" class="hc-input" name="category"><option value="">Tüm kategoriler</option>@foreach(['technical' => 'Teknik', 'content' => 'İçerik', 'schema' => 'Schema'] as $value => $label)<option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>@endforeach</select>
            <label class="sr-only" for="issue-status">Sorun durumu</label><select id="issue-status" class="hc-input" name="status"><option value="open">Açık sorunlar</option><option value="resolved" @selected(request('status') === 'resolved')>Çözülmüş</option></select>
            @if(request('fingerprint'))<input type="hidden" name="fingerprint" value="{{ request('fingerprint') }}" />@endif
            <button class="hc-v3-btn hc-v3-btn--ghost"><x-ui.icon name="sliders" :size="16" /> Filtrele</button>
        </form>
        <p class="mt-4 text-sm text-muted">{{ $issues->total() }} sorun @if(request('fingerprint'))· <a href="{{ route('admin.seo.issues') }}" class="underline">Tüm sorunlar</a>@endif</p>
        <div class="hc-table-wrap mt-4 max-w-full overflow-x-auto">
            <table class="hc-table min-w-[850px]">
                <thead><tr><th>Seviye</th><th>Sorun ve kaynak</th><th>Varlık</th><th>Son tespit</th><th>İşlem</th></tr></thead>
                <tbody>
                    @forelse($issues as $issue)
                        <tr>
                            <td>{{ $issue->severity }}<p class="mt-1 text-xs text-muted">{{ $issue->category }}</p></td>
                            <td class="max-w-sm whitespace-normal [overflow-wrap:anywhere]">{{ $issue->message }}@if($issue->url)<p class="mt-2 text-xs text-muted">{{ $issue->url }}</p>@endif</td>
                            <td class="max-w-xs whitespace-normal [overflow-wrap:anywhere]"><code>{{ $issue->entity_public_id ?? '-' }}</code>@if($issue->entity_type === \App\Models\Recipe::class && $issue->entity_public_id)<a class="mt-2 block text-sm underline" href="{{ route('admin.recipe-verification.show', ['recipe' => $issue->entity_public_id]) }}">Tarifi incele</a>@endif</td>
                            <td>{{ $issue->last_detected_at?->format('d.m.Y H:i') }}</td>
                            <td>@if($issue->resolved_at)<form method="post" action="{{ route('admin.seo.issues.reopen', $issue) }}">@csrf<button class="hc-v3-btn hc-v3-btn--ghost"><x-ui.icon name="refresh" :size="16" /> Yeniden aç</button></form>@else<form method="post" action="{{ route('admin.seo.issues.resolve', $issue) }}">@csrf<button class="hc-v3-btn hc-v3-btn--primary"><x-ui.icon name="check" :size="16" /> Çöz</button></form>@endif</td>
                        </tr>
                    @empty<tr><td colspan="5" class="py-6 text-muted">Gösterilecek SEO sorunu yok.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $issues->links() }}</div>
    </section>
</x-layouts.site>
