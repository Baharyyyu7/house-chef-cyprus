<x-layouts.site title="{{ $collection->name }}">
    <section class="hc-container py-12 sm:py-16">
        <div class="mb-10"><p class="hc-kicker"><a href="{{ route('collections.index') }}">Koleksiyonlarım</a></p><h1 class="mt-2 hc-display-xl">{{ $collection->name }}</h1></div>
        <section class="hc-panel">
            <form class="grid gap-4 lg:grid-cols-[1fr_2fr_auto] lg:items-end" method="post" action="{{ route('collections.update', $collection) }}">@csrf @method('patch')
            <label><span class="text-sm font-bold">Ad</span><input class="hc-input mt-1 w-full" name="name" maxlength="80" required value="{{ old('name', $collection->name) }}"></label>
            <label><span class="text-sm font-bold">Açıklama</span><input class="hc-input mt-1 w-full" name="description" maxlength="280" value="{{ old('description', $collection->description) }}"></label>
            <button class="hc-v3-btn hc-v3-btn--primary">Kaydet</button>
        </form>
        @if($errors->any())<x-ui.alert class="mt-4" type="error" role="alert">{{ $errors->first() }}</x-ui.alert>@endif
    </section>
        <section class="mt-8" aria-labelledby="collection-recipes-title">
            <div class="flex items-end justify-between"><div><p class="hc-kicker">{{ $recipes->total() }} doğrulanmış tarif</p><h2 id="collection-recipes-title" class="mt-2 hc-display-lg">Koleksiyondaki tarifler</h2></div><a class="hc-v3-btn hc-v3-btn--outline" href="{{ route('recipes.index') }}">Tarif eklemek için keşfet</a></div>
            <div class="mt-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">@forelse($recipes as $recipe)<div><x-recipe-card-v3 :recipe="$recipe" /><form class="mt-2" method="post" action="{{ route('collections.recipes.remove', ['recipeCollection' => $collection, 'recipe' => $recipe->slug]) }}">@csrf @method('delete')<button class="hc-v3-btn hc-v3-btn--ghost w-full">Koleksiyondan çıkar</button></form></div>@empty<div class="hc-v3-empty sm:col-span-2 xl:col-span-3"><strong>Bu koleksiyon henüz boş.</strong><p>Yalnız yayınlanmış ve doğrulanmış tarifler koleksiyona eklenebilir.</p></div>@endforelse</div>
            <div class="mt-6">{{ $recipes->links() }}</div>
        </section>
        <form class="mt-10 border-t pt-6" style="border-color: var(--hc-border-subtle)" method="post" action="{{ route('collections.destroy', $collection) }}" onsubmit="return confirm('Bu koleksiyonu silmek istediğinizden emin misiniz?')">@csrf @method('delete')<button class="hc-v3-btn hc-v3-btn--danger">Koleksiyonu sil</button></form>
    </section>
</x-layouts.site>
