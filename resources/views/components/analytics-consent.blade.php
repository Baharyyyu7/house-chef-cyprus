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
    {{--
        Bant kapladığı yerle orantısız bir karar istiyordu: dar ekranda dört
        düğme, iki ayrı grup ve uzun bir paragraf sayfanın önünü kesiyordu.

        Karar ikiye indi - hepsini kabul et ya da yalnız zorunlu - ve ayrı ayrı
        seçmek isteyen "Ayarla" ile bir kat aşağı iniyor. İki izin hâlâ ayrı
        çerezlerde tutuluyor; sadeleşen sunum, saklanan tercih değil.

        Perde yok: bu bir engel değil, alttan gelen bir şerit. Sayfa arkasında
        okunmaya ve kaydırılmaya devam eder.
    --}}
    <aside data-analytics-consent
           x-data="{ ayrintili: false }"
           class="hc-consent fixed inset-x-0 bottom-0 z-[70] p-3 sm:inset-x-4 sm:bottom-4"
           role="region" aria-label="Çerez tercihiniz">
        <div class="hc-consent__panel mx-auto max-w-3xl rounded-3xl p-4 sm:p-5">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <p class="hc-body-sm min-w-0 flex-1" style="color: var(--hc-text-secondary)">
                    Siteyi çalıştıran çerezler her zaman açık.
                    <span class="font-semibold" style="color: var(--hc-text-primary)">Ölçüm@if($reklamSorulacak) ve reklam@endif çerezleri</span> için izninizi soruyoruz.
                </p>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <button type="button" @click="ayrintili = !ayrintili"
                            class="hc-btn hc-btn-outline hc-btn--sm"
                            :aria-expanded="ayrintili">Ayarla</button>
                    <button type="button" data-consent-choice="denied" @if($reklamSorulacak) data-advertising-choice="denied" @endif
                            class="hc-btn hc-btn-outline hc-btn--sm">Yalnız zorunlu</button>
                    <button type="button" data-consent-choice="granted" @if($reklamSorulacak) data-advertising-choice="granted" @endif
                            class="hc-btn hc-btn-primary hc-btn--sm">Kabul et</button>
                </div>
            </div>

            {{-- Ayrı ayrı seçim burada. Ölçüme izin vermek reklam izni sayılmaz. --}}
            <div x-cloak x-show="ayrintili" x-collapse.duration.250ms>
                <div class="mt-4 grid gap-2 border-t pt-4 sm:grid-cols-2" style="border-color: var(--hc-border-subtle)">
                    <div class="flex items-center justify-between gap-3 rounded-2xl px-3 py-2.5" style="background: var(--hc-surface-soft)">
                        <span class="min-w-0">
                            <span class="block text-sm font-bold">Ölçüm</span>
                            <span class="hc-caption block" style="color: var(--hc-text-secondary)">Hangi sayfalar okunuyor</span>
                        </span>
                        <span class="flex shrink-0 gap-1.5">
                            <button type="button" data-consent-choice="denied" class="hc-btn hc-btn-outline hc-btn--sm">Hayır</button>
                            <button type="button" data-consent-choice="granted" class="hc-btn hc-btn-primary hc-btn--sm">İzin ver</button>
                        </span>
                    </div>

                    @if($reklamSorulacak)
                        <div class="flex items-center justify-between gap-3 rounded-2xl px-3 py-2.5" style="background: var(--hc-surface-soft)">
                            <span class="min-w-0">
                                <span class="block text-sm font-bold">Reklam</span>
                                <span class="hc-caption block" style="color: var(--hc-text-secondary)">Size uygun reklam gösterimi</span>
                            </span>
                            <span class="flex shrink-0 gap-1.5">
                                <button type="button" data-advertising-choice="denied" class="hc-btn hc-btn-outline hc-btn--sm">Hayır</button>
                                <button type="button" data-advertising-choice="granted" class="hc-btn hc-btn-primary hc-btn--sm">İzin ver</button>
                            </span>
                        </div>
                    @endif
                </div>

                <a href="{{ route('legal.center') }}#cerez-politikasi" class="hc-caption mt-3 inline-flex font-bold" style="color: var(--hc-text-accent)">Çerez politikasını oku</a>
            </div>
        </div>
    </aside>
@endif
