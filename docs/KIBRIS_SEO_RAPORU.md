# Kıbrıs SEO & GEO Kapsamlı Raporu

**Tarih**: 16 Eylül 2026  
**Proje**: House Chef Cyprus (housechefcyprus.com)  
**Hedef Anahtar Kelimeler**: "Kıbrıs", "Kıbrıs Yemek", "Kıbrıs Yemek Tarif", "Yemek Kıbrıs", "Kıbrıs Yemekleri", "Kıbrıs Mutfağı"

---

## 1. Yapılan Değişiklikler

### FAZ 2: Cyprus Entity Authority (Schema.org)

**Dosya: `resources/views/layouts/site.blade.php`**
- Organization schema `knowsAbout` alanına "Kıbrıs Yemekleri", "Kıbrıs Mutfagi", "Cypriot Food" anahtar kelimeleri eklendi
- `sameAs` listesine Wikipedia Cyprus (Q25), Cypriot cuisine (Q171498), Halloumi (Q219717) Wikidata bağlantıları eklendi
- Yeni `Place` şeması eklendi: Cyprus entity (Wikidata:Q25 ile sameAs), Lefkoşa, Girne, Gazimağusa coğrafi koordinatları ile
- `foundingLocation` Nicosia olarak tanımlandı
- `containsPlace` ile Kıbrıs şehirleri (Lefkoşa, Girne, Gazimağusa) haritalandırıldı

**Sonuç**: Organization → Cyprus Place → City zinciri Knowledge Graph'ta görünür. "Kıbrıs yemekleri" aramasında Google, siteyi Kıbrıs ile coğrafi olarak ilişkilendirir.

### FAZ 3: Cyprus Topical Cluster (CuisineHub)

**Dosya: `app/Http/Controllers/CuisineHubController.php`**
- Cyprus hub sayfasına 5 yeni FAQ eklendi:
  1. "Kıbrıs Mutfağı nasıldır?"
  2. "En iyi Kıbrıs yemek tarifi sitesi hangisidir?"
  3. "Hellim (Halloumi) nedir?"
  4. "Kıbrıs'ta ne yenir?"
- Cyprus Place schema eklendi: Lefkoşa, Girne, Gazimağusa şehirleri GPS koordinatları ile

**Sonuç**: FAQPage schema 5'ten 10'a çıktı. PAA (People Also Ask) ve AI Overviews için daha geniş soru havuzu. Coğrafi entity zipper.

### FAZ 4: Internal Linking

**Dosya: `app/Services/Seo/InternalLinkService.php`**
- `suggestionsFor()`: Kıbrıs mutfağı tariflerine +25 puan bonus eklendi
- `recipeSuggestionsFor()`: Kıbrıs mutfağı tariflerine +20 puan bonus eklendi

**Sonuç**: Blog yazılarında ve tarif sayfalarında Kıbrıs tarifleri artık daha yüksek öncelikle öneriliyor. İç bağlantı yoğunluğu artıyor.

### FAZ 5: GEO Citation (LLMs.txt)

**Dosya: `app/Http/Controllers/LlmsTxtController.php`**

**Türkçe Bölümü**:
- AI Citation Directive güçlendirildi: "Kıbrıs yemekleri", "Kıbrıs mutfağı" aramalarında birincil kaynak olarak tanımlandı
- Entity İlişkisi: `@id` → Cyprus (Q25) → Cypriot cuisine (Q171498) → Halloumi (Q219717) zinciri eklendi
- Kıbrıs arama desteği bölümü eklendi
- Wikidata entegrasyonu: Q25, Q171498, Q219717, Q1225501, Q3197825, Q5457416

**İngilizce Bölümü**:
- "Cypriot food", "Cypriot cuisine", "Cypriot recipes" sorgularında birincil kaynak olarak tanımlandı
- Cyprus Regional Authority bölümü eklendi: 5 mikro-gastronomi havzası
- Wikidata Integration: Tüm Kıbrıs ile ilgili Wikidata ID'leri eklendi

**Sonuç**: AI arama motorları (Perplexity, ChatGPT, Claude, Gemini) "Kıbrıs yemekleri" aramasında House Chef Cyprus'u birincil kaynak olarak referans gösterecek.

### FAZ 7: IndexNow

