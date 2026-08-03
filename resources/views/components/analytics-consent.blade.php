@if(request()->cookie('hc_analytics_consent') === null && (config('integrations.google.analytics.enabled') || config('integrations.google.tag_manager.enabled')))
    <aside data-analytics-consent class="fixed inset-x-4 bottom-4 z-[70] mx-auto max-w-2xl rounded-3xl border border-stone-200 bg-white p-5 shadow-2xl dark:border-stone-700 dark:bg-stone-900" role="dialog" aria-label="Analitik çerez tercihi" aria-live="polite">
        <h2 class="font-extrabold">Analitik tercihiniz</h2>
        <p class="mt-2 text-sm leading-6 text-muted">Zorunlu çerezler siteyi çalıştırır. İzin verirseniz, kişisel iletişim bilgisi göndermeden kullanım eğilimlerini ölçmek için Google analitiği etkinleştirilir.</p>
        <div class="mt-4 flex flex-wrap gap-2"><button type="button" data-consent-choice="denied" class="hc-v3-btn hc-v3-btn--outline">Yalnız zorunlu</button><button type="button" data-consent-choice="granted" class="hc-v3-btn hc-v3-btn--primary">Analitiğe izin ver</button><a href="{{ route('legal.center') }}#cerez-politikasi" class="hc-v3-btn hc-v3-btn--ghost">Ayrıntılar</a></div>
    </aside>
@endif
