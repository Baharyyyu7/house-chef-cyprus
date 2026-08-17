<x-layouts.site title="House Chef Cyprus">
    <x-slot name="title">Dijital Mutfak Mağazası — E-Kitap ve Özel Menü Paketleri</x-slot>
    <x-slot name="description">House Chef Cyprus özel PDF yemek kitapları, dijital menü planları ve gurme mutfak rehberlerini keşfedin.</x-slot>

    <div class="py-12 bg-neutral-50 dark:bg-neutral-900/60 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-12">
                <span class="inline-block px-4 py-1.5 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold text-xs uppercase tracking-widest mb-4">
                    DİJİTAL MAĞAZA
                </span>
                <h1 class="text-3xl sm:text-5xl font-black text-neutral-900 dark:text-white tracking-tight">
                    Özel E-Kitaplar & Diyet Rehberleri
                </h1>
                <p class="mt-4 text-base text-neutral-600 dark:text-neutral-300">
                    Kıbrıs mutfağının tüm inceliklerini ve haftalık pratik beslenme programlarını cihazınıza indirin.
                </p>
            </div>

            <!-- Products Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                @foreach($products as $product)
                    <div class="rounded-3xl bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                        <div>
                            @if($product->cover_image)
                                <div class="aspect-16/9 bg-neutral-100 dark:bg-neutral-900 overflow-hidden">
                                    <img src="{{ $product->cover_image }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                                </div>
                            @endif
                            <div class="p-6">
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 bg-amber-500/10 px-3 py-1 rounded-full">
                                        E-KITAP (PDF)
                                    </span>
                                    @if($product->page_count)
                                        <span class="text-xs text-neutral-500 dark:text-neutral-400">
                                            📄 {{ $product->page_count }} Sayfa
                                        </span>
                                    @endif
                                </div>
                                <h2 class="text-xl font-bold text-neutral-900 dark:text-white mt-2">
                                    {{ $product->title }}
                                </h2>
                                <p class="text-sm text-neutral-600 dark:text-neutral-300 mt-2">
                                    {{ $product->description }}
                                </p>
                            </div>
                        </div>

                        <div class="p-6 pt-0 border-t border-neutral-100 dark:border-neutral-700/60 mt-4 flex items-center justify-between">
                            <div>
                                <span class="text-xs text-neutral-400 dark:text-neutral-500 block">Fiyat</span>
                                <span class="text-2xl font-black text-neutral-900 dark:text-white">
                                    €{{ number_format($product->price, 2) }}
                                </span>
                            </div>

                            @if($paymentsEnabled)
                                <form action="{{ route('shop.checkout', $product->slug) }}" method="POST">
                                    @csrf
                                    @auth
                                        <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                                        <input type="hidden" name="name" value="{{ auth()->user()->name }}">
                                    @else
                                        <div class="mb-3 grid gap-2">
                                            <label class="sr-only" for="buyer-name-{{ $product->id }}">Adınız</label>
                                            <input id="buyer-name-{{ $product->id }}" name="name" required maxlength="255" autocomplete="name" placeholder="Adınız" class="hc-input text-sm">
                                            <label class="sr-only" for="buyer-email-{{ $product->id }}">E-posta adresiniz</label>
                                            <input id="buyer-email-{{ $product->id }}" type="email" name="email" required autocomplete="email" placeholder="E-posta adresiniz" class="hc-input text-sm">
                                        </div>
                                    @endauth
                                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm transition-all shadow-xs">
                                        Güvenli Ödemeye Geç ➔
                                    </button>
                                </form>
                            @else
                                <button type="button" disabled class="px-5 py-2.5 rounded-xl bg-neutral-200 dark:bg-neutral-700 text-neutral-500 font-bold text-sm cursor-not-allowed">
                                    Yakında
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-layouts.site>
