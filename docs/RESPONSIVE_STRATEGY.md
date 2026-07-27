# Responsive Strateji

Mobil öncelikli düzen kullanılır. 375 px'de tek sütun, 768 px'de uygun kart ve form kolonları, 1024 px'de public navigasyon ve admin drawer geçişi, 1440 px'de tam sidebar/çok sütun düzeni hedeflenir.

Geniş tablolarda içerik gizlenmez; `hc-table-wrap` yatay kaydırma sağlar. Public menüde mobil drawer, yönetim bölümünde ayrı admin drawer kullanılır.
# V3 responsive addendum

Global primitive QA genişlikleri 375, 768, 1024 ve 1440 px'tir. Butonlar minimum 44 px touch target, table shell kontrollü yatay scroll, drawer mobile focus/escape davranışı ve uzun Türkçe metin sarımı taşır. Tüm admin tabloları bu fazda taşınmaz; düşük riskli pilot sözleşmeyi doğrular.

## Public Recipe V3

Tarif koleksiyonu 375 px'te tek, 768 px'te iki, 1024 px'te üç ve geniş ekranda dört kart sütununa geçer. Detay sayfasında metadata sarılır, bölüm navigasyonu yatay kayar, malzeme rail'i yalnız geniş ekranda sticky olur ve hero yüksekliği içerikten bağımsız aşırı büyümez.
# Member collections

Matching 1280px altında kontrollü filtre paneli, pantry ve favorites ise 375px'te tek kolon kart düzeni kullanır; geniş ekranlarda dengeli iki/üç kolon davranışı korunur.
