<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlogPost;
use App\Models\ContentRevision;
use Illuminate\Support\Str;

/**
 * Builds private, reviewable proposals for the public blog catalogue.
 *
 * This service never edits a BlogPost, approves a fact check, promotes media,
 * or publishes content. Its output is deliberately an editorial queue.
 */
final class PublishedBlogCompletionService
{
    private const RULE_VERSION = 'published-blog-completion-v1';

    /** @return array<string, mixed> */
    public function audit(): array
    {
        $rows = BlogPost::query()->publiclyVisible()->with(['category', 'heroMedia', 'author', 'recipes'])->orderBy('id')->get()
            ->map(fn (BlogPost $post): array => $this->auditPost($post));

        return [
            'rule_version' => self::RULE_VERSION,
            'generated_at' => now()->toIso8601String(),
            'blogs_audited' => $rows->count(),
            'topic_confirmed' => $rows->where('topic_state', 'TOPIC_CONFIRMED')->count(),
            'needs_expansion' => $rows->where('content_state', 'NEEDS_EXPANSION')->count(),
            'needs_fact_check' => $rows->where('fact_check_state', 'FACT_CHECK_REQUIRED')->count(),
            'needs_media' => $rows->where('media_state', 'NEEDS_MEDIA')->count(),
            'publication_ready' => $rows->where('publication_state', 'PUBLICATION_READY')->count(),
            'auto_publish' => false,
            'human_approval_required' => true,
            'items' => $rows->values()->all(),
        ];
    }

    /** @return array<string, mixed> */
    public function prepare(): array
    {
        $created = 0;
        $updated = 0;
        BlogPost::query()->publiclyVisible()->with(['category', 'heroMedia', 'recipes'])->orderBy('id')->each(function (BlogPost $post) use (&$created, &$updated): void {
            $proposal = $this->proposal($post);
            $existing = ContentRevision::query()->where('blog_post_id', $post->id)->where('reason', self::RULE_VERSION)->first();
            $revision = $existing ?? new ContentRevision([
                'blog_post_id' => $post->id,
                'revision_number' => ((int) $post->revisions()->max('revision_number')) + 1,
                'reason' => self::RULE_VERSION,
            ]);
            $revision->fill([
                'previous_body' => $post->body,
                'proposed_body' => $proposal['body'],
                'changed_seo_fields' => $proposal['review_pack'],
                'created_by' => null,
                'reviewed_at' => null,
            ])->save();
            $existing ? $updated++ : $created++;
        });

        return $this->audit() + [
            'revisions_created' => $created,
            'revisions_updated' => $updated,
            'private_only' => true,
            'published_content_changed' => false,
            'media_published' => false,
        ];
    }

    /** @return array<string, mixed> */
    private function auditPost(BlogPost $post): array
    {
        $highRisk = $this->isHighRisk($post);
        $realMedia = $post->heroMedia?->isApprovedRealMedia() === true;
        $bodyCharacters = mb_strlen($post->body);
        $headingCount = preg_match_all('/^#{2,3}\s+/m', $post->body);
        $canonicalOk = filled($post->canonical_url) && str_ends_with((string) $post->canonical_url, '/blog/'.$post->slug);

        return [
            'public_id' => $post->public_id,
            'title' => $post->title,
            'slug' => $post->slug,
            'category' => $post->category?->name,
            'topic_state' => filled($post->title) && filled($post->excerpt) ? 'TOPIC_CONFIRMED' : 'BLOCKED_TOPIC_UNKNOWN',
            'body_characters' => $bodyCharacters,
            'heading_count' => $headingCount,
            'content_state' => $bodyCharacters >= 4000 && $headingCount >= 3 ? 'READY' : 'NEEDS_EXPANSION',
            'fact_check_state' => $highRisk ? 'FACT_CHECK_REQUIRED' : 'FACT_CHECK_NOT_REQUIRED',
            'media_state' => $realMedia ? 'READY' : 'NEEDS_MEDIA',
            'media_path' => $post->heroMedia?->path,
            'media_approved' => $realMedia,
            'author_present' => $post->author_id !== null,
            'reading_minutes' => max(1, (int) ceil(str_word_count(strip_tags($post->body)) / 180)),
            'related_recipes' => $post->recipes->count(),
            'seo_title_present' => filled($post->meta_title),
            'meta_description_present' => filled($post->meta_description),
            'canonical_valid' => $canonicalOk,
            'publication_state' => $bodyCharacters >= 4000 && $headingCount >= 3 && ! $highRisk && $realMedia && $canonicalOk ? 'PUBLICATION_READY' : 'NEEDS_EDITOR_REVIEW',
        ];
    }

