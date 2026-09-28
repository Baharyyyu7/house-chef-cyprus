# Medya Kitaplığı Sadeleştirme ve Güvenli Temizlik Tasarımı

Tarih: 28 Eylül 2026  
Durum: Kullanıcı tarafından yaklaşımı onaylandı; uygulama planı öncesi inceleme sürümü

## Amaç

Yönetim ekranını medya tablosundaki tarihsel satırları sayan teknik bir görünümden çıkarıp, kullanıcıların görebildiği tariflerin gerçek görsel durumunu gösteren operasyon ekranına dönüştürmek. Ardından gerçek ve doğru yemek görsellerini koruyarak boş, kopmuş ve yer tutucu medya kalıntılarını yedekli ve doğrulanabilir biçimde temizlemek.

Bu çalışma üç sonucu birlikte sağlamalıdır:

1. Yönetici, yayındaki kaç tarifin gerçek görseli olduğunu doğrudan anlayabilmelidir.
2. Yanlış veya doğrulanmamış bir yemek görseli yanlışlıkla korunmamalı ya da yayımlanmamalıdır.
3. Temizlik, yayındaki tarif veya blog sayfalarında görsel kaybı oluşturmamalıdır.

## Mevcut durum

28 Eylül 2026 tarihli canlı ölçümde `media` tablosunda 8.859 kayıt bulunuyordu:

- 6.721 blog medya kaydı
- 2.138 tarif medya kaydı
- 5.168 `no_media` durumlu kayıt
- 3.975 `fallbacks/` yolunu kullanan yer tutucu kayıt
- 3.749 boş dosya yoluna sahip kayıt
- 6.939 bağlı içeriği bulunamayan kayıt

Bu kümeler çakışabilir. Örneğin bir kayıt aynı anda hem dosyasız hem de bağlı içeriği bulunmayan bir kayıt olabilir. Bu nedenle sayılar toplanarak silinecek kayıt adedi hesaplanmayacaktır. Temizlik adayları kayıt kimliği üzerinden tekilleştirilecektir.

Türkçe kullanıcı kataloğunda aynı anda 132 yayındaki, doğrulanmış ve indekslenebilir tarif bulunuyordu. Bu 132 tarife 288 medya satırı bağlıydı. Bazı tariflerde eski, yeni, yer tutucu ve onay bekleyen birden fazla satır bulunması, medya satırı sayısını tarif sayısından büyük gösteriyordu.

## Temel ilkeler

### Gerçek görsel tanımı

Bir görsel yalnız `PublicMediaEligibilityService` sonucu uygun olduğunda canlıda kullanılabilir gerçek görsel sayılır. `media_status` alanı tek başına yeterli değildir. Dosyanın varlığı, tarif kimliği, sadakat, insan onayı, gerekli varyantlar ve kopya kullanımı birlikte denetlenir.

### Korunacak medya

Aşağıdaki kayıtlar temizliğin dışında tutulur:

- Yayındaki bir tarif için `PublicMediaEligibilityService` tarafından uygun bulunan medya.
- Henüz yayında olmayan mevcut bir tarife bağlı, dosyası erişilebilir, yemek kimliği doğrulanmış ve sadakat denetiminden geçmiş medya.
- İnsan onayını bekleyen; kaynağı, gerekli varyantları ve tarif eşleşmesi geçerli medya.
- Mevcut bir blog yazısına ait, dosyası erişilebilir ve gerçekten kullanılan özgün medya.
- Uygulamanın çalışması için gereken tek ortak blog kapak dosyası.

### Temizlik adayı medya

Aşağıdaki kayıtlar yedek ve önizleme sonrasında temizlenebilir:

- Dosya yolu boş olan hotlink veya aday kayıtları.
- Bağlı tarif ya da blog kaydı artık bulunmayan medya satırları.
- Veritabanında ayrı medya kaydı olarak tutulan markalı yer tutucular.
- Aynı tarif için artık kullanılmayan eski kopyalar; ancak uygun birincil görsel kesin olarak doğrulandıktan sonra.
- Kimliği başka bir tarife ait olduğu doğrulanan veya içerikle açıkça uyuşmayan medya.
- Veritabanında hiçbir medya kaydına bağlı olmayan ve korunan dizinlerde bulunmayan fiziksel dosyalar.

