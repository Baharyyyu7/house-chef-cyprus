# SEO Altyapı Envanteri

Sistemde SEO adına ne varsa, baştan sona. Koddan tarandı, hafızadan
yazılmadı. Görsel yok; her şey metin.

Tarih: 21 Ağustos 2026 · Canlı sürüm: `5e3da3a`

---

## 1. Özet

| Katman | Adet |
|---|---|
| SEO servisi | 15 |
| Konsol komutu | 13 |
| Test dosyası | 16 |
| Yayınlanan schema tipi | 9 |
| Sitemap uç noktası | 5 |
| Yapılandırma dosyası | 3 |

Bu, çoğu projede olandan fazlası. Eksik olan kod değil, **ölçüm ve içerik**.

---

## 2. Servisler (`app/Services/Seo/`)

| Servis | İşi |
|---|---|
| `SeoMetadataService` | Sayfa başlığı, açıklama, Open Graph alanları |
| `SeoSchemaService` | JSON-LD yapısal veri üretimi |
| `SeoIndexabilityService` | Canonical ve robots kararı |
| `SeoAuditService` | Teknik SEO, içerik ve schema kural taraması |
| `SeoAuthorityService` | Otorite/E-E-A-T sinyalleri |
| `SitemapAuditService` | Sitemap'e taslak/onaysız içerik sızmasını denetler |
| `LocaleUrlService` | Dil URL'leri, hreflang kümesi, x-default |
| `ContentGapService` | Gerçek platform verisinden içerik fırsatı |
| `ContentQualityService` | İçerik kalite ölçümü |
| `ContentRefreshService` | Tazelenmesi gereken yayınlar |
| `ContentSimilarityService` | Kannibalizasyon/benzerlik tespiti |
| `InternalLinkService` | Açıklanabilir iç bağlantı önerisi |
| `ZeroResultSearchService` | Sonuç dönmeyen aramalar = içerik boşluğu |
| `SocialDraftService` | Sosyal medya taslak metni |
| `SeoProviderHealthService` | Sağlayıcı durumu, secret sızdırmadan |

---

## 3. Komutlar (`php artisan ...`)

| Komut | Ne yapar |
|---|---|
| `seo:audit` | Teknik SEO, içerik ve schema kurallarını tarar |
| `seo:health` | Yalnız gerçek katalog ve audit verisinden sağlık özeti |
| `seo:content-opportunities` | Platform verisinden içerik fırsatı üretir |
| `seo:content-refresh` | Süresi gelen yayınlar için **taslak** tazeleme |
| `seo:internal-links` | İç bağlantı önerisi, yalnız editoryal inceleme için |
| `seo:sitemap-audit` | Sitemap'te taslak/zamanlanmış/onaysız içerik var mı |
| `seo:sitemap-build` | Sitemap kaynaklarını doğrular |
| `seo:deployment-guard` | Production SEO yapılandırmasını doğrular |
| `seo:morning-brief` | İçerik ekibine günlük özet |
| `seo:social-drafts` | Onaylı içerik için sosyal taslak |
| `seo:provider-health` | Sağlayıcı sağlığı |
| `seo:search-console-import` | Search Console entegrasyon durumu |
| `seo:indexnow-submit` | IndexNow gönderim durumu |

**Ortak tasarım ilkesi:** komutların hiçbiri kendiliğinden yayına almıyor.
Hepsi taslak üretiyor ya da rapor veriyor. Bu, 3.200 sayfalık toplu yayın
olayından sonra bilinçli olarak kurulmuş bir sınır.

---

## 4. Yapısal veri (JSON-LD)

Kodun canlıda ürettiği tipler:

```
Recipe          HowToStep       BreadcrumbList
BlogPosting     FAQPage         Question / Answer
Organization    ListItem
```

Canlıda doğrulandı:
- Tarif sayfası: `Recipe` + `HowToStep` + `BreadcrumbList`
- Blog sayfası: `BlogPosting` + `FAQPage` + `Question/Answer` + `Organization`

`FAQPage` özellikle değerli: arama sonucunda genişletilebilir soru kutusu
kazandırıyor ve AI yanıtlarının doğrudan beslendiği biçim.

---

## 5. Sitemap

| Adres | İçerik |
|---|---|
| `/sitemap.xml` | Dizin |
| `/sitemaps/pages.xml` | 16 URL |
| `/sitemaps/recipes.xml` | 181 URL |
| `/sitemaps/blog.xml` | 27 URL |
| `/sitemaps/images.xml` | 86 URL |

Özellikler: `lastmod` taşıyor, türe göre bölünmüş, `xhtml:link` dil
alternatifleri **kayıt başına** hesaplanıyor (çevirisi olmayan dil ilan
edilmiyor).

`SitemapAuditService` taslak ve onaysız içeriğin sitemap'e sızmasını
engelliyor - ayrı bir test bunu koruyor.

---

## 6. Canonical ve indeksleme

`SeoIndexabilityService` tek bir kuralla çalışıyor:

```
sorgu parametresi yok  → index,follow
sorgu parametresi var  → noindex,follow
```

Sebebi: arama, filtre, sayfalama ve porsiyon kontrolleri ziyaretçi için
çalışmaya devam ediyor ama her kombinasyon taranabilir bir URL üretmiyor.
Canonical daima rota tabanlı, sorgusuz.

