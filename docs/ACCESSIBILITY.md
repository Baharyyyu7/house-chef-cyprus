# Erişilebilirlik

- Semantik başlık hiyerarşisi, form label'ları ve görünür odak halkaları zorunludur.
- Mobil menüler `aria-expanded`, `aria-controls` ve dialog semantiğiyle çalışır.
- Renk tek başına anlam taşımaz; durum metni ve badge eşlik eder.
- Kontrast için koyu forest üzerinde cream, vurgu için gold kullanılır.
- CSS geçişleri `prefers-reduced-motion` tercihinde azaltılır.
- Alerjen ve beslenme bilgileri tıbbi kesinlik iddiası taşımaz.
# V3 component accessibility addendum

V3 button, input, modal, drawer, badge ve empty-state primitive'leri semantic focus ring, label/error ilişkisi, theme contrast ve `prefers-reduced-motion` sözleşmesi taşır. Icon'lar dekoratif olduğunda gizlenir; bilgi taşıdığında erişilebilir etiket alır. Pilot QA 375/768/1024/1440 ve %200 zoom'da uygulanır.

## Public Recipe V3

Tarif arşivindeki tüm filtre alanlarının görünür label'ı vardır; aktif chip'ler metinsel kaldırma açıklaması taşır. Kart görselleri anlamlı alt metin, interaktif kartlar ise ayrı güvenli link ve favori butonu kullanır. Detay bölüm navigasyonu anchor linklerinden oluşur; malzeme checklist'i her satırın metniyle etiketlenir. Alerjen ve dietary durumu yalnız renkle değil metin ve simgeyle de anlatılır.