Yalnız onay eksikliği bir silme gerekçesi değildir. Onay bekleyen geçerli görsel yönetici kararına bırakılır.

## Yönetim ekranı

Ana ekranın veri birimi medya satırı değil, benzersiz tarif olacaktır. Varsayılan kapsam Türkçe kullanıcı kataloğunda şu şartları karşılayan tariflerdir:

- `status = published`
- `verification_status = verified`
- Türkçe katalogda gösterilebilir
- SEO kalite kapısıyla `noindex` yapılmamış

Üst bölüm dört sayı gösterir:

1. **Yayındaki Tarifler:** Yukarıdaki katalog sorgusunun toplamı.
2. **Görselli Tarifler:** En az bir uygun medya kaydı bulunan benzersiz tarifler.
3. **Görselsiz Tarifler:** `Yayındaki Tarifler - Görselli Tarifler`.
4. **Onay Bekleyenler:** İnsan onayı verildiğinde medya kapısından geçecek en az bir görseli bulunan benzersiz tarifler.

Sayaçların aritmetik ilişkisi görünür ve test edilmiş olacaktır:

```text
Yayındaki Tarifler = Görselli Tarifler + Görselsiz Tarifler
```

Kart ızgarası her tarif için yalnız bir kart gösterir. Kart durumları:

- `Gerçek görsel yayında`
- `Onay bekliyor`
- `Görsel gerekli`

Filtreler aynı veri kümesi üzerinde çalışır: `Tüm tarifler`, `Görselli`, `Görselsiz`, `Onay bekleyen`. Bloglar, malzemeler, kopmuş kayıtlar ve yer tutucu satırlar ana tarif ekranına karışmaz.

Teknik medya kayıtları gerekiyorsa varsayılan akışın dışında, bakım araçları altında salt okunur bir görünümde tutulur. Bu görünüm katalog sayaçlarını etkilemez.

## Ortak blog kapağı

Görseli olmayan bloglar için tek bir yazısız, logosuz, genel mutfak editoryal kapağı üretilecektir. Dosya sunucudaki kontrollü public medya alanında tek kopya olarak tutulur.

Her blog için ayrı medya satırı veya ayrı dosya kopyası oluşturulmaz. Blog görünümü, uygun özgün hero görseli yoksa ortak kapağın URL'sine düşer. Özgün blog görseli eklendiğinde ortak kapak otomatik olarak devreden çıkar.

Ortak kapak:

- Özgün makale fotoğrafı olarak JSON-LD'ye yazılmaz.
- Görsel sitemap'inde bloga özgü görsel gibi çoğaltılmaz.
- Open Graph için kullanılacaksa bunun genel site kapağı olduğu açık biçimde tanımlanır.
- Üzerinde yazı, logo, filigran veya konuya özgü yanlış bir yemek bulunmaz.

Bu yapı, aynı görseli binlerce medya kaydına çoğaltmadan görsel boşluğunu kapatır.

## Temizlik iş akışı

### 1. Canlı yedek

Temizlikten önce sunucuda tarihli veritabanı yedeği ve silinecek fiziksel medya dosyalarının arşivi oluşturulur. Yedek yolları, boyutları ve checksum değerleri çalışma kaydına yazılır. Yedek doğrulanmadan hiçbir silme yapılmaz.

### 2. Koruma manifesti

Mevcut tarif ve bloglar taranarak korunacak medya kimlikleri ve fiziksel yolları değişmez bir manifestte listelenir. Her yayındaki tarif için temizliğin öncesinde ve sonrasında seçilen uygun görsel kaydedilir.

### 3. Aday raporu

Boş yol, kopmuş ilişki, yer tutucu, eski kopya, kimlik uyuşmazlığı ve fiziksel orphan kategorileri ayrı listelenir. Her kayıt birincil gerekçeyle raporlanır; çakışan kategoriler tek bir silme adayına dönüşür.

### 4. Önizleme kapısı

Silme komutu varsayılan olarak dry-run çalışır. Şu bilgiler görülmeden uygulama moduna geçmez:

- Tekilleştirilmiş kayıt adedi
- Kategori dağılımı
- Etkilenecek fiziksel dosya adedi ve toplam boyut
- Korunan medya adedi
- Yayındaki tariflerde beklenen görsel kaybı: sıfır

