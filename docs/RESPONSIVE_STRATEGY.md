# Responsive Strateji

Mobil öncelikli düzen kullanılır. 375 px'de tek sütun, 768 px'de uygun kart ve form kolonları, 1024 px'de public navigasyon ve admin drawer geçişi, 1440 px'de tam sidebar/çok sütun düzeni hedeflenir.

Geniş tablolarda içerik gizlenmez; `hc-table-wrap` yatay kaydırma sağlar. Public menüde mobil drawer, yönetim bölümünde ayrı admin drawer kullanılır.
# V3 responsive addendum

Global primitive QA genişlikleri 375, 768, 1024 ve 1440 px'tir. Butonlar minimum 44 px touch target, table shell kontrollü yatay scroll, drawer mobile focus/escape davranışı ve uzun Türkçe metin sarımı taşır. Tüm admin tabloları bu fazda taşınmaz; düşük riskli pilot sözleşmeyi doğrular.
