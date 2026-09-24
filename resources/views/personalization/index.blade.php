<x-layouts.site title="Sizin için">
    <section class="hc-container pt-10">
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-orange-600">Kişisel mutfak</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-stone-950 dark:text-white">Sizin için</h1>
        <p class="mt-2 max-w-2xl text-stone-600 dark:text-stone-300">Tercihleriniz yalnız sizin kontrolünüzdeki verilerle önerileri sıralar. Alerjen ve hariç tuttuğunuz malzemeler asla yumuşatılmaz.</p>
    </section>

    

    <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <p class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200" role="status">{{ session('status') }}</p>
        @endif

        <section class="hc-panel p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold">Kişiselleştirme kontrolü</h2>
                    <p class="mt-1 text-sm text-stone-600 dark:text-stone-300">Açık tercihleri kullanır; gizli profil veya sağlık çıkarımı yapmaz.</p>
                </div>
                @if ($profile->personalization_enabled)
                    <form method="POST" action="{{ route('personalization.disable') }}">
                        @csrf
                        <button class="hc-button-secondary" type="submit">Kişiselleştirmeyi kapat</button>
                    </form>
                @else
                    <div class="flex items-center gap-3"><p class="rounded-full bg-stone-100 px-3 py-1 text-sm font-medium dark:bg-stone-800">Kişiselleştirme kapalı</p><form method="POST" action="{{ route('personalization.enable') }}">@csrf<button class="hc-button-primary" type="submit">Yeniden aç</button></form></div>
                @endif
            </div>
        </section>

        @if ($profile->personalization_enabled)
            @if (! $profile->onboarding_completed)
                <section class="rounded-2xl border border-orange-200 bg-orange-50 p-6 dark:border-orange-900 dark:bg-orange-950"><h2 class="text-xl font-bold">Tercihlerinizi 2 dakikada tamamlayın</h2><p class="mt-1 text-sm">Bu adım isteğe bağlıdır. Sadece sizin seçtiğiniz tercihleri kullanırız.</p><a class="hc-button-primary mt-4 inline-flex" href="{{ route('personalization.onboarding') }}">Onboarding'i başlat</a></section>
            @endif
            <section aria-labelledby="personalized-recipes">
                <div class="mb-4 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-orange-600">Açıklanabilir öneriler</p>
                        <h2 id="personalized-recipes" class="text-2xl font-bold">Bugün ne pişirsem?</h2>
                    </div>
                    <a class="text-sm font-semibold underline" href="{{ route('ingredients.explore') }}">Dolabımla eşleştir</a>
                </div>
                @if ($recommendations->isEmpty())
                    <div class="hc-panel p-6 text-stone-600 dark:text-stone-300">Yeterli güvenli öneri bulunamadı. Tercihlerinizi güncelleyebilir veya genel tarifleri inceleyebilirsiniz.</div>
                @else
                    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($recommendations as $recommendation)
                            <article class="hc-panel overflow-hidden p-5">
                                <p class="text-sm font-medium text-orange-600">{{ $recommendation->recipe->cuisine?->name }}</p>
                                <h3 class="mt-2 text-xl font-bold"><a class="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-500" href="{{ route('recipes.show', $recommendation->recipe->slug) }}">{{ $recommendation->recipe->title }}</a></h3>
                                <ul class="mt-3 space-y-1 text-sm text-stone-600 dark:text-stone-300">
                                    @foreach ($recommendation->reason_codes as $reason)
                                        <li>• {{ $reasonLabels[$reason] ?? 'Güvenli yayın kurallarını karşılıyor.' }}</li>
                                    @endforeach
                                </ul>
                                <div class="mt-5 flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('personalization.feedback', $recommendation->recipe->public_id) }}">@csrf<input type="hidden" name="type" value="like"><input type="hidden" name="recommendation_public_id" value="{{ $recommendation->public_id }}"><button class="hc-button-secondary text-sm" type="submit">Beğendim</button></form>
                                    <form method="POST" action="{{ route('personalization.feedback', $recommendation->recipe->public_id) }}">@csrf<input type="hidden" name="type" value="hide_recipe"><input type="hidden" name="recommendation_public_id" value="{{ $recommendation->public_id }}"><button class="hc-button-secondary text-sm" type="submit">Gösterme</button></form>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        <section class="hc-panel p-6" aria-labelledby="preference-heading">
            <h2 id="preference-heading" class="text-xl font-bold">Tercihlerim</h2>
            <p class="mt-1 text-sm text-stone-600 dark:text-stone-300">Alerjen ve hariç tutulan malzeme alanları güvenlik filtresi olarak kullanılır; tıbbi tavsiye değildir.</p>
            <form class="mt-6 grid gap-4 md:grid-cols-2" method="POST" action="{{ route('personalization.update') }}">
                @csrf @method('PATCH')
                <label>Hane büyüklüğü <input class="hc-input mt-1 w-full" name="household_size" type="number" min="1" max="20" value="{{ old('household_size', $profile->household_size) }}"></label>
                <label>Varsayılan porsiyon <input class="hc-input mt-1 w-full" name="default_portions" type="number" min="1" max="20" value="{{ old('default_portions', $profile->default_portions) }}"></label>
                <label>Tercih edilen en uzun süre (dk) <input class="hc-input mt-1 w-full" name="preferred_duration_max" type="number" min="10" max="360" value="{{ old('preferred_duration_max', $profile->preferred_duration_max) }}"></label>
                <label>Zorluk <select class="hc-input mt-1 w-full" name="preferred_difficulty"><option value="">Farketmez</option>@foreach (['easy' => 'Kolay', 'medium' => 'Orta', 'hard' => 'Zor'] as $value => $label)<option value="{{ $value }}" @selected($profile->preferred_difficulty === $value)>{{ $label }}</option>@endforeach</select></label>
                <div class="md:col-span-2"><button class="hc-button-primary" type="submit">Tercihleri kaydet</button></div>
            </form>
            <form class="mt-5" method="POST" action="{{ route('personalization.clear') }}">@csrf<button class="text-sm font-semibold underline" type="submit">Öğrenilmiş tercihleri ve öneri geçmişini sil</button></form>
        </section>

        <section class="hc-panel p-6" aria-labelledby="plan-heading"><h2 id="plan-heading" class="text-xl font-bold">Akıllı menü planı</h2><p class="mt-1 text-sm text-stone-600 dark:text-stone-300">Yalnız yayındaki, doğrulanmış tariflerden; alerjen ve hariç malzeme filtresi korunarak taslak oluşturur.</p><form class="mt-4 flex flex-wrap items-end gap-3" method="POST" action="{{ route('personalization.meal-plan') }}">@csrf<label>Gün<select class="hc-input mt-1" name="days"><option value="7">7 gün</option><option value="14">14 gün</option><option value="30">30 gün</option></select></label><label>En uzun süre (dk)<input class="hc-input mt-1 w-32" name="max_minutes" min="10" max="360" type="number"></label><label class="flex items-center gap-2"><input name="pantry_priority" type="checkbox" value="1">Dolabı öncele</label><button class="hc-button-primary" type="submit">Taslak plan oluştur</button></form></section>

        <section class="hc-panel p-6" aria-labelledby="privacy-heading"><h2 id="privacy-heading" class="text-xl font-bold">Gizlilik kontrolleri</h2><p class="mt-1 text-sm text-stone-600 dark:text-stone-300">Açık tercihler ve sistemin öğrendiği sinyaller birbirinden ayrıdır. Bu ayarlar yeni çıkarımları durdurabilir.</p><form class="mt-4 grid gap-3 sm:grid-cols-2" method="POST" action="{{ route('personalization.settings') }}">@csrf @method('PATCH') @foreach(['history_usage'=>'Pişirme geçmişini kullan','pantry_usage'=>'Dolabımı kullan','favorites_usage'=>'Favorilerimi kullan','cooking_history_usage'=>'Oturum geçmişini kullan','ai_memory_usage'=>'AI hafızasını kullan','inferred_preferences'=>'Öğrenilmiş tercihleri kullan'] as $key=>$label)<label class="flex items-center gap-2 text-sm"><input name="{{ $key }}" type="checkbox" value="1" @checked(($profile->settings[$key] ?? false))>{{ $label }}</label>@endforeach<div class="sm:col-span-2"><button class="hc-button-secondary" type="submit">Gizlilik ayarlarını kaydet</button></div></form><form class="mt-4" method="POST" action="{{ route('personalization.export') }}">@csrf<button class="text-sm font-semibold underline" type="submit">Verilerim için dışa aktarma talebi oluştur</button></form></section>
    </div>
</x-layouts.site>
