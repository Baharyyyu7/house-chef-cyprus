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
           role="region" aria-label="{{ __('consent.region_label') }}">
        <div class="hc-consent__panel mx-auto max-w-3xl rounded-3xl p-4 sm:p-5">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <p class="hc-body-sm min-w-0 flex-1" style="color: var(--hc-text-secondary)">
                    {{ __('consent.always_on') }}
                    {{ __('consent.ask_before') }}<span class="font-semibold" style="color: var(--hc-text-primary)">{{ __($reklamSorulacak ? 'consent.ask_measure_ads' : 'consent.ask_measure') }}</span>{{ __('consent.ask_after') }}
                </p>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <button type="button" @click="ayrintili = !ayrintili"
                            class="hc-btn hc-btn-outline hc-btn--sm"
                            :aria-expanded="ayrintili">{{ __('consent.customize') }}</button>
                    <button type="button" data-consent-choice="denied" @if($reklamSorulacak) data-advertising-choice="denied" @endif
                            class="hc-btn hc-btn-outline hc-btn--sm">{{ __('consent.essential_only') }}</button>
                    <button type="button" data-consent-choice="granted" @if($reklamSorulacak) data-advertising-choice="granted" @endif
                            class="hc-btn hc-btn-primary hc-btn--sm">{{ __('consent.accept') }}</button>
                </div>
            </div>

            {{-- Ayrı ayrı seçim burada. Ölçüme izin vermek reklam izni sayılmaz. --}}
            <div x-cloak x-show="ayrintili" x-collapse.duration.250ms>
                <div class="mt-4 grid gap-2 border-t pt-4 sm:grid-cols-2" style="border-color: var(--hc-border-subtle)">
                    <div class="flex items-center justify-between gap-3 rounded-2xl px-3 py-2.5" style="background: var(--hc-surface-soft)">
                        <span class="min-w-0">
                            <span class="block text-sm font-bold">{{ __('consent.measurement') }}</span>
                            <span class="hc-caption block" style="color: var(--hc-text-secondary)">{{ __('consent.measurement_desc') }}</span>
                        </span>
                        <span class="flex shrink-0 gap-1.5">
                            <button type="button" data-consent-choice="denied" class="hc-btn hc-btn-outline hc-btn--sm">{{ __('consent.no') }}</button>
                            <button type="button" data-consent-choice="granted" class="hc-btn hc-btn-primary hc-btn--sm">{{ __('consent.allow') }}</button>
                        </span>
                    </div>

                    @if($reklamSorulacak)
                        <div class="flex items-center justify-between gap-3 rounded-2xl px-3 py-2.5" style="background: var(--hc-surface-soft)">
                            <span class="min-w-0">
                                <span class="block text-sm font-bold">{{ __('consent.ads') }}</span>
                                <span class="hc-caption block" style="color: var(--hc-text-secondary)">{{ __('consent.ads_desc') }}</span>
                            </span>
                            <span class="flex shrink-0 gap-1.5">
                                <button type="button" data-advertising-choice="denied" class="hc-btn hc-btn-outline hc-btn--sm">{{ __('consent.no') }}</button>
                                <button type="button" data-advertising-choice="granted" class="hc-btn hc-btn-primary hc-btn--sm">{{ __('consent.allow') }}</button>
                            </span>
                        </div>
                    @endif
                </div>

                <a href="{{ route('policy.cerezler') }}" class="hc-caption mt-3 inline-flex font-bold" style="color: var(--hc-text-accent)">{{ __('consent.read_policy') }}</a>
            </div>
        </div>
    </aside>
@endif
