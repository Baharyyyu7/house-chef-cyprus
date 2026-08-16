<x-layouts.site title="AI Mutfak Asistanı" robots="noindex,nofollow">
    @php($result = session('ai_kitchen_result'))
    <section class="hc-container py-6 sm:py-10">
        <div class="hc-workspace-hero">
            <div class="hc-workspace-hero__inner">
                <p class="hc-workspace-hero__eyebrow">Kişisel mutfak desteği</p>
                <h1 class="hc-workspace-hero__title">Bugün mutfakta<br><span class="text-orange-300">neye ihtiyacınız var?</span></h1>
                <p class="hc-workspace-hero__copy">Elinizdekilerden başlayın, haftayı planlayın veya artanları değerlendirin. Öneriler House Chef Cyprus kataloğuna dayanır; yayınlanmış tarif ve uzman tavsiyesinin yerine geçmez.</p>
            </div>
        </div>

        {{-- Birincil eylem tek ve büyük: kullanıcı sayfaya gelince nereden başlayacağını aramasın. --}}
        <section class="hc-ai-primary mt-8" x-data="{ busy: false }">
            <label class="hc-overline" style="color: var(--hc-text-accent)" for="ai-question">Sorunuzu yazın</label>
            <form class="mt-3" method="post" action="{{ route('ai-kitchen.assistant') }}" x-on:submit="busy = true">
                @csrf
                <textarea id="ai-question" name="question" class="hc-v3-input min-h-28 w-full text-base" maxlength="1000" required placeholder="Örn. Akşam için pratik bir sebze yemeği arıyorum. Elimde patlıcan ve domates var."></textarea>
                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <button type="submit" class="hc-v3-btn hc-v3-btn--primary" x-bind:disabled="busy">
                        <span x-show="!busy">Öneri al</span>
                        <span x-show="busy" x-cloak>Hazırlanıyor…</span>
                    </button>
                    <span class="text-xs" style="color: var(--hc-text-muted)">Yanıt birkaç saniye sürebilir.</span>
                </div>
            </form>
        </section>

        @if($result)
            <section id="ai-result" class="hc-v3-card mt-8 p-5 sm:p-6" aria-live="polite" tabindex="-1">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="hc-heading-3">Öneri</h2>
                    <span class="hc-v3-badge">{{ data_get($result, 'cache_hit') ? 'Önbellekten' : 'Yeni yanıt' }}</span>
                </div>
                <p class="hc-body-md mt-4 leading-7" style="color: var(--hc-text-secondary)">{{ data_get($result, 'answer') }}</p>
                @if(data_get($result, 'safety_events'))
                    <div class="hc-v3-alert hc-v3-alert--warning mt-5" role="note"><span aria-hidden="true">ⓘ</span><div>@foreach(data_get($result, 'safety_events', []) as $event)<p>{{ data_get($event, 'message') }}</p>@endforeach</div></div>
                @endif
                @if(data_get($result, 'data.recipes'))
                    <p class="hc-v3-field-label mt-6">Kataloğumuzdan eşleşen tarifler</p>
                    <div class="mt-2 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@foreach(data_get($result, 'data.recipes', []) as $recipe)<a href="{{ route('recipes.show', data_get($recipe, 'slug')) }}" class="rounded-2xl border p-4 transition hover:border-orange-400" style="border-color: var(--hc-border-default)"><strong>{{ data_get($recipe, 'title') }}</strong><span class="mt-1 block text-sm" style="color: var(--hc-text-muted)">{{ data_get($recipe, 'minutes') }} dk · {{ data_get($recipe, 'servings') }} porsiyon</span></a>@endforeach</div>
                @endif
                @if(data_get($result, 'data.items'))
                    <ul class="mt-5 grid gap-2 sm:grid-cols-2">@foreach(data_get($result, 'data.items', []) as $item)<li class="rounded-2xl border px-4 py-3" style="border-color: var(--hc-border-default)"><strong>{{ data_get($item, 'ingredient') }}</strong><span class="ml-2 text-sm" style="color: var(--hc-text-muted)">{{ data_get($item, 'count') }} tarif</span></li>@endforeach</ul>
                @endif
            </section>
        @endif

        <h2 class="hc-heading-3 mt-10">Hazır akışlar</h2>
        <p class="hc-body-sm mt-2" style="color: var(--hc-text-muted)">Soru yazmak yerine tek tıkla başlayabileceğiniz üç yol.</p>

        <div class="mt-5 grid gap-6 lg:grid-cols-2">
            <section class="hc-v3-card p-5 sm:p-6" x-data="{ busy: false }">
                <h3 class="hc-heading-4">Dolabımdakilerle</h3>
                <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">Malzeme dolabınıza kayıtlı, süresi geçmemiş ürünlere göre öneri üretir.</p>
                <form class="mt-4" method="post" action="{{ route('ai-kitchen.pantry') }}" x-on:submit="busy = true">
                    @csrf
                    <button type="submit" class="hc-v3-btn hc-v3-btn--outline" x-bind:disabled="busy"><span x-show="!busy">Dolabıma göre öner</span><span x-show="busy" x-cloak>Hazırlanıyor…</span></button>
                </form>
                <a class="mt-2 inline-flex min-h-11 items-center text-xs font-extrabold" style="color: var(--hc-text-accent)" href="{{ route('pantry.index') }}">Dolabımı düzenle →</a>
            </section>

            <section class="hc-v3-card p-5 sm:p-6" x-data="{ busy: false, rows: 2, max: 8 }">
                <h3 class="hc-heading-4">Artanları değerlendir</h3>
                <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">Dün akşamdan kalanları yazın; israf etmeden ne yapabileceğinizi önerelim.</p>
                <form class="mt-4 grid gap-3" method="post" action="{{ route('ai-kitchen.leftovers') }}" x-on:submit="busy = true">
                    @csrf
                    <label class="hc-v3-field-label" for="leftover-1">Artan malzemeler</label>
                    <input id="leftover-1" class="hc-v3-input" name="leftovers[]" required placeholder="Örn. haşlanmış tavuk">
                    <template x-for="row in rows - 1" :key="row">
                        <input class="hc-v3-input" name="leftovers[]" x-bind:placeholder="row === 1 ? 'Örn. pilav' : 'Bir malzeme daha'" x-bind:aria-label="'Artan malzeme ' + (row + 1)">
                    </template>
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" class="hc-v3-btn hc-v3-btn--ghost" x-show="rows < max" x-on:click="rows++">+ Malzeme ekle</button>
                        <button type="submit" class="hc-v3-btn hc-v3-btn--outline" x-bind:disabled="busy"><span x-show="!busy">Artanları değerlendir</span><span x-show="busy" x-cloak>Hazırlanıyor…</span></button>
                    </div>
                </form>
            </section>

            <section class="hc-v3-card p-5 sm:p-6 lg:col-span-2" x-data="{ busy: false }">
                <h3 class="hc-heading-4">Haftalık menü taslağı</h3>
                <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">Seçtiğiniz süre için taslak bir menü çıkarır. Taslak oluştuktan sonra alışveriş listesine çevirebilirsiniz.</p>
                <form class="mt-4 grid gap-3 sm:grid-cols-[10rem_12rem_auto] sm:items-end" method="post" action="{{ route('ai-kitchen.meal-plan') }}" x-on:submit="busy = true">
                    @csrf
                    <label class="hc-v3-field-label" for="plan-days">Süre
                        <select id="plan-days" class="hc-v3-input mt-1 w-full" name="days"><option value="7">7 gün</option><option value="14">14 gün</option><option value="30">30 gün</option></select>
                    </label>
                    <label class="hc-v3-field-label" for="plan-calories">Günlük maksimum kalori
                        <input id="plan-calories" class="hc-v3-input mt-1 w-full" name="max_calories" min="1" type="number" placeholder="İsteğe bağlı">
                    </label>
                    <button type="submit" class="hc-v3-btn hc-v3-btn--primary" x-bind:disabled="busy"><span x-show="!busy">Taslak menü oluştur</span><span x-show="busy" x-cloak>Hazırlanıyor…</span></button>
                </form>
                @if(optional($plans)->isNotEmpty())
                    <div class="mt-5 border-t pt-4" style="border-color: var(--hc-border-subtle)">
                        <p class="hc-v3-field-label">Oluşturduğunuz taslaklar</p>
                        <div class="mt-2 grid gap-2">@foreach($plans as $plan)<form method="post" action="{{ route('ai-kitchen.shopping', $plan) }}">@csrf<button type="submit" class="text-sm font-extrabold hover:underline" style="color: var(--hc-text-accent)">{{ $plan->days }} günlük taslak için alışveriş listesi oluştur →</button></form>@endforeach</div>
                    </div>
                @endif
            </section>
        </div>

        <section class="hc-v3-card mt-6 p-5 sm:p-6">
            <h2 class="hc-heading-4">Hesap verebilir kullanım</h2>
            <p class="hc-body-sm mt-3 leading-6" style="color: var(--hc-text-secondary)">Yanıtlar kullanıcı oturumunuzla sınırlı bellekte tutulur; anahtarlar, ham sağlayıcı hataları ve hassas veriler gösterilmez. Her kullanım güvenli bir audit kaydı oluşturur.</p>
            <div class="mt-5 space-y-2 text-sm">@forelse($recent ?? [] as $item)<div class="flex justify-between gap-3 rounded-xl border px-3 py-2" style="border-color: var(--hc-border-subtle)"><span>{{ $item->purpose }}</span><span style="color: var(--hc-text-muted)">{{ $item->cache_hit ? 'Önbellek' : $item->provider }}</span></div>@empty<p style="color: var(--hc-text-muted)">Henüz AI kullanım kaydı yok.</p>@endforelse</div>
        </section>
    </section>

    @if($result)
        {{-- Yanıt geldiğinde kullanıcıyı sonuca taşı: aksi halde sayfa başında kalıp bir şey olmadı sanıyor. --}}
        <script>
            (function () {
                var target = document.getElementById('ai-result');
                if (!target) { return; }
                target.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' });
                target.focus({ preventScroll: true });
            })();
        </script>
    @endif
</x-layouts.site>
