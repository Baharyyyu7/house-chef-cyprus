<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\ContentOpportunity;
use App\Models\Recipe;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class BacklinkGrowthService
{
    /** @var list<array{domain: string, topic: string, target_url: string, asset_type: string, asset_keyword: string, rationale: string, authority_score: int}> */
    private const TARGET_PROSPECTS = [
        [
            'domain' => 'emu.edu.tr',
            'topic' => 'Doğu Akdeniz Üniversitesi Gastronomi & Mutfak Sanatları',
            'target_url' => 'https://gastronomy.emu.edu.tr/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-molohiya',
            'rationale' => 'Akademik gastronomi araştırmaları ve Kıbrıs mutfak mirası dersleri için gramajlı, adımlı birincil kaynak.',
            'authority_score' => 88,
        ],
        [
            'domain' => 'neu.edu.tr',
            'topic' => 'Yakın Doğu Üniversitesi Turizm ve Gastronomi Fakültesi',
            'target_url' => 'https://neu.edu.tr/faculties/tourism/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-sofralarinda-temel-malzemeler',
            'rationale' => 'Kıbrıs biyoçeşitliliği, yabani otlar ve ada peynirlerinin gastronomi eğitiminde akademik referans gösterilmesi.',
            'authority_score' => 86,
        ],
        [
            'domain' => 'ciu.edu.tr',
            'topic' => 'Uluslararası Kıbrıs Üniversitesi Gastronomi Bölümü',
            'target_url' => 'https://www.ciu.edu.tr/tr/akademik/fakulteler/uygulamali-bilimler-fakultesi/gastronomi-ve-mutfak-sanatlari',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-kolokas',
            'rationale' => 'Geleneksel Kolokas pişirme teknikleri ve kök sebze mirası için mutfak laboratuvarlarına referans kaynak.',
            'authority_score' => 84,
        ],
        [
            'domain' => 'final.edu.tr',
            'topic' => 'Uluslararası Final Üniversitesi Gastronomi ve Mutfak Sanatları',
            'target_url' => 'https://www.final.edu.tr/tr/programlar/gastronomi-ve-mutfak-sanatlari',
            'asset_type' => 'recipe',
            'asset_keyword' => 'hellimli-kibris-boregi',
            'rationale' => 'Geleneksel ada hamur işleri ve hellimli börek katlama teknikleri üzerine uygulamalı eğitim referansı.',
            'authority_score' => 77,
        ],
        [
            'domain' => 'gau.edu.tr',
            'topic' => 'Girne Amerikan Üniversitesi Gastronomi ve Mutfak Sanatları',
            'target_url' => 'https://www.gau.edu.tr/gastronomi',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-pilavuna',
            'rationale' => 'Akdeniz ada kültürü ve geleneksel bayram çöreklerinin akademik arşivlenmesi ve reçete standardizasyonu.',
            'authority_score' => 82,
        ],
        [
            'domain' => 'ncc.metu.edu.tr',
            'topic' => 'ODTÜ Kuzey Kıbrıs Kampüsü Kültür ve Yaşam Topluluğu',
            'target_url' => 'https://ncc.metu.edu.tr/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mutfagina-baslangic-rehberi',
            'rationale' => 'Adada eğitim gören binlerce uluslararası ve Türkiyeli öğrenci için yerel mutfağa adaptasyon rehberi.',
            'authority_score' => 89,
        ],
        [
            'domain' => 'eul.edu.tr',
            'topic' => 'Lefke Avrupa Üniversitesi Gastronomi Bölümü',
            'target_url' => 'https://www.eul.edu.tr/tr/akademik/gastronomi-ve-mutfak-sanatlari/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-kolokas',
            'rationale' => 'Lefke ve Soli bölgesine özgü tarım ürünleri ve kolokas pişirme yöntemleri akademik kaynak çalışması.',
            'authority_score' => 79,
        ],
        [
            'domain' => 'baucyprus.edu.tr',
            'topic' => 'Bahçeşehir Kıbrıs Üniversitesi Turizm ve Otelcilik',
            'target_url' => 'https://baucyprus.edu.tr/',
            'asset_type' => 'blog',
            'asset_keyword' => 'private-chef-cyprus',
            'rationale' => 'Ada genelinde lüks turizm ve özel şeflik standartları üzerine sektörel analiz referansı.',
            'authority_score' => 76,
        ],
        [
            'domain' => 'kiu.edu.tr',
            'topic' => 'Kıbrıs İlim Üniversitesi Turizm Fakültesi',
            'target_url' => 'https://kiu.edu.tr/tr/fakulteler/turizm-fakultesi/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-seftali-kebabi',
            'rationale' => 'Kıbrıs gastronomi turizmi ve sokak lezzetleri mirası eğitim modülleri için reçete referansı.',
            'authority_score' => 73,
        ],
        [
            'domain' => 'adakent.edu.tr',
            'topic' => 'Kıbrıs Ada Kent Üniversitesi Mutfak Kültürü',
            'target_url' => 'https://adakent.edu.tr/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-pirohu',
            'rationale' => 'Gazimağusa bölgesinde ev yapımı taze makarna ve ada peyniri işleme teknikleri kaynağı.',
            'authority_score' => 71,
        ],
        [
            'domain' => 'bogazici.edu.tr',
            'topic' => 'Boğaziçi Üniversitesi Turizm İşletmeciliği Gastronomi Araştırmaları',
            'target_url' => 'https://boun.edu.tr/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mutfagina-baslangic-rehberi',
            'rationale' => 'Doğu Akdeniz gastronomi rotaları ve ada kimliği üzerine karşılaştırmalı akademik kaynak.',
            'authority_score' => 93,
        ],
        [
            'domain' => 'ege.edu.tr',
            'topic' => 'Ege Üniversitesi Gıda Mühendisliği & Gastronomi',
            'target_url' => 'https://ege.edu.tr/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-sofralarinda-temel-malzemeler',
            'rationale' => 'Hellim peynirinin fermantasyon süreci ve geleneksel peynir altı suyu (nor) üretimi üzerine gıda analizi.',
            'authority_score' => 91,
        ],
        [
            'domain' => 'gazi.edu.tr',
            'topic' => 'Gazi Üniversitesi Gastronomi ve Mutfak Sanatları',
            'target_url' => 'https://gazi.edu.tr/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-usulu-kleftiko',
            'rationale' => 'Geleneksel Türk ve Osmanlı tandır tekniklerinin Kıbrıs ada varyasyonu ile karşılaştırılması.',
            'authority_score' => 90,
        ],
        [
            'domain' => 'istanbul.edu.tr',
            'topic' => 'İstanbul Üniversitesi Akdeniz Dünyası Mutfak Kültürü',
            'target_url' => 'https://istanbul.edu.tr/',
            'asset_type' => 'blog',
            'asset_keyword' => 'geleneksel-kibris-kebabi',
            'rationale' => 'Tarihsel Akdeniz ticaret yolları ve Kıbrıs et pişirme tekniklerinin evrimi üzerine referans.',
            'authority_score' => 92,
        ],
        [
            'domain' => 'akdeniz.edu.tr',
            'topic' => 'Akdeniz Üniversitesi Turizm Fakültesi Gastronomi Bölümü',
            'target_url' => 'https://akdeniz.edu.tr/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'zeytinyagli-taze-fasulye',
            'rationale' => 'Akdeniz havzası zeytinyağlı yemek standartları ve ada zeytin kültürü akademik eşleşmesi.',
            'authority_score' => 87,
        ],
        [
            'domain' => 'hacettepe.edu.tr',
            'topic' => 'Hacettepe Üniversitesi Beslenme ve Diyetetik Bölümü',
            'target_url' => 'https://hacettepe.edu.tr/',
            'asset_type' => 'blog',
            'asset_keyword' => 'ev-olculeri-rehberi',
            'rationale' => 'Mutfak ölçü birimleri, porsiyon kontrolü ve makro besin dönüşümleri üzerine güvenilir kaynak.',
            'authority_score' => 92,
        ],
        [
            'domain' => 'cukurova.edu.tr',
            'topic' => 'Çukurova Üniversitesi Mutfak Sanatları ve Gastronomi',
            'target_url' => 'https://cu.edu.tr/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-seftali-kebabi',
            'rationale' => 'Toroslar ve Kıbrıs arası göç lezzetleri ve et baharatlama dengesi üzerine kaynak.',
            'authority_score' => 85,
        ],
        [
            'domain' => 'deu.edu.tr',
            'topic' => 'Dokuz Eylül Üniversitesi Gastronomi ve Mutfak Sanatları',
            'target_url' => 'https://deu.edu.tr/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mezeleriyle-pratik',
            'rationale' => 'Ege ve Kıbrıs meze kültürünün ortak otları ve soğuk başlangıç reçeteleri referansı.',
            'authority_score' => 88,
        ],
        [
            'domain' => 'anadolu.edu.tr',
            'topic' => 'Anadolu Üniversitesi Açıköğretim Aşçılık Programı',
            'target_url' => 'https://anadolu.edu.tr/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-usulu-kleftiko',
            'rationale' => 'Uzaktan eğitim alan aşçılık öğrencileri için gramajlı ve adımlı bölgesel fırıncılık kaynağı.',
            'authority_score' => 89,
        ],
        [
            'domain' => 'yasar.edu.tr',
            'topic' => 'Yaşar Üniversitesi Gastronomi ve Mutfak Sanatları',
            'target_url' => 'https://yasar.edu.tr/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-pilavuna',
            'rationale' => 'Geleneksel mayalı hamur işleri ve yerel baharat kullanımı eğitim modülü referansı.',
            'authority_score' => 81,
        ],
        [
            'domain' => 'yeditepe.edu.tr',
            'topic' => 'Yeditepe Üniversitesi Gastronomi ve Mutfak Sanatları',
            'target_url' => 'https://yeditepe.edu.tr/',
            'asset_type' => 'blog',
            'asset_keyword' => 'private-chef-cyprus',
            'rationale' => 'Uluslararası gastronomi trendleri ve profesyonel özel şeflik hizmetleri sektörel kaynak.',
            'authority_score' => 86,
        ],
        [
            'domain' => 'ozyegin.edu.tr',
            'topic' => 'Özyeğin Üniversitesi Gastronomi ve Mutfak Sanatları (Le Cordon Bleu)',
            'target_url' => 'https://ozyegin.edu.tr/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-molohiya',
            'rationale' => 'Fransız pişirme disiplinlerinin ada mutfağı teknikleriyle karşılaştırılmalı mutfak atölyesi referansı.',
            'authority_score' => 89,
        ],
        [
            'domain' => 'bilgi.edu.tr',
            'topic' => 'İstanbul Bilgi Üniversitesi Gastronomi ve Mutfak Sanatları',
            'target_url' => 'https://bilgi.edu.tr/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-sofralarinda-temel-malzemeler',
            'rationale' => 'Sürdürülebilir yerel gıda üretimi ve ada biyoçeşitliliği ders içeriği referansı.',
            'authority_score' => 87,
        ],
        [
            'domain' => 'aydin.edu.tr',
            'topic' => 'İstanbul Aydın Üniversitesi Gastronomi ve Mutfak Sanatları',
            'target_url' => 'https://aydin.edu.tr/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-seftali-kebabi',
            'rationale' => 'Geleneksel kasaplık ve sakatat işleme teknikleri üzerine pratik ders referansı.',
            'authority_score' => 80,
        ],
        [
            'domain' => 'baskent.edu.tr',
            'topic' => 'Başkent Üniversitesi Gastronomi ve Mutfak Sanatları',
            'target_url' => 'https://baskent.edu.tr/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-pirohu',
            'rationale' => 'Anadolu ve Kıbrıs hamur işi benzerlikleri ve el açması makarna mirası araştırması.',
            'authority_score' => 84,
        ],
        [
            'domain' => 'visitncy.com',
            'topic' => 'Kıbrıs Gastronomisi ve Geleneksel Mutfak',
            'target_url' => 'https://www.visitncy.com/discover-category/gastronomy/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-seftali-kebabi',
            'rationale' => 'Resmi turizm portalının gastronomi sayfasında Şeftali Kebabı ve Fırın Kebabı için gram ölçülü, doğrulanmış tarif kaynağı sunar.',
            'authority_score' => 85,
        ],
        [
            'domain' => 'visitcyprus.com',
            'topic' => 'Geleneksel Kıbrıs Hellimi ve Ada Ürünleri',
            'target_url' => 'https://www.visitcyprus.com/discover-cyprus/food-drink/local-produce/halloumi-cheese/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-sofralarinda-temel-malzemeler',
            'rationale' => 'Geleneksel hellim üretimi ve adanın temel malzemelerini anlatan editoryal rehberimiz için mükemmel birincil kaynak eşleşmesi.',
            'authority_score' => 90,
        ],
        [
            'domain' => 'lefkosabelediyesi.org',
            'topic' => 'Lefkoşa Türk Belediyesi Kültür ve Gastronomi Rehberi',
            'target_url' => 'https://lefkosabelediyesi.org/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mutfagina-baslangic-rehberi',
            'rationale' => 'Başkent Lefkoşa\'nın tarihi surlar içi lezzet durakları ve geleneksel ev yemekleri kültürü kaynağı.',
            'authority_score' => 78,
        ],
        [
            'domain' => 'girnebelediyesi.com',
            'topic' => 'Girne Belediyesi Zeytin Festivali & Yerel Lezzetler',
            'target_url' => 'https://girnebelediyesi.com/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'zeytinyagli-taze-fasulye',
            'rationale' => 'Zeytinlik köyü festivali ve Girne yöresi soğuk sıkım zeytinyağı yemekleri için referans.',
            'authority_score' => 76,
        ],
        [
            'domain' => 'magusa.org',
            'topic' => 'Gazimağusa Belediyesi Tarih ve Gastronomi Mirası',
            'target_url' => 'https://magusa.org/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-seftali-kebabi',
            'rationale' => 'Mağusa surlar içi geleneksel kebapçıları ve otantik sokak gastronomi kaynağı.',
            'authority_score' => 75,
        ],
        [
            'domain' => 'iskelebelediyesi.com',
            'topic' => 'İskele Belediyesi Karpaz Yöresi Mutfak Kültürü',
            'target_url' => 'https://iskelebelediyesi.com/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mezeleriyle-pratik',
            'rationale' => 'Karpaz kapısı İskele bölgesinde ada mezeleri ve taze balık sofraları rehberi.',
            'authority_score' => 72,
        ],
        [
            'domain' => 'guzelyurtbelediyesi.com',
            'topic' => 'Güzelyurt Belediyesi Portakal & Narenciye Gastronomisi',
            'target_url' => 'https://guzelyurtbelediyesi.com/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-sofralarinda-temel-malzemeler',
            'rationale' => 'Güzelyurt portakal vadisi, turunç macunu ve narenciye tatlıları üzerine yerel otorite.',
            'authority_score' => 71,
        ],
        [
            'domain' => 'lefkebelediyesi.com',
            'topic' => 'Lefke Belediyesi Kolokas & Ceviz Festivali',
            'target_url' => 'https://lefkebelediyesi.com/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-kolokas',
            'rationale' => 'Lefke Kolokas Festivali ve ceviz macunu mirası için geleneksel mutfak rehberi.',
            'authority_score' => 72,
        ],
        [
            'domain' => 'kibrisrehberi.com',
            'topic' => 'Kuzey Kıbrıs Gezi & Yaşam Mutfak Kültürü',
            'target_url' => 'https://www.kibrisrehberi.com/kategori/yeme-icme',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-usulu-kleftiko',
            'rationale' => 'Adaya gelen yerli ve yabancı ziyaretçiler için otantik köy fırını kuzu tandır pişirme adımları rehberi.',
            'authority_score' => 72,
        ],
        [
            'domain' => 'kibris.com.tr',
            'topic' => 'Kuzey Kıbrıs Bilgi ve Kültür Portalı',
            'target_url' => 'https://kibris.com.tr/kategori/kultur-sanat/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mutfagina-baslangic-rehberi',
            'rationale' => 'Kıbrıs ada yaşamı ve geleneksel ev yemekleri üzerine rehber kaynak.',
            'authority_score' => 74,
        ],
        [
            'domain' => 'welcometocyprus.com',
            'topic' => 'Welcome to Cyprus International Travel Directory',
            'target_url' => 'https://welcometocyprus.com/food-and-drink/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-seftali-kebabi',
            'rationale' => 'Uluslararası turistler için Kıbrıs sokak lezzetleri ve Şeftali kebabı rehberi.',
            'authority_score' => 75,
        ],
        [
            'domain' => 'northcyprus.co.uk',
            'topic' => 'North Cyprus Tourism & Living UK Travel Portal',
            'target_url' => 'https://www.northcyprus.co.uk/food-drink/',
            'asset_type' => 'blog',
            'asset_keyword' => 'private-chef-cyprus',
            'rationale' => 'İngiltere\'den adaya gelen tatilciler ve villa kiralayanlar için özel şef ve yerel yemek rehberi.',
            'authority_score' => 77,
        ],
        [
            'domain' => 'cyprusisland.net',
            'topic' => 'Cyprus Island Regional Heritage & Food Guide',
            'target_url' => 'https://cyprusisland.net/gastronomy',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-usulu-kleftiko',
            'rationale' => 'Ada genelinde taş fırın kuzu tandır pişirme geleneği için İngilizce kaynak referansı.',
            'authority_score' => 73,
        ],
        [
            'domain' => 'cyprusliving.org',
            'topic' => 'Cyprus Living & Mediterranean Home Life',
            'target_url' => 'https://cyprusliving.org/recipes/',
            'asset_type' => 'blog',
            'asset_keyword' => 'ev-olculeri-rehberi',
            'rationale' => 'Adada yaşayan yabancıların yerel ölçülerle yemek pişirmesi için pratik dönüşüm cetveli.',
            'authority_score' => 70,
        ],
        [
            'domain' => 'chooseyourcyprus.com',
            'topic' => 'Choose Your Cyprus Gastronomy Experience',
            'target_url' => 'https://chooseyourcyprus.com/activities/gastronomy/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-pilavuna',
            'rationale' => 'Kıbrıs geleneksel köy fırıncılığı ve festivallere özgü çörekler rehberi.',
            'authority_score' => 74,
        ],
        [
            'domain' => 'heartcyprus.com',
            'topic' => 'Heart Cyprus Authentic Culture & Food Heritage',
            'target_url' => 'https://heartcyprus.com/culture/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-sofralarinda-temel-malzemeler',
            'rationale' => 'Adanın otantik lezzetleri, keçiboynuzu ve yabani ada otları üzerine tanıtım kaynağı.',
            'authority_score' => 76,
        ],
        [
            'domain' => 'mycyprustravel.com',
            'topic' => 'My Cyprus Travel Culinary Exploration',
            'target_url' => 'https://mycyprustravel.com/taste/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-pirohu',
            'rationale' => 'Kıbrıs mutfağının gizli kalmış el yapımı makarna lezzetleri için tarif kaynağı.',
            'authority_score' => 73,
        ],
        [
            'domain' => 'discovernortherncyprus.com',
            'topic' => 'Discover Northern Cyprus Heritage & Tastes',
            'target_url' => 'https://discovernortherncyprus.com/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mutfagina-baslangic-rehberi',
            'rationale' => 'Kuzey Kıbrıs\'ı ilk kez ziyaret eden gezginler için gastronomi giriş kılavuzu.',
            'authority_score' => 72,
        ],
        [
            'domain' => 'bellapais.com',
            'topic' => 'Bellapais Village Heritage & Gastronomy',
            'target_url' => 'https://bellapais.com/dining/',
            'asset_type' => 'blog',
            'asset_keyword' => 'private-chef-cyprus',
            'rationale' => 'Bellapais manastırı çevresindeki gurme restoranlar ve özel villa akşam yemekleri rehberi.',
            'authority_score' => 71,
        ],
        [
            'domain' => 'karpaz.org',
            'topic' => 'Karpaz Eko-Turizm ve Geleneksel Üretim',
            'target_url' => 'https://karpaz.org/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-molohiya',
            'rationale' => 'Doğal tarım, kurutulmuş otlar ve geleneksel ada güveçleri için ekolojik mutfak kaynağı.',
            'authority_score' => 69,
        ],
        [
            'domain' => 'ktto.net',
            'topic' => 'Kıbrıs Türk Ticaret Odası Yerel Üretici Ağı',
            'target_url' => 'https://www.ktto.net/iletisim/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mutfagina-baslangic-rehberi',
            'rationale' => 'Yerel keçiboynuzu, hellim ve zeytinyağı üreticilerini kaynak gösteren editoryal Kıbrıs mutfağı başlangıç rehberimiz.',
            'authority_score' => 75,
        ],
        [
            'domain' => 'ktso.net',
            'topic' => 'Kıbrıs Türk Sanayi Odası Hellim Tescil & Coğrafi İşaret Komitesi',
            'target_url' => 'https://www.ktso.net/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-sofralarinda-temel-malzemeler',
            'rationale' => 'Kıbrıs Hellimi PDO tescili, süt oranları ve geleneksel üretim standartları üzerine otoriter kaynak.',
            'authority_score' => 83,
        ],
        [
            'domain' => 'ktoz.org',
            'topic' => 'Kıbrıs Türk Otelciler Birliği Gastronomi & Ada Mirası',
            'target_url' => 'https://www.ktoz.org/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mutfagina-baslangic-rehberi',
            'rationale' => 'Kıbrıs otellerinde sunulan yerel açık büfe ve ada lezzetleri için doğrulanmış gastronomi standart referansı.',
            'authority_score' => 78,
        ],
        [
            'domain' => 'resbir.org',
            'topic' => 'Kıbrıs Türk Restorancılar Birliği (RES-BİR)',
            'target_url' => 'https://resbir.org/',
            'asset_type' => 'blog',
            'asset_keyword' => 'geleneksel-kibris-kebabi',
            'rationale' => 'Ada restoranlarında hijyen, geleneksel reçete sadakati ve şef eğitimleri için sektörel referans.',
            'authority_score' => 77,
        ],
        [
            'domain' => 'ktceb.org',
            'topic' => 'Kıbrıs Türk Çiftçiler Birliği Yerel Bakliyat & Tahıl',
            'target_url' => 'https://ktceb.org/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-kolokas',
            'rationale' => 'Kıbrıs kuru bakliyatı, Mesarya buğdayı ve kolokas üretimi üzerine tarımsal kaynak.',
            'authority_score' => 73,
        ],
        [
            'domain' => 'kibriszeytin.org',
            'topic' => 'Kıbrıs Zeytin ve Zeytinyağı Üreticileri Birliği',
            'target_url' => 'https://kibriszeytin.org/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'zeytinyagli-taze-fasulye',
            'rationale' => 'Geleneksel değirmen sıkımı ada zeytinyağının yemeklerde doğru ısıl kullanımı rehberi.',
            'authority_score' => 74,
        ],
        [
            'domain' => 'hellim.org',
            'topic' => 'Kıbrıs Geleneksel Hellim Üreticileri Platformu',
            'target_url' => 'https://hellim.org/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-sofralarinda-temel-malzemeler',
            'rationale' => 'Koyun ve keçi sütünden katkısız hellim üretimi ve kuru naneleme gelenekleri.',
            'authority_score' => 79,
        ],
        [
            'domain' => 'kibrisnarenciye.com',
            'topic' => 'Kıbrıs Narenciye Üreticileri Birliği',
            'target_url' => 'https://kibrisnarenciye.com/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-sofralarinda-temel-malzemeler',
            'rationale' => 'Adanın ünlü mandalina, portakal ve turunçlarının tatlı ve soslarda kullanımı.',
            'authority_score' => 72,
        ],
        [
            'domain' => 'tobb.org.tr',
            'topic' => 'TOBB Türkiye Odalar ve Borsalar Birliği Gastronomi Meclisi',
            'target_url' => 'https://tobb.org.tr/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mutfagina-baslangic-rehberi',
            'rationale' => 'Türkiye ve KKTC arası coğrafi işaretli ürünlerin korunması ve mutfak ticareti köprüsü.',
            'authority_score' => 89,
        ],
        [
            'domain' => 'tursab.org.tr',
            'topic' => 'TÜRSAB Gastronomi Turizmi İhtisas Başkanlığı',
            'target_url' => 'https://tursab.org.tr/',
            'asset_type' => 'blog',
            'asset_keyword' => 'geleneksel-kibris-kebabi',
            'rationale' => 'Gastronomi turlarında Kıbrıs geleneksel kebap rotaları ve fırın lezzetleri rehberi.',
            'authority_score' => 88,
        ],
        [
            'domain' => 'tures.org.tr',
            'topic' => 'TÜRES Tüm Restoranlar ve Turizmciler Derneği',
            'target_url' => 'https://tures.org.tr/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mezeleriyle-pratik',
            'rationale' => 'Akdeniz mutfak standartları ve restorancılıkta geleneksel meze sunum kuralları.',
            'authority_score' => 83,
        ],
        [
            'domain' => 'tureskibris.org',
            'topic' => 'TÜRES Kıbrıs Temsilciliği Mutfak Standartları',
            'target_url' => 'https://tureskibris.org/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-seftali-kebabi',
            'rationale' => 'Otantik şeftali kebabı standardı ve porsiyon gramaj dengesi referansı.',
            'authority_score' => 75,
        ],
        [
            'domain' => 'cyprusbarassociation.org',
            'topic' => 'Kıbrıs Kültürel Miras Komisyonu Kaynakları',
            'target_url' => 'https://cyprusbarassociation.org/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mutfagina-baslangic-rehberi',
            'rationale' => 'Kıbrıs somut olmayan kültürel mirasının gastronomi ayağı üzerine referans.',
            'authority_score' => 78,
        ],
        [
            'domain' => 'cyprushospitality.org',
            'topic' => 'Cyprus Hospitality & Culinary Association',
            'target_url' => 'https://cyprushospitality.org/',
            'asset_type' => 'blog',
            'asset_keyword' => 'private-chef-cyprus',
            'rationale' => 'Profesyonel ada ağırlama ve şeflik standartları rehberi.',
            'authority_score' => 76,
        ],
        [
            'domain' => 'slowfoodcyprus.org',
            'topic' => 'Slow Food Kıbrıs & Nuh’un Ambarı Miras Lezzetleri',
            'target_url' => 'https://www.slowfood.com/our-network/cyprus/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-kolokas',
            'rationale' => 'Adanın kadim kök sebzesi Kolokas ve yerel bakliyat mirası için geleneksel pişirme yöntemlerini belgeler.',
            'authority_score' => 80,
        ],
        [
            'domain' => 'tasteatlas.com',
            'topic' => 'TasteAtlas Authentic Cypriot Cuisine Database',
            'target_url' => 'https://www.tasteatlas.com/cyprus',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-usulu-kleftiko',
            'rationale' => 'Kleftiko (Kuzu Fırın Tandıra) ait gerçek ada pişirme teknikleri, saatlik ısı grafiği ve besin değerleri verisi sağlar.',
            'authority_score' => 88,
        ],
        [
            'domain' => 'culinarybackstreets.com',
            'topic' => 'Culinary Backstreets Mediterranean Exploration',
            'target_url' => 'https://culinarybackstreets.com/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-seftali-kebabi',
            'rationale' => 'Doğu Akdeniz arka sokak lezzetleri ve geleneksel kömür ızgarası mirası incelemesi.',
            'authority_score' => 85,
        ],
        [
            'domain' => 'seriouseats.com',
            'topic' => 'Serious Eats Mediterranean Food Lab & Science',
            'target_url' => 'https://www.seriouseats.com/mediterranean-recipes-5117865',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-usulu-kleftiko',
            'rationale' => 'Düşük ısıda uzun fırınlama ve kollajen parçalanması üzerine gastronomi bilimi eşleşmesi.',
            'authority_score' => 91,
        ],
        [
            'domain' => 'food52.com',
            'topic' => 'Food52 Community & Authentic Heritage Recipes',
            'target_url' => 'https://food52.com/recipes/mediterranean',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-pirohu',
            'rationale' => 'El yapımı ada makarnaları ve taze nane peynir dolguları için ev aşçılarına referans.',
            'authority_score' => 89,
        ],
        [
            'domain' => 'epicurious.com',
            'topic' => 'Epicurious Mediterranean Classics & Grilling',
            'target_url' => 'https://www.epicurious.com/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-seftali-kebabi',
            'rationale' => 'Kuzu gömleğinde ızgara teknikleri ve nem koruma yöntemleri üzerine şef referansı.',
            'authority_score' => 90,
        ],
        [
            'domain' => 'themediterraneandish.com',
            'topic' => 'The Mediterranean Dish Island Cooking & Olive Oil',
            'target_url' => 'https://www.themediterraneandish.com/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mezeleriyle-pratik',
            'rationale' => 'Geleneksel Kıbrıs soğuk meze tabakları ve taze Akdeniz yeşillikleri kaynağı.',
            'authority_score' => 84,
        ],
        [
            'domain' => 'bbcgoodfood.com',
            'topic' => 'BBC Good Food Mediterranean & Cypriot Recipes',
            'target_url' => 'https://www.bbcgoodfood.com/recipes/collection/cypriot-recipes',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-pilavuna',
            'rationale' => 'Geleneksel Paskalya Flaouna (Pilavuna) peynirli çöreği için ölçülü adım adım kaynak.',
            'authority_score' => 93,
        ],
        [
            'domain' => 'greatbritishchefs.com',
            'topic' => 'Great British Chefs Eastern Mediterranean Hub',
            'target_url' => 'https://www.greatbritishchefs.com/collections/mediterranean-recipes',
            'asset_type' => 'blog',
            'asset_keyword' => 'private-chef-cyprus',
            'rationale' => 'Doğu Akdeniz fine dining şef teknikleri ve otantik ada reçeteleri referansı.',
            'authority_score' => 87,
        ],
        [
            'domain' => 'saveur.com',
            'topic' => 'Saveur Heritage Foods of Cyprus',
            'target_url' => 'https://www.saveur.com/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-molohiya',
            'rationale' => 'Kurutulmuş Molohiya yapraklarının güveçte kuzu etiyle pişirilmesi kültürel dökümantasyonu.',
            'authority_score' => 88,
        ],
        [
            'domain' => 'gastroobscura.com',
            'topic' => 'Atlas Obscura / Gastro Obscura Rare Foods - Cyprus',
            'target_url' => 'https://www.atlasobscura.com/gastro',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-kolokas',
            'rationale' => 'Kıbrıs\'ın kadim taro kökü sebzesi Kolokas ve kerevizli pişirme geleneği incelemesi.',
            'authority_score' => 89,
        ],
        [
            'domain' => 'slowfood.com',
            'topic' => 'Slow Food International Ark of Taste - Cyprus',
            'target_url' => 'https://www.slowfood.com/ark-of-taste/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-sofralarinda-temel-malzemeler',
            'rationale' => 'Tehlike altındaki ada biyoçeşitliliği, yabani enginar ve yerel peynirlerin korunması.',
            'authority_score' => 92,
        ],
        [
            'domain' => 'worldfoodtravel.org',
            'topic' => 'World Food Travel Association Culinary Heritage',
            'target_url' => 'https://worldfoodtravel.org/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mutfagina-baslangic-rehberi',
            'rationale' => 'Kıbrıs gastronomi turizmi rotaları ve yerel üretici keşif rehberi eşleşmesi.',
            'authority_score' => 83,
        ],
        [
            'domain' => '196flavors.com',
            'topic' => '196 Flavors World Culinary Heritage - Cyprus',
            'target_url' => 'https://www.196flavors.com/cyprus/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-pilavuna',
            'rationale' => 'Paskalya ve bayram çöreği Pilavuna (Flaouna) için asırlık peynir harcı ve mahlep oranları dökümantasyonu.',
            'authority_score' => 76,
        ],
        [
            'domain' => 'eatyourworld.com',
            'topic' => 'Eat Your World Regional Foods - Cyprus',
            'target_url' => 'https://eatyourworld.com/destinations/europe/cyprus',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-seftali-kebabi',
            'rationale' => 'Doğu Akdeniz sokak lezzetleri ve otantik mangal kültürü için Şeftali Kebabı kaynak eşleşmesi.',
            'authority_score' => 74,
        ],
        [
            'domain' => 'olivetomato.com',
            'topic' => 'Olive Tomato Mediterranean Diet & Traditional Foods',
            'target_url' => 'https://www.olivetomato.com/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'zeytinyagli-taze-fasulye',
            'rationale' => 'Akdeniz tipi soğuk sıkım zeytinyağlı sebze yemekleri ve ada pişirme teknikleri için besin odaklı referans.',
            'authority_score' => 73,
        ],
        [
            'domain' => 'mediterraneanliving.com',
            'topic' => 'Mediterranean Diet & Healthy Cypriot Home Cooking',
            'target_url' => 'https://www.mediterraneanliving.com/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mezeleriyle-pratik',
            'rationale' => 'Zeytinyağlı taze mezeler, otlar ve sebzeli Akdeniz tabakları için kanıtlanmış porsiyon ve kalori tabloları sunar.',
            'authority_score' => 70,
        ],
        [
            'domain' => 'deliciousmagazine.co.uk',
            'topic' => 'Delicious Magazine Mediterranean Island Travel',
            'target_url' => 'https://www.deliciousmagazine.co.uk/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-seftali-kebabi',
            'rationale' => 'İngiliz okurlar için evde mangalda Kıbrıs usulü kebap yapımı rehberi.',
            'authority_score' => 84,
        ],
        [
            'domain' => 'foodandwine.com',
            'topic' => 'Food & Wine Mediterranean Island Cooking & Cheeses',
            'target_url' => 'https://www.foodandwine.com/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-sofralarinda-temel-malzemeler',
            'rationale' => 'Kızartmalık Hellim peyniri özellikleri ve ada şaraplarıyla eşleştirme rehberi.',
            'authority_score' => 92,
        ],
        [
            'domain' => 'saveur-mediterranee.fr',
            'topic' => 'Saveur Mediterranee Culinary Archive',
            'target_url' => 'https://saveur-mediterranee.fr/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-usulu-kleftiko',
            'rationale' => 'Fransız ve Akdeniz yavaş pişirme teknikleri ile Kıbrıs tandırının karşılaştırması.',
            'authority_score' => 75,
        ],
        [
            'domain' => 'internationalculinarycenter.com',
            'topic' => 'International Culinary Heritage Foundation',
            'target_url' => 'https://internationalculinarycenter.com/',
            'asset_type' => 'blog',
            'asset_keyword' => 'private-chef-cyprus',
            'rationale' => 'Akdeniz ada şefleri eğitim standartları ve mutfak disiplini kaynağı.',
            'authority_score' => 82,
        ],
        [
            'domain' => 'cheftalk.com',
            'topic' => 'ChefTalk Professional Culinary Forum',
            'target_url' => 'https://cheftalk.com/',
            'asset_type' => 'blog',
            'asset_keyword' => 'ev-olculeri-rehberi',
            'rationale' => 'Amerikan cup/ounce ve Akdeniz su bardağı/gram dönüşümleri üzerine profesyonel mutfak tartışması.',
            'authority_score' => 78,
        ],
        [
            'domain' => 'foodrepublic.com',
            'topic' => 'Food Republic World Food Profiles',
            'target_url' => 'https://foodrepublic.com/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-seftali-kebabi',
            'rationale' => 'Dünya sokak yemekleri serisinde Şeftali Kebabı hazırlığı ve porsiyonlama rehberi.',
            'authority_score' => 81,
        ],
        [
            'domain' => 'thekitchn.com',
            'topic' => 'The Kitchn Mediterranean Kitchen Staples',
            'target_url' => 'https://www.thekitchn.com/',
            'asset_type' => 'blog',
            'asset_keyword' => 'ev-olculeri-rehberi',
            'rationale' => 'Evde Akdeniz yemekleri pişirirken malzeme oranlarını doğru tutturma rehberi.',
            'authority_score' => 89,
        ],
        [
            'domain' => 'eatingwell.com',
            'topic' => 'EatingWell Heart-Healthy Mediterranean Recipes',
            'target_url' => 'https://www.eatingwell.com/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'zeytinyagli-taze-fasulye',
            'rationale' => 'Düşük sodyumlu, yüksek lifli zeytinyağlı taze fasulye besin değerleri kaynağı.',
            'authority_score' => 90,
        ],
        [
            'domain' => 'allrecipes.com',
            'topic' => 'Allrecipes Mediterranean Kitchen Guide',
            'target_url' => 'https://www.allrecipes.com/recipes/17561/world-cuisine/european/greek/cypriot/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-usulu-kleftiko',
            'rationale' => 'Ev mutfaklarında döküm tencerede ağır ateşte Kleftiko hazırlama referansı.',
            'authority_score' => 93,
        ],
        [
            'domain' => 'cyprus-mail.com',
            'topic' => 'Cyprus Mail Living & Mediterranean Culinary Heritage',
            'target_url' => 'https://cyprus-mail.com/category/living/food-drink/',
            'asset_type' => 'blog',
            'asset_keyword' => 'private-chef-cyprus',
            'rationale' => 'Kıbrıs gastronomi kültürü ve özel şef deneyimleri rehberi için uluslararası expat ve turist kitlesine yönelik editoryal kaynak.',
            'authority_score' => 82,
        ],
        [
            'domain' => 'in-cyprus.philenews.com',
            'topic' => 'In-Cyprus Food & Culture Living Guide',
            'target_url' => 'https://in-cyprus.philenews.com/lifestyle/food-drink/',
            'asset_type' => 'blog',
            'asset_keyword' => 'geleneksel-kibris-kebabi',
            'rationale' => 'Ada genelinde kebap teknikleri ve geleneksel odun ateşi pişirme kültürü üzerine kapsamlı İngilizce kaynak.',
            'authority_score' => 81,
        ],
        [
            'domain' => 'cyprusscene.com',
            'topic' => 'Cyprus Scene North Cyprus Culture & Expat Living',
            'target_url' => 'https://cyprusscene.com/category/culture/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-pirohu',
            'rationale' => 'Kıbrıs köylerine özgü taze nor ve kuru nane dolgulu ev makarnası Pirohu için adım adım rehber.',
            'authority_score' => 71,
        ],
        [
            'domain' => 'cyprusbutterfly.com.cy',
            'topic' => 'Cyprus Butterfly Lifestyle, Travel & Culinary Notes',
            'target_url' => 'https://cyprusbutterfly.com.cy/travel',
            'asset_type' => 'blog',
            'asset_keyword' => 'ev-olculeri-rehberi',
            'rationale' => 'Akdeniz ve Kıbrıs tariflerini evde uygulayan yabancı okurlar için su bardağı ve gramaj dönüşüm cetveli.',
            'authority_score' => 69,
        ],
        [
            'domain' => 'financialmirror.com',
            'topic' => 'Cyprus Financial Mirror Hospitality & Tourism Sector',
            'target_url' => 'https://www.financialmirror.com/category/cyprus/business/tourism-hospitality/',
            'asset_type' => 'blog',
            'asset_keyword' => 'private-chef-cyprus',
            'rationale' => 'Kıbrıs otelcilik ve özel mutfak hizmetleri sektörü için profesyonel şef standartları referansı.',
            'authority_score' => 79,
        ],
        [
            'domain' => 'angloinfo.com',
            'topic' => 'AngloInfo Cyprus Expat Dining & Living Directory',
            'target_url' => 'https://www.angloinfo.com/cyprus/lifestyle/food-drink',
            'asset_type' => 'blog',
            'asset_keyword' => 'ev-olculeri-rehberi',
            'rationale' => 'Kıbrıs\'ta yaşayan İngiliz ve Avrupalı yerleşiklerin ada pazarlarından alışveriş rehberi.',
            'authority_score' => 76,
        ],
        [
            'domain' => 'expatarrivals.com',
            'topic' => 'Expat Arrivals Cyprus Food, Culture & Local Customs',
            'target_url' => 'https://www.expatarrivals.com/middle-east/cyprus/culture-shock-cyprus',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mutfagina-baslangic-rehberi',
            'rationale' => 'Adaya taşınan aileler için Kıbrıs yemek ritüelleri ve meze adabı kılavuzu.',
            'authority_score' => 75,
        ],
        [
            'domain' => 'lgcnews.com',
            'topic' => 'LGC News North Cyprus Expat Community News',
            'target_url' => 'https://lgcnews.com/lifestyle/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-seftali-kebabi',
            'rationale' => 'Kuzey Kıbrıs\'ta yaşayan expat topluluğu için geleneksel kasaplardan şeftali kebabı hazırlama adımları.',
            'authority_score' => 72,
        ],
        [
            'domain' => 'kibrispostasi.com',
            'topic' => 'Kıbrıs Postası Gastronomi ve Ada Yaşamı',
            'target_url' => 'https://kibrispostasi.com/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-sofralarinda-temel-malzemeler',
            'rationale' => 'Adanın yerel üreticileri, hellim fiyatları ve geleneksel mutfak köşe yazıları referansı.',
            'authority_score' => 82,
        ],
        [
            'domain' => 'yeniduzen.com',
            'topic' => 'Yenidüzen Gaile ve Kıbrıs Mutfak Kültürü',
            'target_url' => 'https://yeniduzen.com/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-molohiya',
            'rationale' => 'Kıbrıs kültürel belleği, sözlü tarih ve kadınların ev mutfaklarındaki molehiya geleneği.',
            'authority_score' => 81,
        ],
        [
            'domain' => 'havadiskibris.com',
            'topic' => 'Havadis Kıbrıs Mutfak Mirası ve Köy Lezzetleri',
            'target_url' => 'https://havadiskibris.com/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-usulu-kleftiko',
            'rationale' => 'Kıbrıs köylerindeki geleneksel kerpiç fırın tandır yapımı haber dosyası referansı.',
            'authority_score' => 78,
        ],
        [
            'domain' => 'kibrisgazetesi.com',
            'topic' => 'Kıbrıs Gazetesi Geleneksel Tatlar Köşesi',
            'target_url' => 'https://kibrisgazetesi.com/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-pirohu',
            'rationale' => 'Pirohu açma sırları ve taze ada norunun mutfakta kullanımı üzerine arşiv referansı.',
            'authority_score' => 80,
        ],
        [
            'domain' => 'detaykibris.com',
            'topic' => 'Detay Kıbrıs Yerel Üretim ve Kültür Haberleri',
            'target_url' => 'https://detaykibris.com/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mutfagina-baslangic-rehberi',
            'rationale' => 'Yerel üreticilerin desteklenmesi ve geleneksel ada yemeklerinin yaşatılması.',
            'authority_score' => 73,
        ],
        [
            'domain' => 'haberankibris.com',
            'topic' => 'Haber Kıbrıs Gurme Köşesi ve Restoran İncelemeleri',
            'target_url' => 'https://haberkibris.com/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-seftali-kebabi',
            'rationale' => 'Ada genelindeki kebap ocakları ve geleneksel lezzet noktaları referansı.',
            'authority_score' => 79,
        ],
        [
            'domain' => 'gundemkibris.com',
            'topic' => 'Gündem Kıbrıs Ada Lezzetleri ve Yaşam',
            'target_url' => 'https://gundemkibris.com/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mezeleriyle-pratik',
            'rationale' => 'Yaz aylarında ada sahillerinde tüketilen soğuk mezeler ve yoğurtlu tabaklar.',
            'authority_score' => 75,
        ],
        [
            'domain' => 'voicecyprus.net',
            'topic' => 'Voice of Cyprus International Culture Portal',
            'target_url' => 'https://voicecyprus.net/',
            'asset_type' => 'blog',
            'asset_keyword' => 'private-chef-cyprus',
            'rationale' => 'İngilizce yayın yapan adadaki uluslararası kültür ve gastronomi portalı.',
            'authority_score' => 70,
        ],
        [
            'domain' => 'cyprusdigest.com',
            'topic' => 'Cyprus Digest Expat & Dining Notes',
            'target_url' => 'https://cyprusdigest.com/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-pilavuna',
            'rationale' => 'Yabancı expat topluluğuna geleneksel Kıbrıs bayram çöreği hazırlama kılavuzu.',
            'authority_score' => 71,
        ],
        [
            'domain' => 'northcyprusfreepress.com',
            'topic' => 'North Cyprus Free Press Living & Kitchen',
            'target_url' => 'https://northcyprusfreepress.com/',
            'asset_type' => 'blog',
            'asset_keyword' => 'ev-olculeri-rehberi',
            'rationale' => 'Ada mutfaklarında uluslararası ölçülerin uyarlanması üzerine pratik ipuçları.',
            'authority_score' => 69,
        ],
        [
            'domain' => 'cyprustoday.online',
            'topic' => 'Cyprus Today English Weekly Food Section',
            'target_url' => 'https://cyprustoday.online/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-sofralarinda-temel-malzemeler',
            'rationale' => 'Kıbrıs\'ın haftalık en eski İngilizce gazetesinin gastronomi ve pazar eki kaynağı.',
            'authority_score' => 76,
        ],
        [
            'domain' => 'mediterraneanobserver.org',
            'topic' => 'Mediterranean Cultural Observer Culinary Heritage',
            'target_url' => 'https://mediterraneanobserver.org/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mutfagina-baslangic-rehberi',
            'rationale' => 'Akdeniz ada mutfaklarının ortak tarihi ve Kıbrıs\'ın mutfak antropolojisi üzerine araştırma.',
            'authority_score' => 77,
        ],
    ];

    /**
     * Identifies, enriches, and stores high-authority backlink candidates in content_opportunities.
     *
     * @return array{created: int, updated: int, prospects: list<array<string, mixed>>}
     */
        public function discoverAndEnrich(): array
    {
        $created = 0;
        $updated = 0;
        $results = [];

        foreach (self::TARGET_PROSPECTS as $prospect) {
            $key = Str::slug('backlink-' . $prospect['domain'] . '-' . $prospect['asset_keyword']);

            $opportunity = ContentOpportunity::query()->firstOrNew(['normalized_key' => $key]);

            if ($opportunity->exists && ($opportunity->status !== 'open' || $opportunity->reviewed_at !== null)) {
                continue;
            }

            $matchedAsset = $this->resolveMatchedAsset($prospect['asset_type'], $prospect['asset_keyword']);
            $assetUrl = $matchedAsset['url'];
            $assetTitle = $matchedAsset['title'];

            $pitchTr = sprintf(
                "Merhaba, %s sitenizde %s üzerine yayınlanan yazınızı ilgiyle inceledik. " .
                "House Chef Cyprus platformumuzda konuyla ilgili bağımsız mutfak ekibimizce doğrulanmış '%s' kaynağımız (%s) yer almaktadır. " .
                "Okuyucularınızın geleneksel gramaj, besin değeri ve ada pişirme tekniklerine ulaşması için kaynak olarak değerlendirebilirsiniz.",
                $prospect['domain'],
                $prospect['topic'],
                $assetTitle,
                $assetUrl
            );

            $pitchEn = sprintf(
                "Dear %s editorial team, we recently explored your culinary feature on %s. " .
                "At House Chef Cyprus, our culinary editors verified an in-depth reference: '%s' (%s), " .
                "complete with verified metric measurements, nutritional data, and cultural heritage notes. " .
                "We believe this serves as an authentic, high-value reference for your international readers.",
                $prospect['domain'],
                $prospect['topic'],
                $assetTitle,
                $assetUrl
            );

            $evidence = [
                'target_domain' => $prospect['domain'],
                'target_url' => $prospect['target_url'],
                'topic' => $prospect['topic'],
                'authority_score' => $prospect['authority_score'],
                'rationale' => $prospect['rationale'],
                'matched_asset_title' => $assetTitle,
                'matched_asset_url' => $assetUrl,
                'pitch_draft_tr' => $pitchTr,
                'pitch_draft_en' => $pitchEn,
                'outreach_status' => 'draft_pending_human_review',
                'delivery_policy' => 'draft_only',
                'publication_requires_human_review' => true,
                'collected_at' => now()->toIso8601String(),
            ];

            $wasCreated = ! $opportunity->exists;

            $opportunity->fill([
                'kind' => 'backlink_prospect',
                'title' => "Backlink Fırsatı: {$prospect['domain']} ({$prospect['topic']})",
                'search_intent' => 'commercial',
                'topic_cluster' => 'Dış Bağlantı & Dijital PR',
                'evidence' => $evidence,
                'risk_level' => 'low',
                'priority' => $prospect['authority_score'],
                'status' => 'open',
            ]);

            $opportunity->save();

            $wasCreated ? $created++ : $updated++;

            $results[] = [
                'domain' => $prospect['domain'],
                'asset_title' => $assetTitle,
                'asset_url' => $assetUrl,
                'priority' => $prospect['authority_score'],
            ];
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'prospects' => $results,
        ];
    }

    /**
     * Pings WebSub / RSS feed hubs to announce fresh content syndication.
     *
     * @return array{pinged: bool, hub_status: int, feeds: list<string>}
     */
    public function pingSyndicationHubs(): array
    {
        $feeds = [
            route('feed'),
            route('feed.en'),
        ];

        $hubs = [
            'https://pubsubhubbub.appspot.com/',
        ];

        $success = true;
        $lastStatus = 200;

        foreach ($hubs as $hub) {
            foreach ($feeds as $feed) {
                try {
                    $response = Http::timeout(5)
                        ->asForm()
                        ->post($hub, [
                            'hub.mode' => 'publish',
                            'hub.url' => $feed,
                        ]);

                    $lastStatus = $response->status();
                    if (! $response->successful() && $lastStatus !== 204 && $lastStatus !== 200) {
                        $success = false;
                    }
                } catch (\Throwable $e) {
                    Log::debug('WebSub hub ping skipped: ' . $e->getMessage());
                    $success = false;
                    $lastStatus = 0;
                }
            }
        }

        return [
            'pinged' => $success,
            'hub_status' => $lastStatus,
            'feeds' => $feeds,
        ];
    }

    /**
     * @return array{title: string, url: string}
     */
    private function resolveMatchedAsset(string $type, string $keyword): array
    {
        if ($type === 'recipe') {
            $recipe = Recipe::query()
                ->where('status', 'published')
                ->where('slug', 'like', "%{$keyword}%")
                ->first();

            if ($recipe) {
                return [
                    'title' => $recipe->title,
                    'url' => route('recipes.show', $recipe),
                ];
            }
        } else {
            $blog = BlogPost::query()
                ->publiclyVisible()
                ->where('slug', 'like', "%{$keyword}%")
                ->first();

            if ($blog) {
                return [
                    'title' => $blog->title,
                    'url' => route('blog.show', $blog),
                ];
            }
        }

        return [
            'title' => 'House Chef Cyprus - Doğrulanmış Kıbrıs Tarifleri',
            'url' => route('cuisines.show', 'kibris-mutfagi'),
        ];
    }
}
