# Motion Implementation V3

## Tokenlar

`duration-instant` 100 ms, `fast` 160 ms, `normal` 220 ms, `slow` 320 ms ve `emphasis` 560 ms'tir. Standard/enter/exit easing CSS semantic tokenlarından gelir.

## Kullanım

- `hc-v3-section-reveal`: sayfa/section ilk görünümü.
- `hc-v3-stagger`: üçe kadar kısa kart dizisi.
- Button: press scale, hover yüzey geçişi.
- Card: hover lift; layout shift yaratmayan `transform` kullanır.
- Modal/drawer/dropdown: mevcut Alpine transitions; Escape, outside click ve scroll lock korunur.

`prefers-reduced-motion: reduce` tüm animasyon/transition sürelerini işlevi engellemeyecek biçimde neredeyse sıfıra indirir. Motion içerik görünürlüğünü geciktirmez; validation shake yalnız gelecekte kontrollü hata geri bildirimi için ayrılmıştır.

