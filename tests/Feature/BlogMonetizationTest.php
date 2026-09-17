<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use App\Services\Affiliate\RecipeAffiliateMatchingService;
use Database\Seeders\MasterAffiliateCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BlogMonetizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterAffiliateCatalogSeeder::class);
    }

    public function test_matching_service_recommends_skillet_for_halloumi_blog_post(): void
    {
        $post = new BlogPost([
            'title' => 'Hellim Peyniri: Üretimden Sofraya Eksiksiz Rehber',
            'slug' => 'hellim-peyniri-rehberi',
            'body' => 'Hellim peynirini ızgarada mühürlerken döküm tavanın yüksek ısısı peynirin dışını çıtır yapar.',
        ]);

        $service = app(RecipeAffiliateMatchingService::class);
        $matches = $service->forBlog($post, 2);

        $this->assertNotEmpty($matches);
        $this->assertTrue($matches->contains('slug', 'dokum-izgara-tava-lava'));
    }

    public function test_matching_service_recommends_dutch_oven_for_kleftiko_blog_post(): void
    {
        $post = new BlogPost([
            'title' => 'Kleftiko Nedir? Otantik Kıbrıs Usulü Kuzu Kleftiko Rehberi',
            'slug' => 'kleftiko-rehberi',
            'body' => 'Geleneksel Kıbrıs tandır lezzeti için döküm tencere ve et termometresi kullanımı hayati önem taşır.',
        ]);

        $service = app(RecipeAffiliateMatchingService::class);
        $matches = $service->forBlog($post, 2);

        $this->assertNotEmpty($matches);
        $this->assertTrue($matches->contains('slug', 'dokum-tencere-lava-28cm') || $matches->contains('slug', 'anlik-dijital-et-termometresi'));
    }

    public function test_matching_service_recommends_diet_tools_for_mediterranean_diet_blog(): void
    {
        $post = new BlogPost([
            'title' => 'Akdeniz Diyeti 30 Günlük Başlangıç Planı',
            'slug' => 'akdeniz-diyeti-30-gun',
            'body' => 'Bol yeşillikli salatalar, sızma zeytinyağı ve taze sebzeler Akdeniz beslenmesinin temel taşıdır.',
        ]);

        $service = app(RecipeAffiliateMatchingService::class);
        $matches = $service->forBlog($post, 2);

        $this->assertNotEmpty($matches);
        $this->assertTrue($matches->contains('slug', 'salata-kurutucu-santrifuj') || $matches->contains('slug', 'zeytinyagi-spreyi-ve-yagdanlik'));
    }

    public function test_blog_show_renders_affiliate_equipment_lead_card_and_ebook(): void
    {
        $author = User::factory()->create();
        $category = BlogCategory::create([
            'name' => 'Kıbrıs Mutfağı',
            'slug' => 'kibris-mutfagi',
            'public_id' => '01TESTBCAT000000000000001',
        ]);

        $post = BlogPost::create([
            'author_id' => $author->id,
            'blog_category_id' => $category->id,
            'title' => 'Kıbrıs Hellim Rehberi ve Pişirme Sırları',
            'slug' => 'kibris-hellim-rehberi-test',
            'excerpt' => 'Hellim nasıl pişirilir ve en iyi nasıl servis edilir?',
            'body' => '## Hellim Kızartma Teknikleri\n\nHellimi döküm tavada pişirmek mükemmel altın sarısı kabuk sağlar.',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'public_id' => '01TESTBPOST00000000000001',
        ]);

        $response = $this->get(route('blog.show', $post));

        $response->assertOk();
        // 1. Ekipman affiliate kartı
        $response->assertSee('Şefin Tavsiyesi · Ekipman');
        $response->assertSee('Lava Döküm Oluklu Kare Izgara Tava 26x26 cm');
        $response->assertSee(route('affiliate.redirect', ['slug' => 'dokum-izgara-tava-lava']));

        // 2. Özel Şef Lead Kartı
        $response->assertSee('Kıbrıs Tatilinizde Villanızda Özel Şef & Gurme Catering');
        $response->assertSee(route('private-chef.index'));
        $response->assertSee(route('catering.index'));

        // 3. E-Kitap Tanıtım Kartı
        $response->assertSee('Kıbrıs Mutfağı Seçmeler E-Kitabı');
        $response->assertSee(route('shop.index'));
    }
}
