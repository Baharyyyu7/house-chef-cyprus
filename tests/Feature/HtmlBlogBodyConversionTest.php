<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Support\HtmlToMarkdown;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class HtmlBlogBodyConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_common_body_markup_converts_to_markdown_that_renders_back(): void
    {
        $html = '<h2>Malzemeler</h2><p>Bir <strong>kase</strong> <a href="/tarifler/humus">humus</a> ve <em>limon</em>.</p>'
            .'<ul><li>Nohut</li><li>Tahin<ul><li>çiğ</li></ul></li></ul><ol><li>Haşla</li><li>Ez</li></ol>'
            .'<table><tr><th>Ürün</th><th>Gram</th></tr><tr><td>Nohut</td><td>200</td></tr></table>'
            .'<blockquote><p>Sıcak servis edin.</p></blockquote>';

        $md = app(HtmlToMarkdown::class)->convert($html);

        $this->assertStringContainsString('## Malzemeler', $md);
        $this->assertStringContainsString('Bir **kase** [humus](/tarifler/humus) ve *limon*.', $md);
        $this->assertStringContainsString("- Nohut\n- Tahin\n  - çiğ", $md);
        $this->assertStringContainsString("1. Haşla\n2. Ez", $md);
        $this->assertStringContainsString("| Ürün | Gram |\n| --- | --- |\n| Nohut | 200 |", $md);
        $this->assertStringContainsString('> Sıcak servis edin.', $md);

        $yazi = new BlogPost(['body' => $md]);
        $this->assertStringContainsString('<table>', $yazi->renderedBody());
        $this->assertStringContainsString('<a href="/tarifler/humus">', $yazi->renderedBody());
    }

    public function test_the_command_is_dry_by_default_and_converts_with_apply(): void
    {
        Storage::fake('local');
        $yazi = BlogPost::query()->create(['title' => 'HTML', 'slug' => 'html-yazi', 'excerpt' => 'Ö', 'body' => '<p>Merhaba dünya.</p>', 'status' => 'draft']);
        $temiz = BlogPost::query()->create(['title' => 'MD', 'slug' => 'md-yazi', 'excerpt' => 'Ö', 'body' => "## Başlık\n\nMetin.", 'status' => 'draft']);

        $this->artisan('blog:html-govde-cevir')->assertSuccessful();
        $this->assertSame('<p>Merhaba dünya.</p>', $yazi->fresh()->body);

        $this->artisan('blog:html-govde-cevir --apply')->assertSuccessful();
        $this->assertSame("Merhaba dünya.\n", $yazi->fresh()->body);
        $this->assertSame("## Başlık\n\nMetin.", $temiz->fresh()->body);
    }
}
