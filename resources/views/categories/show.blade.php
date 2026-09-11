<x-layouts.site
    :title="__('category.page_title', ['name' => $kategori->name])"
    :meta-description="__('category.meta_description', ['name' => $kategori->name, 'count' => $tarifler->count()])"
    :structured-data="$seoSchema"
    :canonical-url="$seoIndexability['canonical']"
    :robots="$seoIndexability['robots']">
    <section class="hc-container py-14">
        <nav class="hc-body-sm text-muted" aria-label="{{ __('category.breadcrumb') }}">
            <a href="{{ route('categories.index') }}">{{ __('category.breadcrumb') }}</a>
            <span aria-hidden="true">›</span> {{ $kategori->name }}
        </nav>
        <h1 class="mt-4 font-display text-4xl font-semibold sm:text-5xl">{{ __('category.h1', ['name' => $kategori->name]) }}</h1>
        {{-- Yer tutucu metin modelde tanimli: sablona gomulen bir metin,
             metin degisince sessizce calismayi birakir ve dolgu yeniden
             gorunur. --}}
        @if($kategori->gercekAciklamasiVar())
            <p class="hc-body-lg mt-4 max-w-3xl text-secondary">{{ $kategori->description }}</p>
        @endif
        <p class="hc-body-lg mt-4 max-w-2xl text-muted">{{ __('category.body_count', ['count' => $tarifler->count()]) }}</p>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
            @foreach($tarifler as $tarif)<x-recipe-card-v3 :recipe="$tarif" :show-favorite="false" />@endforeach
        </div>

        <x-collection-siblings :baslik="__('category.siblings_title')" :ogeler="$kardesler" :mevcut-url="route('categories.show', $kategori->slug)" />
    </section>
</x-layouts.site>
