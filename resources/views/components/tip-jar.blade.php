@props([
    'title' => 'House Chef Cyprus\'a Destek Olun ☕',
    'subtitle' => 'İçeriklerimizi faydalı buluyorsanız, bize bir kahve ısmarlayarak mutfağımızın sürdürülebilirliğine katkıda bulunabilirsiniz.',
])

<div class="tip-jar-card rounded-2xl bg-gradient-to-br from-amber-500/10 via-orange-500/5 to-amber-500/10 border border-amber-500/20 p-6 shadow-sm dark:bg-neutral-800/80 my-8">
    <div class="flex items-start gap-4">
        <div class="w-12 h-12 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl flex-shrink-0">
            ☕
        </div>
        <div class="flex-1">
            <h3 class="text-lg font-bold text-neutral-900 dark:text-white">
                {{ $title }}
            </h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-300 mt-1">
                {{ $subtitle }}
            </p>

            <form action="{{ route('tip.process') }}" method="POST" class="mt-4 flex flex-wrap items-center gap-3">
                @csrf
                <div class="flex items-center gap-2">
                    <button type="submit" name="amount" value="2.00" class="px-4 py-2 rounded-xl bg-white dark:bg-neutral-700 border border-neutral-200 dark:border-neutral-600 font-semibold text-neutral-800 dark:text-neutral-100 hover:border-amber-500 hover:text-amber-600 transition-all text-sm shadow-xs">
                        €2.00 ☕
                    </button>
                    <button type="submit" name="amount" value="5.00" class="px-4 py-2 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition-all text-sm shadow-xs">
                        €5.00 🍰 (Popüler)
                    </button>
                    <button type="submit" name="amount" value="10.00" class="px-4 py-2 rounded-xl bg-white dark:bg-neutral-700 border border-neutral-200 dark:border-neutral-600 font-semibold text-neutral-800 dark:text-neutral-100 hover:border-amber-500 hover:text-amber-600 transition-all text-sm shadow-xs">
                        €10.00 🍲
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
