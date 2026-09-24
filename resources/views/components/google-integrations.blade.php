@php
    $gaEnabled = config('integrations.google.analytics.enabled') && preg_match('/^G-[A-Z0-9]+$/', (string) config('integrations.google.analytics.measurement_id'));
    $gtmEnabled = config('integrations.google.tag_manager.enabled') && preg_match('/^GTM-[A-Z0-9]+$/', (string) config('integrations.google.tag_manager.container_id'));
    $useDirectGa = $gaEnabled && ! $gtmEnabled;
    $adsenseEnabled = config('integrations.google.adsense.enabled') && preg_match('/^ca-pub-\\d+$/', (string) config('integrations.google.adsense.client_id'));
@endphp
{{--
    Advertising is intentionally separate from analytics. Do not replace this
    check with hc_analytics_consent: EEA advertising needs a certified CMP and
    a distinct advertising-consent signal before a third-party ad request.
--}}
@php
    $cmpSaglayici = (string) config('integrations.google.adsense.cmp_provider', 'custom');
    $cmpHazir = (bool) config('integrations.google.adsense.certified_cmp_enabled');

    // Google'ın kendi CMP'si AdSense betiğinin içinden gelir. Betiği onay
    // çerezinin arkasına koymak kilitlenme yaratır: onay yoksa betik
    // yüklenmez, betik yüklenmezse mesaj görünmez, mesaj görünmezse onay hiç
    // oluşmaz. Bu yüzden 'google' modunda betik önce yüklenir; kişisel veri
    // kapısını Consent Mode v2 varsayılanları ve TCF sinyali tutar.
    $reklamBetigi = $adsenseEnabled && $cmpHazir && (
        $cmpSaglayici === 'google' || request()->cookie('hc_advertising_consent') === 'granted'
    );
@endphp
@if($reklamBetigi)
    @if($cmpSaglayici === 'google')
        {{-- Varsayılanlar reddedilmiş durumda başlar. CMP onayı aldığında
             kendisi günceller; biz burada hiçbir izni açmayız. --}}
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('consent','default',{'ad_storage':'denied','ad_user_data':'denied','ad_personalization':'denied','analytics_storage':@json(request()->cookie('hc_analytics_consent') === 'granted' ? 'granted' : 'denied'),'wait_for_update':500});</script>
    @endif
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ urlencode((string) config('integrations.google.adsense.client_id')) }}" crossorigin="anonymous"></script>
@endif
@if(request()->cookie('hc_analytics_consent') === 'granted' && $gtmEnabled)
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('consent','default',{'analytics_storage':'granted','ad_storage':'denied','ad_user_data':'denied','ad_personalization':'denied'});</script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer',@json(config('integrations.google.tag_manager.container_id')));</script>
@elseif(request()->cookie('hc_analytics_consent') === 'granted' && $useDirectGa)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode((string) config('integrations.google.analytics.measurement_id')) }}"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('consent','default',{'analytics_storage':'granted','ad_storage':'denied','ad_user_data':'denied','ad_personalization':'denied'});gtag('js',new Date());gtag('config',@json(config('integrations.google.analytics.measurement_id')),{'debug_mode':@json((bool) config('integrations.google.analytics.debug'))});</script>
@endif
