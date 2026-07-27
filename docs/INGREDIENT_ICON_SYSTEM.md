# Ingredient Icon System

## Sözleşme

Malzeme ikonları kullanıcıya hızlı tanıma sağlar; besin, alerjen veya kalite hükmü değildir. Öncelik tutarlı, erişilebilir SVG setidir. Emoji yalnız kontrollü fallback; raster görsel yalnız doğrulanmış `image_id` ile kullanılır.

## Gelecek alanlar

`icon_type`, `icon_value`, nullable `custom_svg`, nullable `emoji`, nullable `image_id`, `accessibility_label`, `verified`, kaynak ve güncellik bilgisi. SVG allowlist/sanitization ve dekoratif ikonlarda doğru `aria-hidden` zorunludur.

## İlk kapsam

İlk 24 sık malzeme için tasarım tokenlarıyla uyumlu icon manifest hazırlanır: sebze, meyve, tahıl, süt ürünü, protein, bakliyat, yağ/baharat grupları. Üretim verisine toplu ikon atama ancak manifest, lisans ve dark/light QA tamamlanınca yapılır.

