<x-layouts.site title="Medya Yönetimi">
    @php
        $suzgecler = [
            'all' => ['Yayındaki Tarifler', $sayilar['total']],
            'with_image' => ['Görselli Tarifler', $sayilar['with_image']],
            'without_image' => ['Görselsiz Tarifler', $sayilar['without_image']],
            'pending' => ['Onay Bekleyenler', $sayilar['pending']],
        ];
    @endphp

    <section class="mx-auto max-w-7xl px-5 py-10">
        <x-admin.page-header title="Tarif Görselleri" eyebrow="İçerik">
            <x-slot:actions>
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
            </x-slot:actions>
        </x-admin.page-header>

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
                                    <x-admin.media-preview :item="$item" :width="800" />

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

        <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Tarif görsel özeti">
            @foreach($suzgecler as $anahtar => [$etiket, $adet])
                <a class="rounded-2xl border p-4 transition hover:-translate-y-0.5 hover:shadow-md {{ $suzgec === $anahtar ? 'border-orange-500 bg-orange-50' : 'border-stone-200 bg-white' }}"
                   href="{{ route('admin.media.index', array_filter(['durum' => $anahtar, 'q' => $arama ?: null])) }}" @if($suzgec === $anahtar) aria-current="page" @endif>
                    <span class="block text-sm font-bold text-stone-600">{{ $etiket }}</span>
                    <strong class="mt-1 block text-3xl font-black text-stone-950">{{ number_format($adet, 0, ',', '.') }}</strong>
                </a>
            @endforeach
        </div>

        <p class="mt-3 text-sm text-stone-600">Yayındaki tarif sayısı her zaman görselli ve görselsiz tariflerin toplamıdır. Yer tutucular, bloglar ve geçmiş medya kayıtları bu sayılara dahil edilmez.</p>

        <form class="mt-4 flex max-w-lg gap-2" method="get">
            <input type="hidden" name="durum" value="{{ $suzgec }}">
            <label class="sr-only" for="medya-ara">Görsel ara</label>
            <input id="medya-ara" class="hc-input flex-1" name="q" value="{{ $arama }}" placeholder="Tarif adıyla ara">
            <button class="hc-v3-btn hc-v3-btn--primary" aria-label="Ara" title="Ara"><x-ui.icon name="search" size="18" /></button>
            @if($arama !== '')<a class="hc-v3-btn hc-v3-btn--outline" href="{{ route('admin.media.index', ['durum' => $suzgec]) }}">Temizle</a>@endif
        </form>

        <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse($recipes as $recipe)
                @php
                    $item = $recipe->dashboardMedia->first();
                    [$rozet, $rozetSinif] = match ($recipe->media_dashboard_status) {
                        'with_image' => ['Gerçek görsel yayında', 'bg-emerald-100 text-emerald-900'],
                        'pending' => ['İnsan onayı bekliyor', 'bg-amber-100 text-amber-900'],
                        default => ['Görsel gerekli', 'bg-rose-100 text-rose-900'],
                    };
                @endphp
                <article class="hc-v3-card group overflow-hidden">
                    <div class="relative block aspect-[4/3] bg-stone-100 dark:bg-stone-900">
                        @if($item)
                            <x-admin.media-preview :item="$item" />
                        @else
                            <div class="grid h-full place-items-center text-center text-stone-500"><div><x-ui.icon name="image" size="34" /><span class="mt-2 block text-sm font-bold">Gerçek yemek görseli yok</span></div></div>
                        @endif
                        <span class="absolute left-3 top-3 rounded-full px-2.5 py-1 text-[11px] font-bold {{ $rozetSinif }}">{{ $rozet }}</span>
                    </div>
                    <div class="p-4">
                        <p class="font-bold text-sm">{{ $recipe->title }}</p>
                        <p class="mt-1 text-xs" style="color: var(--hc-text-muted)">{{ $recipe->cuisine?->name }}@if($recipe->category) · {{ $recipe->category->name }}@endif</p>
                        <div class="mt-3 flex items-center gap-3">
                            @if($item)<a class="text-sm font-bold text-orange-700 hover:underline" href="{{ route('admin.media.show', $item->public_id) }}">Görsel detayı</a>@endif
                            @if($item && $recipe->media_dashboard_status === 'pending')
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

        <div class="mt-8">{{ $recipes->links() }}</div>

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
