# Recipe Content Expansion Strategy

## Faz 3 tamamlanmış katalog

Eski 12 demo tarif, Faz 3 kapsamında genişletilmiş 50 tariflik sürümlü editoryal katalogla değiştirilmiştir. Katalogdaki 50 kaydın tamamı `published` ve `verified` durumundadır; provenance, household/canonical ölçüler, ingredient grupları, adımlar, nutrition/dietary/allergen sinyalleri ve placeholder medya durumu kayıtlıdır.

Faz 3A'nın 20 tariflik ara hedefi **SUPERSEDED BY COMPLETED FAZ 3** kabul edilir. 20 tarife geri dönmek veri kaybı yaratacağı için yapılmaz. 150 tariflik bir sonraki büyüme hedefi kontrollü içerik genişletme backlog'unda tutulur ve bu dokümantasyon kapanışında başlatılmaz.

## Editorial kayıt standardı

Her yeni veya yenilenen tarif şu alanları taşır: özgün başlık/kısa ve uzun açıklama, cuisine/category, prep/cook/rest/total, servings/difficulty, household + canonical ingredientler, equipment, prep notes, numaralı ve süreli adımlar, chef tip/safety, substitutions, allergen/diet result, nutrition basis/confidence/origin, storage/fridge/freeze/reheat, service, common mistakes, seasonality, source/provenance ve verification state.

Her açıklama House Chef Cyprus için özgün yazılır. Dış kaynakların metni, sırası, görseli veya telifli varlığı kopyalanmaz. Nefis Yemek Tarifleri, Yemek.com ve belgelenmiş güvenilir kaynaklar yalnız kontrollü doğrulama referansı olarak incelenir; lisans, erişim koşulu ve kaynak notu kaydedilir. Toplu scraping bu programın dışındadır.

## Tarif sayısı hedefi ve doğrulama kapısı

| Hedef | Miktar | Kapsam | Kapanış |
| --- | ---: | --- | --- |
| Editorial Faz 1 | 50 | Türk/Kıbrıs/Akdeniz; kahvaltı, çorba, ana yemek, zeytinyağlı, hamur işi, tatlı, salata, atıştırmalık, pratik, vegan/vegetarian | Her tarif kalite checklist + provenance + özgün medya planı |
| Editorial Faz 2 | 150 | Kategori/cuisine boşlukları, mevsim ve diet çeşitliliği | Data quality kritik issue yok |
| Editorial Faz 3 | 500 | Kontrollü editorial büyüme | Duplicate, kaynak ve görsel kalite kapısı |
| Editorial Faz 4 | 1.000+ | Dünya mutfakları genişlemesi | Taxonomy, lisans ve content-review kapasitesi |

İlk 50 için minimum öneri: Türk 18, Kıbrıs 10, Akdeniz 8, kahvaltı 5, çorba 5, ana yemek 12, zeytinyağlı 5, hamur işi 4, tatlı 4, salata 4, atıştırmalık/pratik 6; kategoriler bir tarifte birden fazla işaretlenebildiği için dağılım çakışabilir. En az 10 vegetarian ve 8 vegan adayı, ancak dietary sonucu gerçek analizden gelmelidir.

## Kontrollü seed workflow

1. Editorial brief ve kaynak araştırma notu.
2. Household/canonical measurement doğrulaması.
3. Ingredient alias, allergen, diet, nutrition ve substitution kontrolü.
4. Özgün metin + source/provenance + verification metadata; `draft/researched/verified/published/needs_review/deprecated` yaşam döngüsü.
5. Telif uygun özgün/izinli media manifesti.
6. Idempotent seed, data quality scan ve admin preview.
7. Reviewer onayı; published state.

## Görsel, trust ve import sınırı

Tarif görseli `missing/placeholder/generated/licensed/original/verified/rejected` durumu ve lisans metadata'sı olmadan gerçek editorial görsel sayılmaz. İlk 50 tarifte rastgele web görseli veya toplu scraping kullanılmaz. Import Engine V1.5 yalnız gerçek kaynak seçildiğinde, 100+ içe aktarma ya da otomatik güncelleme ihtiyacı doğduğunda değerlendirilir.

## Başarı ölçütü

Tarif sayısı tek başına kalite ölçütü değildir. Her tarifin completeness, confidence, source ve UI anlaşılabilirliği doğrulanır; eksik bilgi varsa güvenli biçimde görünür kılınır.

## Faz 3 teslimi

Versioned `first_50.php` katalog dosyası ve `recipes:import-editorial` komutu 50 idempotent editoryal kayıt üretir. Kayıtlar source/provenance, household/canonical satırlar, grup, adım, nutrition, dietary/allergen sinyalleri, completeness skoru, lisanslı markalı placeholder ve public ULID içerir. Gerçek dış kaynak importu bu kapsamda değildir.
# Measurement prerequisite

New editorial recipes must carry household display quantities, canonical calculation values, conversion confidence and source/provenance before publication.
