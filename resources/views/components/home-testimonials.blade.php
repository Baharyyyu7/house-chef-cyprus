@props(['testimonials' => [], 'disclosure' => null])

@php
    $items = collect($testimonials)->values();
    $columns = $items->chunk((int) max(1, ceil($items->count() / 3)))->values();
    $tracks = collect([
        ['items' => $items, 'classes' => 'md:hidden', 'duration' => 54],
    ])->concat($columns->map(fn ($column, $index) => [
        'items' => $column,
        'classes' => match ($index) {
            0 => 'hidden md:block',
            1 => 'hidden md:block',
            default => 'hidden lg:block',
        },
        'duration' => [22, 27, 24][$index] ?? 24,
    ]));
@endphp

@if($items->isNotEmpty())
    <section x-data="{ paused: false }" class="hc-testimonials-section border-y border-line bg-surface py-16 dark:border-[#2a2a2a] dark:bg-[#141414]" aria-labelledby="home-testimonials-title">
        <div class="hc-container">
            <div class="mx-auto flex max-w-2xl flex-col items-center text-center" data-reveal>
                <p class="hc-kicker rounded-full border border-orange-200 bg-orange-50 px-4 py-2 dark:border-orange-900/60 dark:bg-orange-950/30">Şeffaflık</p>
                <h2 id="home-testimonials-title" class="hc-title mt-5">Neye güvenebilirsiniz?</h2>
                <p class="mt-4 max-w-xl leading-7 text-warm dark:text-stone-400">Aşağıdakilerin hepsi tarif ve yazı sayfalarında görülebilir; iddia edip göstermediğimiz bir şey yok.</p>
                @if(filled($disclosure))
                    <p class="mt-3 text-xs font-semibold leading-5 text-muted" data-testimonial-disclosure>{{ $disclosure }}</p>
                @endif
                <button
                    type="button"
                    class="hc-btn hc-btn-outline mt-5 !min-h-9 px-4 py-2 text-xs"
                    @click="paused = !paused"
                    :aria-pressed="paused.toString()"
                    :aria-label="paused ? 'Yorum akışını sürdür' : 'Yorum akışını duraklat'"
                >
                    <span x-text="paused ? 'Akışı sürdür' : 'Akışı duraklat'">Akışı duraklat</span>
                </button>
            </div>

            <div class="hc-testimonials-window mt-10" :class="{ 'is-paused': paused }" aria-label="Temsili kullanıcı yorumları">
                <div class="flex justify-center gap-5 lg:gap-6">
                    @foreach($tracks as $track)
                        <div class="hc-testimonials-column min-w-0 flex-1 {{ $track['classes'] }}">
                            <div class="hc-testimonials-track" style="--hc-testimonials-duration: {{ $track['duration'] }}s">
                                @foreach([false, true] as $duplicate)
                                    <div class="hc-testimonials-group" @if($duplicate) aria-hidden="true" @endif>
                                        @foreach($track['items'] as $testimonial)
                                            <blockquote class="hc-testimonial-card">
                                                <span class="hc-testimonial-quote" aria-hidden="true">“</span>
                                                <p class="relative text-sm leading-7 text-ink dark:text-stone-100">{{ $testimonial['text'] }}</p>
                                                <footer class="mt-6 flex items-center gap-3 border-t border-line pt-4 dark:border-[#333]">
                                                    @if(filled($testimonial['image'] ?? null))
                                                        <img data-testimonial-avatar class="h-10 w-10 rounded-full object-cover" src="{{ $testimonial['image'] }}" alt="" loading="lazy">
                                                    @endif
                                                    <span class="min-w-0">
                                                        <cite class="block not-italic font-extrabold text-ink dark:text-stone-50">{{ $testimonial['name'] }}</cite>
                                                        <span class="mt-0.5 block text-xs text-muted">{{ $testimonial['role'] }}</span>
                                                    </span>
                                                </footer>
                                            </blockquote>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
