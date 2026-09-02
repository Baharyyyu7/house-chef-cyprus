<x-layouts.site :title="$metadata['title']" :meta-description="$metadata['description']" :canonical-url="$metadata['canonical']" :robots="$metadata['robots']" :social-image="$seoImage" :social-image-width="$seoImageWidth" :social-image-height="$seoImageHeight" :social-image-alt="$seoImageAlt" :structured-data="$seoSchema">
    @php
        $heroMedia = app(\App\Services\Media\PublicMediaEligibilityService::class)->firstEligible($recipe->media);
        $hasApprovedMedia = $heroMedia !== null;
        $isSyntheticHero = (bool) $heroMedia?->generated_by_ai;
    @endphp

    {{-- Hero başlık çubuğunun altından başlar; çubuk sayfa tepesindeyken
         saydam durduğu için arada krem bir bant kalmaz. --}}
    <section class="hc-recipe-detail-hero relative isolate -mt-[var(--hc-header-h)] overflow-hidden bg-charcoal-950 pt-[var(--hc-header-h)] text-white">
        @if($hasApprovedMedia)
            <picture>
                @if($heroMedia->responsiveSrcset([800, 1200, 1600], 'avif'))<source type="image/avif" srcset="{{ $heroMedia->responsiveSrcset([800, 1200, 1600], 'avif') }}" sizes="100vw">@endif
                @if($heroMedia->responsiveSrcset([800, 1200, 1600], 'webp'))<source type="image/webp" srcset="{{ $heroMedia->responsiveSrcset([800, 1200, 1600], 'webp') }}" sizes="100vw">@endif
                <img class="absolute inset-0 -z-20 h-full w-full object-cover opacity-70" src="{{ $heroMedia->responsiveUrl(1600) }}" srcset="{{ $heroMedia->responsiveSrcset([800, 1200, 1600]) }}" sizes="100vw" width="1600" height="1000" alt="" aria-hidden="true" fetchpriority="high">
            </picture>
        @else
            <div class="absolute inset-0 -z-20 bg-[radial-gradient(circle_at_75%_20%,rgba(255,102,0,.32),transparent_28%),linear-gradient(135deg,#111913,#17251d_48%,#20160e)]" aria-hidden="true"></div>
        @endif
        <div class="absolute inset-0 -z-10 bg-[linear-gradient(90deg,rgba(10,16,13,.97)_0%,rgba(10,16,13,.82)_48%,rgba(10,16,13,.25)_100%)]"></div>
        <div class="hc-container grid gap-8 py-10 sm:py-14 lg:grid-cols-[minmax(0,1fr)_19rem] lg:py-20">
            <div class="max-w-3xl">
                <a class="hc-v3-btn hc-v3-btn--ghost -ml-3 text-white hover:bg-white/10" href="{{ route('recipes.index') }}"><span aria-hidden="true">←</span> Tarif koleksiyonuna dön</a>
                <p class="hc-overline mt-8 text-orange-300">@if($recipe->cuisine && $cuisineUrl)<a href="{{ $cuisineUrl }}">{{ $recipe->cuisine->name }}</a>@else Ev mutfağı @endif <span aria-hidden="true">/</span> {{ $recipe->region ?: ($recipe->category?->name ?? 'Tarif') }}</p>
                <h1 class="hc-display-xl mt-4 max-w-3xl">{{ $recipe->title }}</h1>
                <p class="hc-body-lg mt-5 max-w-2xl text-stone-200">{{ $recipe->excerpt }}</p>
                <div class="mt-7 flex flex-wrap gap-2">@foreach([['Hazırlık', $recipe->prep_minutes.' dk'], ['Pişirme', $recipe->cook_minutes.' dk'], ['Toplam', $recipe->totalMinutes().' dk'], ['Porsiyon', $recipe->servings], ['Zorluk', $recipe->zorlukEtiketi()]] as [$label, $value])<span class="hc-v3-badge border-white/20 bg-white/10 text-white backdrop-blur"><strong class="text-orange-200">{{ $label }}</strong> {{ $value }}</span>@endforeach</div>
                <div class="mt-6 flex flex-wrap items-center gap-3"><span class="text-xs text-stone-300">Son editoryal kontrol: {{ $recipe->editorial_reviewed_at?->format('d.m.Y') ?: 'kayıtlı değil' }}</span>@auth<form method="post" action="{{ route('favorites.toggle', $recipe) }}">@csrf<button class="hc-v3-btn hc-v3-btn--outline border-white/20 bg-white/10 text-white hover:bg-white/20" type="submit" aria-label="{{ $recipe->title }} tarifini favorilere ekle">{{ $isFavorited ? '♥ Favoride' : '♡ Favoriye ekle' }}</button></form>@endauth</div>
                @auth<div class="mt-3 flex flex-wrap gap-2" aria-label="Tarifi koleksiyona ekle">@foreach($recipeCollections as $collection)<form method="post" action="{{ route('collections.recipes.add', ['recipeCollection' => $collection, 'recipe' => $recipe->slug]) }}">@csrf<button class="rounded-full border border-white/20 bg-white/10 px-3 py-2 text-xs font-bold text-white hover:bg-white/20">+ {{ $collection->name }}</button></form>@endforeach<a class="rounded-full border border-white/20 px-3 py-2 text-xs font-bold text-white" href="{{ route('collections.index') }}">Koleksiyonları yönet</a></div>@endauth
                @if($heroMedia?->unsplashAttribution())
                    @php($unsplashCredit = $heroMedia->unsplashAttribution())
                    <p class="mt-4 text-xs text-stone-300">
                        Fotoğraf:
                        @if($unsplashCredit['photographer_url'])
                            <a class="underline" href="{{ $unsplashCredit['photographer_url'] }}" rel="noopener noreferrer" target="_blank">{{ $unsplashCredit['photographer'] }}</a>
                        @else
                            {{ $unsplashCredit['photographer'] }}
                        @endif
                        /
                        @if($unsplashCredit['unsplash_url'])
                            <a class="underline" href="{{ $unsplashCredit['unsplash_url'] }}" rel="noopener noreferrer" target="_blank">Unsplash</a>
                        @else
                            Unsplash
                        @endif
                    </p>
                @elseif($heroMedia?->attribution)
                    <p class="mt-4 text-xs text-stone-300">{{ $heroMedia->attribution }}</p>
                @endif
                @if($hasApprovedMedia && $isSyntheticHero)
                    <p class="mt-2 text-xs text-stone-300">Yapay zekâ ile üretilmiş, editoryal olarak onaylanmış görsel.</p>
                @endif
            </div>
            <aside class="self-end rounded-[1.5rem] border border-white/15 bg-black/30 p-5 backdrop-blur-md">
                <p class="hc-overline text-orange-300">Tarif güvencesi</p>
                <p class="mt-3 text-sm leading-6 text-stone-100">{{ $hasApprovedMedia ? ($isSyntheticHero ? 'Bu tarifteki görsel yapay zekâ ile üretilmiş ve editoryal olarak onaylanmıştır; gerçek fotoğraf olarak sunulmaz.' : 'Tarif kaydı ve görselinin editoryal inceleme durumu burada şeffaf biçimde gösterilir.') : 'Onaylı yemek fotoğrafı henüz yok; yer tutucu görsel yemek fotoğrafı olarak gösterilmez.' }}</p>
                <div class="mt-4 flex flex-wrap gap-2"><span class="hc-v3-badge border-white/20 bg-white/10 text-white">{{ $hasApprovedMedia && $recipe->verification_status === 'verified' ? '✓ Kayıt doğrulandı' : 'ⓘ İnceleme sürüyor' }}</span><span class="hc-v3-badge border-white/20 bg-white/10 text-white">{{ $recipe->ingredients->count() }} malzeme</span></div>
            </aside>
        </div>
    </section>

    <nav class="sticky hc-sticky-under-header z-30 border-b bg-[color:var(--hc-background-page)]/95 backdrop-blur" style="border-color: var(--hc-border-subtle)" aria-label="Tarif bölümleri"><div class="hc-container flex gap-1 overflow-x-auto py-2 text-sm font-extrabold whitespace-nowrap"><a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" href="#ingredients">Malzemeler</a><a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" href="#steps">Hazırlık</a>@if(!empty($recipe->equipment))<a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" href="#equipment">Ekipman</a>@endif<a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" href="#nutrition">Beslenme</a><a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" href="#allergens">Alerjen</a><a class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" href="#storage">Saklama</a></div></nav>

    <section class="hc-container grid gap-8 py-8 sm:py-12 lg:grid-cols-[minmax(17rem,.78fr)_minmax(0,1.22fr)] lg:items-start">
        <aside class="space-y-5 lg:sticky lg:top-24">
            @unless($hasApprovedMedia)
                <section class="hc-v3-alert hc-v3-alert--warning" aria-label="Görsel durumu"><span aria-hidden="true">ⓘ</span><div><strong>Onaylı yemek fotoğrafı bekleniyor.</strong><p>Bu tarif için kullanılan marka tasarımı yemek fotoğrafı değildir. Lisans/provenance ve insan onayı tamamlanınca gerçek görsel gösterilecektir.</p></div></section>
            @endunless
            <section id="ingredients" class="hc-v3-card hc-scroll-target p-5 sm:p-6" aria-labelledby="household-ingredients">
                <div class="flex items-center justify-between gap-3"><h2 id="household-ingredients" class="hc-heading-3">Malzemeler</h2><span class="hc-v3-badge">{{ count($ingredientLines) }} kalem</span></div>
                <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">Ev ölçüleri {{ $servings }} porsiyona göre hesaplanır.</p>
                <form class="mt-5 flex items-end gap-2" method="get"><button class="hc-v3-btn hc-v3-btn--outline !min-h-11 !min-w-11 !px-3" type="submit" name="servings_step" value="-1" aria-label="Porsiyonu azalt" @disabled($servings <= 1)>−</button><div class="min-w-0 flex-1"><label class="hc-v3-field-label" for="servings">Porsiyon</label><input id="servings" class="hc-v3-input text-center" type="number" min="1" max="99" name="servings" value="{{ $servings }}"></div><button class="hc-v3-btn hc-v3-btn--outline !min-h-11 !min-w-11 !px-3" type="submit" name="servings_step" value="1" aria-label="Porsiyonu artır">+</button></form><a class="mt-3 inline-flex min-h-[24px] items-center text-xs font-extrabold" style="color: var(--hc-text-accent)" href="{{ route('recipes.show', $recipe) }}">Varsayılan {{ $recipe->servings }} porsiyona dön</a>
                @php($malzemeGruplari = collect($ingredientLines)->groupBy('group'))
                @foreach($malzemeGruplari as $group => $lines)
                    {{-- Tek grup varsa adini basmak anahati kirletiyor: Cig
                         Kofte'de grup adi "Malzemeler"di ve bolumun kendi
                         basligini tekrar ediyordu. Grup basligi ancak birden
                         fazla grup varken bilgi tasiyor. --}}
                    @if($malzemeGruplari->count() > 1)
                        <h3 class="hc-overline mt-6" style="color: var(--hc-text-muted)">{{ $group }}</h3>
                    @endif
                    <ul class="mt-2 divide-y" style="border-color: var(--hc-border-subtle)">@foreach($lines as $item)<li class="py-3"><label class="flex cursor-pointer items-start gap-3"><input class="mt-1 shrink-0" type="checkbox" aria-label="{{ $item['formatted'] }} malzemesini tamamlandı olarak işaretle"><span><span class="text-sm font-bold" style="color: var(--hc-text-primary)">{{ $item['formatted'] }} @if($item['line']->is_optional)<span class="hc-v3-badge ml-1">isteğe bağlı</span>@endif</span>@if($item['line']->preparation_note)<span class="mt-1 block text-xs" style="color: var(--hc-text-secondary)">{{ $item['line']->preparation_note }}</span>@endif@if($item['canonical_quantity'])<span class="mt-1 block text-xs" style="color: var(--hc-text-muted)">{{ $item['is_approximate'] ? 'yaklaşık ' : '' }}{{ rtrim(rtrim((string) $item['canonical_quantity'], '0'), '.') }} {{ $item['canonical_unit'] }} · {{ $item['line']->conversion_confidence === 'verified' ? 'doğrulanmış ölçü' : 'yaklaşık ölçü' }}</span>@endif@if($item['scaling_note'])<span class="mt-1 block text-xs text-amber-700 dark:text-amber-300">{{ $item['scaling_note'] }}</span>@endif</span></label></li>@endforeach</ul>
                @endforeach
                @auth
                    <form class="mt-6" method="post" action="{{ route('favorites.toggle', $recipe) }}">@csrf<button class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--block">{{ $isFavorited ? '♥ Favorilerden çıkar' : '♡ Favorilere ekle' }}</button></form>
                    <form class="mt-3" method="post" action="{{ route('cooking-sessions.start', $recipe) }}">@csrf<input type="hidden" name="servings" value="{{ $servings }}"><button class="hc-v3-btn hc-v3-btn--primary hc-v3-btn--block">Pişirmeye başla <span aria-hidden="true">→</span></button></form>
                @else
                    <a class="hc-v3-btn hc-v3-btn--primary hc-v3-btn--block mt-6" href="{{ route('login') }}">Pişirmek için giriş yapın</a>
                @endauth
            </section>

            @if($match)<div class="hc-v3-alert hc-v3-alert--success"><span aria-hidden="true">✓</span><div><strong>{{ $match['status'] }}</strong><p>{{ $match['available'] }}/{{ $match['required'] }} zorunlu malzeme dolabınızda.</p></div></div>@endif

            <section id="nutrition" class="hc-v3-card hc-scroll-target p-5 sm:p-6"><div class="flex items-start justify-between gap-3"><div><p class="hc-overline" style="color: var(--hc-text-muted)">Beslenme özeti</p><h2 class="hc-heading-3 mt-2">Porsiyon başına</h2></div><span class="hc-v3-badge hc-v3-badge--accent">%{{ $nutrition['completeness_percentage'] }} tamam</span></div><p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">Güven skoru: %{{ $nutrition['confidence_score'] }} · değerler {{ $nutrition['contains_estimated_values'] ? 'tahmini bileşenler içerir' : 'mevcut veriyle hesaplandı' }}</p><dl class="mt-5 grid grid-cols-2 gap-3">@foreach(['energy_kcal' => 'Kalori', 'protein' => 'Protein', 'carbohydrate' => 'Karbonhidrat', 'fat' => 'Yağ'] as $code => $label)@if(isset($nutrition['nutrients'][$code]))<div class="rounded-2xl p-3" style="background: var(--hc-background-subtle)"><dt class="text-xs" style="color: var(--hc-text-muted)">{{ $label }}</dt><dd class="mt-1 text-sm font-extrabold">{{ rtrim(rtrim($nutrition['nutrients'][$code]['per_serving'], '0'), '.') }} {{ $nutrition['nutrients'][$code]['unit'] }}</dd></div>@endif@endforeach</dl><p class="mt-4 text-xs leading-5" style="color: var(--hc-text-muted)">Değerler kullanılan ürünlere göre değişebilir; tıbbi tavsiye değildir.</p></section>

            <section id="allergens" class="hc-v3-card hc-scroll-target p-5 sm:p-6"><h2 class="hc-heading-3">Alerjenler ve diyet</h2>@if($allergenAnalysis['groups']['contains'])<p class="mt-3 text-sm font-bold text-red-700 dark:text-red-300">⚠ İçerir: {{ collect($allergenAnalysis['groups']['contains'])->pluck('name')->unique()->join(', ') }}</p>@endif@if($allergenAnalysis['groups']['may_contain'])<p class="mt-2 text-sm font-bold text-amber-700 dark:text-amber-300">ⓘ İçerebilir: {{ collect($allergenAnalysis['groups']['may_contain'])->pluck('name')->unique()->join(', ') }}</p>@endif<div class="mt-4 flex flex-wrap gap-2">@foreach($dietaryCompatibility as $code => $compatibility)<span class="hc-v3-badge {{ in_array($compatibility['status'], ['compatible', 'manually_verified'], true) ? 'hc-v3-badge--success' : 'hc-v3-badge--warning' }}">{{ in_array($compatibility['status'], ['compatible', 'manually_verified'], true) ? '✓' : 'ⓘ' }} {{ str($code)->replace('_', ' ')->title() }}: {{ in_array($compatibility['status'], ['compatible', 'manually_verified'], true) ? 'uygun' : ($compatibility['status'] === 'incompatible' ? 'uygun değil' : 'belirsiz') }}</span>@endforeach</div><p class="mt-4 text-xs leading-5" style="color: var(--hc-text-muted)">{{ $allergenAnalysis['medical_disclaimer'] }}</p></section>

            <x-membership-invite />
        </aside>


        <main id="steps" class="min-w-0 hc-scroll-target">
            @php($stepCount = $recipe->steps->count())
            <div x-data="hcRecipeSteps('{{ $recipe->slug }}', {{ $stepCount }})" x-id="['step']">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div><p class="hc-kicker">Adım adım hazırlayın</p><h2 class="hc-display-lg mt-2">Yapım adımları</h2></div>
                <div class="flex items-center gap-2">
                    <span class="hc-v3-badge" x-text="label"></span>
                    <button type="button" class="hc-v3-btn hc-v3-btn--ghost hc-v3-btn--sm" x-show="completed > 0" x-cloak x-on:click="reset()">Sıfırla</button>
                </div>
            </div>
            @if($stepCount > 0)
                <p class="hc-body-sm mt-3" style="color: var(--hc-text-muted)">Tamamladığınız adımları işaretleyin; bu cihazda kaldığınız yer saklanır.</p>
                <div class="hc-step-progress mt-4" role="progressbar" aria-label="Tamamlanan adımlar" x-bind:aria-valuenow="completed" aria-valuemin="0" aria-valuemax="{{ $stepCount }}">
                    <span class="hc-step-progress__bar" x-bind:style="`width: ${percent}%`"></span>
                </div>
            @endif
            <div class="hc-v3-stagger mt-7 space-y-4">@forelse($recipe->steps as $step)<article class="hc-v3-card hc-step flex gap-4 p-5 sm:gap-5 sm:p-6" x-bind:class="isDone({{ $step->position }}) ? 'hc-step--done' : ''"><button type="button" class="hc-step__toggle" x-on:click="toggle({{ $step->position }})" x-bind:aria-pressed="isDone({{ $step->position }}) ? 'true' : 'false'" aria-label="{{ $step->position }}. adımı tamamlandı olarak işaretle"><span x-show="!isDone({{ $step->position }})">{{ $step->position }}</span><span x-show="isDone({{ $step->position }})" x-cloak aria-hidden="true">✓</span></button><div class="min-w-0 flex-1"><div class="flex flex-wrap items-start justify-between gap-2"><h3 class="hc-heading-3">{{ $step->title }}</h3>@if($step->duration_seconds)<span class="hc-v3-badge">⏱ {{ intdiv($step->duration_seconds, 60) }} dk</span>@endif</div><p class="hc-body-md mt-3" style="color: var(--hc-text-secondary)">{{ $step->instruction }}</p>@if($step->equipment)<p class="mt-3 text-sm" style="color: var(--hc-text-secondary)"><strong style="color: var(--hc-text-primary)">Ekipman:</strong> {{ $step->equipment }}</p>@endif@if($step->chef_tip)<p class="mt-4 rounded-2xl bg-orange-50 p-3 text-sm leading-6 text-orange-950 dark:bg-orange-500/10 dark:text-orange-100"><strong>Şef püf noktası:</strong> {{ $step->chef_tip }}</p>@endif@if($step->safety_note)<p class="mt-3 rounded-2xl bg-red-50 p-3 text-sm leading-6 text-red-950 dark:bg-red-500/10 dark:text-red-100"><strong>Güvenlik notu:</strong> {{ $step->safety_note }}</p>@endif</div></article>@empty<div class="hc-v3-empty">Bu tarif için henüz adım eklenmedi.</div>@endforelse</div>
            </div>

            @if(!empty($recipe->equipment))
                <section id="equipment" class="hc-v3-card mt-8 hc-scroll-target p-5 sm:p-6"><p class="hc-overline" style="color: var(--hc-text-muted)">Hazırlık için</p><h2 class="hc-heading-3 mt-2">Ekipman</h2><div class="mt-4 flex flex-wrap gap-2">@foreach($recipe->equipment as $equipment)<span class="hc-v3-badge">⌁ {{ $equipment }}</span>@endforeach</div></section>
            @endif

            @if(!empty($recipe->tips) || !empty($recipe->common_mistakes))
                <section class="mt-8 grid gap-4 md:grid-cols-2">@if(!empty($recipe->tips))<article class="rounded-[1.5rem] border border-orange-200 bg-orange-50 p-5 dark:border-orange-500/20 dark:bg-orange-500/10 sm:p-6"><p class="hc-overline text-orange-700 dark:text-orange-200">✦ İşe yarayan ipuçları</p><h2 class="hc-heading-3 mt-2">Püf noktaları</h2><ul class="mt-4 space-y-3 text-sm leading-6">@foreach($recipe->tips as $tip)<li class="flex gap-2"><span aria-hidden="true">✓</span><span>{{ $tip }}</span></li>@endforeach</ul></article>@endif@if(!empty($recipe->common_mistakes))<article class="hc-v3-card p-5 sm:p-6"><p class="hc-overline" style="color: var(--hc-text-muted)">Sakin bir hatırlatma</p><h2 class="hc-heading-3 mt-2">Sık yapılan hatalar</h2><ul class="mt-4 space-y-3 text-sm leading-6" style="color: var(--hc-text-secondary)">@foreach($recipe->common_mistakes as $mistake)<li class="flex gap-2"><span aria-hidden="true">ⓘ</span><span>{{ $mistake }}</span></li>@endforeach</ul></article>@endif</section>
            @endif

            <section id="storage" class="mt-8 grid gap-4 hc-scroll-target md:grid-cols-2"><article class="hc-v3-card p-5 sm:p-6"><p class="hc-overline" style="color: var(--hc-text-muted)">Servis ve saklama</p><h2 class="hc-heading-3 mt-2">Mutfak notları</h2><p class="hc-body-sm mt-4" style="color: var(--hc-text-secondary)">{{ $recipe->serving_suggestions ?: 'Servis önerisi henüz eklenmedi.' }}</p><p class="hc-body-sm mt-3" style="color: var(--hc-text-secondary)"><strong style="color: var(--hc-text-primary)">Saklama:</strong> {{ data_get($recipe->storage_notes, 'uyari', 'Saklama notu henüz eklenmedi.') }}</p>@if($recipe->reheating_notes)<p class="hc-body-sm mt-3" style="color: var(--hc-text-secondary)"><strong style="color: var(--hc-text-primary)">Yeniden ısıtma:</strong> {{ $recipe->reheating_notes }}</p>@endif@if($recipe->freezing_notes)<p class="hc-body-sm mt-3" style="color: var(--hc-text-secondary)"><strong style="color: var(--hc-text-primary)">Dondurma:</strong> {{ $recipe->freezing_notes }}</p>@endif</article><article class="hc-v3-card p-5 sm:p-6"><p class="hc-overline" style="color: var(--hc-text-muted)">Şeffaf kaynak bilgisi</p><h2 class="hc-heading-3 mt-2">Kaynak ve güncellik</h2><p class="hc-body-sm mt-4" style="color: var(--hc-text-secondary)">Doğrulama: <strong style="color: var(--hc-text-primary)">{{ $hasApprovedMedia && $recipe->verification_status === 'verified' ? 'Kayıt ve onaylı medya kontrolü tamamlandı' : 'yeniden editoryal ve medya incelemesi sürüyor' }}</strong></p><p class="hc-body-sm mt-3" style="color: var(--hc-text-secondary)">Son kontrol: {{ $recipe->editorial_reviewed_at?->format('d.m.Y') ?: 'Henüz kaydedilmedi' }}</p>@if($recipe->provenances->isNotEmpty())<p class="hc-body-sm mt-3" style="color: var(--hc-text-secondary)">Kaynak özeti: {{ $recipe->provenances->count() > 1 ? 'Birden fazla kaynakla editoryal olarak araştırıldı.' : ($recipe->provenances->first()->contentSource?->source_name ?: 'Kayıtlı editoryal kaynak') }}</p>@endif</article></section>

            {{-- Yazı içi reklam adımların bittiği yerde durur. AdSense'in kendi
                 yerleşim rehberi reklamı kenar çubuğuna koymayı açıkça
                 önermiyor; okuma akışının doğal bir molasında duruyor. --}}
            <x-ad-slot slot-name="in_article" />

            <section class="mt-8 rounded-[1.5rem] bg-charcoal-950 p-5 text-white sm:p-7"><p class="hc-overline text-orange-300">Pişirme rehberi</p><h2 class="hc-heading-3 mt-2">Tarif notları</h2><p class="mt-2 text-sm leading-6 text-stone-300">Bu tarifin püf noktalarını, sürelerini ve alerjen bilgilerini birlikte değerlendirin. Sağlık ve alerji kararları için profesyonel desteğin yerini tutmaz.</p><a class="hc-v3-btn hc-v3-btn--primary mt-5" href="#tips">Püf noktalarına git</a></section>

            @if($relatedRecipes->isNotEmpty())
                <section class="mt-12"><div class="flex items-end justify-between gap-4"><div><p class="hc-kicker">Bir sonraki tarifiniz</p><h2 class="hc-display-lg mt-2">Benzer tarifler</h2></div><a class="hc-v3-btn hc-v3-btn--ghost" href="{{ $cuisineUrl ?: route('recipes.index') }}">Tümünü gör <span aria-hidden="true">→</span></a></div><div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">@foreach($relatedRecipes as $relatedRecipe)<x-recipe-card-v3 :recipe="$relatedRecipe" :show-favorite="false" heading="h3" />@endforeach</div></section>
            @endif
            {{-- Multiplex birimi içerik önerisi ızgarası; yazının sonunda,
                 benzer tariflerin ardında duruyor. --}}
            <x-ad-slot slot-name="multiplex" class="mt-12" />
        </main>
    </section>
</x-layouts.site>
