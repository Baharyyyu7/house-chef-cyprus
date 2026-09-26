<x-layouts.site title="Blog">
    <section class="hc-container py-8 sm:py-10">
        <x-admin.page-header eyebrow="Editoryal içerik" title="Blog" description="Taslak, inceleme ve yayın yaşam döngüsü. Yayınlanmayan içerik public alanda görünmez." :back="['url' => route('admin.dashboard'), 'label' => 'Yönetim']">
            <a class="hc-v3-btn hc-v3-btn--primary" href="{{ route('admin.blog.create') }}">Yeni yazı</a>
        </x-admin.page-header>

        @if(session('status'))
            <x-ui.alert class="mt-5" type="success">{{ session('status') }}</x-ui.alert>
        @endif
        @if($errors->has('publication'))
            <x-ui.alert class="mt-5" type="warning">{{ $errors->first('publication') }}</x-ui.alert>
        @endif

        <section class="hc-admin-form-card mt-6" aria-labelledby="bulk-publication-title">
            <div class="flex flex-wrap items-start justify-between gap-5">
                <div>
                    <h2 id="bulk-publication-title" class="hc-heading-3">Taslak hazırlık ve yayın</h2>
                    <span class="sr-only">Uygun olanların tümünü yayınla</span>
                    <p class="hc-body-sm mt-2 text-muted">
                        {{ $publicationCandidateCount }} yayınlanmamış yazıyı önce denetleyin, sonra yalnız mevcut metinden üretilebilen güvenli SEO alanlarını hazırlayın.
                        Kaynak, özgünlük, editoryal ve insan onayı otomatik verilmez.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <form method="post" action="{{ route('admin.blog.readiness-scan') }}">@csrf
                        <button class="hc-v3-btn hc-v3-btn--ghost" @disabled($publicationCandidateCount === 0)>Taslakları denetle</button>
                    </form>
                    <form method="post" action="{{ route('admin.blog.prepare') }}" onsubmit="return confirm('En fazla 100 taslağın yalnız güvenli alanları hazırlansın mı?')">@csrf
                        <button class="hc-v3-btn hc-v3-btn--secondary" @disabled($publicationCandidateCount === 0)>Güvenli alanları hazırla</button>
                    </form>
                    <form method="post" action="{{ route('admin.blog.publish-ready') }}" onsubmit="return confirm('Yalnız tüm yayın kapılarını geçen yazılar yayınlansın mı?')">@csrf
                        <button class="hc-v3-btn hc-v3-btn--primary" @disabled($publicationCandidateCount === 0)>Hazır olanları yayınla</button>
                    </form>
                    <form method="post" action="{{ route('admin.blog.publish-all') }}" onsubmit="return confirm('Yayınlanmamış {{ $publicationCandidateCount }} yazının tamamı kalite, SEO ve onay kontrolleri atlanarak hemen yayınlanacak. Emin misiniz?')">@csrf
                        <button class="hc-v3-btn hc-v3-btn--danger" @disabled($publicationCandidateCount === 0)>Kontrolsüz tümünü yayınla</button>
                    </form>
                </div>
            </div>

            @if(session('readiness_snapshot'))
                @php($snapshot = session('readiness_snapshot'))
                <div class="mt-4 rounded-xl border border-subtle p-4" role="status">
                    <strong>{{ $snapshot['ready'] }} hazır · {{ $snapshot['blocked'] }} hazırlık bekliyor</strong>
                    @if($snapshot['ready'] === 0)<p class="hc-body-sm mt-1 text-muted">Şu anda yayın kapılarının tamamını geçen yazı yok. Aşağıdaki eksikler tamamlanmadan yayın yapılmaz.</p>@endif
                    @if($snapshot['reasons'] !== [])
                        <details class="mt-3"><summary class="cursor-pointer font-semibold">Hazırlık eksiklerini göster</summary>
                            <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-muted">
                                @foreach($snapshot['reasons'] as $reason => $count)<li>{{ $reason }} ({{ $count }} yazı)</li>@endforeach
                            </ul>
                        </details>
                    @endif
                </div>
            @endif

            @if(session('publication_reasons'))
                <details class="mt-4">
                    <summary class="cursor-pointer font-semibold">Atlanma nedenlerini göster</summary>
                    <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-muted">
                        @foreach(session('publication_reasons') as $reason => $count)
                            <li>{{ $reason }} ({{ $count }} yazı)</li>
                        @endforeach
                    </ul>
                </details>
            @endif
        </section>

        <form class="mt-6 grid gap-3 sm:grid-cols-[minmax(0,1fr)_12rem_auto]">
            <input class="hc-input" name="q" value="{{ request('q') }}" placeholder="Başlık ara">
            <select class="hc-input" name="status">
                <option value="">Tüm durumlar</option>
                @foreach(['draft' => 'Taslak', 'in_review' => 'İncelemede', 'scheduled' => 'Planlandı', 'published' => 'Yayınlandı', 'archived' => 'Arşivlendi'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="hc-v3-btn hc-v3-btn--ghost">Filtrele</button>
        </form>

        <div class="hc-admin-table-shell mt-6">
            <div class="hc-table-wrap">
                <table class="hc-table min-w-[800px]">
                    <thead><tr><th>Başlık</th><th>Kategori</th><th>Durum</th><th>Yayın</th><th></th></tr></thead>
                    <tbody>
                        @forelse($posts as $post)
                            <tr>
                                <td><strong>{{ $post->title }}</strong><br><code>{{ $post->public_id }}</code></td>
                                <td>{{ $post->category?->name ?? '—' }}</td>
                                <td>{{ $post->status }}</td>
                                <td>{{ $post->published_at?->format('d.m.Y H:i') ?? '—' }}</td>
                                <td class="flex flex-wrap gap-2">
                                    <a class="hc-v3-btn hc-v3-btn--ghost" href="{{ route('admin.blog.edit', $post) }}">Düzenle</a>
                                    @if($post->completion_revision_exists)
                                        <a class="hc-v3-btn hc-v3-btn--secondary" href="{{ route('admin.blog.review', $post) }}">Karşılaştır</a>
                                    @endif
                                    @if(! in_array($post->status, ['published', 'archived'], true))
                                        <form method="post" action="{{ route('admin.blog.publish', $post) }}">
                                            @csrf
                                            <button class="hc-v3-btn hc-v3-btn--primary">Yayınla</button>
                                        </form>
                                    @elseif($post->status === 'published')
                                        <form method="post" action="{{ route('admin.blog.archive', $post) }}">
                                            @csrf
                                            <button class="hc-v3-btn hc-v3-btn--danger">Arşivle</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="hc-admin-empty">Blog yazısı yok.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-5">{{ $posts->links() }}</div>
        </div>
    </section>
</x-layouts.site>
