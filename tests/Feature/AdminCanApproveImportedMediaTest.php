<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Recipe;
use App\Models\User;
use App\Services\Media\PublicMediaEligibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * İçe aktarılan tarif görselleri hiç yayına çıkmıyordu. Genel kapı gerçek bir
 * kullanıcıya bağlı inceleme ve onay kaydı arıyor -- doğru bir kural -- ama
 * yönetim panelinde bu onayı verecek ekran yoktu: dosya içeri alınıyor,
 * yedi varyantı üretiliyor ve orada kalıyordu.
 */
final class AdminCanApproveImportedMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_imported_image_waits_for_a_human_and_is_listed_as_such(): void
    {
        [$admin, $media] = $this->importedImage();

        $this->assertSame('human_approval_missing', app(PublicMediaEligibilityService::class)->reason($media));

        $this->actingAs($admin)
            ->get(route('admin.media.index'))
            ->assertOk()
            ->assertSee('Onay bekleyen görsel (1)', false);
    }

    public function test_approving_it_records_the_admin_and_puts_it_on_the_site(): void
    {
        [$admin, $media] = $this->importedImage();

        $this->actingAs($admin)
            ->withSession(['_token' => 'test-csrf'])
            ->post(route('admin.media.approve', $media->public_id), ['_token' => 'test-csrf'])
            ->assertRedirect()
            ->assertSessionMissing('error');

        $media->refresh();

        $this->assertSame($admin->id, $media->approved_by_user_id);
        $this->assertSame($admin->id, $media->media_reviewer_user_id);
        $this->assertNull(app(PublicMediaEligibilityService::class)->reason($media));
    }

    public function test_it_refuses_to_approve_an_image_that_fails_another_check(): void
    {
        [$admin, $media] = $this->importedImage();
        // Varyantlar eksikse onay eksik olan tek şey insan kararı değildir.
        $media->forceFill(['responsive_variants' => []])->save();

        $this->actingAs($admin)
            ->withSession(['_token' => 'test-csrf'])
            ->post(route('admin.media.approve', $media->public_id), ['_token' => 'test-csrf'])
            ->assertRedirect();

        $this->assertNull($media->fresh()->approved_by_user_id);
    }

    public function test_several_images_can_be_approved_in_one_action(): void
    {
        [$admin, $birinci] = $this->importedImage();
        [, $ikinci] = $this->importedImage();

        $this->actingAs($admin)
            ->withSession(['_token' => 'test-csrf'])
            ->post(route('admin.media.approve-many'), ['_token' => 'test-csrf', 'public_ids' => [$birinci->public_id, $ikinci->public_id]])
            ->assertRedirect()
            ->assertSessionMissing('error');

        $this->assertSame($admin->id, $birinci->fresh()->approved_by_user_id);
        $this->assertSame($admin->id, $ikinci->fresh()->approved_by_user_id);
    }

    public function test_batch_approval_skips_an_image_that_fails_another_check(): void
    {
        [$admin, $saglam] = $this->importedImage();
        [, $eksik] = $this->importedImage();
        $eksik->forceFill(['responsive_variants' => []])->save();

        $this->actingAs($admin)
            ->withSession(['_token' => 'test-csrf'])
            ->post(route('admin.media.approve-many'), ['_token' => 'test-csrf', 'public_ids' => [$saglam->public_id, $eksik->public_id]])
            ->assertRedirect();

        $this->assertSame($admin->id, $saglam->fresh()->approved_by_user_id);
        $this->assertNull($eksik->fresh()->approved_by_user_id);
    }

    public function test_a_member_cannot_approve_many(): void
    {
        [, $media] = $this->importedImage();
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);

        $this->actingAs($member)
            ->withSession(['_token' => 'test-csrf'])
            ->post(route('admin.media.approve-many'), ['_token' => 'test-csrf', 'public_ids' => [$media->public_id]])
            ->assertForbidden();

        $this->assertNull($media->fresh()->approved_by_user_id);
    }

    public function test_a_member_cannot_approve_media(): void
    {
        [, $media] = $this->importedImage();
        $member = User::factory()->create(['role' => 'member', 'email_verified_at' => now()]);

        $this->actingAs($member)
            ->withSession(['_token' => 'test-csrf'])
            ->post(route('admin.media.approve', $media->public_id), ['_token' => 'test-csrf'])
            ->assertForbidden();

        $this->assertNull($media->fresh()->approved_by_user_id);
    }

    private bool $diskHazir = false;

    /** @return array{0: User, 1: Media} */
    private function importedImage(): array
    {
        // Storage::fake her çağrıda diski siler; iki görsel kuran testte ilkinin
        // dosyaları kaybolur ve kaynak kontrolünü geçemez.
        if (! $this->diskHazir) {
            Storage::fake('public');
            $this->diskHazir = true;
        }

        $admin = User::query()->where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $benzersiz = uniqid();
        $recipe = Recipe::query()->create([
            'title' => 'Adana Kebabı',
            'slug' => 'adana-kebabi-'.$benzersiz,
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        $kaynak = 'media/approved-synthetic/recipe/'.Str::ulid().'/source.png';
        Storage::disk('public')->put($kaynak, 'binary');

        // Kapı varyantları $item['name'] üzerinden indeksler; bu alan olmadan
        // varyant listesi dolu olsa bile eksik sayılır.
        $variants = [];
        foreach (array_keys((array) config('media_excellence.recipe_profiles')) as $profile) {
            $variants[$profile] = ['name' => $profile];
            foreach (['jpeg' => 'jpg', 'webp' => 'webp', 'avif' => 'avif'] as $format => $uzanti) {
                $yol = "media/approved-synthetic/recipe/{$benzersiz}/{$profile}.{$uzanti}";
                Storage::disk('public')->put($yol, 'binary');
                $variants[$profile][$format] = $yol;
            }
        }

        $media = $recipe->media()->create([
            'public_id' => (string) Str::ulid(),
            'kind' => 'recipe_synthetic',
            'disk' => 'public',
            'path' => $kaynak,
            'mime_type' => 'image/png',
            'width' => 1600,
            'height' => 1350,
            'visibility' => 'public',
            'media_status' => 'approved_real_media',
            'fidelity_status' => 'strong_match',
            'fidelity_confidence' => 100,
            'license' => 'House Chef Cyprus production-approved synthetic media',
            'license_review_status' => 'approved',
            'attribution_review_status' => 'approved',
            'verification_status' => 'verified',
            'processing_status' => 'ready',
        ]);

        $media->forceFill(['responsive_variants' => $variants])->save();

        return [$admin, $media];
    }
}
