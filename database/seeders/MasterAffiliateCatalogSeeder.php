<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AffiliateLink;
use Illuminate\Database\Seeder;

final class MasterAffiliateCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $amazonTag = config('integrations.affiliate.amazon.tag', 'housechefcyprus-21');

        $products = [
            [
                'slug' => 'dokum-tencere-lava-28cm',
                'title' => 'Lava Döküm Yuvarlak Tencere 28 cm',
                'category' => 'equipment',
                'description' => 'Tandır, güveç, sulu et yemekleri ve fırında ekmek pişirmek için eşit ısı dağıtan emaye döküm tencere.',
                'target_url' => "https://www.amazon.com.tr/dp/B00K8K7X6?tag={$amazonTag}",
                'provider' => 'amazon',
                'price' => 2450.00,
                'currency' => 'TRY',
                'image_url' => 'https://images.unsplash.com/photo-1584992236310-6edddc08acff?w=400&auto=format&fit=crop',
                'is_active' => true,
            ],
            [
                'slug' => 'pirge-ecco-sef-bicagi',
                'title' => 'Pirge Ecco Profesyonel Şef Bıçağı 20 cm',
                'category' => 'equipment',
                'description' => 'Et, sebze ve taze ot doğramada mutfakta hız ve hassasiyet sağlayan yüksek karbonlu paslanmaz çelik bıçak.',
                'target_url' => "https://www.amazon.com.tr/s?k=pirge+ecco+sef+bicagi&tag={$amazonTag}",
                'provider' => 'amazon',
                'price' => 495.00,
                'currency' => 'TRY',
                'image_url' => 'https://images.unsplash.com/photo-1593618998160-e34014e67546?w=400&auto=format&fit=crop',
                'is_active' => true,
            ],
            [
                'slug' => 'braun-el-blender-seti',
                'title' => 'Braun MultiQuick 5 Vario El Blender Seti 1000W',
                'category' => 'equipment',
                'description' => 'Lokanta usulü ipeksi süzme mercimek çorbaları, pürüzsüz humus, ezmeler ve soslar için güçlü parçalayıcı bıçak sistemi.',
                'target_url' => "https://www.amazon.com.tr/s?k=braun+el+blenderi+seti&tag={$amazonTag}",
                'provider' => 'amazon',
                'price' => 1850.00,
                'currency' => 'TRY',
                'image_url' => 'https://images.unsplash.com/photo-1570222094114-d054a817e56b?w=400&auto=format&fit=crop',
                'is_active' => true,
            ],
            [
                'slug' => 'dijital-hassas-mutfak-tartisi',
                'title' => 'Fakir / Soehnle Dijital Hassas Mutfak Tartısı',
                'category' => 'equipment',
                'description' => 'House Chef ölçülerinin ve hamur işlerinin tam kıvamında tutması için 1 gram hassasiyetinde dara özellikli tartı.',
                'target_url' => "https://www.amazon.com.tr/s?k=dijital+hassas+mutfak+tartisi&tag={$amazonTag}",
                'provider' => 'amazon',
                'price' => 390.00,
                'currency' => 'TRY',
                'image_url' => 'https://images.unsplash.com/photo-1590794056226-79ef3a8147e1?w=400&auto=format&fit=crop',
                'is_active' => true,
            ],
            [
                'slug' => 'dokum-izgara-tava-lava',
                'title' => 'Lava Döküm Oluklu Kare Izgara Tava 26x26 cm',
                'category' => 'equipment',
                'description' => 'Kıbrıs hellim ızgarası, köfte, antrikot ve tavuk için döküm lezzeti ve mükemmel mühürleme çizgileri.',
                'target_url' => "https://www.amazon.com.tr/s?k=lava+dokum+izgara+tava&tag={$amazonTag}",
                'provider' => 'amazon',
                'price' => 1290.00,
                'currency' => 'TRY',
                'image_url' => 'https://images.unsplash.com/photo-1584992236310-6edddc08acff?w=400&auto=format&fit=crop',
                'is_active' => true,
            ],
            [
                'slug' => 'dokum-kek-kalibi',
                'title' => 'Granit Döküm Kek & Tart Kalıbı 26 cm',
                'category' => 'equipment',
                'description' => 'Yapışmaz granit döküm kaplama ile kek ve tatlıların kalıptan kusursuz, dağılmadan çıkmasını sağlar.',
                'target_url' => "https://www.amazon.com.tr/s?k=dokum+kek+kalibi&tag={$amazonTag}",
                'provider' => 'amazon',
                'price' => 540.00,
                'currency' => 'TRY',
                'image_url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=400&auto=format&fit=crop',
                'is_active' => true,
            ],
            [
                'slug' => 'sicak-hava-fritozu-airfryer',
                'title' => 'Philips Airfryer XXL Sıcak Hava Fritözü',
                'category' => 'equipment',
                'description' => 'Minimum yağ ile çıtır börekler, tavuk kanat, sebze kızartmaları ve pratik Akdeniz lezzetleri.',
                'target_url' => "https://www.amazon.com.tr/s?k=philips+airfryer+xxl&tag={$amazonTag}",
                'provider' => 'amazon',
                'price' => 5200.00,
                'currency' => 'TRY',
                'image_url' => 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?w=400&auto=format&fit=crop',
                'is_active' => true,
            ],
            [
                'slug' => 'paslanmaz-celik-duduklu-tencere',
                'title' => 'Tefal Clipso Hassas Basınçlı Düdüklü Tencere 6L',
                'category' => 'equipment',
                'description' => 'Kuru fasulye, nohut ve kemikli et yemeklerini besin değerini koruyarak yarı sürede lokum gibi pişirir.',
                'target_url' => "https://www.amazon.com.tr/s?k=tefal+duduklu+tencere&tag={$amazonTag}",
                'provider' => 'amazon',
                'price' => 4600.00,
                'currency' => 'TRY',
                'image_url' => 'https://images.unsplash.com/photo-1584992236310-6edddc08acff?w=400&auto=format&fit=crop',
                'is_active' => true,
            ],
            [
                'slug' => 'ahsap-bambu-kesme-tahtasi',
                'title' => 'Geniş Doğal Bambu Kesme ve Sunum Tahtası',
                'category' => 'equipment',
                'description' => 'Bıçak ağzını köreltmeyen, hijyenik ve sıvı oluklu doğal bambu hazırlık tahtası.',
                'target_url' => "https://www.amazon.com.tr/s?k=bambu+kesme+tahtasi&tag={$amazonTag}",
                'provider' => 'amazon',
                'price' => 380.00,
                'currency' => 'TRY',
                'image_url' => 'https://images.unsplash.com/photo-1590794056226-79ef3a8147e1?w=400&auto=format&fit=crop',
                'is_active' => true,
            ],
            [
                'slug' => 'kilitli-cam-saklama-kabi-seti',
                'title' => 'Paşabahçe Borcam Kilitli Kapaklı Cam Saklama Seti',
                'category' => 'equipment',
                'description' => 'Artan yemekleri buzdolabında taze tutan, fırına ve mikrodalgaya girebilen dayanıklı cam kaplar.',
                'target_url' => "https://www.amazon.com.tr/s?k=kilitli+cam+saklama+kabi+seti&tag={$amazonTag}",
                'provider' => 'amazon',
                'price' => 620.00,
                'currency' => 'TRY',
                'image_url' => 'https://images.unsplash.com/photo-1584992236310-6edddc08acff?w=400&auto=format&fit=crop',
                'is_active' => true,
            ],
        ];

        foreach ($products as $item) {
            AffiliateLink::query()->updateOrCreate(
                ['slug' => $item['slug']],
                $item
            );
        }

        $this->command?->info('Master Affiliate Katalog Seeder tamamlandı: ' . count($products) . ' temel ekipman aktif.');
    }
}
