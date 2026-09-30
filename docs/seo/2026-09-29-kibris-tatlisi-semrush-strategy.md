# Kıbrıs tatlısı SEO, GEO ve rakip izleme planı

Gözlem zamanı: 2026-09-29, Asia/Famagusta

## Ölçüm durumu

- Mülk: `housechefcyprus.com`
- SEMrush veri kaynağı: SEO API v4 Keyword Metrics ve Backlinks Competitors
- Denenen sorgu: `kıbrıs tatlısı`, ülke `TR`
- Sonuç: Her iki uç da HTTP 403 `Forbidden` ve `retryable: false` döndürdü.
- Sonuç olarak hacim, zorluk, rakip veya backlink sayısı raporlanmadı. Sıfır değer yazılmadı ve tahmin üretilmedi.
- Anahtar yalnız istek başlığında kullanıldı; bu dosyaya, git geçmişine ve otomasyon metnine eklenmedi.

SEMrush Business API erişimi ve ilgili v4 anahtar izinleri açıldığında ilk ölçüm; sorgu, ülke, uç, gözlem zamanı ve dönen ay bilgisiyle yeniden alınmalıdır.

## İçerik kararı

Sitede aynı konuda yayımlı kanonik yazı bulundu:

`kibris-tatlisi-serbet-ve-krema-dengesi-agizda-eriyen-orijinal-ada-recetesi-HJHAK`

Yeni bir URL açmak yerine bu kayıt için Türkçe inceleme revizyonu ve `en`, `de`, `fa`, `pl`, `ru`, `pt` yerelleştirme teslimleri hazırlanır. Bu karar içerik kannibalizasyonunu önler. Metin, “Kıbrıs tatlısı” adının Türkiye’de yaygınlaşan katmanlı tatlı için kullanıldığını; bunun bütün Kıbrıs tatlı geleneğini veya tartışmasız tarihsel köken iddiasını temsil etmediğini açıkça belirtir.

## Hedef sorgu kümeleri

Bu kümeler ölçüm yapılana kadar hipotezdir; hacim değeri atanmaz.

| Pazar | Birincil niyet | Destekleyici niyet |
| --- | --- | --- |
| TR | kıbrıs tatlısı | kıbrıs tatlısı tarifi, şerbet krema dengesi, galeta unlu tatlı |
| EN | Cyprus dessert / Cypriot dessert | what is Kıbrıs tatlısı, semolina syrup cake with cream |
| DE | zypriotisches Dessert | Kıbrıs tatlısı Rezept, Grießkuchen mit Sirup und Creme |
| PL | deser cypryjski | Kıbrıs tatlısı przepis, ciasto z kaszą manną i kremem |
| RU | кипрский десерт | Kıbrıs tatlısı рецепт, манный пирог с сиропом и кремом |
| PT | sobremesa cipriota | receita de Kıbrıs tatlısı, bolo de sêmola com calda e creme |
| FA | دسر قبرسی | دستور Kıbrıs tatlısı، کیک سمولینا با شربت و کرم |

## GEO gereksinimleri

- Başlık, özet ve ilk paragraf yemeği doğrudan tanımlar.
- Hazırlama tekniği, katman sırası, şerbet sıcaklığı ve dinlendirme süresi görünür içerikte bulunur.
- Köken iddiası temkinli ve kaynaklanabilir biçimde yazılır.
- Yazar, güncelleme tarihi ve kaynak alanları görünür sayfayla schema içinde aynı olmalıdır.
- SSS yalnız metinde gerçekten yanıtlanan soruları tekrarlar.
- `llms.txt` veya schema tek başına atıf ve görünürlük garantisi sayılmaz.

## Rakip ve backlink çalışması

API erişimi açıldığında sıralı çalışma:

1. Her pazar için Keyword Metrics ile sorgu hacmi ve zorluk ölçülür.
2. Organik sonuçlar incelenerek yemek siteleri, turizm yayınları ve video sonuçları ayrı sınıflanır.
3. Backlinks Competitors ile en az beş ortak yönlendiren alan adı bulunan gerçek rakipler çıkarılır.
4. Rakiplerin yönlendiren alan adları; Kıbrıs turizmi, diaspora, yemek kültürü ve yerel yayıncı olarak etiketlenir.
5. Aday, iletişim bilgisi ve doğrulanmış canlı bağlantı ayrı sayılır. Satın alınmış bağlantı veya toplu iletişim yapılmaz.

## Otomasyon sınırı

Takip haftada bir çalışır. API 403, 429 veya kota hatasında tekrar tekrar istek göndermez; engeli kaydeder ve durur. Ölçüm değişmediyse bildirim üretmez. İçerik yayınlamaz, dil etkinleştirmez ve backlink iletişimi göndermez.
