<x-layouts.site
    title="House Chef Premium"
    meta-description="House Chef Premium avantajlarını keşfedin. Güvenli ödeme altyapısı tamamlandığında üyelik başvuruları açılacaktır."
>
    <section class="hc-section min-h-[70vh]">
        <div class="hc-container">
            <div class="mx-auto max-w-3xl text-center">
                <span class="inline-flex items-center rounded-full bg-amber-100 px-4 py-2 text-xs font-black uppercase tracking-[.16em] text-amber-800 dark:bg-amber-400/10 dark:text-amber-300">👑 Premium ayrıcalıkları</span>
                <h1 class="mt-6 text-4xl font-black tracking-tight text-charcoal-950 dark:text-white sm:text-6xl">Mutfağınız için daha güçlü bir deneyim</h1>
                <p class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-charcoal-600 dark:text-charcoal-300">Sınırsız AI mutfak asistanı, gelişmiş menü planlama ve reklamsız kullanım için hazırlıklarımız sürüyor.</p>
                <div class="hc-alert mt-8 text-left" role="status">
                    <strong>Henüz ödeme almıyoruz.</strong> Güvenli ödeme ve doğrulanmış abonelik sistemi tamamlandığında Premium üyelik burada açılacak.
                </div>
            </div>

            <div class="mx-auto mt-12 grid max-w-5xl gap-5 md:grid-cols-3">
                @foreach([
                    ['AI Mutfak Asistanı', 'Tarif, alternatif malzeme ve mutfak planlamasında daha yüksek kullanım limitleri.'],
                    ['Akıllı Menü Planlama', 'Haftalık menü ve alışveriş hazırlığını tek bir düzenli akışta yönetin.'],
                    ['Reklamsız Deneyim', 'Tariflere ve rehberlere daha sakin, kesintisiz bir arayüzle ulaşın.'],
                ] as [$heading, $copy])
                    <article class="hc-card p-6 text-left">
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-amber-100 text-xl dark:bg-amber-400/10" aria-hidden="true">✓</span>
                        <h2 class="mt-5 text-lg font-black text-charcoal-950 dark:text-white">{{ $heading }}</h2>
                        <p class="mt-3 text-sm leading-7 text-charcoal-600 dark:text-charcoal-300">{{ $copy }}</p>
                    </article>
                @endforeach
            </div>

            <div class="mt-10 text-center">
                <a href="{{ route('register') }}" class="hc-btn hc-btn-primary">Ücretsiz hesabını oluştur</a>
            </div>
        </div>
    </section>
</x-layouts.site>
