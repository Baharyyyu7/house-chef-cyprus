<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use App\Services\Media\PublicMediaEligibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ManualMediaUploadReachesApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_the_form_lets_you_pick_a_recipe_by_name(): void
    {
        // Form 26 karakterlik ULID'i elle yazmayı istiyordu; kimse bir tarifin
        // ULID'ini bilmiyor ve yükleme pratikte yapılamıyordu.
        $tarif = $this->tarif('Kıbrıs Usulü Fırın Makarna');

        $this->actingAs($this->yonetici())
            ->get(route('admin.media.create'))
            ->assertOk()
            ->assertSee('Kıbrıs Usulü Fırın Makarna')
            ->assertSee($tarif->public_id, false);
    }

    public function test_an_uploaded_image_lands_in_the_approval_queue_instead_of_a_dead_end(): void
    {
        // Yükleme media_status, license_review_status gibi alanların hiçbirini
        // yazmıyordu. isApprovedRealMedia() false kalıyor, kapı
        // "approval_incomplete" diyordu ve bu alanları yazacak hiçbir arayüz
        // yoktu: elle yüklenen görsel hiçbir zaman yayına çıkamıyordu.
        $tarif = $this->tarif('Hellimli Börek');
        $yonetici = $this->yonetici();

        $this->actingAs($yonetici)
            ->post(route('admin.media.store'), $this->gecerliForm($tarif->public_id))
            ->assertRedirect(route('admin.media.index', ['durum' => 'onay']));

        $media = $tarif->media()->sole();

        $this->assertTrue($media->isApprovedRealMedia(), 'Yüklenen görsel kapının aradığı alanları taşımalı.');
        $this->assertNull($media->approved_by_user_id, 'Yükleme tek başına yayına çıkarmamalı.');
        $this->assertSame($yonetici->id, $media->media_reviewer_user_id, 'Beyanı kimin verdiği kaydedilmeli.');

        // Geriye kalan tek engel insan onayı olmalı; yani görsel kuyrukta.
        $this->assertSame(
            'human_approval_missing',
            app(PublicMediaEligibilityService::class)->reason($media)
        );
    }

    public function test_the_upload_is_refused_without_both_declarations(): void
    {
        // Beyanı sistem uyduramaz. Kutular işaretlenmeden yükleme kabul edilmez.
        $tarif = $this->tarif('Kolokas');

        foreach (['telif_beyani', 'icerik_beyani'] as $eksik) {
            $veri = $this->gecerliForm($tarif->public_id);
            unset($veri[$eksik]);

            $this->actingAs($this->yonetici())
                ->post(route('admin.media.store'), $veri)
                ->assertSessionHasErrors($eksik);
        }

        $this->assertSame(0, $tarif->media()->count());
    }

    public function test_the_uploader_is_told_the_image_is_not_live_yet(): void
    {
        // Eski mesaj "yüklendi" deyip bırakıyordu ve kimse görselin neden
        // sitede görünmediğini anlamıyordu.
        $tarif = $this->tarif('Molohiya');

        $this->actingAs($this->yonetici())
            ->post(route('admin.media.store'), $this->gecerliForm($tarif->public_id))
            ->assertSessionHas('status', fn (string $mesaj): bool => str_contains($mesaj, 'onay'));
    }

    /** @return array<string, mixed> */
    private function gecerliForm(string $publicId): array
    {
        return [
            'file' => UploadedFile::fake()->image('yemek.jpg', 1600, 1350),
            'entity_type' => 'recipe',
            'entity_public_id' => $publicId,
            'visibility' => 'public',
            'alt_text' => 'Servis edilmiş yemek',
            'telif_beyani' => '1',
            'icerik_beyani' => '1',
        ];
    }

    private function tarif(string $baslik): Recipe
    {
        return Recipe::query()->create([
            'title' => $baslik,
            'slug' => Str::slug($baslik).'-'.uniqid(),
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);
    }

    private function yonetici(): User
    {
        return User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }
}
