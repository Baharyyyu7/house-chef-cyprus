@props([
    'slotName' => 'recipe_mid',
    'class' => 'my-6',
])

@php
    /**
     * Reklam yuvası iki kapıya birden bağlıdır ve ikisi de aynı kaynaktan okur:
     * yayıncı kimliği ve onay durumu integrations.google.adsense'ten gelir.
     * Daha önce burada ikinci bir yapılandırma vardı (monetization.adsense_client_id)
     * ve o yol onay çerezine hiç bakmıyordu; iki kaynak olduğu sürece biri
     * kapalıyken diğerinin açık kalması an meselesiydi.
     *
     * Yayıncı kimliği doğrulanmadan hiçbir şey basılmaz. Yer tutucu kimlikle
     * ("ca-pub-XXXXXXXXXXXXXXXX") istek atmak, başvurusu incelemede olan bir
     * hesabın reddedilmesinin bilinen yollarından biridir.
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

    $slotId = (string) config("monetization.slots.{$slotName}", '');
    $gecerliSlot = $slotId !== '' && preg_match('/^\d{6,}$/', $slotId) === 1 && ! str_starts_with($slotId, '12345678');

    // Ödeme yapan üye reklam görmez: abonelikte satılan şeylerden biri budur.
    $ucretliUye = auth()->check() && auth()->user()->isPremium();

    $gosterilsin = $acik && $cmpHazir && $onayVar && $gecerliKimlik && $gecerliSlot && ! $ucretliUye;
@endphp

@if($gosterilsin)
    <div class="ad-slot {{ $class }}" role="complementary" aria-label="Reklam">
        <p class="mb-2 text-center text-[11px] font-bold uppercase tracking-[.14em]" style="color: var(--hc-text-muted)">Reklam</p>
        <ins class="adsbygoogle"
             style="display:block; text-align:center;"
             data-ad-layout="in-article"
             data-ad-format="fluid"
             data-ad-client="{{ $clientId }}"
             data-ad-slot="{{ $slotId }}"></ins>
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">(adsbygoogle = window.adsbygoogle || []).push({});</script>
    </div>
@endif
