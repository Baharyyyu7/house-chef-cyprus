@php
    /**
     * Reklam onayı analitik onayından ayrı sorulur ve ayrı bir çerezde tutulur:
     * ölçüme izin veren biri kişiselleştirilmiş reklama izin vermiş sayılmaz.
     *
     * Bu bant reklam yayını için tek başına yeterli değildir. Kıbrıs AB
     * içindedir ve Google, AEA kullanıcılarına reklam sunmak için sertifikalı
     * bir CMP şartı koyar; bu yüzden reklam seçeneği yalnız o CMP ayrı olarak
     * yapılandırıldığında sorulur ve onaysız hiçbir reklam isteği çıkmaz.
     */
    $reklamSorulacak = config('integrations.google.adsense.enabled')
        && config('integrations.google.adsense.certified_cmp_enabled')
        && request()->cookie('hc_advertising_consent') === null;

    $olcumSorulacak = request()->cookie('hc_analytics_consent') === null
        && (config('integrations.google.analytics.enabled') || config('integrations.google.tag_manager.enabled'));
@endphp

@if($olcumSorulacak || $reklamSorulacak)
    <aside data-analytics-consent class="fixed inset-x-4 bottom-4 z-[70] mx-auto max-w-2xl rounded-3xl border border-stone-200 bg-white p-5 shadow-2xl dark:border-stone-700 dark:bg-stone-900" role="dialog" aria-label="Çerez tercihiniz" aria-live="polite">
        <h2 class="font-extrabold">{{ $reklamSorulacak ? 'Çerez tercihleriniz' : 'Analitik tercihiniz' }}</h2>
        <p class="mt-2 text-sm leading-6 text-muted">
            Zorunlu çerezler siteyi çalıştırır. İzin verirseniz, kişisel iletişim bilgisi göndermeden kullanım eğilimlerini ölçmek için Google analitiği etkinleştirilir.
            @if($reklamSorulacak)
                Reklam tercihini ayrıca seçebilirsiniz; ölçüme izin vermeniz reklam izni sayılmaz.
            @endif
        </p>

        @if($reklamSorulacak)
            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                <div class="rounded-2xl border p-3" style="border-color: var(--hc-line)">
                    <p class="text-sm font-bold">Ölçüm</p>
                    <div class="mt-2 flex gap-2">
                        <button type="button" data-consent-choice="denied" class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Hayır</button>
                        <button type="button" data-consent-choice="granted" class="hc-v3-btn hc-v3-btn--primary hc-v3-btn--sm">İzin ver</button>
                    </div>
                </div>
                <div class="rounded-2xl border p-3" style="border-color: var(--hc-line)">
                    <p class="text-sm font-bold">Reklam</p>
                    <div class="mt-2 flex gap-2">
                        <button type="button" data-advertising-choice="denied" class="hc-v3-btn hc-v3-btn--outline hc-v3-btn--sm">Hayır</button>
                        <button type="button" data-advertising-choice="granted" class="hc-v3-btn hc-v3-btn--primary hc-v3-btn--sm">İzin ver</button>
                    </div>
                </div>
            </div>
            <a href="{{ route('legal.center') }}#cerez-politikasi" class="mt-3 inline-flex text-sm font-bold" style="color: var(--hc-text-accent)">Ayrıntılar</a>
        @else
            <div class="mt-4 flex flex-wrap gap-2">
                <button type="button" data-consent-choice="denied" class="hc-v3-btn hc-v3-btn--outline">Yalnız zorunlu</button>
                <button type="button" data-consent-choice="granted" class="hc-v3-btn hc-v3-btn--primary">Analitiğe izin ver</button>
                <a href="{{ route('legal.center') }}#cerez-politikasi" class="hc-v3-btn hc-v3-btn--ghost">Ayrıntılar</a>
            </div>
        @endif
    </aside>
@endif
