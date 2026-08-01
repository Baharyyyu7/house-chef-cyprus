<x-layouts.site title="AI Mutfak Asistanı" robots="noindex,nofollow">
    @php($result = session('ai_kitchen_result'))
    <section class="hc-container py-10 sm:py-14">
        <div class="max-w-3xl">
            <p class="hc-eyebrow">Kişisel mutfak desteği</p>
            <h1 class="hc-heading-1 mt-3">AI Mutfak Asistanı</h1>
            <p class="mt-4 text-lg text-muted">Tarif kataloğu ve dolabınıza göre öneri alın. AI çıktıları yayınlanmış tarif veya uzman tavsiyesi değildir.</p>
        </div>

        @if($result)
            <section class="hc-admin-form-card mt-8" aria-live="polite">
                <div class="flex flex-wrap items-center justify-between gap-3"><h2 class="hc-heading-4">Son öneri</h2><span class="hc-status-badge">{{ $result['cache_hit'] ? 'Önbellekten' : 'Yeni yanıt' }}</span></div>
                <p class="mt-4 leading-7 text-muted">{{ $result['answer'] }}</p>
                @if($result['safety_events'])<div class="hc-alert hc-alert-error mt-5" role="note">@foreach($result['safety_events'] as $event)<p>{{ $event['message'] }}</p>@endforeach</div>@endif
                @if(data_get($result, 'data.recipes'))<div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@foreach(data_get($result, 'data.recipes') as $recipe)<a href="{{ route('recipes.show', $recipe['slug']) }}" class="rounded-xl border p-4 transition hover:border-orange-400"><strong>{{ $recipe['title'] }}</strong><span class="mt-1 block text-sm text-muted">{{ $recipe['minutes'] }} dk · {{ $recipe['servings'] }} porsiyon</span></a>@endforeach</div>@endif
                @if(data_get($result, 'data.items'))<ul class="mt-5 grid gap-2 sm:grid-cols-2">@foreach(data_get($result, 'data.items') as $item)<li class="rounded-xl border px-4 py-3"><strong>{{ $item['ingredient'] }}</strong><span class="ml-2 text-sm text-muted">{{ $item['count'] }} tarif</span></li>@endforeach</ul>@endif
            </section>
        @endif

        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            <section class="hc-admin-form-card"><h2 class="hc-heading-4">Mutfak sorusu</h2><form class="mt-5 grid gap-3" method="post" action="{{ route('ai-kitchen.assistant') }}">@csrf<label class="text-sm font-bold" for="ai-question">Ne hazırlamak istiyorsunuz?</label><textarea id="ai-question" name="question" class="hc-input min-h-28" maxlength="1000" required placeholder="Örn. Akşam için pratik bir sebze yemeği arıyorum."></textarea><button class="hc-btn hc-btn-primary w-fit">Güvenli öneri al</button></form></section>
            <section class="hc-admin-form-card"><h2 class="hc-heading-4">Dolabım ve artanlar</h2><form class="mt-5" method="post" action="{{ route('ai-kitchen.pantry') }}">@csrf<button class="hc-btn hc-btn-outline">Dolabıma göre öner</button></form><form class="mt-5 grid gap-3" method="post" action="{{ route('ai-kitchen.leftovers') }}">@csrf<label class="text-sm font-bold">Artan malzemeler</label><input class="hc-input" name="leftovers[]" required placeholder="Örn. haşlanmış tavuk"><input class="hc-input" name="leftovers[]" placeholder="Örn. pilav"><button class="hc-btn hc-btn-outline w-fit">Artanları değerlendir</button></form></section>
            <section class="hc-admin-form-card"><h2 class="hc-heading-4">Menü ve alışveriş</h2><form class="mt-5 grid gap-3 sm:grid-cols-2" method="post" action="{{ route('ai-kitchen.meal-plan') }}">@csrf<label class="text-sm font-bold">Süre<select class="hc-input mt-1" name="days"><option value="7">7 gün</option><option value="14">14 gün</option><option value="30">30 gün</option></select></label><label class="text-sm font-bold">Maksimum kalori<input class="hc-input mt-1" name="max_calories" min="1" type="number"></label><button class="hc-btn hc-btn-primary w-fit sm:col-span-2">Taslak menü oluştur</button></form>@if($plans->isNotEmpty())<div class="mt-5 grid gap-2">@foreach($plans as $plan)<form method="post" action="{{ route('ai-kitchen.shopping', $plan) }}">@csrf<button class="text-sm font-bold text-orange-700 hover:underline">{{ $plan->days }} günlük taslak için alışveriş listesi oluştur →</button></form>@endforeach</div>@endif</section>
            <section class="hc-admin-form-card"><h2 class="hc-heading-4">Hesap verebilir kullanım</h2><p class="mt-3 text-sm leading-6 text-muted">Yanıtlar kullanıcı oturumunuzla sınırlı bellekte tutulur; anahtarlar, ham sağlayıcı hataları ve hassas veriler gösterilmez. Her kullanım güvenli bir audit kaydı oluşturur.</p><div class="mt-5 space-y-2 text-sm">@forelse($recent as $item)<div class="flex justify-between gap-3 rounded-xl border px-3 py-2"><span>{{ $item->purpose }}</span><span class="text-muted">{{ $item->cache_hit ? 'Önbellek' : $item->provider }}</span></div>@empty<p class="text-muted">Henüz AI kullanım kaydı yok.</p>@endforelse</div></section>
        </div>
    </section>
</x-layouts.site>
