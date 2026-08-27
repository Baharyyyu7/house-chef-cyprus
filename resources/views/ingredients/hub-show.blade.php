<x-layouts.site
    :title="$malzeme->name.' ile Ne Yapılır? '.$tarifler->count().' Tarif · House Chef Cyprus'"
    :meta-description="$malzeme->name.' ile yapabileceğiniz '.$tarifler->count().' doğrulanmış tarif: ölçüler, süreler ve adım adım anlatım.'"
    :canonical-url="$seoIndexability['canonical']"
    :robots="$seoIndexability['robots']">

    <section class="hc-container py-14">
        <nav class="hc-body-sm text-muted" aria-label="Yol">
            <a class="font-semibold text-gold-700" href="{{ route('ingredients.hub.index') }}">Malzemeler</a>
            <span aria-hidden="true"> › </span>{{ $malzeme->name }}
        </nav>

        {{-- Başlık doğrudan arama niyetini karşılıyor: insanlar "hellim ile ne
             yapılır" diye arıyor, "hellim" diye değil. --}}
        <h1 class="mt-3 font-display text-4xl font-semibold">{{ $malzeme->name }} ile ne yapılır?</h1>

        <p class="hc-body-lg mt-4 max-w-2xl text-muted">
            {{ $malzeme->name }} kullanan {{ $tarifler->count() }} doğrulanmış tarif.
            Her tarifte ölçüler, süreler ve besin değerleri görünür.
        </p>

        @if (filled($malzeme->notes))
            <p class="hc-body-sm mt-4 max-w-2xl">{{ $malzeme->notes }}</p>
        @endif

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($tarifler as $tarif)
                <x-recipe-card-v3 :recipe="$tarif" :show-favorite="false" />
            @endforeach
        </div>
    </section>
</x-layouts.site>
