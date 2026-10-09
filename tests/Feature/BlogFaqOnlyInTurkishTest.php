<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Services\Seo\SeoSchemaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BlogFaqOnlyInTurkishTest extends TestCase
{
    use RefreshDatabase;

    public function test_faq_schema_is_emitted_only_for_the_turkish_source_text(): void
    {
        // 9 Ekim denetimi: çevirili yazıda SSS metni ve FAQPage JSON-LD Türkçe kalıyordu.
        $yazi = BlogPost::create(['title' => 'SSS yazısı', 'slug' => 'sss-yazisi', 'excerpt' => 'Özet', 'body' => $this->cesitliMetin('Yazının özgün gövde metni; konusunu taşıyacak uzunlukta. ', 40), 'fact_check_status' => 'passed', 'originality_status' => 'passed', 'status' => 'published', 'published_at' => now(),
            'faq_blocks' => [['question' => 'Hellim nedir?', 'answer' => 'Kıbrıs peyniridir.']]]);

        app()->setLocale('tr');
        $this->assertContains('FAQPage', array_column(app(SeoSchemaService::class)->article($yazi)['@graph'] ?? [], '@type'));

        app()->setLocale('en');
        $this->assertNotContains('FAQPage', array_column(app(SeoSchemaService::class)->article($yazi)['@graph'] ?? [], '@type'));
        app()->setLocale('tr');
    }
}
