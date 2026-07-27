# Ingredient Icon System

## Sözleşme

Malzeme ikonları kullanıcıya hızlı tanıma sağlar; besin, alerjen veya kalite hükmü değildir. Öncelik tutarlı, erişilebilir SVG setidir. Emoji yalnız kontrollü fallback; raster görsel yalnız doğrulanmış `image_id` ile kullanılır.

## Gelecek alanlar

`icon_type`, `icon_value`, nullable `custom_svg`, nullable `emoji`, nullable `image_id`, `accessibility_label`, `verified`, kaynak ve güncellik bilgisi. SVG allowlist/sanitization ve dekoratif ikonlarda doğru `aria-hidden` zorunludur.

## İlk kapsam

İlk 24 sık malzeme için tasarım tokenlarıyla uyumlu `icon_key`, `icon_label` ve `icon_status=pilot` metadata'sı oluşturuldu. Pilot yalnız admin malzeme listesi ve malzeme seçicisindedir; erişilebilir isim görünür kalır ve ikon yoksa güvenli metin fallback kullanılır.