    /** @return array{body:string,review_pack:array<string,mixed>} */
    private function proposal(BlogPost $post): array
    {
        $plan = $this->plans()[$post->slug] ?? $this->fallbackPlan($post);
        $body = $post->excerpt."\n\n";
        foreach ($plan['sections'] as $section) {
            $body .= '## '.$section['title']."\n\n".implode("\n\n", $section['paragraphs'])."\n\n";
        }
        $body .= "## Uygulama kontrolü\n\n".$plan['checklist']."\n\n";
        $body .= "## Sonuç\n\n".$plan['conclusion'];

        $highRisk = $this->isHighRisk($post);
        $sources = $highRisk ? $this->officialSources($post->slug) : [['kind' => 'editorial_review', 'note' => 'Özgün House Chef Cyprus editoryal kontrolü ve ilgili tarif bağlantılarının insan incelemesi gerekir.']];
        $description = Str::limit($post->excerpt.' Uygulanabilir adımlar, dikkat noktaları ve ilgili House Chef Cyprus tarifleriyle ayrıntılı rehber.', 155, '');

        return [
            'body' => $body,
            'review_pack' => [
                'rule_version' => self::RULE_VERSION,
                'status' => 'NEEDS_EDITOR_REVIEW',
                'topic_state' => 'TOPIC_CONFIRMED',
                'fact_check_state' => $highRisk ? 'FACT_CHECK_REQUIRED' : 'FACT_CHECK_NOT_REQUIRED',
                'fact_check_sources' => $sources,
                'fact_check_approved' => false,
                'proposed_meta_title' => Str::limit($post->title.' | House Chef Cyprus', 65, ''),
                'proposed_meta_description' => $description,
                'proposed_canonical' => url('/blog/'.$post->slug),
                'related_recipe_public_ids' => $post->recipes->pluck('public_id')->all(),
                'media_brief' => $this->mediaBrief($post, $plan['visual']),
                'media_state' => 'NEEDS_MEDIA_APPROVAL',
                'editorial_approval' => null,
                'auto_apply' => false,
                'auto_publish' => false,
                'prepared_at' => now()->toIso8601String(),
            ],
        ];
    }

    private function isHighRisk(BlogPost $post): bool
    {
        return Str::contains(Str::lower($post->title.' '.$post->category?->name), [
            'alerjen', 'besin', 'sakla', 'ısıt', 'sıcaklık', 'güvenli', 'buzdolabı',
        ]);
    }

    /** @return list<array<string,string>> */
    private function officialSources(string $slug): array
    {
        return match ($slug) {
            'alerjen-etiketleri-nasil-okunur' => [['kind' => 'official', 'authority' => 'UK Food Standards Agency', 'url' => 'https://www.food.gov.uk/news-alerts/news/fsa-publishes-updates-to-best-practice-guidance-on-food-allergen-labelling']],
            'besin-degerlerinin-tahmini-olmasi' => [['kind' => 'official', 'authority' => 'European Food Safety Authority', 'url' => 'https://www.efsa.europa.eu/en/data-report/food-composition']],
            'artan-yemekleri-saklama-ve-isitma' => [['kind' => 'official', 'authority' => 'FoodSafety.gov', 'url' => 'https://www.foodsafety.gov/blog/leftovers-gift-keeps-giving']],
            'firin-yemeklerinde-sicaklik-ve-sure' => [['kind' => 'official', 'authority' => 'USDA FSIS', 'url' => 'https://www.fsis.usda.gov/food-safety/safe-food-handling-and-preparation/food-safety-basics/food-thermometers']],
            default => [['kind' => 'source_required', 'authority' => 'Yetkili yerel veya uluslararası gıda güvenliği kurumu', 'url' => '']],
        };
    }

