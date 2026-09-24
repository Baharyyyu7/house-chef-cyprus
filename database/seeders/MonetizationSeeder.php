<?php

namespace Database\Seeders;

use App\Models\AffiliateLink;
use App\Models\DigitalProduct;
use Illuminate\Database\Seeder;

class MonetizationSeeder extends Seeder
{
    public function run(): void
    {
        // Taslak kayıtlar bilerek pasiftir. Doğrulanmış özel dosya, lisans ve
        // ticari onay olmadan satışa ya da affiliate yönlendirmesine açılamaz.
        DigitalProduct::firstOrCreate([
            'slug' => 'kibris-mutfagi-master-rehberi-e-kitap',
        ], [
            'title' => 'Geleneksel Kıbrıs Mutfağı Master Rehberi (PDF E-Kitap)',
            'description' => '50+ otantik Kıbrıs tarifi, püf noktaları, kültürel hikayeleri ve özel lezzet sırlarıyla dolu 120 sayfalık kapsamlı dijital yemek kitabı.',
            'features' => json_encode([
                '120 Sayfa Yüksek Çözünürlüklü PDF',
                'Şeftali Kebabı, Molohiya ve Hellimli Lezzet Sırları',
                'Print-ready (Yazdırılabilir Formatta)',
                'Tüm Cihazlarla Uyumlu',
            ]),
            'price' => 14.99,
            'currency' => 'EUR',
            'cover_image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=600&auto=format&fit=crop',
            'page_count' => 120,
            'sales_count' => 0,
            'is_active' => false,
        ]);

        DigitalProduct::firstOrCreate([
            'slug' => '30-gunluk-fit-akdeniz-beslenme-menusu',
        ], [
            'title' => '30 Günlük Pratik Akdeniz Diyet & Menü Planı',
            'description' => 'Her gün ne pişirsem derdine son! Günlük kalorileri, alışveriş listeleri ve pratik hazırlanış süreçleriyle Akdeniz stili beslenme rehberi.',
            'features' => json_encode([
                '30 Günlük Komple Yemek & Atıştırmalık Listesi',
                'Haftalık Hazır Alışveriş Listeleri',
                'Kalori & Makro Dengeli Tarifler',
            ]),
            'price' => 9.99,
            'currency' => 'EUR',
            'cover_image' => 'https://images.unsplash.com/photo-1498837167922-ddd27525d352?w=600&auto=format&fit=crop',
            'page_count' => 65,
            'sales_count' => 0,
            'is_active' => false,
        ]);

        AffiliateLink::firstOrCreate([
            'slug' => 'dokum-dökme-demir-tencere-26cm',
        ], [
            'title' => 'Lava Döküm Yuvarlak Tencere 26 cm',
            'category' => 'equipment',
            'description' => 'Kuzu Tandır ve yavaş pişen Güveç yemekleri için mükemmel ısı dağılımı sağlayan döküm tencere.',
            'target_url' => 'https://www.amazon.com.tr/dp/B00K8K7X6?tag=housechefcyprus-21',
            'provider' => 'amazon',
            'price' => 1890.00,
            'currency' => 'TRY',
            'image_url' => 'https://images.unsplash.com/photo-1584992236310-6edddc08acff?w=400&auto=format&fit=crop',
            'is_active' => false,
        ]);

        AffiliateLink::firstOrCreate([
            'slug' => 'profesyonel-sef-bicagi-20cm',
        ], [
            'title' => 'Pirge Ecco Şef Bıçağı 20 cm',
            'category' => 'equipment',
            'description' => 'Sebze ve et doğramada yüksek hassasiyet sunan paslanmaz çelik şef bıçağı.',
            'target_url' => 'https://www.trendyol.com/pirge/ecco-sef-bicagi-p-1234567?boutiqueId=61&merchantId=1050',
            'provider' => 'trendyol',
            'price' => 450.00,
            'currency' => 'TRY',
            'image_url' => 'https://images.unsplash.com/photo-1593618998160-e34014e67546?w=400&auto=format&fit=crop',
            'is_active' => false,
        ]);

        $this->command->info('Monetization taslakları pasif olarak eklendi; yayın için ayrı ticari doğrulama gerekir.');
    }
}
