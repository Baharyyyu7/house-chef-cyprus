@props([
    'product',
])

<div class="affiliate-product-card rounded-2xl bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 p-4 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
    <div>
        @if($product->image_url)
            <div class="aspect-4/3 rounded-xl overflow-hidden bg-neutral-100 dark:bg-neutral-900 mb-3">
                <img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            </div>
        @endif

        <span class="inline-block text-[11px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2.5 py-1 rounded-full mb-2">
            {{ ucfirst($product->provider) }}
        </span>

        <h4 class="font-bold text-neutral-900 dark:text-white text-base line-clamp-2">
            {{ $product->title }}
        </h4>

        @if($product->description)
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1 line-clamp-2">
                {{ $product->description }}
            </p>
        @endif
    </div>

    <div class="mt-4 pt-3 border-t border-neutral-100 dark:border-neutral-700/60 flex items-center justify-between">
        @if($product->price)
            <div class="font-extrabold text-neutral-900 dark:text-white text-lg">
                {{ number_format($product->price, 2) }} {{ $product->currency }}
            </div>
        @else
            <div></div>
        @endif

        <a href="{{ route('affiliate.redirect', $product->slug) }}" target="_blank" rel="sponsored nofollow" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-neutral-900 hover:bg-neutral-800 dark:bg-amber-500 dark:hover:bg-amber-600 text-white font-semibold text-xs transition-all shadow-xs">
            İncele & Satın Al ➔
        </a>
    </div>
</div>