    /** @return array<string,mixed> */
    private function mediaBrief(BlogPost $post, string $visual): array
    {
        return [
            'article_title' => $post->title,
            'topic' => $post->category?->name,
            'concept' => $visual,
            'required_objects' => [$visual, 'temiz ve gerçek bir ev mutfağı bağlamı'],
            'forbidden_objects' => ['logo taklidi', 'okunamayan ambalaj metni', 'ilgisiz yemek', 'sağlık garantisi', 'watermark'],
            'mood' => 'güvenilir, sıcak, düzenli ve editoryal',
            'composition' => 'yatay hero; ana konu merkezden hafif sapmalı; başlık için güvenli negatif alan',
            'lighting' => 'doğal ve yumuşak gün ışığı',
            'brand_usage' => 'Görsel içine logo veya metin basılmaz; marka sayfa arayüzünde kullanılır.',
            'text_overlay_allowed' => false,
            'crops' => ['hero' => '1600x900', 'card' => '800x600', 'square' => '1080x1080', 'portrait' => '1080x1350', 'open_graph' => '1200x630', 'thumbnail' => '400x300'],
            'formats' => ['webp', 'avif', 'jpeg'],
            'alt_text' => $post->title.' konusunu gösteren House Chef Cyprus editoryal görseli',
            'source' => null,
            'licence' => null,
            'attribution' => null,
            'synthetic' => null,
            'approval_state' => 'NEEDS_MEDIA_APPROVAL',
            'human_approval_required' => true,
        ];
    }

