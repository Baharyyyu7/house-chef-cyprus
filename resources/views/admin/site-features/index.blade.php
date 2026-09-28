<x-layouts.site title="Geliştirmeler">
    <section class="hc-admin-dashboard hc-v3-section-reveal">
        <div class="hc-admin-page-header">
            <div>
                <p class="hc-kicker">YAYIN KONTROLÜ</p>
                <h1 class="hc-heading-1 mt-2">Geliştirmeler</h1>
                <p class="hc-body-sm mt-3 max-w-2xl" style="color:var(--hc-text-secondary)">Hazır olmayan ürün alanları menüden, doğrudan erişimden ve sitemap'ten kapalı tutulur. Bir alanı yalnız içerik ve akışları tamamlandığında açın.</p>
            </div>
        </div>

        <form method="post" action="{{ route('admin.site-features.update') }}" class="mt-7 space-y-4">
            @csrf
            @method('put')
            @foreach($definitions as $key => $definition)
                <label class="hc-v3-card flex cursor-pointer items-start justify-between gap-5 p-5">
                    <span><strong class="hc-heading-4">{{ $definition['label'] }}</strong><span class="hc-body-sm mt-1 block" style="color:var(--hc-text-secondary)">{{ $definition['description'] }}</span></span>
                    <span class="flex shrink-0 items-center gap-2"><input type="checkbox" name="enabled[]" value="{{ $key }}" @checked($states[$key] ?? false) class="h-5 w-5 rounded border-stone-300 text-orange-600 focus:ring-orange-500"><span class="text-sm font-bold">Açık</span></span>
                </label>
            @endforeach
            <button class="hc-v3-btn hc-v3-btn--primary" type="submit">Görünürlükleri kaydet</button>
        </form>
    </section>
</x-layouts.site>
