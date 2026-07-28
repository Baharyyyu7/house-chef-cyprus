<x-layouts.site title="Ölçü Birimleri">
    <section class="hc-container hc-admin-crud-page py-8 sm:py-10">
        <x-admin.page-header eyebrow="Tarif verisi" title="Household ölçü birimleri" description="Tarif, dolap ve porsiyon akışlarında kullanılan Türkçe ölçü sözlüğünü yönetin." :back="['url' => route('admin.dashboard'), 'label' => 'Yönetim']">
            <x-slot:actions><a class="hc-v3-btn hc-v3-btn--secondary" href="{{ route('admin.measurements.conversions') }}">Malzeme dönüşümleri</a></x-slot:actions>
        </x-admin.page-header>

        @if(session('status')) <x-ui.alert class="mt-5" type="success">{{ session('status') }}</x-ui.alert> @endif
        @if($errors->any()) <x-ui.alert class="mt-5" type="error">{{ $errors->first() }}</x-ui.alert> @endif

        <x-admin.filter-bar class="mt-6 grid-cols-1 sm:grid-cols-2 xl:grid-cols-6">
            <input class="hc-input" name="q" value="{{ request('q') }}" placeholder="Ad veya kod ara" aria-label="Ölçü ara">
            <select class="hc-input" name="category"><option value="">Tüm kategoriler</option>@foreach(['mass'=>'Ağırlık','volume'=>'Hacim','count'=>'Adet','household'=>'Ev ölçüsü','other'=>'Diğer'] as $key=>$label)<option value="{{ $key }}" @selected(request('category')===$key)>{{ $label }}</option>@endforeach</select>
            @foreach(['fractions'=>'Kesir desteği','active'=>'Durum','canonical'=>'Canonical','visible'=>'Kullanıcı görünürlüğü'] as $key=>$label)
                <select class="hc-input" name="{{ $key }}"><option value="">{{ $label }}</option><option value="1" @selected(request($key)==='1')>Evet</option><option value="0" @selected(request($key)==='0')>Hayır</option></select>
            @endforeach
        </x-admin.filter-bar>

        <div class="hc-admin-table-shell mt-6">
            <div class="hc-table-wrap"><table class="hc-table min-w-[940px]"><thead><tr><th>Ölçü</th><th>Kod</th><th>Kısa ad</th><th>Kategori</th><th>Kesir</th><th>Canonical</th><th>Görünürlük</th><th>Durum</th><th>Son güncelleme</th><th>Aksiyonlar</th></tr></thead><tbody>
                @forelse($units as $unit)
                    <tr>
                        <td><strong>{{ $unit->singular_name ?: $unit->name }}</strong>@if($unit->plural_name)<span class="block text-xs text-muted">{{ $unit->plural_name }}</span>@endif</td>
                        <td><code>{{ $unit->code }}</code></td><td>{{ $unit->short_label ?: $unit->symbol ?: '—' }}</td><td>{{ ['mass'=>'Ağırlık','volume'=>'Hacim','count'=>'Adet','household'=>'Ev ölçüsü','other'=>'Diğer'][$unit->unit_type] ?? $unit->unit_type }}</td>
                        <td><span class="hc-admin-status {{ $unit->supports_fractions ? 'is-success' : 'is-muted' }}">{{ $unit->supports_fractions ? 'Destekler' : 'Yok' }}</span></td>
                        <td><span class="hc-admin-status {{ $unit->is_canonical ? 'is-info' : 'is-muted' }}">{{ $unit->is_canonical ? 'Evet' : 'Hayır' }}</span></td>
                        <td>{{ $unit->is_user_visible ? 'Kullanıcıya açık' : 'Yalnız yönetim' }}</td><td><span class="hc-admin-status {{ $unit->is_active ? 'is-success' : 'is-muted' }}">{{ $unit->is_active ? 'Aktif' : 'Pasif' }}</span></td>
                        <td>{{ $unit->updated_at?->format('d.m.Y') }}</td><td class="whitespace-nowrap"><details><summary class="hc-v3-btn hc-v3-btn--ghost">Düzenle</summary><form class="hc-admin-form-card mt-2 w-80 p-3" method="post" action="{{ route('admin.measurements.units.update',$unit) }}">@csrf @method('PUT')<input class="hc-input" name="singular_name" value="{{ $unit->singular_name ?: $unit->name }}" aria-label="Tekil ad"><input class="hc-input mt-2" name="plural_name" value="{{ $unit->plural_name }}" placeholder="Çoğul ad"><input class="hc-input mt-2" name="short_label" value="{{ $unit->short_label }}" placeholder="Kısa ad"><input type="hidden" name="code" value="{{ $unit->code }}"><input type="hidden" name="unit_type" value="{{ $unit->unit_type }}"><input type="hidden" name="precision" value="{{ $unit->precision }}"><input type="hidden" name="sort_order" value="{{ $unit->sort_order }}"><label class="mt-2 block text-sm"><input type="checkbox" name="is_active" value="1" @checked($unit->is_active)> Aktif</label><label class="block text-sm"><input type="checkbox" name="is_user_visible" value="1" @checked($unit->is_user_visible)> Kullanıcıya görünür</label><button class="hc-v3-btn hc-v3-btn--primary mt-3" type="submit">Kaydet</button></form></details><form class="mt-2" method="post" action="{{ route('admin.measurements.units.toggle',$unit) }}">@csrf<button class="text-sm font-semibold text-gold-700">{{ $unit->is_active ? 'Pasifleştir' : 'Aktifleştir' }}</button></form></td>
                    </tr>
                @empty <tr><td colspan="10"><div class="hc-admin-empty">Bu filtrelerle eşleşen ölçü birimi yok.</div></td></tr>@endforelse
            </tbody></table></div><div class="mt-5">{{ $units->links() }}</div>
        </div>

        <section class="hc-admin-form-card mt-8" id="yeni-olcu"><div class="hc-admin-form-section"><h2>Yeni ölçü birimi</h2><p>Tekrarlanan kodlar engellenir; pasif ölçüler yeni kullanıcı kayıtlarında seçilmez.</p></div><form method="post" action="{{ route('admin.measurements.units.store') }}" class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">@csrf
            <input class="hc-input" name="singular_name" required placeholder="Türkçe tekil ad"><input class="hc-input" name="plural_name" placeholder="Türkçe çoğul ad"><input class="hc-input" name="short_label" placeholder="Kısa ad"><input class="hc-input" name="code" required placeholder="Kod (ör. yemek_kasigi)">
            <select class="hc-input" name="unit_type"><option value="household">Ev ölçüsü</option><option value="mass">Ağırlık</option><option value="volume">Hacim</option><option value="count">Adet</option><option value="other">Diğer</option></select><input class="hc-input" name="precision" type="number" min="0" max="6" value="2" aria-label="Hassasiyet"><input class="hc-input" name="sort_order" type="number" min="0" value="0" aria-label="Sıralama">
            <div class="flex flex-wrap gap-3 text-sm md:col-span-2"><label><input type="checkbox" name="supports_fractions" value="1"> Kesir desteği</label><label><input type="checkbox" name="is_active" value="1" checked> Aktif</label><label><input type="checkbox" name="is_user_visible" value="1" checked> Kullanıcıya görünür</label><label><input type="checkbox" name="is_canonical" value="1"> Canonical</label></div><div class="md:col-span-2 xl:col-span-4"><button class="hc-v3-btn hc-v3-btn--primary" type="submit">Ölçü birimini kaydet</button></div>
        </form></section>
    </section>
</x-layouts.site>
