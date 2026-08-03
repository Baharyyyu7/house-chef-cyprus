@php
    $gaEnabled = config('integrations.google.analytics.enabled') && preg_match('/^G-[A-Z0-9]+$/', (string) config('integrations.google.analytics.measurement_id'));
    $gtmEnabled = config('integrations.google.tag_manager.enabled') && preg_match('/^GTM-[A-Z0-9]+$/', (string) config('integrations.google.tag_manager.container_id'));
    $useDirectGa = $gaEnabled && ! $gtmEnabled;
@endphp
@if(request()->cookie('hc_analytics_consent') === 'granted' && $gtmEnabled)
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">window.dataLayer=window.dataLayer||[];window.dataLayer.push({event:'consent_default',analytics_storage:'denied',ad_storage:'denied'});</script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer',@json(config('integrations.google.tag_manager.container_id')));</script>
@elseif(request()->cookie('hc_analytics_consent') === 'granted' && $useDirectGa)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode((string) config('integrations.google.analytics.measurement_id')) }}"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('consent','default',{'analytics_storage':'denied','ad_storage':'denied'});gtag('js',new Date());gtag('config',@json(config('integrations.google.analytics.measurement_id')),{'send_page_view':false,'debug_mode':@json((bool) config('integrations.google.analytics.debug'))});</script>
@endif
