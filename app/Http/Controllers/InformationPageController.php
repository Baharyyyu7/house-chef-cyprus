<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

final class InformationPageController extends Controller
{
    /** @var array<string, array{title: string, eyebrow: string, lead: string, sections: array<int, array{title: string, body: string}>}> */
    private const PAGES = [
        'hakkimizda' => [
            'title' => 'Hakkımızda', 'eyebrow' => 'House Chef Cyprus',
            'lead' => 'Kıbrıs’tan ilham alan, güvenilir tarif verisini günlük mutfak kararlarına yaklaştıran bir platformuz.',
            'sections' => [
                ['title' => 'Ne yapıyoruz?', 'body' => 'Tarifleri malzeme, ölçü, beslenme, alerjen ve kaynak bilgileriyle birlikte düzenliyoruz.'],
                ['title' => 'Güven yaklaşımımız', 'body' => 'Yayınlanan tarifler için doğrulama ve provenance kayıtları saklanır; belirsiz bilgi kesin bilgi gibi sunulmaz.'],
            ],
        ],
        'nasil-calisir' => [
            'title' => 'Nasıl çalışır?', 'eyebrow' => 'Mutfakta daha az belirsizlik',
            'lead' => 'Tarif seçin, malzemelerinizi dolabınıza ekleyin ve uygun seçenekleri temkinli eşleştirme ile keşfedin.',
            'sections' => [
                ['title' => 'Tarifi keşfedin', 'body' => 'Süre, mutfak, beslenme ve alerjen filtreleriyle tarife ulaşın.'],
                ['title' => 'Dolabınızla eşleştirin', 'body' => 'Elinizdeki malzemeleri kaydedin; eksik veya belirsiz eşleşmeler açıkça belirtilir.'],
            ],
        ],
        'sss' => [
            'title' => 'Sık sorulan sorular', 'eyebrow' => 'Yardım merkezi',
            'lead' => 'Tarif verisi, beslenme bilgileri ve hesap özellikleri hakkında temel yanıtlar.',
            'sections' => [
                ['title' => 'Beslenme bilgileri tıbbi tavsiye midir?', 'body' => 'Hayır. Bilgiler tarif planlaması içindir; sağlık kararları için uzman desteği alın.'],
                ['title' => 'Alerjen bilgisi nasıl kullanılmalı?', 'body' => 'Etiketler yardımcıdır. Ambalajı ve çapraz bulaşma riskini her zaman ayrıca kontrol edin.'],
                ['title' => 'Ev ölçüleri nasıl çalışır?', 'body' => 'Tariflerde günlük kullanıma uygun ev ölçüleri bulunur. Gram ve mililitre karşılıkları, kaynak ve güven düzeyi elverdiğinde ayrıca gösterilir.'],
                ['title' => 'Porsiyon ölçekleme ne yapar?', 'body' => 'Miktarları seçtiğiniz porsiyona göre hesaplar; bazı ev ölçülerinde sonuç temkinli yorumlanmalıdır.'],
                ['title' => 'Dolabım ve Ne Pişirebilirim nasıl çalışır?', 'body' => 'Kaydettiğiniz malzemelerle tarifleri eşleştirir; eksik, alternatifle mümkün veya tam uygun durumlarını açıkça ayırır.'],
                ['title' => 'Görseller nereden gelir?', 'body' => 'Onaylanmış sağlayıcı görselleri veya tarife özel markalı fallback kullanılır. Pexels ve AI görselleri yayınlanmadan önce insan incelemesinden geçer.'],
                ['title' => 'Hesabımı nasıl silebilirim?', 'body' => 'Hesap ayarlarından silme talebi oluşturabilirsiniz. Bu işlem için desteklenen veri saklama ve güvenlik kuralları uygulanır.'],
            ],
        ],
        'editorial-ilkeler' => [
            'title' => 'Editoryal ilkeler', 'eyebrow' => 'İçerik güveni',
            'lead' => 'Özgün, izlenebilir ve temkinli içerik üretim standartlarımız.',
            'sections' => [
                ['title' => 'Özgünlük', 'body' => 'Başka sitelerden metin, tarif veya görsel kopyalanmaz.'],
                ['title' => 'Düzeltme', 'body' => 'Belirsiz veya hatalı veri incelemeye alınır; değişiklik geçmişi korunur.'],
            ],
        ],
        'gizlilik' => [
            'title' => 'Gizlilik', 'eyebrow' => 'Veri minimizasyonu',
            'lead' => 'Hesap deneyimi için gerekli verileri işler, parolaları geri döndürülemez hash olarak saklarız.',
            'sections' => [
                ['title' => 'Aktivite ve güvenlik', 'body' => 'Güvenlik olaylarında tam IP yerine maskeli ve özetlenmiş kayıtlar kullanılır.'],
                ['title' => 'Tercihler', 'body' => 'Tema ve mutfak dolabı gibi hesap tercihleri yalnız ilgili hesabın deneyimi için kullanılır.'],
            ],
        ],
        'kullanim-kosullari' => [
            'title' => 'Kullanım koşulları', 'eyebrow' => 'Şeffaf kullanım',
            'lead' => 'Platformu güvenli, saygılı ve hukuka uygun biçimde kullanma esasları.',
            'sections' => [
                ['title' => 'İçerik kullanımı', 'body' => 'Platform içeriği izinsiz toplu kopyalama veya yeniden yayınlama için sunulmaz.'],
                ['title' => 'Sorumluluk', 'body' => 'Alerjen, beslenme ve saklama bilgilerini kendi koşullarınıza göre doğrulayın.'],
            ],
        ],
        'tarif-dogrulama-politikasi' => [
            'title' => 'Tarif Doğrulama Politikası', 'eyebrow' => 'İçerik güveni',
            'lead' => 'Tarif kimliği, malzeme mantığı, ölçüler ve kaynak bilgileri editoryal incelemeyle birlikte değerlendirilir.',
            'sections' => [
                ['title' => 'Yayın eşiği', 'body' => 'Yayınlanan tariflerde kaynak/provenance ve doğrulama durumu korunur. Belirsiz kayıtlar kesinmiş gibi sunulmaz.'],
                ['title' => 'Düzeltmeler', 'body' => 'Hata bildirimi veya iç denetim sonrasında tarif incelemeye alınabilir; değişiklik geçmişi ve kaynak bağlamı silinmez.'],
            ],
        ],
        'kaynak-ve-veri-guven-politikasi' => [
            'title' => 'Kaynak ve Veri Güven Politikası', 'eyebrow' => 'İzlenebilir bilgi',
            'lead' => 'Kaynak gerektiren bilgi, kaynağı ve inceleme bağlamıyla birlikte ele alınır; belirsiz bilgi kesin olarak sunulmaz.',
            'sections' => [
                ['title' => 'Kaynak kapsamı', 'body' => 'Beslenme, alerjen, saklama, kültürel bağlam ve sağlıkla ilişkili iddialar kaynak ihtiyacına göre insan incelemesine yönlendirilir.'],
                ['title' => 'Düzeltme ve izlenebilirlik', 'body' => 'İçerik güncellemelerinde editoryal karar, destekleyici referans ve değişiklik bağlamı ayrı kayıtlarda tutulur.'],
            ],
        ],
        'besin-degeri-aciklamasi' => [
            'title' => 'Besin Değeri Açıklaması', 'eyebrow' => 'Temkinli hesaplama',
            'lead' => 'Besin değerleri tarif planlamasına yardımcı olur; tıbbi tanı, tedavi veya kişisel beslenme önerisi değildir.',
            'sections' => [
                ['title' => 'Güven düzeyi', 'body' => 'Hesaplama kapsamı, kaynak durumu ve eksik malzemeler sonucun güvenini etkiler. Belirsiz sonuçlar kesin değer gibi sunulmaz.'],
                ['title' => 'Kişisel ihtiyaçlar', 'body' => 'Alerji, hastalık, ilaç kullanımı veya özel beslenme gereksinimlerinde sağlık uzmanına danışın.'],
            ],
        ],
        'alerjen-uyarisi' => [
            'title' => 'Alerjen Uyarısı', 'eyebrow' => 'Güvenli kullanım',
            'lead' => 'Alerjen etiketleri yardımcı bilgidir; ürün ambalajı, çapraz bulaşma ve kişisel koşullar ayrıca kontrol edilmelidir.',
            'sections' => [
                ['title' => 'Kesinlik sınırı', 'body' => 'Tarif ilişkilerinde içerir, içerebilir ve çapraz bulaşma riski aynı anlamda değerlendirilmez.'],
                ['title' => 'Mutfak güvenliği', 'body' => 'Ev ekipmanı, marka farklılıkları ve çapraz temas kişisel riskleri değiştirebilir. Kritik kararları yalnız bu etikete dayandırmayın.'],
            ],
        ],
        'gorsel-ve-medya-politikasi' => [
            'title' => 'Görsel ve Medya Politikası', 'eyebrow' => 'Şeffaf medya',
            'lead' => 'Görsel kaynakları, lisans bilgileri ve editoryal inceleme durumu tarif verisinden ayrı olarak takip edilir.',
            'sections' => [
                ['title' => 'İnceleme zinciri', 'body' => 'Dış kaynaklı ve AI üretimi görseller önce private inceleme alanına alınır. İnsan onayı olmadan public alanda yayınlanmaz.'],
                ['title' => 'Fallback görseller', 'body' => 'Uygun gerçek görsel yoksa tarife özel markalı fallback kullanılır; bu görsel gerçek yemek fotoğrafı olarak tanıtılmaz.'],
            ],
        ],
        'icerik-duzeltme-politikasi' => [
            'title' => 'İçerik Düzeltme Politikası', 'eyebrow' => 'Düzeltme süreci',
            'lead' => 'Tarif, blog, kaynak veya medya bilgisindeki olası hatalar insan editoryal incelemesine alınır.',
            'sections' => [
                ['title' => 'İnceleme', 'body' => 'Bildirimler kaynak, veri bağlamı ve olası kullanıcı etkisi üzerinden değerlendirilir; kritik güncellemelerde yayınlanan içerik inceleme kilidine alınabilir.'],
                ['title' => 'Sonuç', 'body' => 'Düzeltme, ek kaynak isteği, yayın dışına alma veya değişiklik gerekmediği kararı audit kaydıyla ilişkilendirilir.'],
            ],
        ],
        'hata-bildir' => [
            'title' => 'Hata Bildir', 'eyebrow' => 'İçerik ve teknik hata bildirimi',
            'lead' => 'Tarif, kaynak, alerjen, medya veya teknik davranışla ilgili bir sorun fark ettiğinizde bunu değerlendirmemiz için bildirebilirsiniz.',
            'sections' => [
                ['title' => 'Neleri eklemeli?', 'body' => 'İlgili sayfanın bağlantısını, sorunun kısa açıklamasını ve varsa güvenli bir kaynak referansını paylaşın. Parola, ödeme veya kişisel sağlık bilgisi göndermeyin.'],
                ['title' => 'Kanal durumu', 'body' => 'Resmî destek kanalı yayına alınana kadar hesap ve içerik talepleri için doğrulanmış iletişim bilgisi yayınlamıyoruz. Bu durum, inceleme kayıtlarının otomatik kapatıldığı anlamına gelmez.'],
            ],
        ],
        'cerez-politikasi' => [
            'title' => 'Çerez Politikası', 'eyebrow' => 'Şeffaf tercih yönetimi',
            'lead' => 'Oturum ve güvenlik için gerekli çerezleri, yalnız hizmetin çalışması için kullanırız.',
            'sections' => [
                ['title' => 'Zorunlu çerezler', 'body' => 'Oturum güvenliği, CSRF koruması ve tema tercihi gibi temel işlevler için kullanılır.'],
                ['title' => 'Tercihler', 'body' => 'İsteğe bağlı analitik veya pazarlama araçları, ilgili açık tercih ve hesap yapılandırması olmadan etkinleştirilmez.'],
            ],
        ],
        'iletisim' => [
            'title' => 'İletişim', 'eyebrow' => 'Geri bildirim',
            'lead' => 'İçerik doğruluğu, hesap güvenliği ve gizlilik talepleri için yalnız doğrulanmış iletişim kanalları kullanılır.',
            'sections' => [
                ['title' => 'İçerik düzeltmesi', 'body' => 'Tarif, görsel, kaynak veya alerjen bilgisindeki olası hataları bildirirken tarif adını ve mümkünse public kimliğini paylaşın.'],
                ['title' => 'Gizlilik ve hesap talepleri', 'body' => 'Veri erişimi, silme veya gizlilik talepleri için resmî destek kanalı yayımlandığında bu sayfadan duyurulur. Sahte adres veya telefon bilgisi yayınlamıyoruz.'],
            ],
        ],
    ];

    public function show(string $page): View
    {
        $istenen = $page;
        $page = $page === 'gorsel-ve-kaynak-politikasi' ? 'gorsel-ve-medya-politikasi' : $page;
        abort_unless(isset(self::PAGES[$page]), 404);

        $content = $this->icerik($page);
        $schema = $page === 'sss'
            ? ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => collect($content['sections'])->map(fn (array $section): array => ['@type' => 'Question', 'name' => $section['title'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $section['body']]])->all()]
            : ['@context' => 'https://schema.org', '@type' => 'WebPage', 'name' => $content['title'], 'description' => $content['lead'], 'url' => route('information.show', $page)];

        return view('information.show', ['page' => $content, 'schema' => $schema, 'canonical' => route('information.show', $istenen)]);
    }

    /** İngilizce sayfada lang/en/information_pages.php, diğer dillerde Türkçe kaynak. */
    private function icerik(string $page): array
    {
        $ceviri = app()->getLocale() === 'en' ? trans('information_pages.'.$page) : null;

        return is_array($ceviri) ? $ceviri : self::PAGES[$page];
    }
}
