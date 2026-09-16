@props([
    'title' => '',
    'url' => null,
    'image' => null,
    'description' => '',
])

@php
    $shareUrl = $url ?: url()->current();
    $encodedUrl = urlencode($shareUrl);
    $encodedTitle = urlencode($title);
    $encodedImage = $image ? urlencode($image) : '';
    $encodedDesc = urlencode($description ?: $title);
@endphp

<div x-data="{
    copied: false,
    copyLink() {
        if (navigator.clipboard) {
            navigator.clipboard.writeText('{{ $shareUrl }}');
            this.copied = true;
            setTimeout(() => this.copied = false, 2500);
        }
    },
    nativeShare() {
        if (navigator.share) {
            navigator.share({
                title: '{{ addslashes($title) }}',
                text: '{{ addslashes($description ?: $title) }}',
                url: '{{ $shareUrl }}'
            }).catch(() => {});
        } else {
            this.copyLink();
        }
    }
}" class="flex flex-wrap items-center gap-2 pt-4 pb-2" aria-label="{{ __('share.aria_label') }}" data-social-share>
    <span class="text-xs font-bold uppercase tracking-wider text-muted mr-1">{{ __('share.title') }}</span>

    {{-- WhatsApp --}}
    <a href="https://api.whatsapp.com/send?text={{ $encodedTitle }}%20{{ $encodedUrl }}"
       target="_blank"
       rel="noopener noreferrer"
       class="hc-v3-btn hc-v3-btn--ghost !px-3 !py-1.5 !text-xs !rounded-full border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition-colors inline-flex items-center gap-1.5"
       title="WhatsApp">
        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.311.045-.698.055-1.146-.088-.3-.095-.694-.23-1.196-.445-2.107-.905-3.486-3.039-3.59-3.18-.105-.14-1.026-1.365-1.026-2.604 0-1.238.647-1.849.877-2.099.231-.25.503-.312.67-.312.167 0 .334.002.48.01.156.008.364-.06.57.433.212.508.723 1.764.787 1.892.063.13.105.281.02.45-.084.168-.125.271-.25.417-.126.147-.264.327-.377.439-.126.125-.257.262-.11.514.146.251.651 1.074 1.397 1.738.96.855 1.769 1.121 2.022 1.246.252.126.4-.041.547-.21.147-.168.629-.732.796-.983.167-.251.334-.209.564-.125.231.084 1.464.69 1.716.816.251.125.419.188.48.293.063.104.063.606-.081 1.011z"/></svg>
        <span>WhatsApp</span>
    </a>

    {{-- Pinterest (Güçlü Backlink Kaynağı) --}}
    @if($encodedImage)
        <a href="https://pinterest.com/pin/create/button/?url={{ $encodedUrl }}&media={{ $encodedImage }}&description={{ $encodedDesc }}"
           target="_blank"
           rel="noopener noreferrer"
           class="hc-v3-btn hc-v3-btn--ghost !px-3 !py-1.5 !text-xs !rounded-full border border-red-500/30 text-red-700 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors inline-flex items-center gap-1.5"
           title="Pinterest">
            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.372-12 12 0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
            <span>Pinterest</span>
        </a>
    @endif

    {{-- X (Twitter) --}}
    <a href="https://twitter.com/intent/tweet?text={{ $encodedTitle }}&url={{ $encodedUrl }}"
       target="_blank"
       rel="noopener noreferrer"
       class="hc-v3-btn hc-v3-btn--ghost !px-3 !py-1.5 !text-xs !rounded-full border border-sky-500/30 text-sky-700 dark:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-950/30 transition-colors inline-flex items-center gap-1.5"
       title="X">
        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
        <span>X</span>
    </a>

    {{-- Yazdır / PDF İndir --}}
    <button type="button"
            onclick="window.print()"
            class="hc-v3-btn hc-v3-btn--ghost !px-3 !py-1.5 !text-xs !rounded-full border border-orange-500/30 text-orange-700 dark:text-orange-300 hover:bg-orange-50 dark:hover:bg-orange-950/30 transition-colors inline-flex items-center gap-1.5"
            title="Tarifi Yazdır veya PDF Olarak Kaydet">
        <svg class="w-3.5 h-3.5 fill-none stroke-current" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        <span>Yazdır / PDF</span>
    </button>

    {{-- Mobil Paylaş / Bağlantıyı Kopyala --}}
    <button type="button"
            @click="nativeShare()"
            class="hc-v3-btn hc-v3-btn--ghost !px-3 !py-1.5 !text-xs !rounded-full border border-stone-300 dark:border-stone-700 text-stone-700 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors inline-flex items-center gap-1.5"
            :class="copied ? '!border-emerald-500 !text-emerald-600' : ''">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
        </svg>
        <span x-text="copied ? '{{ __('share.copied') }}' : '{{ __('share.copy_action') }}'"></span>
    </button>
</div>