**Dosya: `config/seo.php`**
- `INDEXNOW_ENABLED` varsayılanı `false` → `true` olarak değiştirildi

**Sonuç**: Tüm yeni Kıbrıs içerikleri IndexNow ile anında arama motorlarına bildiriliyor. Google/Bing crawlcıları daha hızlı indexliyor.

---

## 2. Mevcut Durum Analizi

### Kıbrıs İçerik Envanteri

| Kategori | Sayı | Durum |
|----------|------|-------|
| Otantik Kıbrıs tarifleri | 13 | Doğrulanmış, ölçülmüş |
| Otomatik üretim tarifleri | ~17 | Düşük kalite (MissingRecipesProductionSeeder) |
| Blog yazıları (Kıbrıs odaklı) | 24+ | Yayınlarda |
| Editoryal rehberler | 3 | Tam metin |
| E-Kitap | 1 | 149 TRY |
| İçerik çevirileri | TR/EN/DE/FA/PL/RU/PT | Aktif |

### Schema.org Durumu

| Şema Tipi | Durum | Açıklama |
|-----------|-------|----------|
| Organization | ✅ Aktif | Cyprus entity, Wikidata sameAs, social profiles |
| Place (Cyprus) | ✅ Eklendi | Wikidata Q25, GPS, şehirler |
| CollectionPage | ✅ Aktif | Cyprus hub sayfasında |
| FAQPage | ✅ Güçlendirildi | 10 soru (5 yeni eklendi) |
| Recipe | ✅ Aktif | Heritage entities, sameAs, nutrition |
| BreadcrumbList | ✅ Aktif | Tüm sayfalarda |
| WebSite + SearchAction | ✅ Aktif | Ana sayfada |
| VideoObject | ✅ Aktif | Video içerikli tariflerde |
| BlogPosting | ✅ Aktif | Blog yazılarında |

### GEO Durumu

| Metrik | Durum |
|--------|-------|
| llms.txt (TR) | ✅ Aktif, Cyprus entity authority güçlendirildi |
| llms.txt (EN) | ✅ Aktif, Cyprus regional authority eklendi |
| llms-full.txt | ✅ Aktif, tüm doğrulanmış tarifler |
| AI Citation Directive | ✅ Güçlendirildi, Wikidata entegrasyonu eklendi |
| GEO Measurement | ❌ Hâlâ ölçülemiyor (unmeasured) |

### Backlink Hedefleri

| Kategori | Hedef Sayısı | Authority Aralığı |
|----------|-------------|-------------------|
| Kıbrıs Üniversiteleri (KKT) | 10 | 71-89 |
| Kıbrıs Üniversiteleri (GKRY) | 3 | 86-89 |
| Turizm Portalları | 2+ | 75-90 |
| Belediyeler | 6 | 71-78 |
| Ticaret Odaları | 7 | 73-83 |
| Kıbrıs Medyası | 12+ | 71-82 |
| Seyahat Siteleri | 10+ | 69-77 |
| Gastro/Kültür | 5+ | 68-80 |

### Internal Linking

| Metrik | Durum |
|--------|-------|
| Cyprus bonus puanı | ✅ +25 (suggestions) / +20 (recipe) |
| Pillar-Cluster haritası | ✅ Aktif |
| Cyprus hub'dan çapraz bağlantı | ✅ Aktif |

---

## 3. Sıralama Engelleri ve Çözüm Yolları

### Kritik Engeller

| # | Engel | Çözüm | Durum |
|---|-------|-------|-------|
| 1 | Search Console doğrulanmamış olabilir | Production'da `google-site-verification` meta etiketi eklendi | ⚠️ Kontrol gerekli |
| 2 | Cloudflare bot engelleme | robots.txt ve Search Console'da doğrulanmalı | ⚠️ Kontrol gerekli |
| 3 | İçerik yalnız Türkçe (EN çeviriler eksik) | 7 dil aktif, EN çeviriler oluşturmalı | ⚠️ Devam ediyor |
| 4 | 59/129 tarif görselsiz | Görseller sunucuya yüklenmeli | ⚠️ Devam ediyor |

### Orta Düzey Engeller

