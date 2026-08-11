<x-layouts.site title="Koleksiyonlarım">
    <section class="hc-container py-12 sm:py-16">
        <div class="mb-10"><p class="hc-kicker">Kişisel tarif arşivi</p><h1 class="mt-2 hc-display-xl">Koleksiyonlarım</h1></div>
        <div class="grid gap-6 lg:grid-cols-[22rem_minmax(0,1fr)]">
            <section class="hc-panel" aria-labelledby="new-collection-title">
                <h2 id="new-collection-title" class="hc-heading-3">Yeni koleksiyon</h2>
            <form class="mt-5 space-y-4" method="post" action="{{ route('collections.store') }}">@csrf
                <label class="block"><span class="text-sm font-bold">Ad</span><input class="hc-input mt-1 w-full" name="name" maxlength="80" required value="{{ old('name') }}"></label>
                <label class="block"><span class="text-sm font-bold">Açıklama <span class="font-normal text-muted">(isteğe bağlı)</span></span><textarea class="hc-input mt-1 w-full" name="description" maxlength="280" rows="4">{{ old('description') }}</textarea></label>
                @if($errors->any())<x-ui.alert type="error" role="alert">{{ $errors->first() }}</x-ui.alert>@endif
                <button class="hc-v3-btn hc-v3-btn--primary" type="submit">Koleksiyon oluştur</button>
            </form>
        </section>
        <section aria-labelledby="collection-list-title">
            <div class="flex items-end justify-between gap-3"><div><h2 id="collection-list-title" class="hc-display-lg">Kaydettiğiniz gruplar</h2><p class="mt-1" style="color: var(--hc-text-muted)">Koleksiyonlar yalnız size görünür.</p></div><a class="hc-v3-btn hc-v3-btn--outline" href="{{ route('recipes.index') }}">Tarif keşfet</a></div>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">@forelse($collections as $collection)
                <a class="hc-v3-card hc-v3-card--interactive p-6" href="{{ route('collections.show', $collection) }}"><p class="hc-kicker">{{ $collection->recipes_count }} tarif</p><h3 class="mt-2 hc-heading-3">{{ $collection->name }}</h3><p class="mt-2 hc-body-sm" style="color: var(--hc-text-secondary)">{{ $collection->description ?: 'Bu koleksiyon için açıklama eklenmedi.' }}</p></a>
            @empty<div class="hc-v3-empty sm:col-span-2"><strong>Henüz koleksiyonunuz yok.</strong><p>Haftalık menü, misafir sofrası veya tekrar pişirmek istedikleriniz için özel gruplar oluşturun.</p></div>@endforelse</div>
            <div class="mt-6">{{ $collections->links() }}</div>
        </section>
        </div>
    </section>
</x-layouts.site>
