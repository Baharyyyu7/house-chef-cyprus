@props([
    'slotName' => 'in_article',
    'class' => 'my-8',
])

@php
    /**
     * Reklam yuvası tek kaynaktan okur: yayıncı kimliği ve onay durumu
     * integrations.google.adsense'ten gelir. Daha önce ikinci bir yapılandırma
     * vardı (monetization.adsense_client_id) ve o yol onay çerezine hiç
     * bakmıyordu; iki kaynak olduğu sürece biri kapalıyken diğerinin açık
     * kalması an meselesiydi.
     *
     * Yayıncı kimliği doğrulanmadan hiçbir şey basılmaz. Yer tutucu kimlikle
     * ("ca-pub-XXXXXXXXXXXXXXXX") istek atmak, incelemedeki bir hesabın
     * reddedilmesinin bilinen yollarından biridir.
     */
    $clientId = (string) config('integrations.google.adsense.client_id');
    $gecerliKimlik = preg_match('/^ca-pub-\d+$/', $clientId) === 1;

    $cmpHazir = (bool) config('integrations.google.adsense.certified_cmp_enabled');
    $cmpSaglayici = (string) config('integrations.google.adsense.cmp_provider', 'custom');

    // Onayı kim topluyorsa karar da onundur. Google'ın kendi CMP'si devredeyse
    // TCF sinyalini o üretir ve adsbygoogle etiketi ona kendisi uyar; sunucu o
    // sinyali göremediği için kendi çerezimize bakmak yanlış cevap verir.
    // 'custom' modunda karar bizim çerezimizdedir ve hiçbir şey değişmez.
    $onayVar = $cmpSaglayici === 'google'
        || request()->cookie('hc_advertising_consent') === 'granted';

    $acik = (bool) config('integrations.google.adsense.enabled');

    $yuva = config("monetization.slots.{$slotName}");
    $slotId = (string) (is_array($yuva) ? ($yuva['id'] ?? '') : $yuva);
    $gecerliSlot = $slotId !== '' && preg_match('/^\d{6,}$/', $slotId) === 1 && ! str_starts_with($slotId, '12345678');

    // Ödeme yapan üye reklam görmez: abonelikte satılan şeylerden biri budur.
    $ucretliUye = auth()->check() && auth()->user()->isPremium();

    // Kalite kapısının kapattığı sayfa reklam da taşımaz: kendi ölçümümüzle
    // "aramada gösterilmeye değmez" dediğimiz sayfada reklam yayınlamak
    // AdSense incelemesinde düşük değerli içerik olarak okunur.
    $kapaliSayfa = (bool) request()->attributes->get('hc_page_is_noindex');

    $gosterilsin = $acik && $cmpHazir && $onayVar && $gecerliKimlik && $gecerliSlot && ! $ucretliUye && ! $kapaliSayfa;

    // Her AdSense birimi kendi biçimini ister ve yanlış öznitelikle basılan
    // birim ya hiç dolmaz ya da bozuk görünür. Biçim yuvanın tanımından gelir.
    $bicim = is_array($yuva) ? ($yuva['format'] ?? 'in_article') : 'in_article';
    $layoutKey = is_array($yuva) ? ($yuva['layout_key'] ?? null) : null;
@endphp

@if($gosterilsin)
    <div class="ad-slot {{ $class }}" role="complementary" aria-label="Reklam">
        <p class="mb-2 text-center text-[11px] font-bold uppercase tracking-[.14em]" style="color: var(--hc-text-muted)">Reklam</p>

        @if($bicim === 'in_feed')
            <ins class="adsbygoogle"
                 style="display:block"
                 data-ad-format="fluid"
                 data-ad-layout-key="{{ $layoutKey }}"
                 data-ad-client="{{ $clientId }}"
                 data-ad-slot="{{ $slotId }}"></ins>
        @elseif($bicim === 'multiplex')
            <ins class="adsbygoogle"
                 style="display:block"
                 data-ad-format="autorelaxed"
                 data-ad-client="{{ $clientId }}"
                 data-ad-slot="{{ $slotId }}"></ins>
        @elseif($bicim === 'display')
            <ins class="adsbygoogle"
                 style="display:block"
                 data-ad-client="{{ $clientId }}"
                 data-ad-slot="{{ $slotId }}"
                 data-ad-format="auto"
                 data-full-width-responsive="true"></ins>
        @else
            <ins class="adsbygoogle"
                 style="display:block; text-align:center;"
                 data-ad-layout="in-article"
                 data-ad-format="fluid"
                 data-ad-client="{{ $clientId }}"
                 data-ad-slot="{{ $slotId }}"></ins>
        @endif

        <script nonce="{{ request()->attributes->get('csp_nonce') }}">(adsbygoogle = window.adsbygoogle || []).push({});</script>
    </div>
@endif
