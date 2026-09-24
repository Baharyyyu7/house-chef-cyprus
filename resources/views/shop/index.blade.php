<x-layouts.site
    title="Dijital Mutfak Mağazası — E-Kitap ve Menü Paketleri · House Chef Cyprus"
    meta-description="Kıbrıs ve Akdeniz mutfağından derlenmiş tariflerin PDF e-kitabı. Sitede yayında olan, ölçüleri doğrulanmış tariflerden hazırlandı; indirip çevrimdışı kullanın.">

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
                                    {{ $product->formattedPrice() }}
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
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <a href="https://api.whatsapp.com/send?text={{ urlencode('Merhaba, House Chef Cyprus üzerinden "' . $product->title . '" (' . $product->formattedPrice() . ') e-kitabını FAST/Havale ile satın almak istiyorum. Hesap bilgilerini iletir misiniz?') }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-all shadow-xs inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.311.045-.698.055-1.146-.088-.3-.095-.694-.23-1.196-.445-2.107-.905-3.486-3.039-3.59-3.18-.105-.14-1.026-1.365-1.026-2.604 0-1.238.647-1.849.877-2.099.231-.25.503-.312.67-.312.167 0 .334.002.48.01.156.008.364-.06.57.433.212.508.723 1.764.787 1.892.063.13.105.281.02.45-.084.168-.125.271-.25.417-.126.147-.264.327-.377.439-.126.125-.257.262-.11.514.146.251.651 1.074 1.397 1.738.96.855 1.769 1.121 2.022 1.246.252.126.4-.041.547-.21.147-.168.629-.732.796-.983.167-.251.334-.209.564-.125.231.084 1.464.69 1.716.816.251.125.419.188.48.293.063.104.063.606-.081 1.011z"/></svg>
                                        <span>WhatsApp ile Satın Al ➔</span>
                                    </a>
                                    <a href="mailto:destek@housechefcyprus.com?subject={{ urlencode($product->title . ' E-Kitap Satın Alma') }}&body={{ urlencode('Merhaba, ' . $product->title . ' e-kitabını FAST/Havale ile satın almak istiyorum.') }}"
                                       class="px-3 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-600 hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-200 font-bold text-xs transition-all inline-flex items-center">
                                        <span>E-posta ile Talep</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-layouts.site>
