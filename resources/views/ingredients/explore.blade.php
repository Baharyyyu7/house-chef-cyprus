<x-layouts.site
    title="Ne Pişirebilirim? · Elinizdeki malzemelerle tarif bulun"
    meta-description="Buzdolabınızdaki malzemeleri yazın, hangi tarifleri tam olarak yapabileceğinizi ve neyin eksik olduğunu açıkça görün. Kıbrıs ve Akdeniz mutfağından doğrulanmış tarifler."
    :canonical-url="route('ingredients.explore')">
    @php
        $activeFilters = collect(request()->except(['page', 'ingredients', 'excluded']))->filter(fn ($value) => filled($value) || is_array($value) && $value !== []);
        $exactCount = $results->where('can_cook_exactly', true)->count();
        $substitutionCount = $results->where('can_cook_with_substitutions', true)->count();
        $missingCount = $results->where('cannot_cook', true)->count();
        $normalize = static fn (string $value): string => trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9 ]/', ' ', strtr(mb_strtolower($value, 'UTF-8'), ['ı' => 'i', 'ğ' => 'g', 'ü' => 'u', 'ş' => 's', 'ö' => 'o', 'ç' => 'c', 'â' => 'a', 'î' => 'i', 'û' => 'u']))) ?? '');
        $ingredientOptions = $ingredients->map(fn ($ingredient) => [
            'id' => $ingredient->public_id,
            'name' => $ingredient->name,
            'search' => $normalize($ingredient->name.' '.collect($ingredient->getRelation('aliases') ?? [])->pluck('alias')->join(' ')),
        ])->values()->all();
    @endphp
    <section class="relative isolate overflow-hidden bg-charcoal-950 text-white">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_78%_18%,rgba(255,102,0,.28),transparent_30%),linear-gradient(135deg,#111913,#17251d_48%,#20160e)]" aria-hidden="true"></div>
        <div class="hc-container py-12 sm:py-16">
            <p class="hc-overline text-orange-300">Açıklanabilir eşleştirme</p>
            <h1 class="hc-display-xl mt-4 max-w-3xl">Elinizde ne varsa,<br><span class="text-orange-400">onunla pişirin.</span></h1>
            <p class="hc-body-lg mt-5 max-w-2xl text-stone-200">Malzemelerinizi yazın; hangi tarifi eksiksiz yapabileceğinizi, nerede alternatif kullanabileceğinizi ve neyin eksik olduğunu tek tek gösterelim.</p>
            @guest<p class="mt-6 text-sm text-stone-300">Üyelik gerekmez. <a class="font-extrabold text-orange-300 underline" href="{{ route('register') }}">Üye olursanız</a> malzeme dolabınız da eşleştirmeye katılır.</p>@endguest
        </div>
    </section>
    <section class="hc-container hc-page">
        <div class="grid gap-6 xl:grid-cols-[minmax(20rem,.8fr)_minmax(0,1.5fr)]">
            <aside class="xl:sticky xl:top-24 xl:self-start">
                {{-- Malzeme girişi sayfanın birincil eylemi; hiçbir ekran genişliğinde katlanmaz. --}}
                <form method="get" class="hc-panel space-y-6" aria-label="Malzemelerle tarif eşleştirme">
                    <div x-data="hcIngredientPicker(@js($ingredientOptions), @js($selected))">
                        <div class="flex items-center justify-between gap-3">
                            <div><h2 class="font-display text-2xl font-semibold">Malzemeler</h2><p class="mt-1 text-sm text-muted">Yazın, listeden seçin. Takma adlar da aranır.</p></div>
                            @auth<a href="{{ route('pantry.index') }}" class="text-sm font-bold" style="color: var(--hc-text-accent)">Dolabım</a>@endauth
                        </div>

                        @auth
                            <label class="mt-4 flex items-center gap-2 rounded-xl bg-[var(--hc-surface-soft)] p-3 text-sm font-semibold"><input name="use_pantry" type="checkbox" value="1" @checked(request('use_pantry'))> Dolabımdaki {{ $availablePantryCount }} kullanılabilir ürünü seç</label>
                            @if($expiredPantryCount > 0)<p class="mt-2 text-xs text-amber-700 dark:text-amber-300">{{ $expiredPantryCount }} süresi geçmiş ürün eşleştirmeye dahil edilmez.</p>@endif
                        @endauth

                        <div class="relative mt-4">
                            <label class="hc-v3-field-label" for="ingredient-search">Malzeme ara</label>
                            <input id="ingredient-search" class="hc-v3-input mt-1 w-full" type="search" autocomplete="off" placeholder="Örn. domates, peynir, bulgur"
                                x-model="query"
                                x-on:keydown.arrow-down.prevent="move(1)"
                                x-on:keydown.arrow-up.prevent="move(-1)"
                                x-on:keydown.enter.prevent="commit()"
                                x-on:keydown.escape="query = ''"
                                role="combobox" aria-controls="ingredient-results" aria-autocomplete="list"
                                x-bind:aria-expanded="matches.length > 0 ? 'true' : 'false'">
                            <ul id="ingredient-results" class="hc-ingredient-results" x-show="matches.length > 0" x-cloak x-transition.opacity role="listbox">
                                <template x-for="(option, index) in matches" :key="option.id">
                                    <li role="option" x-bind:aria-selected="index === highlighted ? 'true' : 'false'">
                                        <button type="button" class="hc-ingredient-results__item" x-bind:class="index === highlighted ? 'is-active' : ''" x-on:click="add(option.id)" x-on:mouseenter="highlighted = index" x-text="option.name"></button>
                                    </li>
                                </template>
                            </ul>
                            <p class="mt-2 text-xs" style="color: var(--hc-text-muted)" x-show="query.length > 0 && matches.length === 0" x-cloak>Eşleşen malzeme yok.</p>
                        </div>

                        <div class="mt-4" x-show="chosen.length > 0" x-cloak>
                            <div class="flex items-center justify-between gap-2">
                                <span class="hc-v3-field-label"><span x-text="chosen.length"></span> malzeme seçildi</span>
                                <button type="button" class="text-xs font-extrabold" style="color: var(--hc-text-accent)" x-on:click="clear()">Hepsini kaldır</button>
                            </div>
                            <ul class="mt-2 flex flex-wrap gap-2">
                                <template x-for="option in chosen" :key="option.id">
                                    <li><button type="button" class="hc-ingredient-chip" x-on:click="remove(option.id)" x-bind:aria-label="option.name + ' malzemesini kaldır'"><span x-text="option.name"></span><span aria-hidden="true">×</span></button></li>
                                </template>
                            </ul>
                        </div>

                        <template x-for="id in selected" :key="id"><input type="hidden" name="ingredients[]" x-bind:value="id"></template>

                        @if($popularIngredients->isNotEmpty())
                            <div class="mt-5" x-show="chosen.length === 0" x-cloak>
                                <p class="hc-v3-field-label">Sık kullanılanlar</p>
                                <ul class="mt-2 flex flex-wrap gap-2">
                                    @foreach($popularIngredients as $popular)
                                        <li><button type="button" class="hc-ingredient-chip hc-ingredient-chip--add" x-on:click="add('{{ $popular->public_id }}')">{{ $popular->name }} <span aria-hidden="true">+</span></button></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <noscript>
                            <label class="hc-v3-field-label mt-4 block" for="ingredients">Malzeme seçin</label>
                            <select id="ingredients" class="hc-v3-input mt-1 min-h-48 w-full" name="ingredients[]" multiple>@foreach($ingredients as $ingredient)<option value="{{ $ingredient->public_id }}" @selected(in_array($ingredient->public_id, $selected, true))>{{ $ingredient->name }}</option>@endforeach</select>
                        </noscript>
                    </div>
                    <details class="group border-t border-[var(--hc-border)] pt-4" open><summary class="cursor-pointer font-display text-xl font-semibold">Tercihler</summary><div class="mt-4 grid gap-3 text-sm"><label class="flex gap-2"><input name="exact_only" type="checkbox" value="1" @checked(request('exact_only'))><span><strong>Tam yapılabilir</strong><small class="block text-muted">Zorunlu eksik malzemesi olmayanlar.</small></span></label><label class="flex gap-2"><input name="include_substitutions" type="checkbox" value="1" @checked(request()->boolean('include_substitutions', true))><span><strong>Alternatifleri kullan</strong><small class="block text-muted">Doğrulanmış alternatifler ayrıca açıklanır.</small></span></label><label for="max_missing"><span class="font-semibold">Maksimum eksik</span><select id="max_missing" class="hc-input mt-1 w-full" name="max_missing">@foreach([0, 1, 2, 3] as $missing)<option value="{{ $missing }}" @selected((string) request('max_missing', '2') === (string) $missing)>{{ $missing }} malzeme</option>@endforeach</select></label><label for="max_minutes"><span class="font-semibold">En fazla süre</span><select id="max_minutes" class="hc-input mt-1 w-full" name="max_minutes"><option value="">Her süre</option>@foreach([20,40,60] as $minutes)<option value="{{ $minutes }}" @selected((string) request('max_minutes') === (string) $minutes)>{{ $minutes }} dk</option>@endforeach</select></label><label for="sort"><span class="font-semibold">Sırala</span><select id="sort" class="hc-input mt-1 w-full" name="sort"><option value="newest">Önerilen</option><option value="time" @selected(request('sort') === 'time')>En kısa süre</option></select></label></div></details>
                    <details class="group border-t border-[var(--hc-border)] pt-4"><summary class="cursor-pointer font-display text-xl font-semibold">Beslenme ve güvenlik</summary><div class="mt-4 grid gap-3 text-sm"><label for="min_protein"><span class="font-semibold">En az protein (g)</span><input id="min_protein" class="hc-input mt-1 w-full" type="number" min="0" step="0.1" name="min_protein" value="{{ request('min_protein') }}"></label><label for="max_calories"><span class="font-semibold">En çok kalori</span><input id="max_calories" class="hc-input mt-1 w-full" type="number" min="1" name="max_calories" value="{{ request('max_calories') }}"></label><label class="flex gap-2"><input name="verified_only" type="checkbox" value="1" @checked(request('verified_only'))><span>Doğrulanmış beslenme verisi</span></label><label for="dietary_tag"><span class="font-semibold">Diyet tercihi</span><select id="dietary_tag" class="hc-input mt-1 w-full" name="dietary_tag"><option value="">Fark etmez</option>@foreach($dietaryTags as $tag)<option value="{{ $tag->code }}" @selected(request('dietary_tag') === $tag->code)>{{ $tag->name }}</option>@endforeach</select></label><label for="allergens"><span class="font-semibold">Alerjen hariç tut</span><select id="allergens" class="hc-input mt-1 min-h-24 w-full" name="exclude_allergens[]" multiple>@foreach($allergens as $allergen)<option value="{{ $allergen->public_id }}" @selected(in_array($allergen->public_id, request()->input('exclude_allergens', []) ?: [], true))>{{ $allergen->name }}</option>@endforeach</select></label></div></details>
                    <div class="flex gap-3"><button class="hc-v3-btn hc-v3-btn--primary flex-1" type="submit">Tarifleri bul</button><a class="hc-v3-btn hc-v3-btn--outline" href="{{ route('ingredients.explore') }}">Temizle</a></div>
                </form>
            </aside>
            <div>
                @if($activeFilters->isNotEmpty())<div class="mb-5 flex flex-wrap items-center gap-2" aria-label="Aktif filtreler"><span class="text-sm font-semibold">Seçili:</span>@foreach($activeFilters as $key => $value)<a class="hc-v3-chip" href="{{ route('ingredients.explore', request()->except($key)) }}" aria-label="{{ $key }} filtresini kaldır">{{ is_array($value) ? count($value).' seçim' : $value }} ×</a>@endforeach</div>@endif
                @if($selected !== [] || request()->boolean('use_pantry'))
                    <div class="flex flex-wrap items-end justify-between gap-4"><div><p class="hc-kicker">Eşleşme sonucu</p><h2 class="mt-2 font-display text-4xl font-semibold">Mutfağınıza uygun tarifler</h2></div><span class="hc-badge">{{ $results->count() }} sonuç</span></div>
                    <div class="mt-6 grid gap-3 sm:grid-cols-3"><div class="hc-v3-card"><strong class="text-2xl">{{ $exactCount }}</strong><p class="text-sm text-muted">Tam yapılabilir</p></div><div class="hc-v3-card"><strong class="text-2xl">{{ $substitutionCount }}</strong><p class="text-sm text-muted">Alternatifle yapılabilir</p></div><div class="hc-v3-card"><strong class="text-2xl">{{ $missingCount }}</strong><p class="text-sm text-muted">Eksik malzemeli</p></div></div>
                    <div class="mt-6 grid gap-5">@forelse($results as $result)<article class="hc-v3-card hc-v3-card--interactive" x-data="{ open: false }"><div class="flex flex-wrap items-start justify-between gap-4"><div><p class="hc-kicker">{{ $result['status'] }}</p><h3 class="mt-2 hc-heading-3"><a class="before:absolute before:inset-0 focus-visible:rounded" href="{{ route('recipes.show', $result['recipe']->slug) }}">{{ $result['title'] }}</a></h3><p class="mt-2 text-sm text-muted">{{ $result['recipe']->cuisine?->name }} · {{ $result['recipe']->category?->name }} · {{ $result['recipe']->totalMinutes() }} dk</p></div><span class="hc-badge-gold relative z-10">{{ $result['effective_match_percentage'] }}% uyum</span></div><div class="mt-4 grid gap-3 text-sm sm:grid-cols-3"><p><strong>{{ $result['matched_required_count'] }}/{{ $result['required_count'] }}</strong><br><span class="text-muted">eşleşen zorunlu malzeme</span></p><p><strong>{{ $result['missing_required_count'] }}</strong><br><span class="text-muted">eksik malzeme</span></p><p><strong>{{ collect($result['quantity_results'])->where('status', 'insufficient')->count() }}</strong><br><span class="text-muted">miktar yetersiz</span></p></div><button class="mt-4 text-sm font-bold text-gold-700 relative z-10" type="button" @click="open = !open" :aria-expanded="open.toString()">Neden bu sonuç? <span x-text="open ? '−' : '+'"></span></button><div class="mt-3 grid gap-3 border-t border-[var(--hc-border)] pt-3 text-sm relative z-10" x-show="open" x-transition><p><strong>Eşleşenler:</strong> {{ $result['matched_required_count'] }} zorunlu malzeme bulundu.</p>@if($result['missing_required_ingredients'])<p><strong>Eksikler:</strong> {{ collect($result['missing_required_ingredients'])->pluck('name')->join(', ') }}</p>@endif@if($result['available_substitutions'])<p><strong>Alternatifler:</strong> @foreach($result['available_substitutions'] as $sub){{ $sub['ingredient'] }} → {{ $sub['substitute'] }}@if(!$loop->last), @endif@endforeach</p>@endif@if(collect($result['quantity_results'])->where('status', 'quantity_unknown')->isNotEmpty())<p>Miktarı doğrulanamayan ürünler için sonuç temkinli gösterilir.</p>@endif</div><div class="mt-5 flex flex-wrap gap-3 relative z-10"><a class="hc-v3-btn hc-v3-btn--outline" href="{{ route('recipes.show', $result['recipe']->slug) }}">Tarifi incele</a>@auth<form method="post" action="{{ route('favorites.toggle', $result['recipe']) }}">@csrf<button class="hc-v3-btn hc-v3-btn--ghost" aria-label="{{ $result['title'] }} tarifini favorilere ekle">♡ Favori</button></form>@endauth</div></article>@empty
                        @php($currentMissing = (int) request('max_missing', 2))
                        <div class="hc-v3-empty">
                            <strong>Bu tercihlerle sonuç bulunamadı.</strong>
                            <p class="mt-2">{{ count($selected) }} malzeme seçtiniz ve en fazla <strong>{{ $currentMissing }}</strong> eksik malzemeye izin veriyorsunuz. Az malzemeyle çok tarif eşleşmez; sınırı gevşetmek genelde işe yarar.</p>
                            <div class="mt-5 flex flex-wrap justify-center gap-3">
                                @if($currentMissing < 10)
                                    <a class="hc-v3-btn hc-v3-btn--primary" href="{{ route('ingredients.explore', array_merge(request()->except('page'), ['max_missing' => min(10, $currentMissing + 2)])) }}">{{ min(10, $currentMissing + 2) }} eksiğe kadar göster</a>
                                @endif
                                @if($activeFilters->isNotEmpty())
                                    <a class="hc-v3-btn hc-v3-btn--outline" href="{{ route('ingredients.explore', ['ingredients' => $selected]) }}">Filtreleri kaldır, malzemeleri koru</a>
                                @endif
                                <a class="hc-v3-btn hc-v3-btn--ghost" href="{{ route('ingredients.explore') }}">Baştan başla</a>
                            </div>
                        </div>
                    @endforelse</div>
                @else
                    <div class="hc-v3-empty">
                        <strong>Birkaç malzeme yazarak başlayın.</strong>
                        <p class="mt-2">Örneğin <em>domates</em>, <em>yumurta</em> ve <em>peynir</em> yazın; tam yapabileceğiniz tarifleri, alternatifle yapılabilenleri ve tek malzeme eksik olanları ayrı ayrı listeleriz.</p>
                        @auth
                            <a class="hc-v3-btn hc-v3-btn--outline mt-5" href="{{ route('pantry.index') }}">Malzeme dolabımı kullan</a>
                        @else
                            <a class="hc-v3-btn hc-v3-btn--outline mt-5" href="{{ route('recipes.index') }}">Tüm tarifleri gez</a>
                        @endauth
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-layouts.site>
