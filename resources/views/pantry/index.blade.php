<x-layouts.site title="Malzeme Dolabım">
    <section class="hc-container hc-page">
        <div class="grid gap-8 lg:grid-cols-[.85fr_1.15fr]">
            <aside>
                <p class="hc-kicker">Kişisel mutfak</p>
                <h1 class="hc-title mt-2">Malzeme dolabım</h1>
                <p class="hc-subtitle">Ev ölçüleriyle ekleyin; uygun tarifleri canonical değerlerle eşleştirelim.</p>
                <a href="{{ route('ingredients.explore', ['use_pantry' => 1]) }}" class="hc-btn hc-btn-gold mt-6">Dolabımla eşleştir →</a>
            </aside>
            <div>
                @if (session('status')) <x-ui.alert type="success">{{ session('status') }}</x-ui.alert> @endif
                @if ($errors->any()) <x-ui.alert type="error" class="mt-4">{{ $errors->first() }}</x-ui.alert> @endif

                <form class="hc-panel mt-4 grid gap-3 sm:grid-cols-2" method="post" action="{{ route('pantry.store') }}">
                    @csrf
                    <div class="sm:col-span-2 flex items-center justify-between gap-3">
                        <h2 class="font-display text-2xl font-semibold">{{ $editingItem ? 'Dolap kaydını düzenle' : 'Dolaba malzeme ekle' }}</h2>
                        @if ($editingItem)
                            <a class="text-sm font-bold text-gold-700" href="{{ route('pantry.index') }}">← Düzenlemeyi iptal et</a>
                        @endif
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-sm font-bold" for="ingredient_id">Malzeme</label>
                        <select id="ingredient_id" class="hc-input mt-1 w-full" name="ingredient_id" required>
                            <option value="">Malzeme seçin</option>
                            @foreach ($ingredients as $ingredient)
                                <option value="{{ $ingredient->id }}" @selected(old('ingredient_id', $editingItem?->ingredient_id) == $ingredient->id)>{{ $ingredient->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-bold" for="quantity">Miktar</label>
                        <input id="quantity" class="hc-input mt-1 w-full" type="number" name="quantity" min=".0001" step=".0001" value="{{ old('quantity', $editingItem?->quantity) }}" required>
                    </div>
                    <div>
                        <label class="text-sm font-bold" for="unit">Ölçü birimi</label>
                        <select id="unit" class="hc-input mt-1 w-full" name="unit" required>
                            <option value="">Ölçü seçin</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->code }}" @selected(old('unit', $editingItem?->unit) === $unit->code)>{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-bold" for="expires_at">Son kullanma</label>
                        <input id="expires_at" class="hc-input mt-1 w-full" type="date" name="expires_at" value="{{ old('expires_at', $editingItem?->expires_at?->format('Y-m-d')) }}">
                    </div>
                    <div>
                        <label class="text-sm font-bold" for="notes">Not</label>
                        <input id="notes" class="hc-input mt-1 w-full" name="notes" value="{{ old('notes', $editingItem?->notes) }}" placeholder="İsteğe bağlı not">
                    </div>
                    <div class="sm:col-span-2 rounded-xl bg-[var(--hc-surface-soft)] p-3 text-sm text-muted">
                        @if ($editingItem?->canonical_quantity)
                            Mevcut canonical karşılık: yaklaşık {{ $editingItem->canonical_quantity }} {{ $editingItem->canonical_unit }} · {{ $editingItem->conversion_confidence === 'verified' ? 'doğrulanmış' : 'güven seviyesi: '.$editingItem->conversion_confidence }}.
                        @else
                            Malzeme, miktar ve ölçü seçildiğinde güvenli dönüşüm kaydedilir. Dönüşüm yoksa “Canonical karşılık bulunamadı” olarak korunur.
                        @endif
                    </div>
                    @if (old('expires_at', $editingItem?->expires_at?->format('Y-m-d')) && old('expires_at', $editingItem?->expires_at?->format('Y-m-d')) < now()->toDateString())
                        <div class="sm:col-span-2 text-sm font-semibold text-amber-700">Bu son kullanma tarihi geçmiş; kayıt saklanır ancak eşleştirmede kullanılmaz.</div>
                    @endif
                    <div class="sm:col-span-2"><button class="hc-btn hc-btn-primary w-full">{{ $editingItem ? 'Dolabı güncelle' : 'Dolaba ekle' }}</button></div>
                </form>

                <div class="mt-7">
                    <h2 class="font-display text-3xl font-semibold">Dolaptakiler</h2>
                    <div class="mt-4 space-y-3">
                        @forelse ($items as $item)
                            <article class="hc-card flex items-center justify-between gap-4">
                                <div>
                                    <h3 class="font-semibold">{{ $item->ingredient->name }}</h3>
                                    <p class="mt-1 text-sm text-muted">
                                        {{ $item->quantity }} {{ $item->unit }}
                                        @if ($item->canonical_quantity) · yaklaşık {{ $item->canonical_quantity }} {{ $item->canonical_unit }} @else · miktar doğrulanamadı @endif
                                        @if ($item->expires_at) · SKT: {{ $item->expires_at->format('d.m.Y') }} @endif
                                    </p>
                                </div>
                                <div class="flex gap-3">
                                    <a class="text-sm font-bold text-gold-700" href="{{ route('pantry.index', ['edit' => $item->id]) }}">Düzenle</a>
                                    <form method="post" action="{{ route('pantry.destroy', $item) }}">@csrf @method('delete')<button class="text-sm font-bold text-red-700">Kaldır</button></form>
                                </div>
                            </article>
                        @empty
                            <div class="hc-empty">Dolabınız henüz boş.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.site>