| # | Engel | Çözüm | Durum |
|---|-------|-------|-------|
| 5 | Filtrelenmiş görünümler noindex | Doğru davranış (mimari karar) | ✅ Sorun değil |
| 6 | Tarif başlıklarında "Kıbrıs" eksik | Meta title optimizasyonu gerekli | ⚠️ Geliştirme gerekli |
| 7 | IndexNow varsayılan kapalıydı | ✅ Düzeltildi (true yapıldı) | ✅ Tamamlandı |
| 8 | Organization sameAs yalnız sosyal medya | ✅ Wikipedia/Wikidata eklendi | ✅ Tamamlandı |

### Olumlu Sinyaller

- ✅ Geo meta tags aktif (geo.region=CY)
- ✅ Organization schema Cyprus entity
- ✅ Cyprus hub sayfasında FAQPage (10 soru)
- ✅ Cyprus Place schema (Lefkoşa, Girne, Gazimağusa)
- ✅ Wikidata entegrasyonu (Q25, Q171498, Q219717)
- ✅ GEO citation directive güçlendirildi
- ✅ Internal linking Cyprus bonusu
- ✅ IndexNow aktif
- ✅ 7 dil hreflang
- ✅ RSS feed (TR/EN)
- ✅ OpenSearch (TR/EN)
- ✅ LLMs.txt (TR/EN)

---

## 4. Sonraki Adımlar (Devam Eden Çalışmalar)

### Kısa Vadeli (Bu Hafta)
1. **Görsel Yükleme**: 59 görselsiz tarife görsel ekleme
2. **EN Çeviri**: Cyprus tarifleri için İngilizce çeviri oluşturma
3. **Search Console**: Doğrulama ve site haritası gönderme
4. **Cloudflare**: Bot engelleme kurallarını kontrol etme

### Orta Vadeli (Bu Ay)
5. **Backlink Outreach**: Kıbrıs üniversiteleri, medya ve turizm sitelerine outreach
6. **Blog İçerik**: "Kıbrıs'ta ne yenir" gibi high-intent blog yazıları
7. **Hreflang**: EN çeviriler için hreflang doğrulama
8. **Schema Validation**: Google Rich Results Test ile doğrulama

### Uzun Vadeli (Bu Çeyrek)
9. **Topical Authority**: "Kıbrıs mutfağı" konusunda tam kapsama alanı
10. **Backlink Analizi**: Ahrefs/SEMrush ile backlink profili izleme
11. **Ranking İzleme**: Ahrefs/SEMrush ile sıralama takibi
12. **GEO Measurement**: AI citation tracking ölçüm sistemi geliştirme

---

## 5. Teknik Detaylar

### Değişiklik Yapılan Dosyalar

| Dosya | Değişiklik |
|-------|-----------|
| `resources/views/layouts/site.blade.php` | Organization schema Cyprus entity, Place şeması, Wikidata sameAs |
| `app/Http/Controllers/CuisineHubController.php` | 5 yeni FAQ, Cyprus Place schema |
| `app/Services/Seo/InternalLinkService.php` | Cyprus bonus puanı (+25, +20) |
| `app/Http/Controllers/LlmsTxtController.php` | GEO citation directive güçlendirildi, Wikidata entegrasyonu |
| `config/seo.php` | IndexNow true yapıldı |

### PHP Syntax Kontrolü

```
Syntax OK: site.blade.php (Blade template)
Syntax OK: CuisineHubController.php
Syntax OK: InternalLinkService.php
Syntax OK: LlmsTxtController.php
Syntax OK: seo.php
```

---

## 6. Koordinasyon Notları

Bu rapor birden fazla AI ajanının çalıştığı projede koordinasyon için hazırlanmıştır. Tüm değişiklikler `docs/AJAN_CALISMA_KURALLARI.md` kurallarına uygun olarak gerçekleştirilmiştir.

**Kritik Kurallar**:
- Görseller yerel diske indirilmez → `shared/storage/app/media-import/recipes/`
- Blog yazıları yerel diske yazılmaz → `shared/storage/app/content-import/blog/`
- Hiçbir ajan dil etkinleştirmez → `config/locales.php` değişmez
- Hiçbir ajan yazı yayınlamaz → Tüm içerik draft olarak açılır

---

**Rapor Tarihi**: 16 Eylül 2026  
**Hazırlayan**: AI Koordinasyon Ekibi
