<?php

declare(strict_types=1);

/*
 * Reklam bölümü yalnız reklam gerçekten açıkken metne girer.
 *
 * Bu belgenin kendi ilkesi var ve gizlilik bölümünde yazılı:
 * "Kullanılmayan analiz, reklam veya takip sistemleri bu metinde
 * kullanılıyormuş gibi gösterilmez." Reklam kapalıyken bölüm hiç
 * görünmez, açıldığı anda görünür; böylece metin iki durumda da doğrudur.
 *
 * Google, gizlilik politikasının üçüncü taraf reklam çerezlerini açıkça
 * anlatmasını şart koşuyor. Bölüm o şartı karşılamak için var.
 */
$reklamAcik = (bool) env('GOOGLE_ADSENSE_ENABLED', false);

return [
    'title' => 'House Chef Cyprus Kullanım Koşulları, Gizlilik, Çerez ve İçerik Politikası',
    'updated_at' => 'Son güncelleme: 3 Ağustos 2026',
    'introduction' => 'Bu metin, House Chef Cyprus internet sitesinin kullanım koşullarını, tarif ve blog içeriklerine ilişkin bilgilendirmeleri, kullanıcı hesaplarını, kişisel verilerin işlenmesini, çerez kullanımını, telif haklarını, kullanıcı tarafından gönderilen içerikleri ve iletişim süreçlerini açıklamaktadır. House Chef Cyprus internet sitesini ziyaret eden, üyelik oluşturan veya sitedeki hizmetlerden yararlanan kullanıcılar, aşağıda yer alan koşulları okuduklarını ve kabul ettiklerini beyan ederler. House Chef Cyprus; yemek tarifleri, mutfak bilgileri, blog yazıları, kullanıcı hesapları, favoriler, alışveriş listeleri, kişiselleştirilmiş öneriler ve benzeri dijital hizmetler sunabilir. Site adresi https://housechefcyprus.com, iletişim e-posta adresi ise info@housechefcyprus.com şeklindedir.',
    'sections' => [
        [
            'id' => 'kullanim-kosullari',
            'title' => 'Kullanım Koşulları',
            'paragraphs' => [
                'House Chef Cyprus yalnızca hukuka uygun amaçlarla kullanılabilir. Kullanıcılar başkasına ait hesapları kullanamaz, yanıltıcı veya sahte bilgiler veremez, siteye zarar verecek yazılım, saldırı veya otomatik işlem uygulayamaz, üçüncü kişilerin telif, marka, kişilik ve gizlilik haklarını ihlal edemez, hukuka aykırı, tehdit edici, ayrımcı, hakaret içeren veya zararlı içerikler yayımlayamaz ve site içeriğini izinsiz şekilde ticari amaçla kopyalayamaz veya yeniden yayımlayamaz. House Chef Cyprus; güvenlik, hukuki zorunluluk, sistem bütünlüğü veya diğer kullanıcıların korunması amacıyla içerikleri inceleyebilir, erişimi sınırlandırabilir veya hesapları geçici ya da kalıcı olarak durdurabilir.',
                'Kullanıcılar hesap oluştururken doğru ve güncel bilgi vermelidir. Hesap sahibi şifresini gizli tutmak, şüpheli girişleri bildirmek, hesabıyla yapılan işlemleri kontrol etmek ve e-posta adresini güncel tutmak konusunda sorumludur. Şüpheli etkinlik durumunda oturumlar kapatılabilir, parola yenileme istenebilir, hesap geçici olarak durdurulabilir veya ek güvenlik doğrulaması uygulanabilir.',
                'House Chef Cyprus bu koşulları ve politikaları hizmetlerin değişmesi, yeni özelliklerin eklenmesi, güvenlik ihtiyaçları, teknik altyapı değişiklikleri veya mevzuat değişiklikleri nedeniyle güncelleyebilir. Önemli değişikliklerde sayfanın güncelleme tarihi yenilenir ve gerekli durumlarda kullanıcıya bildirim yapılır. House Chef Cyprus, sitenin kesintisiz veya hatasız çalışacağını kesin olarak garanti etmez. Teknik bakım, internet kesintisi, sunucu problemi, üçüncü taraf hizmetleri veya güvenlik önlemleri nedeniyle geçici erişim sorunları yaşanabilir. Kullanıcıların tarifleri kendi sağlık durumlarına göre değerlendirmesi, malzemeleri kontrol etmesi, gıda güvenliği önlemlerini uygulaması ve üçüncü taraf bağlantılarda dikkatli olması gerekir. House Chef Cyprus, kullanıcı hatası, yanlış uygulama, uygunsuz saklama, alerjen kontrolünün yapılmaması veya üçüncü taraf hizmetlerinden kaynaklanan sonuçlardan sorumlu tutulamaz.',
            ],
        ],
        [
            'id' => 'tarif-ve-saglik-uyarilari',
            'title' => 'Tarif ve Sağlık Uyarıları',
            'paragraphs' => [
                'House Chef Cyprus’ta yayımlanan tarifler genel bilgilendirme ve ev mutfağında kullanım amacıyla hazırlanır. Tarif sonuçları kullanılan malzemenin markası ve yapısı, fırın ve ocak farklılıkları, mutfak ekipmanları, porsiyon büyüklüğü, ortam sıcaklığı, malzemenin tazeliği ve kişisel uygulama farklılıklarına göre değişebilir. Bu nedenle tariflerde verilen süre, sıcaklık, gramaj ve ölçüler uygulamada küçük farklılıklar gösterebilir. Kullanıcıların malzeme tazeliği, pişirme güvenliği, saklama koşulları, çapraz bulaşma riski ve kişisel sağlık durumları konusunda kendi kontrollerini yapmaları gerekir. Tariflerde ev kullanıcılarının kolayca anlayabilmesi için su bardağı, çay bardağı, yemek kaşığı, tatlı kaşığı, çay kaşığı, adet, tutam, demet, diş, paket, dilim ve kepçe gibi ölçüler kullanılabilir. Gram ve mililitre bilgileri besin hesabı, porsiyon ölçekleme ve teknik eşleştirmeler için ayrıca kullanılabilir. Bardak, kaşık ve adet ölçüleri malzemenin yoğunluğuna ve büyüklüğüne göre yaklaşık değerler içerebilir.',
                'Kalori, protein, karbonhidrat, yağ ve benzeri beslenme değerleri tahmini olabilir. Bu değerler kullanılan ürün markasına, porsiyon büyüklüğüne, malzeme miktarına, pişirme yöntemine ve eklenen veya çıkarılan malzemelere göre değişiklik gösterebilir. House Chef Cyprus tıbbi teşhis, tedavi veya kişiye özel beslenme hizmeti sunmaz. Özel diyet uygulayan, kronik hastalığı bulunan, hamile olan, çocuk beslenmesiyle ilgilenen veya sağlık açısından özel durumu bulunan kullanıcıların uzman görüşü alması önerilir. Tariflerde alerjen bilgisi sunulabilir; ancak bu bilgi yardımcı niteliktedir ve kesin garanti olarak kabul edilmemelidir. Kullanıcıların gluten, süt ve süt ürünleri, yumurta, fındık ve diğer sert kabuklu yemişler, yer fıstığı, soya, balık, kabuklu deniz ürünleri, susam, kereviz ve hardal gibi alerjenleri ayrıca kontrol etmesi gerekir. Ambalajlı ürünlerde üreticinin güncel etiket bilgisi mutlaka incelenmelidir. Ciddi alerjisi bulunan kullanıcılar yalnızca site üzerindeki bilgiye dayanarak hareket etmemelidir.',
                'Tariflerin hazırlanması sırasında çiğ ve pişmiş ürünlerin ayrı tutulması, ellerin ve ekipmanların temizlenmesi, et, tavuk, balık ve yumurtanın uygun şekilde pişirilmesi, soğuk ürünlerin uygun sıcaklıkta saklanması, artan yemeklerin güvenli süre içinde soğutulması ve yeniden ısıtılan yemeklerin yeterli sıcaklığa ulaşması gerekir. Gıda güvenliğiyle ilgili içerikler genel bilgilendirme niteliğindedir. Güncel resmi tavsiyeler ve ürün etiketleri ayrıca dikkate alınmalıdır. House Chef Cyprus, içeriklerin doğru ve güncel olması için editoryal kontrol uygulamayı hedefler. Bununla birlikte ürün içerikleri değişebilir, resmi sağlık ve güvenlik tavsiyeleri güncellenebilir, teknik veya yazım hataları oluşabilir, bağlantı verilen dış siteler değişebilir ve tarif sonuçları kişisel uygulamaya göre farklılaşabilir. Bir hata fark edilmesi hâlinde info@housechefcyprus.com adresi üzerinden bildirim yapılabilir. Gerekli görülen içerikler düzeltilebilir, incelemeye alınabilir veya geçici olarak yayından kaldırılabilir.',
            ],
        ],
        [
            'id' => 'icerik-ve-kaynak-politikasi',
            'title' => 'İçerik ve Kaynak Politikası',
            'paragraphs' => [
                'House Chef Cyprus içerikleri özgün editoryal çalışmalar, genel mutfak bilgileri, güvenilir kaynak araştırmaları, kullanıcı tarafından sağlanan içerikler, uzman görüşleri ve yapay zekâ destekli taslaklardan hazırlanabilir. Başka internet sitelerindeki tarifler, yazılar veya görseller izinsiz olarak kopyalanmaz. Kaynaklardan yararlanılan durumlarda içerik özgün biçimde hazırlanır ve gerekli görülmesi hâlinde kaynak bilgisi eklenir. Yapay zekâ destekli içerikler otomatik olarak doğru kabul edilmez. Yayın öncesinde insan incelemesi, ölçü kontrolü, kaynak kontrolü ve editoryal değerlendirme uygulanabilir. House Chef Cyprus; içerik taslağı, görsel üretimi, içerik düzenleme, ölçü kontrolü, arama, sınıflandırma ve benzeri işlemlerde yapay zekâ sistemlerinden yararlanabilir. Yapay zekâ tarafından oluşturulan içerikler insan onayı olmadan kesin doğru kabul edilmez, gerektiğinde “yapay zekâ destekli” veya “temsili görsel” olarak işaretlenebilir, sağlık, alerjen, beslenme ve gıda güvenliği konularında ayrıca kontrol edilir ve doğrudan otomatik şekilde yayına alınmayabilir.',
            ],
        ],
        [
            'id' => 'gorsel-ve-yapay-zeka-politikasi',
            'title' => 'Görsel ve Yapay Zekâ Politikası',
            'paragraphs' => [
                'Sitedeki tarif ve blog görselleri House Chef Cyprus’a ait özgün çekimlerden, lisanslı stok görsellerden, açık lisanslı medyadan, kullanıcı tarafından izin verilerek gönderilen görsellerden veya yapay zekâ ile oluşturulan temsili görsellerden sağlanabilir. Yapay zekâ ile oluşturulan yemek görselleri gerçek çekim olmayabilir. Görsellerin tarifteki yemeği mümkün olduğunca doğru temsil etmesi amaçlanır; ancak görseldeki sunum, porsiyon veya dekorasyon gerçek sonuçtan farklı olabilir.',
            ],
        ],
        [
            'id' => 'kullanici-icerikleri',
            'title' => 'Kullanıcı İçerikleri',
            'paragraphs' => [
                'Kullanıcılar tarif, fotoğraf, yorum, profil görseli veya benzeri içerikler gönderebilir. İçerik gönderen kullanıcı, içeriği paylaşma hakkına sahip olduğunu, başkasına ait telif hakkını ihlal etmediğini, gerekli izinleri aldığını, içeriğin hukuka aykırı olmadığını ve yanıltıcı veya zararlı bilgi içermediğini beyan eder. Kullanıcı, gönderdiği içeriğin House Chef Cyprus tarafından site üzerinde gösterilmesine, biçimlendirilmesine, teknik olarak düzenlenmesine ve hizmetin tanıtımında kullanılmasına izin verir. Bu izin, içeriğin mülkiyetinin tamamen House Chef Cyprus’a geçtiği anlamına gelmez. Gerekli durumlarda içerik kaldırılabilir, düzenlenebilir veya incelemeye alınabilir.',
            ],
        ],
        [
            'id' => 'telif-haklari',
            'title' => 'Telif Hakları',
            'paragraphs' => [
                'House Chef Cyprus’a ait site tasarımı, yazılım kodu, logo ve marka unsurları, özgün tarif metinleri, blog yazıları, grafikler, görseller, video içerikleri ve editoryal düzenlemeler ilgili telif ve fikrî mülkiyet hükümleri kapsamında korunur. İçerikler yazılı izin olmadan kopyalanamaz, yeniden yayımlanamaz, satılamaz, toplu veri hâline getirilemez, başka bir internet sitesinin temel içeriği olarak kullanılamaz veya ticari amaçla çoğaltılamaz. Kısa alıntılar, kaynak gösterilmesi ve anlamın değiştirilmemesi şartıyla değerlendirilebilir.',
            ],
        ],
        [
            'id' => 'gizlilik-ve-kisisel-veriler',
            'title' => 'Gizlilik ve Kişisel Veriler',
            'paragraphs' => [
                'Kullanılan özelliklere göre ad ve soyadı, e-posta adresi, hesap ve profil bilgileri, şifrelenmiş parola bilgisi, favoriler, tarif ve blog tercihleri, kullanıcı tarafından gönderilen içerikler, yorumlar, IP adresi, tarayıcı ve cihaz bilgileri, oturum kayıtları, güvenlik kayıtları, çerez tercihleri, iletişim ve destek talepleri ile e-posta gönderim bilgileri işlenebilir. Bu veriler kullanıcı hesabı oluşturmak, oturum açmayı sağlamak, hesap güvenliğini korumak, e-posta doğrulamak, şifre sıfırlama işlemi yapmak, favorileri ve kişisel tercihleri saklamak, kullanıcı destek taleplerine cevap vermek, kötüye kullanımı önlemek, site performansını geliştirmek, teknik hataları tespit etmek, yasal yükümlülükleri yerine getirmek, kullanıcı deneyimini geliştirmek ve izin verilmişse bilgilendirme mesajları göndermek amacıyla kullanılabilir.',
                'Kişisel veriler yalnızca hizmetin sunulması için gerekli olduğu ölçüde hosting ve sunucu hizmetleri, e-posta gönderim hizmetleri, güvenlik hizmetleri, hata ve performans izleme araçları, yedekleme ve depolama hizmetleri, analiz araçları ve yasal olarak yetkili kurumlarla paylaşılabilir. Kullanılmayan analiz, reklam veya takip sistemleri bu metinde kullanılıyormuş gibi gösterilmez. Kişisel veriler yalnızca gerekli olduğu süre boyunca saklanır. Saklama süresi sona erdiğinde veriler silinebilir, anonim hâle getirilebilir, yasal zorunluluk nedeniyle sınırlı süreyle arşivlenebilir veya yedekleme döngüsü sonunda tamamen kaldırılabilir. Hesap silme talebi, verilerin tüm teknik yedeklerden anında silineceği anlamına gelmeyebilir. Yedeklerde bulunan veriler normal yedekleme döngüsü içinde silinir.',
                'House Chef Cyprus kullanıcı verilerini korumak amacıyla HTTPS bağlantısı, güvenli parola hash’leme, yetki ve rol sistemi, güvenlik kayıtları, oturum güvenliği, yazılım güncellemeleri, yedekleme, giriş denemesi sınırlama, kötüye kullanım tespiti ve veritabanı erişim kontrolü gibi önlemler uygulayabilir. Hiçbir internet sistemi mutlak güvenlik garantisi veremez. Kullanıcıların kendi cihazlarını ve parolalarını koruması gerekir.',
            ],
        ],
        [
            'id' => 'cerez-politikasi',
            'title' => 'Çerez Politikası',
            'paragraphs' => [
                'House Chef Cyprus, site işlevlerinin çalışması ve kullanıcı deneyiminin geliştirilmesi için çerezler kullanabilir. Zorunlu çerezler oturum açma, hesap güvenliği, çerez tercihlerinin saklanması, dil ve görünüm ayarları ile form güvenliği için kullanılabilir. İşlevsel çerezler favoriler, tema tercihleri, kullanıcı ayarları ve kişiselleştirilmiş görünüm gibi özellikleri destekleyebilir. Analitik çerezler site trafiğini ve kullanımını toplu şekilde anlamak için kullanılabilir. Analitik sistemler yalnızca gerçekten etkinleştirilmişse kullanılır. Reklam veya yeniden pazarlama araçları kullanılmıyorsa bu tür çerezler etkin değildir. Gelecekte kullanılmaları hâlinde gerekli izin alınmadan etkinleştirilmez. Kullanıcılar zorunlu olmayan çerezleri kabul edebilir veya reddedebilir, tümünü kabul etme, tümünü reddetme, tercihleri düzenleme ve daha sonra kararını değiştirme hakkına sahiptir. Zorunlu olmayan çerez kategorileri önceden seçili olmamalıdır.',
                'House Chef Cyprus başka internet sitelerine bağlantılar verebilir. Bu sitelerin içerikleri, güvenlikleri, çerezleri, gizlilik politikaları ve veri işleme faaliyetleri House Chef Cyprus’un kontrolünde değildir. Kullanıcıların yönlendirildikleri sitelerin politikalarını ayrıca incelemesi önerilir. House Chef Cyprus, uygulanabilir hukukta ebeveyn izni gerektiren yaşın altındaki çocuklardan bilerek kişisel veri toplamayı amaçlamaz. Bir çocuğa ait verinin izinsiz olarak işlendiği düşünülüyorsa info@housechefcyprus.com adresi üzerinden bildirim yapılabilir.',
            ],
        ],
        ...($reklamAcik ? [[
            'id' => 'reklam-ve-ucuncu-taraflar',
            'title' => 'Reklamlar ve Üçüncü Taraflar',
            'paragraphs' => [
                'House Chef Cyprus sitesinde Google tarafından sunulan reklamlar gösterilebilir. Google dâhil üçüncü taraf sağlayıcılar, kullanıcıların bu siteye ve başka sitelere yaptığı önceki ziyaretlere dayanarak reklam sunmak amacıyla çerez kullanabilir. Reklam çerezleri, reklamların gösterilmesi, tekrar gösterim sayısının sınırlanması, reklam performansının ölçülmesi ve kötüye kullanımın engellenmesi amacıyla kullanılabilir. Bu kapsamda IP adresi, tarayıcı ve cihaz bilgileri, yaklaşık konum ve site içi gezinme bilgileri reklam sağlayıcısına iletilebilir. House Chef Cyprus reklam sağlayıcısına ad, soyad, e-posta adresi veya parola gibi doğrudan kimlik bilgisi göndermez.',
                'Avrupa Ekonomik Alanı, Birleşik Krallık ve İsviçre kullanıcıları için reklam ve ölçüm izni, Google tarafından sertifikalandırılmış bir izin yönetim platformu üzerinden ayrıca alınır. İzin verilmediği sürece kişiselleştirilmiş reklam isteği yapılmaz. Kullanıcılar kararlarını istedikleri zaman değiştirebilir; izin ekranı site üzerinden yeniden açılabilir. Ölçüm iznine verilen onay, reklam izni sayılmaz ve iki tercih ayrı tutulur.',
                'Kullanıcılar kişiselleştirilmiş reklamları https://adssettings.google.com adresinden kapatabilir. Google’ın reklam amacıyla veri kullanımı hakkında ayrıntılı bilgi https://policies.google.com/technologies/ads adresinde yer alır. Üçüncü taraf sağlayıcıların genel tercih yönetimi için https://www.aboutads.info/choices ve https://www.youronlinechoices.com/tr adresleri kullanılabilir. Ücretli üyelik kullanan hesaplara reklam gösterilmez.',
            ],
        ]] : []),
        [
            'id' => 'kullanici-haklari',
            'title' => 'Kullanıcı Hakları',
            'paragraphs' => [
                'Kullanıcılar yürürlükteki mevzuat kapsamında verilerinin işlenip işlenmediğini öğrenme, işlenen verilere ilişkin bilgi isteme, işleme amacını öğrenme, verilerin aktarıldığı tarafları öğrenme, yanlış bilgilerin düzeltilmesini isteme, şartları oluştuğunda silinmesini isteme, otomatik değerlendirme sonuçlarına itiraz etme ve hukuka aykırı işlem nedeniyle zarar oluşmuşsa giderim talep etme haklarına sahip olabilir. Kullanıcılar hesap silme, kişisel veri talebi, bilgi düzeltme, içerik kaldırma, telif bildirimi, tarif hatası, gizlilik soruları, çerez tercihleri ve teknik destek konularında info@housechefcyprus.com adresine başvurabilir. Başvurunun değerlendirilebilmesi için talebin açık biçimde belirtilmesi gerekir. Gerekli durumlarda hesabın sahibini doğrulamak amacıyla ek bilgi istenebilir.',
            ],
        ],
        [
            'id' => 'iletisim',
            'title' => 'İletişim',
            'paragraphs' => [
                'Kullanım koşulları, gizlilik, çerezler, telif, tarif içerikleri, hesap işlemleri veya kişisel verilerle ilgili sorular için info@housechefcyprus.com adresi üzerinden iletişime geçilebilir. İnternet sitesi adresi https://housechefcyprus.com şeklindedir.',
            ],
        ],
    ],
];
