<x-layouts.site :title="$baslik" :meta-description="$ozet" :canonical-url="route('policy.'.$sayfa)" :structured-data="$schema">
    <section class="hc-container hc-page">
        <article data-legal-document class="mx-auto max-w-3xl">
            <nav class="hc-body-sm" style="color: var(--hc-text-muted)" aria-label="{{ __('legal.location') }}">
                <a class="hover:underline" href="{{ route('legal.center') }}">{{ __('legal.breadcrumb') }}</a>
                <span aria-hidden="true">/</span>
                <span>{{ $baslik }}</span>
            </nav>

            <h1 class="hc-display-lg mt-5">{{ $baslik }}</h1>
            <p class="hc-body-lg mt-4" style="color: var(--hc-text-secondary)">{{ $ozet }}</p>
            <p class="hc-caption mt-4" style="color: var(--hc-text-muted)">{{ $updatedAt }}</p>

            <div class="mt-8 flex flex-wrap gap-2">
                <button type="button" class="hc-btn hc-btn-primary" onclick="window.print()">{{ __('legal.print') }}</button>
                <button type="button" data-copy-page class="hc-btn hc-btn-outline">{{ __('legal.copy_link') }}</button>
            </div>

            <div class="hc-prose mt-10">
                @foreach($bolumler as $bolum)
                    <section id="{{ $bolum['id'] }}" class="hc-scroll-target">
                        @unless($loop->first && $bolum['title'] === $baslik)
                            <h2>{{ $bolum['title'] }}</h2>
                        @endunless
                        @foreach($bolum['paragraphs'] as $paragraf)
                            <p>{{ $paragraf }}</p>
                        @endforeach
                    </section>
                @endforeach
            </div>

            {{-- Diğer politikalar: okuyan kişi aradığını burada bulamadıysa
                 nereye gideceğini görmeli. --}}
            <aside class="mt-14 border-t pt-8" style="border-color: var(--hc-border-subtle)">
                <p class="hc-overline" style="color: var(--hc-text-muted)">{{ __('legal.other_policies') }}</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach(config('legal-center.standalone_pages') as $anahtar => $tanim)
                        @continue($anahtar === $sayfa)
                        <a class="hc-card-hover rounded-2xl border p-4" style="border-color: var(--hc-line); background: var(--hc-surface)" href="{{ route('policy.'.$anahtar) }}">
                            <span class="block font-bold">{{ $tanim['baslik'] }}</span>
                            <span class="hc-caption mt-1 block" style="color: var(--hc-text-secondary)">{{ $tanim['ozet'] }}</span>
                        </a>
                    @endforeach
                    <a class="hc-card-hover rounded-2xl border p-4" style="border-color: var(--hc-line); background: var(--hc-surface)" href="{{ route('legal.center') }}">
                        <span class="block font-bold">{{ __('legal.breadcrumb') }}</span>
                        <span class="hc-caption mt-1 block" style="color: var(--hc-text-secondary)">{{ __('legal.center_summary') }}</span>
                    </a>
                </div>
            </aside>
        </article>
    </section>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('click', function (olay) {
        var dugme = olay.target.closest('[data-copy-page]');
        if (!dugme || !navigator.clipboard) { return; }
        navigator.clipboard.writeText(window.location.href).then(function () {
            var onceki = dugme.textContent;
            dugme.textContent = @js(__('legal.copied'));
            setTimeout(function () { dugme.textContent = onceki; }, 1800);
        });
    });
    </script>
</x-layouts.site>
