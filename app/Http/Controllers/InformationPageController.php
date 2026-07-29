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
    ];

    public function show(string $page): View
    {
        abort_unless(isset(self::PAGES[$page]), 404);

        return view('information.show', ['page' => self::PAGES[$page]]);
    }
}
