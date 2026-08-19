<x-layouts.site
    title="House Chef Premium — Sınırsız AI Mutfak ve Reklamsız Deneyim"
    meta-description="Premium ile sınırsız AI tarif asistanı, haftalık otomatik menü planlama, akıllı alışveriş listesi ve tamamen reklamsız bir deneyim.">

    <div class="py-12 bg-neutral-50 dark:bg-neutral-900/60 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="inline-block px-4 py-1.5 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold text-xs uppercase tracking-widest mb-4">
                    PREMIUM AYRICALIKLAR
                </span>
                <h1 class="text-3xl sm:text-5xl font-black text-neutral-900 dark:text-white tracking-tight leading-tight">
                    Mutfağınızda Şef Düzeyinde <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-500 to-orange-600">AI Gücü ve Kolaylık</span>
                </h1>
                <p class="mt-4 text-base sm:text-lg text-neutral-600 dark:text-neutral-300">
                    Sınırsız yapay zeka tarif asistanı, otomatik menü ve alışveriş listesi planlama ile tamamen reklamsız bir gurme deneyimi yaşayın.
                </p>
                {{-- Ödeme açık değilken bunu açıkça söylemek bir güvenlik şartı:
                     sayfa görünür kalır ama kimse ödeme yaptığını sanmaz. --}}
                @unless($paymentsEnabled)
                    <div class="hc-alert mt-6" role="status">Henüz ödeme almıyoruz. Premium üyelik yakında açılacak; bu sayfadan hiçbir ücret tahsil edilmez.</div>
                @endunless
                @if(request('checkout') === 'success')
                    <div class="hc-alert hc-alert-success mt-6" role="status">Ödemeniz alındı. Premium üyeliğiniz güvenli ödeme bildirimi doğrulanınca etkinleşir.</div>
                @elseif(request('checkout') === 'cancelled')
                    <div class="hc-alert hc-alert-error mt-6" role="status">Ödeme iptal edildi; hesabınızdan ücret alınmadı.</div>
                @endif
            </div>

            {{-- Ödeme sıklığı bir seçim, iki ayrı ürün değil. Segmentli anahtar
                 seçileni taşıyan bir yüzeyle gösterir; kart altında değişir. --}}
            <div
                x-data="{
                    sıklık: 'yearly',
                    gostergeStil: '',
                    kaydir() {
                        const secili = this.$refs.raylar?.querySelector('[data-secili=\'true\']');
                        if (! secili) return;
                        this.gostergeStil = `transform: translateX(${secili.offsetLeft}px); width: ${secili.offsetWidth}px;`;
                    },
                }"
                x-init="$nextTick(() => kaydir()); $watch('sıklık', () => kaydir()); window.addEventListener('resize', () => kaydir())"
                class="max-w-4xl mx-auto"
            >
                {{-- Seçili yüzey sekmeden sekmeye kayar, iki ayrı yerde yanıp
                     sönmez: göz nereye gittiğini takip edebilsin. --}}
                <div x-ref="raylar" class="hc-plan-switch mx-auto" role="tablist" aria-label="Ödeme sıklığı">
                    <span class="hc-plan-switch__gosterge" :style="gostergeStil" aria-hidden="true"></span>
                    @foreach($plans as $key => $plan)
                        <button type="button" role="tab" :aria-selected="(sıklık === '{{ $key }}').toString()"
                            :data-secili="(sıklık === '{{ $key }}').toString()"
                            @click="sıklık = '{{ $key }}'"
                            class="hc-plan-switch__tab" :class="sıklık === '{{ $key }}' ? 'is-selected' : ''">
                            <span>{{ $key === 'yearly' ? 'Yıllık' : 'Aylık' }}</span>
                            @isset($plan['discount_badge'])
                                <span class="hc-plan-switch__badge">{{ $plan['discount_badge'] }}</span>
                            @endisset
                        </button>
                    @endforeach
                </div>

            <!-- Pricing Grid -->
            <div class="mt-8 grid grid-cols-1 gap-8 max-w-xl mx-auto">
                @foreach($plans as $key => $plan)
                    <div x-show="sıklık === '{{ $key }}'" x-cloak
                        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                        class="relative rounded-3xl bg-white dark:bg-neutral-800 border-2 {{ $key === 'yearly' ? 'border-amber-500 shadow-xl' : 'border-neutral-200 dark:border-neutral-700 shadow-sm' }} p-8 flex flex-col justify-between transition-all">
                        
                        @if(isset($plan['discount_badge']))
                            <div class="absolute -top-3.5 right-8 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-black text-xs px-3.5 py-1 rounded-full uppercase tracking-wider shadow-sm">
                                {{ $plan['discount_badge'] }}
                            </div>
                        @endif

                        <div>
                            <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">
                                {{ $plan['name'] }}
                            </h2>
                            <div class="mt-4 flex items-baseline gap-2">
                                <span class="text-4xl sm:text-5xl font-black text-neutral-900 dark:text-white">
                                    {{ $plan['symbol'] }}{{ number_format($plan['price'], 2) }}
                                </span>
                                <span class="text-sm font-semibold text-neutral-500 dark:text-neutral-400">
                                    / {{ $plan['interval'] }}
                                </span>
                            </div>

                            <ul class="mt-8 space-y-4">
                                @foreach($plan['features'] as $feature)
                                    <li class="flex items-center gap-3 text-sm text-neutral-700 dark:text-neutral-200">
                                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="mt-10">
                            @auth
                                @if(auth()->user()->isPremium())
                                    <button disabled class="w-full py-3.5 px-6 rounded-2xl bg-neutral-200 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300 font-bold text-center cursor-not-allowed">
                                        {{ auth()->user()->premium_plan === $key ? 'Mevcut Planınız' : 'Portalda Plan Değiştirin' }}
                                    </button>
                                @elseif($paymentsEnabled)
                                    <form action="{{ route('subscription.checkout') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="plan" value="{{ $key }}">
                                        <button type="submit" class="w-full py-3.5 px-6 rounded-2xl {{ $key === 'yearly' ? 'bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white shadow-md' : 'bg-neutral-900 hover:bg-neutral-800 dark:bg-white dark:hover:bg-neutral-100 text-white dark:text-neutral-900' }} font-bold text-center transition-all">
                                            Hemen Başlat ➔
                                        </button>
                                    </form>
                                @else
                                    <button type="button" disabled class="w-full py-3.5 px-6 rounded-2xl bg-neutral-200 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300 font-bold text-center cursor-not-allowed">
                                        Yakında Aktif
                                    </button>
                                @endif
                            @else
                                @if($paymentsEnabled)
                                    <a href="{{ route('register') }}" class="block w-full py-3.5 px-6 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-center transition-all shadow-md">
                                        Hesap Oluştur ve Katıl ➔
                                    </a>
                                @else
                                    <button type="button" disabled class="w-full py-3.5 px-6 rounded-2xl bg-neutral-200 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300 font-bold text-center cursor-not-allowed">
                                        Yakında Aktif
                                    </button>
                                @endif
                            @endauth
                        </div>
                    </div>
                @endforeach
            </div>
            </div>

            @auth
                @if(auth()->user()->isPremium())
                    <div class="mt-10 max-w-4xl mx-auto rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
                        <h2 class="text-lg font-black text-neutral-900 dark:text-white">Abonelik ve faturalar</h2>
                        <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">Faturalarınızı görüntülemek, ödeme yönteminizi değiştirmek veya yenilemeyi iptal etmek için Stripe müşteri portalını kullanın.</p>
                        <form action="{{ route('subscription.portal') }}" method="POST" class="mt-4">
                            @csrf
                            <button type="submit" class="hc-v3-btn hc-v3-btn--outline">Aboneliği ve faturaları yönet</button>
                        </form>
                    </div>
                @endif
            @endauth

            <!-- Tip Jar Section -->
            <div class="mt-16 max-w-4xl mx-auto">
                <x-tip-jar />
            </div>

        </div>
    </div>
</x-layouts.site>
