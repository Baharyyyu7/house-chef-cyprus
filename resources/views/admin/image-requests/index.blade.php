<x-layouts.site title="Görsel Talepleri">
    <section class="mx-auto max-w-5xl px-5 py-10"
             x-data="{
                kopyalanan: null,
                kopyala(metin, anahtar) {
                    navigator.clipboard.writeText(metin).then(() => {
                        this.kopyalanan = anahtar;
                        setTimeout(() => { if (this.kopyalanan === anahtar) this.kopyalanan = null }, 2000);
                    });
                }
             }">
        <h1 class="hc-heading-1">Görsel talepleri</h1>
        <p class="hc-body-sm mt-2 max-w-2xl" style="color: var(--hc-text-secondary)">
            Görselleri Antigravity üretiyor. Yemeği bulun, talebi kopyalayın, ona yapıştırın.
            Gelen dosyayı <code>storage/app/media-import/recipes/</code> içine koymak yeterli;
            gerisini içe aktarma yapar.
        </p>

        <div class="mt-6 grid gap-3 sm:grid-cols-3">
            <div class="hc-v3-card p-4">
                <p class="hc-kicker">Görseli hazır</p>
                <p class="mt-1 text-3xl font-black">{{ number_format($hazir) }}</p>
            </div>
            <div class="hc-v3-card p-4">
                <p class="hc-kicker">Görsel bekleyen</p>
                <p class="mt-1 text-3xl font-black text-orange-700">{{ number_format($eksik->count()) }}</p>
            </div>
            <div class="hc-v3-card p-4">
                <p class="hc-kicker">Toplam gerçek tarif</p>
                <p class="mt-1 text-3xl font-black">{{ number_format($toplam) }}</p>
            </div>
        </div>

        <form class="mt-7 flex max-w-lg gap-2" method="get">
            <label class="sr-only" for="tarif-ara">Yemek ara</label>
            <input id="tarif-ara" class="hc-input flex-1" name="q" value="{{ $arama }}" placeholder="Yemek adı yazın">
            <button class="hc-v3-btn hc-v3-btn--primary">Ara</button>
            @if($arama !== '')<a class="hc-v3-btn hc-v3-btn--outline" href="{{ route('admin.image-requests.index') }}">Temizle</a>@endif
        </form>

        @if($eksik->isEmpty())
            <div class="hc-v3-empty mt-8">
                <strong>{{ $arama !== '' ? 'Bu aramada görsel bekleyen yemek yok.' : 'Görsel bekleyen yemek kalmadı.' }}</strong>
                <p>Yeni gelen görseller için içe aktarmayı çalıştırın.</p>
            </div>
        @else
            <div class="mt-8 space-y-3">
                @foreach($eksik as $tarif)
                    <article class="hc-v3-card overflow-hidden" x-data="{ acik: false }">
                        <button type="button" @click="acik = !acik"
                                class="flex w-full items-center justify-between gap-4 p-4 text-left transition-colors duration-200 hover:bg-stone-50 dark:hover:bg-white/5"
                                :aria-expanded="acik">
                            <div class="min-w-0">
                                <p class="truncate font-bold">{{ $tarif['title'] }}</p>
                                <p class="mt-0.5 truncate text-xs" style="color: var(--hc-text-muted)">{{ $tarif['cuisine'] ?? 'Akdeniz' }} · <code>{{ $tarif['filename'] }}</code></p>
                            </div>
                            <span class="shrink-0 transition-transform duration-300" :class="acik ? 'rotate-180' : ''"><x-ui.icon name="chevron-down" size="18" /></span>
                        </button>

                        <div x-show="acik" x-collapse.duration.250ms class="border-t px-4 pb-4 pt-3" style="border-color: var(--hc-border-subtle)">
                            <pre class="max-h-64 overflow-auto whitespace-pre-wrap rounded-xl p-4 text-xs leading-relaxed" style="background: var(--hc-surface-muted)">{{ $tarif['request'] }}</pre>
                            <button type="button"
                                    @click="kopyala(@js($tarif['request']), @js($tarif['slug']))"
                                    class="hc-v3-btn hc-v3-btn--primary hc-v3-btn--sm mt-3">
                                <span x-show="kopyalanan !== @js($tarif['slug'])">Talebi kopyala</span>
                                <span x-show="kopyalanan === @js($tarif['slug'])" x-cloak class="inline-flex items-center gap-1"><x-ui.icon name="check" size="15" /> Kopyalandı</span>
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.site>