### 5. Uygulama sırası

Silme sırası şöyledir:

1. Yedek oluştur ve doğrula.
2. Koruma manifestini oluştur.
3. Veritabanındaki boş aday kayıtlarını kaldır.
4. Bağlı içeriği bulunmayan kayıtları kaldır.
5. Veritabanı yer tutucu kayıtlarını kaldır.
6. Güvenli eski kopyaları kaldır.
7. Kalan referanslardan fiziksel orphan listesini yeniden hesapla.
8. Yalnız yeniden hesaplanan güvenli orphan dosyalarını kaldır.

Fiziksel dosya hiçbir zaman ilişkili veritabanı kaydından önce silinmez.

### 6. Son doğrulama

Temizlik sonrasında:

- Bütün yayındaki tarif URL'leri açılmalıdır.
- Temizlik öncesinde uygun görseli olan hiçbir tarif görselsiz kalmamalıdır.
- Görselli ve görselsiz tarif toplamı yayındaki tarif sayısına eşit olmalıdır.
- Onay kuyruğundaki geçerli görseller görünmeye devam etmelidir.
- Bloglar özgün görsel veya ortak kapak göstermelidir.
- Sitemap, JSON-LD ve Open Graph çıktılarında silinmiş dosya URL'si bulunmamalıdır.
- `PublicMediaEligibilityService` denetimi yeni hata üretmemelidir.

## Hata yönetimi ve geri alma

Her temizlik aşaması ayrı işlem grubu olarak çalışır ve adım sonuçları kaydedilir. Veritabanı silme işlemi başarısız olursa fiziksel dosya temizliğine geçilmez. Canlı doğrulama başarısız olursa işlem durdurulur ve veritabanı ile dosyalar aynı yedek noktasından geri alınır.

Yedek, geri alma testi tamamlanana kadar silinmez. Otomatik temizlik zamanlayıcıya bağlanmaz; yeni kurallar gözlemlenip doğrulanana kadar yalnız yönetici tarafından çalıştırılır.

## Test kapsamı

Feature testleri en az şu durumları kapsar:

- Sayaçların medya satırlarını değil benzersiz yayındaki tarifleri sayması.
- `Yayındaki = Görselli + Görselsiz` eşitliği.
- Yer tutucunun görselli tarif sayılmaması.
- İnsan onayı bekleyen geçerli görselin doğru filtrede görünmesi.
- Blog ve orphan kayıtlarının tarif sayaçlarına karışmaması.
- Aynı tarifte birden fazla medya kaydının tarif sayısını artırmaması.
- Ortak blog kapağının yalnız özgün hero bulunmadığında kullanılması.
- Ortak kapağın makaleye özgü JSON-LD görseli olarak yazılmaması.
- Dry-run temizliğinin hiçbir kayıt veya dosya silmemesi.
- Koruma manifestindeki medyanın uygulama modunda silinememesi.

İlgili feature testleri ve tam `php artisan test` çalıştırılmadan canlı temizlik yapılmaz.

## Başarı ölçütleri

- Yönetim ekranındaki toplam tarif sayısı canlı katalog sorgusuyla aynıdır.
- Görselli ve görselsiz tarif sayıları birlikte toplam tarifi eksiksiz açıklar.
- Ana ekranda 8.859 gibi tarihsel medya satırı sayıları gösterilmez.
- Yayındaki mevcut uygun görsellerde kayıp sıfırdır.
- Boş, kopmuş ve yer tutucu kayıtlar tekilleştirilmiş güvenli adaylar olarak temizlenir.
- Görselsiz blogların tamamı tek ortak kapağı, veritabanı çoğaltması olmadan gösterir.
- İnsan onayı gerektiren görseller otomatik onaylanmaz.

## Kapsam dışı

- Tarif veya blog yayın durumunu değiştirmek.
- Yeni dil etkinleştirmek.
- Yanlış tarif içeriğini otomatik düzeltmek.
- Bütün bloglar için konuya özgü özgün görsel üretmek.
- Onay bekleyen görsellere insan adına toplu onay vermek.
- Temizliği periyodik ve denetimsiz bir otomasyona bağlamak.
