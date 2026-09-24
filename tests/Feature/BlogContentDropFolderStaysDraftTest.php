<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\ContentRevision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BlogContentDropFolderStaysDraftTest extends TestCase
{
    use RefreshDatabase;

    private string $klasor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->klasor = storage_path('app/content-import/blog');

        if (! is_dir($this->klasor)) {
            mkdir($this->klasor, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        foreach (glob($this->klasor.'/*') ?: [] as $dosya) {
            @unlink($dosya);
        }

        @rmdir($this->klasor);

        parent::tearDown();
    }

    public function test_a_new_article_from_the_drop_folder_is_created_as_a_draft(): void
    {
        // Bir kez ~3200 makine üretimi sayfa toplu yayına çıktı ve arama
        // sonuçlarını doldurdu. Üretim hattı hiçbir koşulda yayın durumuna
        // dokunmamalı; yayın insanın kararı.
        $this->icerikYaz('dunya-mutfaginda-hellim', 'Dünya Mutfağında Hellim');

        $this->artisan('blog:apply-editorial-content', ['--apply' => true])
            ->assertExitCode(0);

        $yazi = BlogPost::query()->where('slug', 'dunya-mutfaginda-hellim')->sole();

        $this->assertSame('draft', $yazi->status);
        $this->assertNull($yazi->published_at);
        $this->assertSame('Dünya Mutfağında Hellim', $yazi->title);
    }

    public function test_report_mode_writes_nothing(): void
    {
        $this->icerikYaz('rapor-modu-denemesi', 'Rapor Modu');

        $this->artisan('blog:apply-editorial-content')->assertExitCode(0);

        $this->assertSame(0, BlogPost::query()->where('slug', 'rapor-modu-denemesi')->count());
    }

    public function test_php_files_in_the_server_drop_folder_are_never_executed_or_imported(): void
    {
        file_put_contents($this->klasor.'/calistirilabilir-icerik.php', "<?php file_put_contents('".storage_path('blog-import-rce-marker')."', 'unsafe'); return [];\n");

        $this->artisan('blog:apply-editorial-content', ['--apply' => true, '--slug' => 'calistirilabilir-icerik'])
            ->assertExitCode(0);

        $this->assertFileDoesNotExist(storage_path('blog-import-rce-marker'));
        $this->assertDatabaseMissing('blog_posts', ['slug' => 'calistirilabilir-icerik']);
    }

    public function test_an_already_published_article_receives_a_private_revision_without_changing_live_content(): void
    {
        // Teslim klasöründen gelen içerik, yayımlı bir yazının canlı metnini
        // değiştiremez. Önce insanın inceleyeceği özel bir revision açılır.
        $yayindaki = BlogPost::query()->create([
            'slug' => 'yayindaki-yazi',
            'title' => 'Eski Başlık',
            'excerpt' => 'Eski özet.',
            'body' => 'Eski gövde.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->icerikYaz('yayindaki-yazi', 'Yeni Başlık');

        $this->artisan('blog:apply-editorial-content', ['--apply' => true])->assertExitCode(0);

        $canli = $yayindaki->fresh();
        $revision = ContentRevision::query()->where('blog_post_id', $yayindaki->id)->sole();

        $this->assertSame('Eski Başlık', $canli->title);
        $this->assertSame('Eski gövde.', $canli->body);
        $this->assertSame('published', $canli->status, 'Yayın durumu değişmemeli.');
        $this->assertSame('Teslim edilen editoryal güncelleme', $revision->reason);
        $this->assertSame('Gövde metni. '.str_repeat('Gövde metni. ', 29), $revision->proposed_body);
        $this->assertSame('Yeni Başlık', $revision->changed_seo_fields['title']);
    }

    private function icerikYaz(string $slug, string $baslik): void
    {
        $govde = str_repeat('Gövde metni. ', 30);

        file_put_contents($this->klasor.'/'.$slug.'.json', (string) json_encode([
            'title' => $baslik,
            'excerpt' => 'Özet metni.',
            'body' => $govde,
            'meta_title' => $baslik,
            'meta_description' => 'Açıklama.',
            'primary_keyword' => 'hellim',
            'secondary_keywords' => ['kibris'],
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }
}
