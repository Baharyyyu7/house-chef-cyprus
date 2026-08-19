<x-layouts.site title="Medya Yönetimi">
    @php
        // Süzgeç adları ne yaptıklarını söylemeli. "Dosyası olan" bir
        // durumu değil bir alanı tarif ediyordu; "Dosyasız kayıt" ise
        // aslında hiçbir işe yaramayan ölü satırlar demek.
        $suzgecler = [
            // Onay bekleyenler kendi bölümünde duruyordu ama şeritte yoktu:
            // sayfayı açan kimse kaç görselin onayını beklediğini görmüyordu.
            'onay' => ['Onay bekliyor', $sayilar['onay_bekleyen']],
            'dosyali' => ['Görseli hazır', $sayilar['tumu'] - $sayilar['dosyasiz']],
            'yayinda' => ['Sitede görünüyor', $sayilar['yayinda']],
            'dosyasiz' => ['Boş kayıt (görsel yok)', $sayilar['dosyasiz']],
            'tumu' => ['Tümü', $sayilar['tumu']],
        ];

        $durumRozeti = static function ($item): array {
            if ($item->approved_by_user_id !== null && $item->media_status === 'approved_real_media') {
                return ['Yayında', 'bg-emerald-100 text-emerald-900'];
            }
            if ($item->media_status === 'approved_real_media') {
                return ['Onay bekliyor', 'bg-amber-100 text-amber-900'];
            }
            if (blank($item->path)) {
                return ['Dosya yok', 'bg-rose-100 text-rose-900'];
            }

            return ['Yayında değil', 'bg-stone-200 text-stone-700'];
        };
    @endphp

    <section class="mx-auto max-w-7xl px-5 py-10">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="hc-heading-1">Medya</h1>
                <p class="hc-body-sm mt-2" style="color: var(--hc-text-secondary)">Yayındaki her görselin dosyası, boyutları ve onayı burada görünür.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                {{-- Teslim edilen görselleri onay kuyruğuna alır. Aynı iş SSH
                     ile çalıştırılıyordu; onayı verecek kişi terminale girmek
                     zorunda kalıyordu. --}}
                <form method="post" action="{{ route('admin.media.intake') }}"
                    x-data="{ calisiyor: false }" @submit="calisiyor = true">
                    @csrf
                    <button class="hc-v3-btn hc-v3-btn--outline" x-bind:disabled="calisiyor">
                        <x-ui.icon name="archive" size="17" />
                        <span x-show="! calisiyor">Teslim edilenleri içe aktar</span>
                        <span x-cloak x-show="calisiyor">Aktarılıyor…</span>
                    </button>
                </form>
                <a class="hc-v3-btn hc-v3-btn--primary" href="{{ route('admin.media.create') }}"><x-ui.icon name="image" size="17" /> Görsel yükle</a>
            </div>
        </div>

        @if(session('status'))<p class="mt-5 rounded-xl bg-emerald-50 p-3 text-sm font-semibold text-emerald-900">{{ session('status') }}</p>@endif
        @if(session('error'))<p class="mt-5 rounded-xl bg-rose-50 p-3 text-sm font-semibold text-rose-900">{{ session('error') }}</p>@endif

        {{-- Dosyası ve varyantları hazır olduğu hâlde yalnız insan onayı bekleyen
             görseller. Bu onay verilmeden görsel siteye çıkmaz; bekleyenler
             listenin içinde kaybolduğu için en üstte ayrı durur. --}}
        @if($onayBekleyen->isNotEmpty())
            {{-- Tek tek onaylamak bekleyen sayısı kadar tıklama demek. Karar
                 yine editörün: ızgaraya bakar, göstermek istediklerini işaretler
                 ve bir kez onaylar. --}}
            <form method="post" action="{{ route('admin.media.approve-many') }}"
                  class="mt-7 rounded-2xl border border-amber-300 bg-amber-50/70 p-5"
                  x-data="{ acik: true, secili: [] }">
                @csrf
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-black text-amber-950">Bu {{ $onayBekleyen->count() }} görsel yayına hazır, onayını bekliyor</h2>
                        <p class="mt-1 text-sm text-amber-900">Her birine bakın, yemeğe ait olanları işaretleyin ve tek seferde onaylayın. Onaylamadığınız hiçbiri sitede görünmez.</p>
                    </div>
                    <button type="button" @click="acik = !acik" class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">
                        <span x-text="acik ? 'Gizle' : 'Göster'"></span>
                        <span class="transition-transform duration-300" :class="acik ? 'rotate-180' : ''"><x-ui.icon name="chevron-down" size="15" /></span>
                    </button>
                </div>

                <div x-show="acik" x-collapse.duration.250ms>
                    <div class="mt-4 flex flex-wrap items-center gap-3 border-y border-amber-200 py-3">
                        <button type="button" @click="secili = @js($onayBekleyen->pluck('public_id')->all())" class="text-sm font-bold text-amber-900 hover:underline">Tümünü seç</button>
                        <button type="button" @click="secili = []" class="text-sm font-bold text-amber-900 hover:underline">Seçimi temizle</button>
                        <span class="text-sm text-amber-900" x-text="secili.length + ' seçili'"></span>
                        <button type="submit" x-bind:disabled="secili.length === 0"
                                class="ml-auto rounded-lg bg-emerald-700 px-4 py-2 text-sm font-bold text-white transition-all duration-200 hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-40">
                            Seçilenleri onayla
                        </button>
                    </div>

                    {{-- Bir yemek fotoğrafı 80 pikselde değerlendirilemez. Kararı
                         veren şey görselin kendisi, o yüzden kart görselle
                         başlıyor ve yemeğin adı onun altında duruyor. Kodlar
                         ve ölçüler karara girmiyor; detay sayfasında kalıyor. --}}
                    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($onayBekleyen as $item)
                            <label class="group block cursor-pointer overflow-hidden rounded-2xl bg-white shadow-sm transition-all duration-200 hover:shadow-lg"
                                   :class="secili.includes(@js($item->public_id)) ? 'ring-2 ring-emerald-600' : ''">
                                <span class="relative block aspect-[4/3] bg-stone-100">
                                    <img class="h-full w-full object-cover" src="{{ $item->responsiveUrl(800) }}" alt="{{ $item->alt_text }}" loading="lazy">

                                    <span class="absolute left-3 top-3 grid h-7 w-7 place-items-center rounded-lg bg-white/90 shadow backdrop-blur">
                                        <input type="checkbox" name="public_ids[]" value="{{ $item->public_id }}" x-model="secili" class="h-4 w-4 rounded border-stone-300 text-emerald-700 focus:ring-emerald-600">
                                    </span>

                                    <a class="absolute bottom-3 right-3 rounded-lg bg-black/60 px-2.5 py-1.5 text-xs font-bold text-white opacity-0 backdrop-blur transition-opacity duration-200 group-hover:opacity-100 focus:opacity-100"
                                       href="{{ route('admin.media.show', $item->public_id) }}">Tam boy</a>
                                </span>

                                <span class="flex items-center justify-between gap-3 p-3.5">
                                    <span class="min-w-0">
                                        <span class="block truncate font-bold">{{ $item->mediable?->title ?? class_basename($item->mediable_type) }}</span>
                                        <span class="hc-caption block" style="color: var(--hc-text-muted)">Onay bekliyor</span>
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </form>
        @endif

        <div class="mt-8 flex flex-wrap items-center gap-2">
            @foreach($suzgecler as $anahtar => [$etiket, $adet])
                <a href="{{ route('admin.media.index', array_filter(['durum' => $anahtar, 'q' => $arama ?: null])) }}" @class([
                    'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-bold transition-all duration-200',
                    'bg-stone-900 text-white shadow-sm' => $suzgec === $anahtar,
                    'bg-stone-100 text-stone-700 hover:bg-stone-200' => $suzgec !== $anahtar,
                ])>
                    {{ $etiket }}
                    <span @class(['rounded-full px-2 py-0.5 text-xs', 'bg-white/20' => $suzgec === $anahtar, 'bg-white' => $suzgec !== $anahtar])>{{ number_format($adet) }}</span>
                </a>
            @endforeach
        </div>

        <form class="mt-4 flex max-w-lg gap-2" method="get">
            <input type="hidden" name="durum" value="{{ $suzgec }}">
            <label class="sr-only" for="medya-ara">Görsel ara</label>
            <input id="medya-ara" class="hc-input flex-1" name="q" value="{{ $arama }}" placeholder="Yemek veya yazı adıyla ara">
            <button class="hc-v3-btn hc-v3-btn--primary">Ara</button>
            @if($arama !== '')<a class="hc-v3-btn hc-v3-btn--outline" href="{{ route('admin.media.index', ['durum' => $suzgec]) }}">Temizle</a>@endif
        </form>

        <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse($media as $item)
                @php([$rozet, $rozetSinif] = $durumRozeti($item))
                <article class="hc-v3-card group overflow-hidden transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                    <a class="relative block aspect-[4/3] bg-stone-100 dark:bg-stone-900" href="{{ route('admin.media.show', $item->public_id) }}">
                        @if(filled($item->path))
                            <img class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]" src="{{ $item->responsiveUrl(400) }}" alt="{{ $item->alt_text }}" loading="lazy">
                        @else
                            <span class="grid h-full w-full place-items-center text-sm font-semibold" style="color: var(--hc-text-muted)">Dosya yok</span>
                        @endif
                        <span class="absolute left-3 top-3 rounded-full px-2.5 py-1 text-[11px] font-bold {{ $rozetSinif }}">{{ $rozet }}</span>
                    </a>
                    <div class="p-4">
                        <p class="truncate font-bold">{{ $item->mediable?->title ?? '—' }}</p>
                        <p class="mt-1 text-xs" style="color: var(--hc-text-muted)">
                            {{ class_basename($item->mediable_type) === 'Recipe' ? 'Tarif' : 'Blog' }}
                            @if($item->width) · {{ $item->width }}×{{ $item->height }} @endif
                            @if($item->size) · {{ number_format($item->size / 1024) }} KB @endif
                        </p>
                        <div class="mt-3 flex items-center gap-3">
                            <a class="text-sm font-bold text-orange-700 hover:underline" href="{{ route('admin.media.show', $item->public_id) }}">Detay</a>
                            @if($item->media_status === 'approved_real_media' && $item->approved_by_user_id === null && filled($item->path))
                                <form method="post" action="{{ route('admin.media.approve', $item->public_id) }}">@csrf<button class="text-sm font-bold text-emerald-700 hover:underline">Onayla</button></form>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="hc-v3-empty sm:col-span-2 lg:col-span-3 xl:col-span-4">
                    <strong>Bu süzgeçte görsel yok.</strong>
                    <p>Başka bir durum seçin ya da aramayı temizleyin.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">{{ $media->links() }}</div>

        <details class="mt-10 rounded-2xl border p-5" style="border-color: var(--hc-border-subtle)">
            <summary class="cursor-pointer font-bold">Bakım araçları</summary>
            <div class="mt-4 flex flex-wrap gap-3">
                <form method="post" action="{{ route('admin.media.scan-orphans') }}">@csrf<button class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Orphan tara</button></form>
                <form method="post" action="{{ route('admin.media.cleanup-temporary') }}">@csrf<button class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Geçicileri tara</button></form>
            </div>
            <p class="mt-3 text-xs" style="color: var(--hc-text-muted)">İkisi de dry-run çalışır; hiçbir dosyayı silmez.</p>
        </details>
    </section>
</x-layouts.site>
