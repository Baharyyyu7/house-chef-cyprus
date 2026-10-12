<x-layouts.site :title="$session->recipe->title.' · Yemek yapma modu'">
    @php($completedIds = $session->completedSteps->pluck('id')->all())
    @php($totalSteps = $session->recipe->steps->count())
    @php($completedCount = count($completedIds))

    <section class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8" x-data="{ progress: {{ $progress }}, activeTab: 'steps' }" @keydown.arrow-right.window="document.querySelector('[data-next-step]')?.click()" @keydown.arrow-left.window="document.querySelector('[data-prev-step]')?.click()">
        <!-- Header & Progress Card -->
        <header class="hc-v3-card bg-charcoal-950 p-6 text-white sm:p-8">
            <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="hc-overline text-orange-300">Yemek yapma modu</span>
                        <span class="hc-v3-badge border-white/20 bg-white/10 text-white">{{ $session->status === 'paused' ? '⏸ Duraklatıldı' : '▶ Canlı oturum' }}</span>
                    </div>
                    <h1 class="mt-2 hc-display-lg text-white">{{ $session->recipe->title }}</h1>
                    <p class="mt-2 text-sm text-stone-300">{{ $session->servings_snapshot }} porsiyon · Toplam {{ $totalSteps }} adım · {{ $completedCount }}/{{ $totalSteps }} adım tamamlandı</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    @if($session->status === 'paused')
                        <form method="post" action="{{ route('cooking-sessions.resume', $session) }}">
                            @csrf
                            <button class="hc-v3-btn hc-v3-btn--primary min-h-12 px-5"><x-ui.icon name="play" size="18" />Devam et</button>
                        </form>
                    @else
                        <form method="post" action="{{ route('cooking-sessions.pause', $session) }}">
                            @csrf
                            <button class="hc-v3-btn hc-v3-btn--outline border-white/20 bg-white/10 text-white min-h-12 px-5"><x-ui.icon name="pause" size="18" />Duraklat</button>
                        </form>
                    @endif
                    <form method="post" action="{{ route('cooking-sessions.complete', $session) }}">
                        @csrf
                        <button class="hc-v3-btn hc-v3-btn--ghost border border-white/20 bg-white/5 text-stone-200 hover:bg-white/15 min-h-12 px-5"><x-ui.icon name="check-circle" size="18" />Tamamla</button>
                    </form>
                    <a href="{{ route('recipes.show', $session->recipe) }}" class="hc-v3-btn hc-v3-btn--outline border-white/20 bg-white/10 text-stone-300 hover:text-white min-h-12 px-4" title="Tarif sayfasına dön">
                        <x-ui.icon name="arrow-left" size="18" />Tarife dön
                    </a>
                </div>
            </div>

            <!-- Visual Progress Bar -->
            <div class="mt-6">
                <div class="flex items-center justify-between text-xs font-bold text-stone-300 mb-2">
                    <span>Oturum İlerlemesi</span>
                    <span x-text="`${progress}%`"></span>
                </div>
                <div class="h-3 overflow-hidden rounded-full bg-white/15" role="progressbar" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100">
                    <div class="h-full bg-gradient-to-r from-orange-500 to-orange-400 transition-all duration-500 motion-reduce:transition-none" :style="`width: ${progress}%`"></div>
                </div>
            </div>
        </header>

        @if(session('status'))
            <x-ui.alert type="success" class="mt-5">{{ session('status') }}</x-ui.alert>
        @endif

        <!-- Main Cooking Workspace -->
        <main class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <!-- Steps Feed -->
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="hc-display-lg">Hazırlık Adımları</h2>
                    <span class="text-xs text-[var(--hc-text-muted)] hidden sm:inline">Klavye: Sol/Sağ yön tuşlarıyla adım geçişi yapabilirsiniz</span>
                </div>

                @foreach($session->recipe->steps as $index => $step)
                    @php($done = in_array($step->id, $completedIds, true))
                    <article class="hc-v3-card transition-all duration-200 p-6 sm:p-7 {{ $done ? 'border-emerald-500/30 bg-emerald-50/20 dark:bg-emerald-950/10' : 'hover:shadow-lg' }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl {{ $done ? 'bg-emerald-500 text-white' : 'bg-orange-500/15 text-orange-600 dark:text-orange-300' }} font-display text-base font-extrabold">
                                    {{ $done ? '✓' : $step->position }}
                                </span>
                                <div>
                                    <p class="hc-overline {{ $done ? 'text-emerald-700 dark:text-emerald-300' : 'text-orange-600 dark:text-orange-300' }}">Adım {{ $step->position }} · {{ $step->is_optional ? 'İsteğe bağlı' : 'Gerekli' }}</p>
                                    <h3 class="mt-1 hc-heading-3">{{ $step->title ?: ($step->position.'. Hazırlık Adımı') }}</h3>
                                </div>
                            </div>
                            @if($done)
                                <span class="hc-v3-badge border-emerald-300 bg-emerald-100 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">✓ Tamamlandı</span>
                            @endif
                        </div>

                        <!-- Step Text -->
                        <p class="mt-5 text-base leading-relaxed text-[var(--hc-text-primary)] sm:text-lg sm:leading-8">{{ $step->instruction }}</p>

                        <!-- Step Metadata Badges -->
                        <div class="mt-5 flex flex-wrap gap-2 text-sm">
                            @if($step->duration_seconds)
                                <span class="hc-v3-chip font-bold text-orange-700 dark:text-orange-300 bg-orange-50 dark:bg-orange-950/40 border border-orange-200 dark:border-orange-900/50">⏱ {{ \App\Support\SureMetni::dakika(intdiv($step->duration_seconds, 60)) }}</span>
                            @endif
                            @if($step->temperature)
                                <span class="hc-v3-chip font-bold text-stone-700 dark:text-stone-300 bg-stone-100 dark:bg-stone-800">🔥 {{ $step->temperature }}</span>
                            @endif
                        </div>

                        <!-- Action Buttons (Min 48px height for kitchen usage) -->
                        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-[var(--hc-border-subtle)] pt-5">
                            <div>
                                @if($done)
                                    <form method="post" action="{{ route('cooking-sessions.steps.undo', [$session, $step]) }}">
                                        @csrf @method('delete')
                                        <button class="hc-v3-btn hc-v3-btn--outline border-stone-300 text-stone-700 dark:border-stone-700 dark:text-stone-300 min-h-12 px-5">
                                            <x-ui.icon name="undo" size="18" />Adımı Geri Al
                                        </button>
                                    </form>
                                @else
                                    <form method="post" action="{{ route('cooking-sessions.steps.complete', [$session, $step]) }}">
                                        @csrf
                                        <button class="hc-v3-btn hc-v3-btn--primary min-h-12 px-6 shadow-md hover:shadow-lg" aria-label="{{ $step->position }}. adımı tamamla">
                                            <x-ui.icon name="check" size="20" />Adımı Tamamla
                                        </button>
                                    </form>
                                @endif
                            </div>

                            @if($step->timer_required && $step->duration_seconds)
                                <form method="post" action="{{ route('cooking-sessions.timers.create', [$session, $step]) }}">
                                    @csrf
                                    <input type="hidden" name="duration_seconds" value="{{ $step->duration_seconds }}">
                                    <button class="hc-v3-btn hc-v3-btn--outline min-h-12 px-4 text-orange-600 border-orange-300 hover:bg-orange-50 dark:text-orange-300 dark:border-orange-800">
                                        <x-ui.icon name="clock" size="18" />Zamanlayıcı Başlat ({{ intdiv($step->duration_seconds, 60) }} dk)
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Side Tools Panel (Timers & Equipment) -->
            <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
                <section class="hc-v3-card p-6 shadow-md">
                    <div class="flex items-center justify-between border-b border-[var(--hc-border-subtle)] pb-4">
                        <div>
                            <h2 class="hc-heading-4">⏱ Zamanlayıcılar</h2>
                            <p class="mt-0.5 text-xs text-[var(--hc-text-muted)]">Canlı pişirme zamanlayıcıları</p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse($session->timers as $timer)
                            <div class="rounded-2xl border border-[var(--hc-border-subtle)] bg-[var(--hc-surface-soft)] p-4 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <strong class="text-sm font-bold text-[var(--hc-text-primary)]">{{ $timer->label }}</strong>
                                    <span class="hc-v3-badge {{ $timer->status === 'running' ? 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-950 dark:text-orange-200' : 'bg-stone-100 text-stone-700 dark:bg-stone-800 dark:text-stone-300' }}">
                                        {{ $timer->status === 'running' ? '▶ Çalışıyor' : ($timer->status === 'paused' ? '⏸ Duraklatıldı' : '✓ Tamamlandı') }}
                                    </span>
                                </div>
                                <p class="mt-2 text-2xl font-extrabold font-mono text-[var(--hc-text-primary)]">
                                    {{ gmdate('i:s', max(0, $timerService->remainingSeconds($timer))) }}
                                </p>
                                <div class="mt-3 flex items-center gap-2">
                                    @if($timer->status === 'running')
                                        <form method="post" action="{{ route('cooking-sessions.timers.pause', [$session, $timer]) }}">
                                            @csrf
                                            <button class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm min-h-9 px-3">Duraklat</button>
                                        </form>
                                    @elseif($timer->status === 'paused')
                                        <form method="post" action="{{ route('cooking-sessions.timers.resume', [$session, $timer]) }}">
                                            @csrf
                                            <button class="hc-v3-btn hc-v3-btn--primary hc-v3-btn--sm min-h-9 px-3">Devam et</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-[var(--hc-border-subtle)] p-5 text-center">
                                <p class="text-xs leading-5 text-[var(--hc-text-muted)]">Bu oturumda aktif zamanlayıcı bulunmuyor. Adımlardaki zamanlayıcı butonlarını kullanabilirsiniz.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </aside>
        </main>
    </section>
</x-layouts.site>

