<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\SeoIssue;
use App\Models\User;
use App\Services\Seo\SeoMetadataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class SeoAuthorityProgramTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_recipe_emits_recipe_and_breadcrumb_schema_without_ratings_or_reviews(): void
    {
        $recipe = $this->recipe();
        $this->makeRecipePubliclyEligible($recipe);

        $this->get(route('recipes.show', $recipe))
            ->assertOk()
            ->assertSee('"@type":"Recipe"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            // mainEntityOfPage düz adres yerine WebPage nesnesi olarak basılıyor
            // (94fba00). İki biçim de schema.org'da geçerli; korunan şey alanın
            // tarifin kendi kanonik adresini göstermesi.
            ->assertSee('"mainEntityOfPage":{"@type":"WebPage","@id":"'.route('recipes.show', $recipe).'"}', false)
            ->assertSee('"dateModified"', false)
            ->assertDontSee('aggregateRating', false)
            ->assertDontSee('reviewRating', false);
    }

    public function test_public_article_emits_blog_posting_and_breadcrumb_schema_from_real_post_data(): void
    {
        $post = BlogPost::query()->create([
            'title' => 'Kıbrıs mutfağı rehberi', 'slug' => 'kibris-mutfagi-rehberi', 'excerpt' => 'Gerçek editoryal kaynaklara dayalı kısa rehber.',
            'body' => str_repeat('Özgün editoryal bilgi. ', 100), 'fact_check_status' => 'passed', 'originality_status' => 'passed',  'status' => 'published', 'published_at' => now(),
        ]);

        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertSee('"@type":"BlogPosting"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertDontSee('aggregateRating', false);
    }

    public function test_only_approved_public_media_contributes_social_image_metadata(): void
    {
        Storage::fake('public');
        $post = BlogPost::query()->create([
            'title' => 'Onaylı görsel rehberi', 'slug' => 'onayli-gorsel-rehberi', 'excerpt' => 'Görsel kaynakları hakkında kısa rehber.',
            'body' => str_repeat('Özgün editoryal bilgi. ', 100), 'fact_check_status' => 'passed', 'originality_status' => 'passed',  'status' => 'published', 'published_at' => now(),
        ]);
        $reviewer = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        Storage::disk('public')->put('media/blog/approved.jpg', 'source');
        $variants = [];
        foreach (['thumbnail', 'square', 'card', 'open_graph', 'hero'] as $profile) {
            $jpeg = "media/blog/approved-{$profile}.jpg";
            $webp = "media/blog/approved-{$profile}.webp";
            Storage::disk('public')->put($jpeg, 'jpeg');
            Storage::disk('public')->put($webp, 'webp');
            $variants[] = ['name' => $profile, 'width' => 1600, 'height' => 900, 'jpeg' => $jpeg, 'webp' => $webp];
        }
        $media = $post->media()->create([
            'kind' => 'editorial_synthetic_illustration', 'disk' => 'public', 'path' => 'media/blog/approved.jpg', 'visibility' => 'public',
            'media_status' => 'approved_real_media', 'verification_status' => 'verified', 'license_review_status' => 'approved', 'attribution_review_status' => 'approved', 'fidelity_status' => 'strong_match', 'fidelity_confidence' => 95,
            'alt_text' => 'Onaylı konuya özel editoryal görsel', 'width' => 1600, 'height' => 900, 'responsive_variants' => $variants,
            'media_reviewer_user_id' => $reviewer->id, 'media_reviewed_at' => now(), 'approved_by_user_id' => $reviewer->id, 'approved_at' => now(), 'checksum_sha256' => 'approved-blog-media-checksum',
        ]);
        $post->update(['hero_media_id' => $media->id]);

        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertSee('property="og:image:width" content="1600"', false)
            ->assertSee('property="og:image:height" content="900"', false)
            ->assertSee('property="og:image:alt" content="Onaylı konuya özel editoryal görsel"', false)
            ->assertSee('name="twitter:image:alt" content="Onaylı konuya özel editoryal görsel"', false);
    }

    public function test_public_blog_related_recipes_exclude_drafts_and_unverified_records(): void
    {
        $post = BlogPost::query()->create([
            'title' => 'Tarif bağlantısı rehberi', 'slug' => 'tarif-baglantisi-rehberi', 'excerpt' => 'Güvenli ilgili tarif bağlantıları.',
            'body' => str_repeat('Özgün editoryal bilgi. ', 100), 'fact_check_status' => 'passed', 'originality_status' => 'passed',  'status' => 'published', 'published_at' => now(),
        ]);
        $visible = $this->recipe();
        $hidden = Recipe::query()->create([
            'title' => 'Taslak tarif', 'slug' => 'taslak-tarif', 'status' => 'draft', 'verification_status' => 'needs_review',
        ]);
        $post->recipes()->attach([$visible->id, $hidden->id]);

        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertSee($visible->title)
            ->assertDontSee($hidden->title)
            ->assertDontSee(route('recipes.show', $hidden));
    }

    public function test_faq_schema_is_emitted_only_for_actual_editorial_question_and_answer_blocks(): void
    {
        $post = BlogPost::query()->create([
            'title' => 'Gerçek soru cevap rehberi', 'slug' => 'gercek-soru-cevap-rehberi', 'excerpt' => 'Editoryal soru cevap özeti.',
            'body' => str_repeat('Özgün editoryal bilgi. ', 100), 'fact_check_status' => 'passed', 'originality_status' => 'passed',  'status' => 'published', 'published_at' => now(),
            'faq_blocks' => [['question' => 'Tarif ne zaman hazırlanır?', 'answer' => 'Malzemeler hazır olduğunda.']],
        ]);

        $this->get(route('blog.show', $post))->assertOk()->assertSee('"@type":"FAQPage"', false);
    }

    public function test_external_canonical_override_is_not_rendered(): void
    {
        $recipe = $this->recipe();
        app(SeoMetadataService::class)->update($recipe, ['canonical_override' => 'https://untrusted.example/recipe']);

        $this->get(route('recipes.show', $recipe))
            ->assertOk()
            ->assertSee('rel="canonical" href="'.route('recipes.show', $recipe).'"', false)
            ->assertDontSee('untrusted.example');
    }

    public function test_audit_records_missing_alt_text_as_an_idempotent_schema_issue(): void
    {
        $recipe = $this->recipe();
        $recipe->media()->create(['kind' => 'image', 'disk' => 'public', 'path' => 'recipes/seo.jpg', 'visibility' => 'public', 'verification_status' => 'verified', 'alt_text' => null]);

        $this->artisan('seo:audit --scope=schema')->assertSuccessful();
        $this->assertDatabaseHas('seo_issues', ['entity_public_id' => $recipe->public_id, 'category' => 'schema']);
        $this->artisan('seo:audit --scope=schema')->assertSuccessful();
        $this->assertSame(1, SeoIssue::query()->where('entity_public_id', $recipe->public_id)->where('message', 'Public tarif görseli açıklayıcı alternatif metin içermiyor.')->count());
    }

    public function test_health_command_and_admin_dashboard_report_real_catalogue_counts(): void
    {
        $this->recipe();
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $this->artisan('seo:health --json')->expectsOutputToContain('"recipes":1')->assertSuccessful();
        $this->actingAs($admin)->get(route('admin.seo.index'))->assertOk()->assertSee('Otorite ve kapsama sinyalleri')->assertSee('Indexlenebilir tarif');
    }

    private function recipe(): Recipe
    {
        return Recipe::query()->create([
            'title' => 'Kıbrıs usulü sebze yemeği', 'slug' => 'kibris-usulu-sebze-yemegi',
            'excerpt' => 'Mevsim sebzeleriyle hazırlanan doğrulanmış tarif.',
            'description' => str_repeat('Tarif bağlamı ve hazırlık notu. ', 8),
            'prep_minutes' => 15, 'cook_minutes' => 25, 'rest_minutes' => 0, 'servings' => 4,
            'difficulty' => 'easy', 'status' => 'published', 'verification_status' => 'verified', 'published_at' => now(),
        ]);
    }
}
