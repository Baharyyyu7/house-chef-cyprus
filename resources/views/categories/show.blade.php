<x-layouts.site :title="$kategori->name.' Tarifleri · House Chef Cyprus'" :meta-description="$kategori->name.' kategorisinde '.$tarifler->count().' doğrulanmış tarif.'" :structured-data="$seoSchema" :canonical-url="$seoIndexability['canonical']" :robots="$seoIndexability['robots']">
    <section class="hc-container py-14">
        <nav class="hc-body-sm text-muted" aria-label="Yol"><a href="{{ route('categories.index') }}">Kategoriler</a> <span aria-hidden="true">›</span> {{ $kategori->name }}</nav>
        <h1 class="mt-4 font-display text-4xl font-semibold sm:text-5xl">{{ $kategori->name }} Tarifleri</h1>
        <p class="hc-body-lg mt-4 max-w-2xl text-muted">{{ $tarifler->count() }} doğrulanmış tarif; ölçüler, süreler ve adım adım anlatımlarla.</p>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
            @foreach($tarifler as $tarif)<x-recipe-card-v3 :recipe="$tarif" :show-favorite="false" />@endforeach
        </div>
    </section>
</x-layouts.site>
