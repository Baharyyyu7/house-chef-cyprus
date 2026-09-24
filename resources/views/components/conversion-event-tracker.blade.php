{{--
    House Chef Cyprus — Named Conversion Event Dispatcher (SEO & AEO Rule Zero)
    Takip edilen adlandırılmış dönüşüm etkinlikleri:
    - lead_chef_inquiry
    - whatsapp_contact_click
    - recipe_cook_mode_start
    - pantry_search_executed
    - ad_impression_viewable
--}}
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
(function() {
    window.dataLayer = window.dataLayer || [];
    window.trackHcConversion = function(eventName, params) {
        params = params || {};
        params.event = eventName;
        params.page_path = window.location.pathname;
        params.timestamp = new Date().toISOString();

        // 1. DataLayer push (GTM / PostHog uyumlu)
        window.dataLayer.push(params);

        // 2. Direct gtag push (GA4 uyumlu)
        if (typeof window.gtag === 'function') {
            window.gtag('event', eventName, params);
        }

        // Konsol bilgilendirme (Geliştirici modu)
        if (window.location.hostname === 'localhost' || window.location.search.includes('debug=1')) {
            console.log('[HouseChef:Conversion]', eventName, params);
        }
    };

    // Otomatik data-hc-event dinleyicisi
    document.addEventListener('click', function(e) {
        var target = e.target.closest('[data-hc-event]');
        if (target) {
            var evt = target.getAttribute('data-hc-event');
            var meta = {};
            try {
                var metaAttr = target.getAttribute('data-hc-event-meta');
                if (metaAttr) { meta = JSON.parse(metaAttr); }
            } catch(err) {}
            window.trackHcConversion(evt, meta);
        }

        // WhatsApp linkleri için otomatik whatsapp_contact_click dispatch
        var waLink = e.target.closest('a[href*="whatsapp.com"], a[href*="api.whatsapp.com"]');
        if (waLink && !waLink.getAttribute('data-hc-event')) {
            window.trackHcConversion('whatsapp_contact_click', { href: waLink.href });
        }
    });

    // Otomatik sayfa dönüşüm tetikleyicileri (Flash session kontrolü)
    @if(session('status') && (request()->routeIs('private-chef.*') || request()->routeIs('legal.center.contact.*')))
        window.trackHcConversion('lead_chef_inquiry', {
            source: 'private_chef_form',
            status: 'success'
        });
    @endif

    @if(request()->routeIs('cooking-sessions.*'))
        window.trackHcConversion('recipe_cook_mode_start', {
            session_id: @json(request()->route('cookingSession')?->id ?? null),
            source: 'cooking_mode'
        });
    @endif
})();
</script>