Ayrıca yönetim, giriş, kayıt, profil gibi yüzeyler otomatik
`noindex,nofollow`.

---

## 7. Çok dillilik

| Parça | Durum |
|---|---|
| Desteklenen diller | `tr` (etkin), `en`, `de`, `fa`, `pl` (kapalı) |
| Dil URL öneki | `/en/...` biçiminde, yalnız etkin diller |
| Kapalı dil öneki | 404 |
| `/tr/...` | 301 → köke |
| hreflang | Etkin dil ≥2 olunca basılır, `x-default` dahil |
| Çeviri saklama | `content_translations` tablosu |
| Kapı kuralı | Çevirisi olmayan kayıt o dilin sitesinde yok |

**Belirleyici kural:** liste, detay, sitemap ve hreflang aynı "bu dilde var
mı" tanımını paylaşıyor. Ayrışsalardı hreflang var olmayan sayfaları
gösterir ve küme geçersiz olurdu.

Şu an tek dil etkin olduğu için hreflang hiç basılmıyor - doğru davranış.

---

## 8. robots.txt

Canlıda servis edilen dosya `public/robots.txt`. Laravel'in `/robots.txt`
görünümü **çalışmıyor**: web sunucusu aynı adda gerçek dosya bulunca onu
servis ediyor.

İçerik:
```
Disallow: /dashboard /dolabim /favoriler /pisir/ /yonetim/
Sitemap: https://housechefcyprus.com/sitemap.xml
```

Cloudflare kendi "Managed robots.txt" bloğunu bunun **önüne** ekliyor ve
kod tarafından değiştirilemiyor.

---

## 9. Yapılandırma

`config/seo.php` anahtarları:

```
locale                        country
indexing_enabled              auto_publish
content_automation_enabled    max_drafts_per_day
max_refresh_drafts_per_day    max_social_drafts_per_day
audit                         search_console
indexnow                      social
```

Günlük taslak limitleri (`max_*_per_day`) ölçekli içerik üretimine karşı
kurulmuş bir fren.

---

## 10. Testler

16 test dosyası SEO davranışını koruyor:

```
SeoIndexabilityGuardTest          SitemapOmitsNoindexPagesTest
SeoSitemapAuditTest               SeoContentAutomationTest
SeoAuthorityProgramTest           EditorialBlogContentFitsSchemaTest
HreflangOnlyForEnabledLocalesTest HreflangOnlyForOpenLocalesTest
LocaleUrlPrefixTest               LocaleInfrastructureTest
LocalePagesOnlyShowTranslatedContentTest
ContentTranslationGatesLocalePagesTest
SearchConsoleVerificationFileTest GoogleSearchConsoleVerificationTest
InformationAndSeoPagesTest        NutritionSearchContractTest
```

---

## 11. Ölçüm hattı

| Parça | Durum |
|---|---|
| Search Console doğrulama dosyası | Canlıda, `200` |
| Mülkiyet doğrulaması | **Senin "DOĞRULA" tıklaman gerekiyor** |
| Sitemap gönderimi | **Bekliyor** |
| `seo:search-console-import` | Yetkilendirme yok, bekliyor |
| IndexNow | Kapalı |

Doğrulama dosyası eşitleme betiğine bağlandı; bir sonraki deploy'da
kaybolmayacak. Silinip geri geldiği test edildi.

---

## 12. Gerçek durum: eksik olan kod değil

Altyapı büyük ve düzgün. Trafiği tıkayan üç şey var, **üçü de kod dışı**:

**1. Cloudflare Googlebot ve BingBot'u engelliyor.**
Panelde `Block Crawler` anahtarları açık. Bu düzelmeden Search Console veri
toplayamaz, sitemap işlenmez, sıralama oluşmaz. Kodla erişilemiyor,
panel-only. Diğer her şeyden önce gelir.

**2. Search Console bağlı değil.**
Doğrulama dosyası hazır ama mülkiyet henüz doğrulanmadı. GSC verisi
olmadan `seo:content-opportunities` yalnız iç veriye bakıyor; hangi
sorguda gösterim alıp tıklanmadığın bilinmiyor. Ajanlara verilen bütün
"ölç, sonra yaz" talimatları bu veriye dayanıyor.

**3. İçerik tek dilde.**
181 tarif, 27 yazı - hepsi Türkçe. Çeviri altyapısı hazır, `content_translations`
tablosu boş. Dört dil desteği var ama içerik olmadan açılamaz.

---

## 13. Sıra

```
1. Cloudflare: Googlebot + BingBot engelini kaldır
2. Search Console: doğrula, sitemap gönder
3. 1-2 hafta veri birikmesini bekle
4. GSC verisinden hedef sorguları çıkar
5. Türkçe içeriği derinleştir
6. En çok gösterim alan içeriği İngilizceye çevir
7. Dili aç, hreflang otomatik devreye girer
```

Sırayı bozmak pahalıya patlar: 5'i 1'den önce yapmak ölçüsüz içerik
üretmek, 7'yi 6'dan önce yapmak Türkçe içerikli yabancı dil sayfası
üretmek demek.
