<x-layouts.site
    title="House Chef Premium — Sınırsız AI Mutfak ve Reklamsız Deneyim"
    meta-description="Premium ile sınırsız AI tarif asistanı, haftalık otomatik menü planlama, akıllı alışveriş listesi ve tamamen reklamsız bir deneyim.">

    <div class="py-12 sm:py-16 min-h-screen" style="background: var(--hc-background-page)">
        <div class="hc-container max-w-5xl mx-auto">
            
            <!-- Header -->
            <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
                <span class="hc-v3-badge hc-v3-badge--accent text-xs uppercase tracking-widest px-3 py-1 mb-4 inline-block">
                    PREMIUM AYRICALIKLAR
                </span>
                <h1 class="hc-display-xl tracking-tight leading-tight" style="color: var(--hc-text-primary)">
                    Mutfağınızda Şef Düzeyinde <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-500 to-orange-600">AI Gücü ve Kolaylık</span>
                </h1>
                <p class="hc-body-lg mt-4" style="color: var(--hc-text-secondary)">
                    Sınırsız yapay zeka tarif asistanı, otomatik menü ve alışveriş listesi planlama ile tamamen reklamsız bir gurme deneyimi yaşayın.
                </p>
                
                @unless($paymentsEnabled)
                    <div class="hc-v3-alert hc-v3-alert--warning max-w-2xl mx-auto mt-6 text-left" role="status">
                        <span aria-hidden="true">ⓘ</span>
                        <div><strong>Henüz ödeme almıyoruz.</strong><p>Premium üyelik yakında açılacaktır; bu sayfadan hiçbir ücret tahsil edilmez.</p></div>
                    </div>
                @endunless
                @if(request('checkout') === 'success')
                    <div class="hc-v3-alert hc-v3-alert--success max-w-2xl mx-auto mt-6 text-left" role="status">
                        <span aria-hidden="true">✓</span>
                        <div><strong>Ödemeniz alındı!</strong><p>Premium üyeliğiniz güvenli ödeme bildirimi doğrulanınca etkinleşir.</p></div>
                    </div>
                @elseif(request('checkout') === 'cancelled')
                    <div class="hc-v3-alert hc-v3-alert--error max-w-2xl mx-auto mt-6 text-left" role="status">
                        <span aria-hidden="true">⚠</span>
                        <div><strong>Ödeme iptal edildi.</strong><p>Hesabınızdan hiçbir ücret tahsil edilmedi.</p></div>
                    </div>
                @endif
            </div>

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
                <div x-ref="raylar" class="hc-plan-switch mx-auto mb-8" role="tablist" aria-label="Ödeme sıklığı">
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
                <div class="grid grid-cols-1 gap-8 max-w-xl mx-auto">
                    @foreach($plans as $key => $plan)
                        <div x-show="sıklık === '{{ $key }}'" x-cloak
                            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                            class="hc-v3-card relative p-8 flex flex-col justify-between border-2 {{ $key === 'yearly' ? 'border-amber-500 shadow-lg' : '' }}">
                            
                            @if(isset($plan['discount_badge']))
                                <div class="absolute -top-3.5 right-8 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-black text-xs px-3.5 py-1 rounded-full uppercase tracking-wider shadow-sm">
                                    {{ $plan['discount_badge'] }}
                                </div>
                            @endif

                            <div>
                                <h2 class="text-2xl font-extrabold" style="color: var(--hc-text-primary)">
                                    {{ $plan['name'] }}
                                </h2>
                                <div class="mt-4 flex items-baseline gap-2">
                                    <span class="text-4xl sm:text-5xl font-black" style="color: var(--hc-text-primary)">
                                        {{ $plan['symbol'] }}{{ number_format($plan['price'], 2) }}
                                    </span>
                                    <span class="text-sm font-semibold" style="color: var(--hc-text-muted)">
                                        / {{ $plan['interval'] }}
                                    </span>
                                </div>

                                <ul class="mt-8 space-y-4">
                                    @foreach($plan['features'] as $feature)
                                        <li class="flex items-center gap-3 text-sm font-medium" style="color: var(--hc-text-secondary)">
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
                                        <button disabled class="hc-v3-btn hc-v3-btn--outline w-full py-3.5 opacity-60 cursor-not-allowed">
                                            {{ auth()->user()->premium_plan === $key ? 'Mevcut Planınız' : 'Portalda Plan Değiştirin' }}
                                        </button>
                                    @elseif($paymentsEnabled)
                                        <form action="{{ route('subscription.checkout') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="plan" value="{{ $key }}">
                                            <button type="submit" class="hc-v3-btn hc-v3-btn--primary w-full py-3.5">
                                                Hemen Başlat ➔
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" disabled class="hc-v3-btn hc-v3-btn--outline w-full py-3.5 opacity-60 cursor-not-allowed">
                                            Yakında Aktif
                                        </button>
                                    @endif
                                @else
                                    @if($paymentsEnabled)
                                        <a href="{{ route('register') }}" class="hc-v3-btn hc-v3-btn--primary block w-full py-3.5 text-center">
                                            Hesap Oluştur ve Katıl ➔
                                        </a>
                                    @else
                                        <button type="button" disabled class="hc-v3-btn hc-v3-btn--outline w-full py-3.5 opacity-60 cursor-not-allowed">
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
                    <div class="mt-10 max-w-4xl mx-auto hc-v3-card p-6 sm:p-8">
                        <h2 class="text-lg font-extrabold" style="color: var(--hc-text-primary)">Abonelik ve faturalar</h2>
                        <p class="mt-2 text-sm" style="color: var(--hc-text-secondary)">Faturalarınızı görüntülemek, ödeme yönteminizi değiştirmek veya yenilemeyi iptal etmek için Stripe müşteri portalını kullanın.</p>
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
