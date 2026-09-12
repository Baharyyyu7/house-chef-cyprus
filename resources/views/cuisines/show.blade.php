<x-layouts.site
    :title="__('cuisine.page_title', ['name' => $mutfak->name, 'count' => $tarifler->count()])"
    :meta-description="__('cuisine.meta_description', ['name' => $mutfak->name, 'count' => $tarifler->count()])"
    :structured-data="$seoSchema"
    :canonical-url="$seoIndexability['canonical']"
    :robots="$seoIndexability['robots']">

    <section class="hc-container py-14">
        <nav class="hc-body-sm text-muted" aria-label="{{ __('cuisine.breadcrumb') }}">
            <a class="font-semibold text-gold-700 transition hover:underline dark:text-gold-400" href="{{ route('cuisines.index') }}">{{ __('cuisine.breadcrumb') }}</a>
            <span aria-hidden="true"> › </span><span class="text-charcoal-900 dark:text-charcoal-100 font-medium">{{ $mutfak->name }}</span>
        </nav>

        {{-- Başlık arama niyetini birebir karşılıyor: insanlar "kıbrıs mutfağı
             tarifleri" diye arıyor. --}}
        <h1 class="mt-4 font-display text-4xl font-semibold sm:text-5xl">{{ __('cuisine.h1', ['name' => $mutfak->name]) }}</h1>

        <p class="hc-body-lg mt-4 max-w-2xl text-muted">
            {{ __('cuisine.body_count', ['name' => $mutfak->name, 'count' => $tarifler->count()]) }}
            {{ __('cuisine.body_detail') }}
        </p>

        @if (filled($mutfak->description))
            <p class="hc-body-sm mt-3 max-w-2xl leading-relaxed text-muted">{{ $mutfak->description }}</p>
        @endif

        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
            @foreach ($tarifler as $tarif)
                <x-recipe-card-v3 :recipe="$tarif" :show-favorite="false" />
                @if($loop->iteration === 4 && ! $loop->last)
                    <x-ad-slot slot-name="in_feed" class="col-span-full my-2" />
                @endif
            @endforeach
        </div>


        <x-collection-siblings :baslik="__('cuisine.siblings_title')" :ogeler="$kardesler" :mevcut-url="route('cuisines.show', $mutfak->slug)" />
    </section>
</x-layouts.site>
