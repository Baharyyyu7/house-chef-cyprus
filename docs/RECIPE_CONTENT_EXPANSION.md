# Recipe Content Expansion Strategy

## Mevcut 12 demo tarif denetimi

Seed'de 12 published tarif ve 20 malzeme vardır. Temel başlık, cuisine, category, süre, porsiyon, zorluk, dört adım, ingredient pivot, nutrition JSON, storage JSON, source name ve featured flag bulunur. Ancak aşağıdaki içerik açıkları her demo tarif için kapatılmalıdır:

- Aynı kısa açıklama ve uzun açıklama tekrar edilir.
- Tek `Ana Yemek` kategorisi, sınırlı cuisine dağılımı ve dar ingredient havuzu kullanılır.
- Ingredientler yalnız `g` ile seed edilir; household gösterim yoktur.
- Ekipman, ön hazırlık, püf noktası, güvenlik, alternatif, servis, saklama, tekrar ısıtma ve sık hata bilgileri genel/tekrarlı metindir.
- Mevsim, dondurma uygunluğu, verification state, alan bazlı provenance ve gerçek editorial source notları eksiktir.
- Recipe-level gerçek medya yoktur; aynı placeholder görseli tekrar kullanılmaktadır.

## Editorial kayıt standardı

Her yeni veya yenilenen tarif şu alanları taşır: özgün başlık/kısa ve uzun açıklama, cuisine/category, prep/cook/rest/total, servings/difficulty, household + canonical ingredientler, equipment, prep notes, numaralı ve süreli adımlar, chef tip/safety, substitutions, allergen/diet result, nutrition basis/confidence/origin, storage/fridge/freeze/reheat, service, common mistakes, seasonality, source/provenance ve verification state.

Her açıklama House Chef Cyprus için özgün yazılır. Dış kaynakların metni, sırası, görseli veya telifli varlığı kopyalanmaz. Nefis Yemek Tarifleri, Yemek.com ve belgelenmiş güvenilir kaynaklar yalnız kontrollü doğrulama referansı olarak incelenir; lisans, erişim koşulu ve kaynak notu kaydedilir. Toplu scraping bu programın dışındadır.

## Tarif sayısı hedefi

| Hedef | Miktar | Kapsam | Kapanış |
| --- | ---: | --- | --- |
| Editorial Faz 1 | 50 | Türk/Kıbrıs/Akdeniz; kahvaltı, çorba, ana yemek, zeytinyağlı, hamur işi, tatlı, salata, atıştırmalık, pratik, vegan/vegetarian | Her tarif kalite checklist + provenance + özgün medya planı |
| Editorial Faz 2 | 150 | Kategori/cuisine boşlukları, mevsim ve diet çeşitliliği | Data quality kritik issue yok |
| Editorial Faz 3 | 500+ | Kontrollü import ve editorial review | Import Engine V1.5 ve lisans kapısı |

İlk 50 için minimum öneri: Türk 18, Kıbrıs 10, Akdeniz 8, kahvaltı 5, çorba 5, ana yemek 12, zeytinyağlı 5, hamur işi 4, tatlı 4, salata 4, atıştırmalık/pratik 6; kategoriler bir tarifte birden fazla işaretlenebildiği için dağılım çakışabilir. En az 10 vegetarian ve 8 vegan adayı, ancak dietary sonucu gerçek analizden gelmelidir.

## Kontrollü seed workflow

1. Editorial brief ve kaynak araştırma notu.
2. Household/canonical measurement doğrulaması.
3. Ingredient alias, allergen, diet, nutrition ve substitution kontrolü.
4. Özgün metin + source/provenance + verification metadata.
5. Telif uygun özgün/izinli media manifesti.
6. Idempotent seed, data quality scan ve admin preview.
7. Reviewer onayı; published state.

## Başarı ölçütü

Tarif sayısı tek başına kalite ölçütü değildir. Her tarifin completeness, confidence, source ve UI anlaşılabilirliği doğrulanır; eksik bilgi varsa güvenli biçimde görünür kılınır.
