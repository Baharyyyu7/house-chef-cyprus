# 11 Ekim 2026 - eksik malzeme satırları, grup başlıkları, pt blog

## Kod (canlı 6e510e5; detektör düzeltmesi ayrı commit)
- 82ecb3f `coni:malzeme-ekle`: yayındaki tarife satır ekler. Panel akışı
  (IngredientConversionService; dönüşüm yoksa nedenli elle kanonik). Adı 6 dilde
  çevrilmemiş malzemeyi reddeder; açık sayfa kapanacaksa hiçbir şey yazmaz; `--dene`;
  `--geri-al=<rapor>` satırları siler.
- 6e510e5 malzeme grubu başlığı yabancı dilde çevrilir (`lang/<dil>/malzeme_gruplari.php`),
  karşılığı yoksa basılmaz. Önce: 6 tarif, 30 açık sayfada "Ana malzemeler", "Şerbet".
- Blog yer tutucu denetimi `\bTODO\b` /i ile Portekizce "todo"yu yakalıyordu;
  büyük harfe çevrildi.

## Veri (canlı)
- 34 tarife 60 satır: `coni:malzeme-ekle --geri-al=30632`. Workflow wf_bf653ee5-040
  (8 düzeltici + 8 doğrulayıcı) + elle gözden geçirme + mekanik kontrol 0 hata.
- İzmir şambali: şerbet yoktu. 3 su b. şeker, 2 su b. su, 1 çay k. limon (TÜRKPATENT
  coğrafi işaret: 1 kg şekere 650-850 ml su). TR adım 1 ve 5 adım başlığı
  `coni:kaynak --geri-al=30633`; 6 dil adım 1 `coni:ceviri-toplu --zorla`.
- pt blog 3240 ve 3510 pt-BR'ye: rapor 30634 (geri al: `php -- --geri-al 30634`
  betiği scratchpad `blogpt/yaz.php`, stdin ile).

## Doğrulama
- Yeniden dışa aktarım: 34 tarif satırları dosyayla birebir, izmir 6 dil + TR birebir,
  blog 6 alan birebir. Açık tarif sayfası 738 (önce 738).
- 238 canlı sayfa (34 tarif x 7 dil): 200, index,follow, sayfadaki malzeme sayısı
  DB'ye eşit, eklenen her malzeme o dildeki adıyla görünüyor.

## Tuzaklar
- Ölçüm adresine `?k=` eklemek sayfayı noindex yapar; robots parametresiz ölçülür.
- Ana checkout'un `public/build/manifest.json`'u 27 Eyl'den kalma; tam paket orada
  125 Vite hatası verir. Aynı commit worktree'de yeşil.
- Blog çevirisi yazınca o dilde `BlogIndexability::engeller()` ölç: gövde denetimi
  çevrilmiş metne bakar.
