<x-layouts.site title="House Chef Cyprus">
    <x-slot name="title">Sipariş Onayı — {{ $order->product->title }}</x-slot>

    <div class="py-16 bg-neutral-50 dark:bg-neutral-900/60 min-h-screen">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 p-8 shadow-sm text-center">
                
                <div class="w-16 h-16 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-3xl mx-auto mb-4">
                    ✓
                </div>

                <h1 class="text-2xl sm:text-3xl font-black text-neutral-900 dark:text-white">
                    Siparişiniz Başarıyla Tamamlandı!
                </h1>

                <p class="text-sm text-neutral-600 dark:text-neutral-300 mt-2">
                    Satın aldığınız dijital ürün hazır. Aşağıdaki butona tıklayarak hemen indirebilirsiniz.
                </p>

                <div class="mt-6 p-4 rounded-2xl bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 text-left">
                    <h3 class="font-bold text-neutral-900 dark:text-white text-base">
                        {{ $order->product->title }}
                    </h3>
                    <div class="mt-2 text-xs text-neutral-500 dark:text-neutral-400 space-y-1">
                        <p>Sipariş Kodu: <span class="font-mono text-neutral-700 dark:text-neutral-300">{{ $order->order_uuid }}</span></p>
                        <p>Kalan İndirme Hakkı: <span class="font-bold text-amber-600 dark:text-amber-400">{{ $order->max_downloads - $order->download_count }} / {{ $order->max_downloads }}</span></p>
                    </div>
                </div>

                <div class="mt-8">
                    <a href="{{ route('shop.download', $order->download_token) }}" class="inline-flex items-center justify-center gap-2 w-full py-3.5 px-6 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-base transition-all shadow-md">
                        📥 Dijital Ürünü İndir (PDF)
                    </a>
                </div>

                <div class="mt-6">
                    <a href="{{ route('shop.index') }}" class="text-xs text-neutral-500 dark:text-neutral-400 underline hover:text-amber-600">
                        ← Mağazaya Geri Dön
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-layouts.site>
