<x-layouts.site title="Tarif Yönetimi">
    <section class="hc-admin-crud-page">
        <x-admin.page-header eyebrow="İçerik" title="Tarif yönetimi" description="Yayın, doğrulama ve veri tamlığını tek çalışma alanından yönetin.">
            <x-slot:actions><a class="hc-v3-btn hc-v3-btn--primary" href="{{ route('admin.recipes.create') }}">+ Yeni tarif</a></x-slot:actions>
        </x-admin.page-header>

        @if(session('status'))<x-ui.alert class="mt-5" variant="success">{{ session('status') }}</x-ui.alert>@endif

        <x-admin.filter-bar class="mt-6">
            <label class="sr-only" for="recipe-search">Tarif ara</label><input id="recipe-search" class="hc-input" name="q" value="{{ request('q') }}" placeholder="Tarif veya slug ara">
            <select class="hc-input" name="status"><option value="">Tüm durumlar</option>@foreach(['draft' => 'Taslak', 'published' => 'Yayında'] as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select>
            <select class="hc-input" name="verification_status"><option value="">Tüm doğrulamalar</option>@foreach(['verified'=>'Doğrulandı','needs_review'=>'İnceleme bekliyor','partially_verified'=>'Kısmi','unreviewed'=>'İncelenmedi'] as $key=>$label)<option value="{{ $key }}" @selected(request('verification_status') === $key)>{{ $label }}</option>@endforeach</select>
            <select class="hc-input" name="cuisine"><option value="">Tüm mutfaklar</option>@foreach($cuisines as $cuisine)<option value="{{ $cuisine->id }}" @selected((string) request('cuisine') === (string) $cuisine->id)>{{ $cuisine->name }}</option>@endforeach</select>
            <select class="hc-input" name="category"><option value="">Tüm kategoriler</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>
            <select class="hc-input" name="image_status"><option value="">Görsel: tümü</option>@foreach(['approved'=>'Onaylı','pending'=>'Bekliyor','missing'=>'Eksik'] as $key=>$label)<option value="{{ $key }}" @selected(request('image_status') === $key)>{{ $label }}</option>@endforeach</select>
            <select class="hc-input" name="sort"><option value="">Son güncellenen</option><option value="completeness" @selected(request('sort') === 'completeness')>Tamlık: düşükten</option><option value="title" @selected(request('sort') === 'title')>Ada göre</option></select>
        </x-admin.filter-bar>

        <div class="hc-admin-table-shell mt-6">
            <div class="hc-admin-table-shell__meta"><strong>{{ $recipes->total() }} tarif</strong><span>Her satır yayınlama için gerekli durumları gösterir.</span></div>
            <div class="hc-table-wrap"><table class="hc-table hc-admin-data-table"><thead><tr><th>Tarif</th><th>Durum</th><th>Veri güveni</th><th>İçerik</th><th>Son güncelleme</th><th>Aksiyonlar</th></tr></thead><tbody>
                @forelse($recipes as $recipe)
                    <tr>
                        <td><div class="font-extrabold text-[var(--hc-text-primary)]">{{ $recipe->title }}</div><div class="mt-1 text-xs text-muted">{{ $recipe->cuisine?->name ?? 'Mutfak yok' }} · {{ $recipe->category?->name ?? 'Kategori yok' }}</div></td>
                        <td><span class="hc-admin-status hc-admin-status--{{ $recipe->published_at ? 'success' : 'neutral' }}">{{ $recipe->published_at ? 'Yayında' : 'Taslak' }}</span><div class="mt-1 text-xs text-muted">{{ $recipe->verification_status ?: 'İncelenmedi' }}</div></td>
                        <td><span class="hc-admin-score">%{{ $recipe->completeness_score ?? 0 }}</span><div class="mt-1 text-xs text-muted">{{ $recipe->provenances_count }} kaynak · {{ $recipe->nutrition_snapshots_count }} snapshot</div></td>
                        <td><div class="text-xs text-muted">{{ $recipe->recipe_ingredients_count }} malzeme · {{ $recipe->steps_count }} adım · {{ $recipe->media_count }} medya</div></td>
                        <td><time class="text-xs text-muted" datetime="{{ $recipe->updated_at?->toDateString() }}">{{ $recipe->updated_at?->diffForHumans() }}</time></td>
                        <td><div class="flex justify-end gap-2"><a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" href="{{ route('recipes.show', $recipe->slug) }}">Görüntüle</a><a class="hc-v3-btn hc-v3-btn--secondary hc-v3-btn--sm" href="{{ route('admin.recipes.edit', $recipe) }}">Düzenle</a></div></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="hc-admin-empty"><strong>Tarif bulunamadı</strong><p>Filtreleri temizleyin veya yeni bir tarif oluşturun.</p><a class="hc-v3-btn hc-v3-btn--primary" href="{{ route('admin.recipes.create') }}">Yeni tarif</a></div></td></tr>
                @endforelse
            </tbody></table></div>
            <div class="mt-5">{{ $recipes->links() }}</div>
        </div>
    </section>
</x-layouts.site>
