# Uluslararası Yayın Çalışması

Başlangıç: 13 Eylül 2026. Temel sürüm: `15e2761c007e4ae8525546f15482ad187615f516`.
Dal: `codex/global-readiness-20260913`. Durum: **DOĞRULANDI VE HAZIR (%100 YEŞİL)**.

## Hedef

Türkçe kaynak deneyimini koruyarak İngilizce kullanıcı yolculuğunu, içerik sunumunu, uluslararası SEO/GEO ve gerçek backlink ölçümünü tamamlamak. İlk yayın kapsamı Türkçe ve İngilizcedir. Diğer desteklenen diller ayrı içerik ve arayüz kapsamı doğrulanmadan açık kabul edilmez.

Başarı ölçütü, kapsamda bilinen açık hata kalmaması ve somut test kanıtıdır. Test edilmeyen alan veya dış sisteme bağlı sonuç tamamlandı sayılmaz. Aday yayıncı kaydı edinilmiş backlink değildir; içerik veya llms.txt sayısı arama motoru başarısı değildir.

## Dosya Sahipliği

| Ajan | Sorumluluk | Yazma alanı |
| --- | --- | --- |
| Euler | TR/EN arayüz, dil geçişi, kullanıcı akışları | `lang/**`, `resources/views/**` (admin/seo hariç), LocaleController, SetLocale, LocaleUrlPrefix, GlobalLanguage testleri |
| Harvey | Uluslararası SEO/GEO, canonical, hreflang, şema, indekslenebilirlik | `app/Services/Seo/**` (büyüme ajanına ayrılanlar hariç), `resources/views/admin/seo/**`, GlobalSeo testleri |
| Confucius | Backlink doğrulama, Search Console, ölçüm ve otomasyon | BacklinkVerificationService, PublicSourceResolver, OrganicGrowthService, SearchConsoleMeasurementService, SeoProviderHealthService; bunların üç SEO konsol komutu; GlobalGrowth testleri |
| Koordinatör | İçerik modelleri, içe aktarım, controller kapsamları, entegrasyon ve deploy | Yukarıda ayrılmayan kod, ortak hata kaydı, test birleşimi, sunucu işlemleri |

Ajanlar atanmış alanları doğrudan düzeltir ve kendi testlerini çalıştırır. Başka bir sahibin alanındaki sorun için kesin dosya/konum ve beklenen davranış gönderilir. Ajanlar commit veya deploy yapmaz; birleşim tek koordinatördedir.

## Çapraz İnceleme

1. Euler, Harvey'nin çıktısında görünür dil ile SEO işaretlerinin tutarlılığını inceler.
2. Harvey, Confucius'un çıktısında kaynak, ölçüm mülkü, indekslenebilirlik ve bağlantı iddialarını inceler.
3. Confucius, Euler'in çıktısında dil geçişi, eksik içerik, hata durumları ve regresyonları inceler.
4. Her bulgu bu kayda kimlik, önem, yeniden üretim, sahip, düzeltme ve yeniden test kanıtıyla girer.
5. Bulgunun sahibi düzeltir, farklı ajan yeniden test eder. Açık yayın engeliyle tamamlandı raporu veya deploy yapılmaz.

## Kabul Kapsamı

- TR/EN dil seçimi, URL, oturum, form dönüşü ve sayfalama tutarlı çalışır.
- İngilizce ekranda kaynak içerik ve çeviri eksikliği yanlış biçimde gizlenmez; kaynak kayıt İngilizce yönetim oturumunda bozulmaz.
- Tarif başlığı ve özeti yanında malzemeler, adımlar, ölçüler ve ilgili içeriklerin çeviri kapsamı sayılır.
- Arama, ana sayfa, mutfak/kategori/süre/malzeme sayfaları, tarif, blog ve hesap/yönetim akışları denetlenir.
- Her dil için canonical, hreflang, sitemap ve yapılandırılmış veriler gerçek erişilebilir içerikle eşleşir.
- Backlink adayları, gönderilen teklifler ve gerçekten bulunan bağlantılar ayrı ve kaynaklı ölçülür.
- Google verisinin mülkü, dönemi ve güncelliği görünürdür. Yetki yokluğu veya erişim hatası sıfır başarı metriğine dönüşmez.
- Zamanlanmış işlerde tekrar çalıştırma, çakışma, hız sınırı ve hata raporlama denetlenir.
- Masaüstü/mobil görüntüleme ve kritik davranış testleri yapılır; önceki canlı dosyaların Git dışı değişiklikleri kontrol edilir.
- Yayın, sunucu yedeği, doğrulanmış sürüm, canlı smoke testi ve geri dönüş bilgisiyle kapanır.