    /** @return array<string,array{sections:list<array{title:string,paragraphs:list<string>}>,checklist:string,conclusion:string,visual:string}> */
    private function plans(): array
    {
        $make = static fn (string $first, string $second, string $third, string $visual): array => [
            'sections' => [
                ['title' => 'Konuyu doğru çerçevelemek', 'paragraphs' => [$first, 'Başlamadan önce elinizdeki malzemeyi, ekipmanı ve zaman sınırını görünür hâle getirin. Böylece öneriyi körlemesine uygulamak yerine kendi mutfağınıza göre uyarlayabilirsiniz.']],
                ['title' => 'Mutfakta uygulanabilir yöntem', 'paragraphs' => [$second, 'Küçük bir denemeyle ilerleyin; doku, koku, görünüm ve ürün etiketi gibi doğrudan gözlemleri not edin. Sonucu etkileyen değişkenleri aynı anda değiştirmemek, bir sonraki denemeyi daha öngörülebilir kılar.']],
                ['title' => 'Dikkat edilmesi gerekenler', 'paragraphs' => [$third, 'Gıda güvenliği, alerji veya kişisel beslenme ihtiyacı söz konusuysa genel mutfak önerisini tek başına karar ölçütü kabul etmeyin. Güncel ürün etiketi, yerel resmî rehber ve gerektiğinde sağlık profesyoneli önceliklidir.']],
            ],
            'checklist' => 'Malzemeleri ve ekipmanı kontrol edin. Uygulamayı küçük adımlara bölün. Değişiklikleri not edin. İlgili House Chef Cyprus tarifindeki özel süre ve güvenlik notlarını ayrıca okuyun.',
            'conclusion' => 'İyi sonuç, tek bir ezberden çok düzenli gözlem ve bağlama uygun küçük kararlarla oluşur. Bu rehberi ilgili tariflerle birlikte kullanın ve belirsiz noktaları kesin bilgi gibi yorumlamayın.',
            'visual' => $visual,
        ];

        return [
            'kibris-mutfagina-baslangic-rehberi' => $make('Kıbrıs mutfağını tek bir reçeteye indirgemek yerine ada coğrafyası, mevsimsellik ve paylaşmalı sofra düzeni üzerinden okumak daha sağlıklı bir başlangıçtır.', 'Limon, zeytinyağı, otlar, bakliyat, sebze ve peynir gibi ortak malzemelerden küçük bir temel dolap kurun; ardından bir fırın yemeği, bir serin eşlikçi ve bir kahvaltı tabağı seçin.', '“Otantik” iddiasını genelleştirmeyin; aile, bölge ve dönem farklılıklarını görünür tutun.', 'Kıbrıs esintili paylaşmalı sofra, limon, otlar ve küçük tabaklar'),
            'kibris-sofralarinda-temel-malzemeler' => $make('Temel malzeme listesi, her evde aynı yemeği üretmek için değil farklı tabaklar arasında bağlantı kurmak için kullanılır.', 'Malzemeleri taze, kuru ve soğuk saklama ihtiyacına göre ayırın; limon ve otları servis öncesi, tuzlu peynirleri ise tarifteki toplam tuzu düşünerek kullanın.', 'Peynir ve paketli ürünlerde güncel alerjen etiketini kontrol edin; tuzluluk ve yoğunluk markaya göre değişebilir.', 'zeytinyağı, limon, domates, otlar ve hellimle düzenli malzeme seçkisi'),
            'ev-olculeri-rehberi' => $make('Bardak ve kaşık hacim belirtir; aynı hacimdeki un, pirinç ve yağ aynı gram ağırlığına sahip değildir.', 'Tarifin kullandığı bardak hacmini öğrenin, malzemeyi kaşığa bastırmadan veya tarif özellikle istemedikçe sıkıştırmadan ölçün; hassas hamurlarda tartıyı tercih edin.', 'Tuz, maya, kabartıcı ve jelatin gibi küçük miktarlı bileşenleri kaba yuvarlamayın.', 'ölçü kapları, kaşık seti ve dijital mutfak tartısı'),
            'gram-yerine-ev-olcusu-kullanmak' => $make('Ev ölçüsü pratiklik sağlar; gram karşılığı ise ancak malzeme yoğunluğu ve ölçme yöntemi biliniyorsa anlamlıdır.', 'Önce tarifin ana birimini koruyun, porsiyon değişiminde sıvı ve kuru malzemeleri ayrı değerlendirin, sonucu kıvam işaretleriyle kontrol edin.', 'Tek bir evrensel bardak-gram tablosunu bütün malzemelere uygulamayın.', 'un, pirinç ve yağın ayrı kaplarda ölçülmesi'),
            'kuru-bakliyat-nasil-saklanir' => $make('Kuru bakliyatın kalitesi nem, ısı, ışık ve stok devriyle ilişkilidir.', 'Temiz, kuru ve kapanan kap kullanın; ürün adı ile açılış tarihini yazın ve önce eski paketi tüketin.', 'Küf, böceklenme, nem veya sıra dışı koku bulunan ürünü diğer stoktan ayırın.', 'etiketli cam kaplarda nohut, mercimek ve fasulye'),
            'taze-sebzeleri-buzdolabinda-saklamak' => $make('Sebzelerin nem ve soğuk ihtiyacı aynı değildir; amaç yüzey nemini ve ezilmeyi kontrol etmektir.', 'Çürük parçaları ayırın, yıkanmış ürünü iyice kurutun ve hava dolaşımını tamamen kesmeyen uygun bölmeye yerleştirin.', 'Bozulma güvenliği yalnız görünüm veya kokuyla kesinleştirilemez; şüpheli ürünü tüketmeyin.', 'buzdolabı çekmecesinde ayrı kaplarda taze sebzeler'),
            'sogan-ve-sarimsak-nasil-saklanmali' => $make('Bütün soğan ve sarımsak kuru, serin ve hava alan ortam ister; kesilmiş ürün aynı koşulda saklanmaz.', 'Stoku sıkışık doldurmayın, yumuşayan parçaları ayırın ve kesilmiş ürünü temiz kapta soğukta tutun.', 'Küflü parçayı yalnız dış kısmını keserek kurtarmaya çalışmayın.', 'hava alan sepetlerde soğan ve sarımsak'),
            'corba-kivami-nasil-ayarlanir' => $make('Çorba kıvamı nişasta, buharlaşma, sıcaklık ve dinlenmeyle değişir.', 'Sıvıyı küçük porsiyonlarda ekleyin; koyulaştırmak için malzemenin bir kısmını ezmeyi veya kontrollü buharlaştırmayı deneyin.', 'Kaynar çorbaya soğuk yoğurtlu terbiye eklemek kesilmeye yol açabilir; ılıştırma adımını atlamayın.', 'kepçeden akan çorba kıvamının yakın planı'),
            'pilavin-tane-tane-olmasi' => $make('Pirinç çeşidi, yıkama, sıvı oranı, tencere ve dinlenme birlikte sonucu belirler.', 'Pirinci nazikçe yıkayın, tarife uygun sıvıyla kapağı sık açmadan pişirin ve ocaktan sonra dinlendirin.', 'Sıvı oranını farklı pirinç türleri arasında değişmez kabul etmeyin.', 'tencerede tane tane pilav ve tahta kaşık'),
            'makarna-sosu-secimi' => $make('Makarna şekli sosun yüzeye tutunma biçimini değiştirir.', 'İnce sosları uzun ve ince makarnayla, parçalı yoğun sosları kıvrımlı veya oluklu şekillerle deneyin; pişirme suyunu azar azar kullanın.', 'Sosu gereğinden fazla sulandırmayın ve makarnayı sosla buluşturmadan bekletmeyin.', 'farklı makarna şekilleri ve üç sos dokusu'),
            'firin-yemeklerinde-sicaklik-ve-sure' => $make('Fırın göstergesi, kabın malzemesi ve yemeğin kalınlığı gerçek pişme süresini değiştirir.', 'Fırını önceden ısıtın, kabı uygun rafa yerleştirin ve özellikle etli yemeklerde güvenliği gıda termometresiyle doğrulayın.', 'Yalnız renge bakarak güvenli iç sıcaklık sonucu çıkarmayın; üretici ve resmî sıcaklık rehberini izleyin.', 'fırın termometresi, kontrol paneli ve fırın kabı'),
            'hamur-islerinde-sik-yapilan-hatalar' => $make('Hamurda sıcaklık, unun su kaldırması ve dinlenme süresi birbirine bağlıdır.', 'Sıvıyı kademeli ekleyin, hamuru tarifteki doku işaretine göre değerlendirin ve mayalanmayı yalnız saate bağlamayın.', 'Fazla un ekleyerek yapışkanlığı hemen bastırmak kuru sonuç doğurabilir.', 'yoğurma tezgâhında hamur, spatula ve un kabı'),
            'dolaptaki-malzemelerle-yemek-planlama' => $make('Planlama, önce tüketilmesi gereken malzemeyi görünür hâle getirmekle başlar.', 'Malzemeleri tazelik ve kullanım tarihine göre sıralayın; ortak hazırlık adımı olan iki veya üç tarif seçin.', 'Şüpheli ürünü israfı azaltma amacıyla kullanmaya çalışmayın.', 'açık buzdolabı, düzenli kaplar ve haftalık kontrol listesi'),
            'artan-yemekleri-saklama-ve-isitma' => $make('Artan yemek güvenliği soğutma, saklama süresi ve eşit yeniden ısıtma adımlarının tümüne bağlıdır.', 'Yemeği sığ kaplara bölerek gecikmeden soğutun; yeniden ısıtırken karıştırın ve merkez dahil farklı noktalarda sıcaklığı kontrol edin.', 'Oda sıcaklığında uzun süre kalmış yemeği yalnız kokusuna güvenerek tüketmeyin; resmî süre ve sıcaklık rehberini izleyin.', 'etiketli sığ saklama kapları ve gıda termometresi'),
            'tariflerde-porsiyon-olcekleme' => $make('Ana malzemeler çoğunlukla oransal büyür; tuz, baharat, maya ve kabartıcılar aynı kolaylıkla ölçeklenmez.', 'Önce ana malzemeleri hesaplayın, hassas bileşenleri kademeli ayarlayın ve tava veya tencere kapasitesini yeniden değerlendirin.', 'Pişirme süresini porsiyon katsayısıyla doğrudan çarpmayın.', 'ölçü kapları, porsiyon kaseleri ve hesap notu'),
            'alerjen-etiketleri-nasil-okunur' => $make('“İçerir” ile ihtiyati “içerebilir” uyarıları farklı bilgi türleridir; her ikisi de güncel ambalaj üzerinden okunmalıdır.', 'İçindekiler listesini, vurgulanan alerjenleri ve üreticinin çapraz bulaşma uyarısını birlikte kontrol edin.', 'Vegan veya benzeri pazarlama ifadesini alerjen güvenliği garantisi kabul etmeyin; ciddi alerjide profesyonel yönlendirme alın.', 'paket üzerindeki içerik ve alerjen satırını inceleyen eller'),
            'besin-degerlerinin-tahmini-olmasi' => $make('Tarif besin hesabı kullanılan veri tabanı, marka, pişme kaybı, ev ölçüsü ve porsiyon tanımından etkilenir.', 'Değeri tek bir kesin sayı yerine yöntem, kaynak tarihi ve güven düzeyiyle birlikte okuyun.', 'Tahmini sonucu tanı, tedavi veya kişisel diyet reçetesi gibi kullanmayın.', 'besin verisi tablosu, tartı ve ölçü kapları'),
            'kibris-mezeleriyle-pratik-sofra' => $make('İyi bir meze sofrası çok sayıda tabaktan önce sıcaklık, doku ve servis sırası dengesi kurar.', 'Serin bir yoğurtlu tabak, bakliyat veya sebze temelli bir ezme ve sıcak bir eşlikçi seçin.', 'Kıbrıs mutfağını tek ve değişmez bir sunum kalıbına indirgemeyin.', 'Kıbrıs esintili üç küçük meze tabağı ve ortak sofra'),
            'yaz-sicaklarinda-hafif-akdeniz-tarifleri' => $make('Yaz menüsü taze sebze, bakliyat ve kolay servis edilen tabaklarla sadeleşebilir.', 'Hazırlığı serin saatlere alın, sosları ayrı tutun ve soğuk zincir gerektiren ürünleri servis anına kadar soğukta saklayın.', '“Hafif” ifadesini kişisel sağlık veya kilo sonucu garantisi olarak kullanmayın.', 'yaz ışığında sebze, bakliyat ve limonlu Akdeniz sofrası'),
            'cooking-mode-ile-adim-adim-ilerleme' => $make('Cooking Mode, tarif adımlarını ve zamanlayıcıları görünür kılar; mutfak gözleminin yerine geçmez.', 'Başlamadan mise en place yapın, her zamanlayıcıyı adlandırın ve tamamlanan adımı yalnız gerçekten bittiğinde işaretleyin.', 'Ekrandaki süreyi renk, kıvam, sıcaklık ve güvenlik kontrolünün yerine koymayın.', 'tablet üzerinde Cooking Mode ve hazırlık kapları'),
        ];
    }

    /** @return array{sections:list<array{title:string,paragraphs:list<string>}>,checklist:string,conclusion:string,visual:string} */
    private function fallbackPlan(BlogPost $post): array
    {
        return [
            'sections' => [['title' => 'Konu özeti', 'paragraphs' => [$post->excerpt]], ['title' => 'Uygulama', 'paragraphs' => ['Adımları mutfak koşullarınıza göre küçük ve doğrulanabilir parçalara ayırın.']], ['title' => 'Dikkat noktaları', 'paragraphs' => ['Belirsiz veya güvenlik etkisi olan bilgiyi kesin iddia olarak kullanmayın.']]],
            'checklist' => 'Başlığı, malzemeleri, ekipmanı, süreyi ve güvenlik notlarını kontrol edin.',
            'conclusion' => 'İlgili tarifleri okuyun ve sonucu kendi mutfak koşullarınıza göre değerlendirin.',
            'visual' => $post->title,
        ];
    }
}
