<x-layouts.site
    :title="($seoTitle ?? __('cuisine.page_title', ['name' => $mutfak->name, 'count' => $tarifler->count()]))"
    :meta-description="($seoDescription ?? __('cuisine.meta_description', ['name' => $mutfak->name, 'count' => $tarifler->count()]))"
    :structured-data="$seoSchema"
    :canonical-url="$seoIndexability['canonical']"
    :robots="$seoIndexability['robots']">

    <section class="hc-container py-14">
        <nav class="hc-body-sm text-muted" aria-label="{{ __('cuisine.breadcrumb') }}">
            <a class="font-semibold text-gold-700 transition hover:underline dark:text-gold-400" href="{{ route('cuisines.index') }}">{{ __('cuisine.breadcrumb') }}</a>
            <span aria-hidden="true"> › </span><span class="text-charcoal-900 dark:text-charcoal-100 font-medium">{{ $mutfak->name }}</span>
        </nav>

        <h1 class="mt-4 font-display text-4xl font-semibold sm:text-5xl">{{ $seoH1 ?? __('cuisine.h1', ['name' => $mutfak->name]) }}</h1>

        @if(($isKibrisHub ?? false) === true)
            <p class="hc-body-sm mt-2 max-w-2xl font-semibold tracking-wide text-gold-700 dark:text-gold-300">Kıbrıs · Kıbrıs Yemek · Kıbrıs Yemek Tarif · Yemek Kıbrıs · Kıbrıs Yemekleri</p>
            <p class="hc-body-lg mt-3 max-w-3xl leading-relaxed text-muted">Bu sayfa <strong>“Kıbrıs”</strong>, <strong>“Kıbrıs Yemek”</strong>, <strong>“Kıbrıs Yemek Tarif”</strong>, <strong>“Yemek Kıbrıs”</strong> ve <strong>“Kıbrıs Yemekleri”</strong> aramalarının tamamı için kanonik kaynaktır. Ayrı 5 sayfa kanibalizasyon yaratır; tek güçlü hub 5 niyeti de karşılar. Aşağıdaki 65 tarifin tamamı “Kıbrıs Mutfağı” mutfağına aittir ve gramajlı, süreli, adımlı olarak doğrulanmıştır.</p>
        @else
            <p class="hc-body-lg mt-4 max-w-2xl text-muted">
                {{ __('cuisine.body_count', ['name' => $mutfak->name, 'count' => $tarifler->count()]) }}
                {{ __('cuisine.body_detail') }}
            </p>
        @endif

        @if (filled($mutfak->description))
            <p class="hc-body-sm mt-3 max-w-3xl leading-relaxed text-muted">{{ $mutfak->description }}</p>
        @endif

        @if(($isKibrisHub ?? false) === true)
            <div class="mt-8 max-w-3xl rounded-2xl border border-gold-100 bg-gold-50/50 p-6 dark:border-gold-900/30 dark:bg-gold-950/20">
                <h2 class="font-display text-xl font-semibold">Kıbrıs Yemekleri Nedir? Adanın Mutfak Mirası</h2>
                <p class="hc-body-sm mt-3 leading-relaxed text-muted">Kıbrıs Yemekleri; hellim, şeftali kebabı, molehiya, kolokas, magarina bulli, pirohu ve pilavuna gibi ada mutfağına özgü lezzetler ile Akdeniz'in zeytinyağlı ve ızgara geleneğini birleştiren mutfaktır. House Chef Cyprus arşivi bu mirası 65 doğrulanmış <strong>Kıbrıs Yemek Tarifi</strong> ile belgeler. Her tarif porsiyonu, gramajı ve pişirme süresiyle birlikte test edilmiştir.</p>
                <h2 class="mt-6 font-display text-lg font-semibold">Kıbrıs Yemek Tarifleri — 65 Doğrulanmış Tarif</h2>
                <p class="hc-body-sm mt-2 leading-relaxed text-muted">Aşağıdaki koleksiyon “Kıbrıs Yemek Tarif” ve “Yemek Kıbrıs” niyetlerini de karşılar: filtre, arama veya kategoriyle daraltmadan önce tüm <strong>Kıbrıs Yemek Tarifleri</strong> tek adreste listelenir. Aradığınızı bulamadıysanız üst menüdeki “Ne Pişirebilirim?” ile dolabınıza göre eşleşen Kıbrıs tariflerini görün.</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('recipes.index') }}?q=Kıbrıs" class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Kıbrıs ara →</a>
                    <a href="{{ route('blog.index') }}?q=Kıbrıs" class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Kıbrıs rehberleri →</a>
                    <a href="{{ route('ingredients.hub.show', 'hellim') }}" class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Hellim ile →</a>
                </div>
            </div>
        @endif

        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
            @foreach ($tarifler as $tarif)
                <x-recipe-card-v3 :recipe="$tarif" :show-favorite="false" />
                @if($loop->iteration === 4 && ! $loop->last)
                    <x-ad-slot slot-name="in_feed" class="col-span-full my-2" />
                @endif
            @endforeach
        </div>

        @if(($isKibrisHub ?? false) === true)
            <section class="mt-12 max-w-3xl">
                <h2 class="font-display text-xl font-semibold">Sık Sorulanlar — Kıbrıs Yemekleri</h2>
                <dl class="mt-6 space-y-4">
                    <div class="rounded-xl border border-charcoal-100 bg-white p-4 dark:border-charcoal-800 dark:bg-charcoal-900">
                        <dt class="font-semibold">Kıbrıs Yemekleri nelerdir?</dt>
                        <dd class="hc-body-sm mt-2 leading-relaxed text-muted">Hellim, şeftali kebabı, molehiya, kolokas, magarina bulli, pirohu, pilavuna ve 58 ada lezzeti daha — toplam 65 doğrulanmış Kıbrıs Mutfağı tarifi.</dd>
                    </div>
                    <div class="rounded-xl border border-charcoal-100 bg-white p-4 dark:border-charcoal-800 dark:bg-charcoal-900">
                        <dt class="font-semibold">Kıbrıs Yemek Tarifi nereden bulunur?</dt>
                        <dd class="hc-body-sm mt-2 leading-relaxed text-muted">Bu sayfada. “Kıbrıs Usulü” etiketli 65 tarifin tamamı ölçülmüş, denenmiş ve besin değerleriyle birlikte yayınlanır.</dd>
                    </div>
                    <div class="rounded-xl border border-charcoal-100 bg-white p-4 dark:border-charcoal-800 dark:bg-charcoal-900">
                        <dt class="font-semibold">Kıbrıs Mutfağı ile Kıbrıs Yemekleri aynı mı?</dt>
                        <dd class="hc-body-sm mt-2 leading-relaxed text-muted">Evet. “Kıbrıs Mutfağı”, “Kıbrıs Yemekleri” ve “Yemek Kıbrıs” aynı ada mutfağını ifade eder ve bu sayfa 5 varyantın kanonik kaynağıdır.</dd>
                    </div>
                </dl>
                <p class="hc-body-sm mt-6 leading-relaxed text-muted">Daha fazla Kıbrıs rehberi için <a class="font-semibold text-gold-700 underline-offset-2 hover:underline dark:text-gold-400" href="{{ route('blog.index') }}?q=Kıbrıs">Kıbrıs blog arşivi</a> ve <a class="font-semibold text-gold-700 underline-offset-2 hover:underline dark:text-gold-400" href="{{ route('ingredients.hub.show', 'hellim') }}">Hellim</a> / <a class="font-semibold text-gold-700 underline-offset-2 hover:underline dark:text-gold-400" href="{{ route('cuisines.show', 'kibris-mutfagi') }}">Kıbrıs Mutfağı</a> koleksiyonuna bakın.</p>
            </section>
        @endif

        <x-collection-siblings :baslik="__('cuisine.siblings_title')" :ogeler="$kardesler" :mevcut-url="route('cuisines.show', $mutfak->slug)" />
    </section>
</x-layouts.site>