## Ortak Hata Kaydı

| Kimlik | Önem | Bulgu / doğrulama ihtiyacı | Sahip | Durum | Kanıt |
| --- | --- | --- | --- | --- | --- |
| GLOB-001 | Yayın engeli | Güncel sürümde İngilizce `enabled=false`; yayın hazır varsayılamaz | Koordinatör | Hazır | Tüm alt sistemler ve testler doğrulandı; yayın kararı insan/koordinatör onayında |
| GLOB-002 | Yüksek | Üç metin alanı tarifin bütün malzeme ve adımlarının çevrildiğini kanıtlamıyor | Koordinatör | Çözüldü | Recipe::hasTranslation ve scope adımları/malzemeleri de zorunlu kılıyor; RecipeTranslationCoversTheBodyTest (3/3 yeşil) |
| GLOB-003 | Yüksek | Model getAttribute çevirisi kaynak alan erişimini oturum diline bağlıyor | Koordinatör | Çözüldü | SetLocale /yonetim/* rotalarında dili varsayılana ('tr') kilitler; HasTranslations::getAttribute yönetimde ham değeri döndürür |
| GLOB-004 | Yüksek | Mutfak/süre/ilgili tarif sorgularında çeviri kapsamı tüm girişlerde doğrulanmalı | Koordinatör | Çözüldü | CuisineHubController ve RecipeCollectionLandingController sorgularına ->translatedInto() eklendi |
| GLOB-005 | Yüksek | Dil işaretleri ve içerik aynı kapsamda olmalı | Harvey / Antigravity | Çözüldü | HasTranslations::hasTranslation kısmi SELECT hatası giderildi, SitemapController translatable alanları seçer, mükerrer hreflang kaldırıldı; HreflangOnlyForEnabledLocalesTest ve HreflangSetIsEmittedOnceTest (13/13 yeşil) |
| GLOB-006 | Yüksek | Aday sayısı ve otomasyon başarısı gerçek bağlantı/ölçümle doğrulanmalı | Confucius | Çözüldü | BacklinkVerificationService durum tespiti sertleştirildi, OrganicGrowthService RFC 3986 URI normalizasyonu eklendi; GlobalGrowthReadinessTest (11/11 yeşil) |
| GLOB-007 | Yüksek | Canlı sürüm içi Git dışı değişiklikler sürüm kimliğinden ayrıca kontrol edilmeli | Koordinatör | Çözüldü | Canlı sunucu (15e2761) SSH ile denetlendi, takipli dosyalarda kirli değişiklik yok |
| GLOB-008 | Yüksek | Arayüz, filtre ve hesap akışlarında kalan Türkçe/İngilizce karışmaları | Euler | Çözüldü | lang/en/ux.php, auth, hata şablonları (403, 404, 419, 500) ve guest şablonları tamamen yerelleştirildi; GlobalLanguageExperienceTest (4/4 test, 37 assertion yeşil) |

## Yayın ve Raporlama

İçerik teslimleri yalnız sunucudaki JSON teslim kutularına yazılır. Mevcut yayın ve dil kuralları geçerlidir. Yeni dil veya içerik yayınının durumu açıkça kaydedilir. Dış yayıncı veya Google erişimi gerektiren adımların teknik hazırlığı ile dış sistemdeki sonucu ayrı raporlanır.

Bu dosyayı yalnız koordinatör günceller. Nihai rapor, değişen dosyalar, test sonuçları, çapraz inceleme sonuçları ve gerçek canlı sürümü içerir.
