@props(['recipe'])

@php
    $whatsappNumber = config('contact-requests.whatsapp_number', '905338000000');
    $dishTitle = $recipe->title;
    $waText = urlencode("Merhaba House Chef Cyprus! '{$dishTitle}' tarifinizi gördüm. Kıbrıs'taki villamız / davetimiz için özel şef hizmeti ve fiyat teklifi almak istiyorum.");
    $waUrl = "https://api.whatsapp.com/send?phone={$whatsappNumber}&text={$waText}";
@endphp

<aside aria-label="Özel Şef Hizmeti" class="hc-v3-card mt-8 p-6 sm:p-7 border-2 border-orange-300/70 dark:border-orange-500/30 bg-gradient-to-br from-amber-500/10 via-orange-500/5 to-amber-500/15 dark:from-amber-950/40 dark:via-stone-900 dark:to-stone-900 shadow-md">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="flex items-start gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-orange-600 to-amber-500 text-3xl text-white shadow-lg shadow-orange-600/30">
                👨‍🍳
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1 rounded-full bg-orange-600/15 px-3 py-0.5 text-[11px] font-black uppercase tracking-wider text-orange-700 dark:text-orange-300">
                        <span aria-hidden="true">✦</span> Ada Geneli Özel Şef & Catering
                    </span>
                    <span class="text-xs font-semibold text-stone-500 dark:text-stone-400">
                        Girne · Lefkoşa · Mağusa · İskele
                    </span>
                </div>
                <h3 class="mt-1.5 text-lg sm:text-xl font-black text-stone-900 dark:text-white">
                    Bu Yemeği Villanızda Usta Şefimize Pişirtin
                </h3>
                <p class="mt-1.5 text-xs sm:text-sm leading-relaxed text-stone-600 dark:text-stone-300 max-w-xl">
                    Kıbrıs'ta tatil veya özel davet mi planlıyorsunuz? Taze ada malzemeleri temini, profesyonel villa mutfağı hazırlığı, 5 yıldızlı masa servisi ve mutfak temizliği dahil eksiksiz şef deneyimi.
                </p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row md:flex-col items-stretch sm:items-center md:items-end gap-3 shrink-0 pt-3 md:pt-0 border-t md:border-t-0 border-orange-200/80 dark:border-stone-800">
            <a href="{{ $waUrl }}"
               target="_blank"
               rel="noopener noreferrer"
               class="flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-xs sm:text-sm font-black text-white shadow-md shadow-emerald-950/20 transition hover:bg-emerald-700 hover:scale-102">
                <span class="text-base" aria-hidden="true">💬</span>
                <span>WhatsApp'tan Anında Fiyat Al</span>
            </a>
            
            <a href="{{ route('private-chef.index') }}"
               class="hc-v3-btn hc-v3-btn--outline text-xs px-4 py-2.5 inline-flex items-center justify-center gap-1.5">
                <span>Paketleri ve Menüleri İncele</span>
                <span aria-hidden="true">➔</span>
            </a>
        </div>
    </div>
</aside>
