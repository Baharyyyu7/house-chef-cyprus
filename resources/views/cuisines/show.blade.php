<x-layouts.site
    :title="($seoTitle ?? __('cuisine.page_title', ['name' => taxonomy_name($mutfak), 'count' => $tarifler->count()]))"
    :meta-description="($seoDescription ?? __('cuisine.meta_description', ['name' => taxonomy_name($mutfak), 'count' => $tarifler->count()]))"
    :structured-data="$seoSchema"
    :canonical-url="$seoIndexability['canonical']"
    :robots="$seoIndexability['robots']">

    <section class="hc-container py-14">
        <nav class="hc-body-sm text-muted" aria-label="{{ __('cuisine.breadcrumb') }}">
            <a class="font-semibold text-gold-700 transition hover:underline dark:text-gold-400" href="{{ route('cuisines.index') }}">{{ __('cuisine.breadcrumb') }}</a>
            <span aria-hidden="true"> › </span><span class="text-charcoal-900 dark:text-charcoal-100 font-medium">{{ taxonomy_name($mutfak) }}</span>
        </nav>

        <h1 class="mt-4 font-display text-4xl font-semibold sm:text-5xl">{{ $seoH1 ?? __('cuisine.h1', ['name' => taxonomy_name($mutfak)]) }}</h1>

        @if($isKibrisHub)
            <p class="hc-body-sm mt-2 max-w-2xl font-semibold tracking-wide text-gold-700 dark:text-gold-300">Kıbrıs · Kıbrıs Yemek · Kıbrıs Yemek Tarif · Yemek Kıbrıs · Kıbrıs Yemekleri</p>
            <p class="hc-body-lg mt-3 max-w-3xl leading-relaxed text-muted">Kıbrıs mutfağını evde keşfetmek için bu koleksiyondaki {{ $tarifler->count() }} tarifi inceleyebilirsiniz. Hellimle hazırlanan yemeklerden sebze ve et yemeklerine, hamur işlerinden tatlılara kadar farklı seçeneklerin malzemelerini ve hazırlama adımlarını bir arada bulabilirsiniz.</p>
            <section class="mt-8 max-w-3xl">
                <h2 class="font-display text-xl font-semibold">Sofranıza uygun tarifi seçin</h2>
                <p class="hc-body-sm mt-3 leading-relaxed text-muted">Tarif kartlarından hazırlama süresini ve porsiyon bilgisini kontrol edin. Evdeki malzemelerle yemek arıyorsanız malzemeye göre tarif bulma aracını kullanabilirsiniz.</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('ingredients.explore') }}" class="hc-v3-btn hc-v3-btn--outline">Malzemelerime göre tarif bul</a>
                    <a href="{{ route('recipes.index') }}?q=Kıbrıs" class="hc-v3-btn hc-v3-btn--outline">Kıbrıs ara →</a>
                </div>
            </section>
            <section class="mt-8 max-w-3xl">
                <h2 class="font-display text-xl font-semibold">Koleksiyondan tarifler</h2>
                <ul class="mt-4 space-y-2">
                    @foreach($tarifler->take(12) as $tarif)
                        <li><a href="{{ route('recipes.show', $tarif) }}" class="hc-body-sm hover:underline">{{ $tarif->translated('title') }}</a></li>
                    @endforeach
                </ul>
            </section>
            <section class="mt-12 max-w-3xl">
                <h2 class="font-display text-xl font-semibold">Kıbrıs yemekleri hakkında sık sorulanlar</h2>
                <dl class="mt-6 space-y-4">
                    @foreach($cuisineFaq as $item)
                        <div class="rounded-xl border border-charcoal-100 bg-white p-4 dark:border-charcoal-800 dark:bg-charcoal-900">
                            <dt class="font-semibold">{{ $item['question'] }}</dt>
                            <dd class="hc-body-sm mt-2 leading-relaxed text-muted">{{ $item['answer'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @else
            <p class="hc-body-lg mt-4 max-w-2xl text-muted">{{ __('cuisine.body_count', ['name' => taxonomy_name($mutfak), 'count' => $tarifler->count()]) }} {{ __('cuisine.body_detail') }}</p>
        @endif

        <div class="mt-12">
            <h2 class="font-display text-2xl font-semibold">{{ taxonomy_name($mutfak) }} {{ __('cuisine.recipes_suffix') }}</h2>
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
                @foreach($tarifler as $tarif)
                    <x-recipe-card-v3 :recipe="$tarif" :show-favorite="false" />
                    @if($loop->iteration === 4 && ! $loop->last)
                        <x-ad-slot slot-name="in_feed" class="col-span-full my-2" />
                    @endif
                @endforeach
            </div>
        </div>

        <div class="mt-12 max-w-3xl">
            <x-ad-slot slot-name="multiplex" class="my-8" />
        </div>

        <x-collection-siblings :baslik="__('cuisine.siblings_title')" :ogeler="$kardesler" :mevcut-url="route('cuisines.show', $mutfak->slug)" />
    </section>
</x-layouts.site>
