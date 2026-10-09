<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class MediaUploaderCannotApproveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_the_uploader_cannot_approve_their_own_upload(): void
    {
        // 9 Ekim denetimi: yükleyen kendi beyan ettiği görseli onaylayıp yayına
        // alabiliyordu. Onay media_reviewer_user_id'yi onaylayanla ezdiği için
        // yükleyen ayrı bir alanda tutulmalı ki kontrol yapılabilsin.
        $yukleyen = $this->yonetici();
        $media = $this->yukle($yukleyen);

        $this->assertSame($yukleyen->id, $media->uploaded_by_user_id);

        $this->actingAs($yukleyen)
            ->withSession(['_token' => 'test-csrf'])
            ->post(route('admin.media.approve', $media->public_id), ['_token' => 'test-csrf'])
            ->assertSessionHas('error', 'Kendi yüklediğiniz görseli onaylayamazsınız; onayı başka bir editör vermeli.');

        $this->assertNull($media->fresh()->approved_by_user_id);
    }

    public function test_the_uploader_cannot_approve_their_upload_in_a_batch(): void
    {
        $yukleyen = $this->yonetici();
        $media = $this->yukle($yukleyen);

        $this->actingAs($yukleyen)
            ->withSession(['_token' => 'test-csrf'])
            ->post(route('admin.media.approve-many'), ['_token' => 'test-csrf', 'public_ids' => [$media->public_id]])
            ->assertSessionHas('status', '0 görsel onaylandı, 1 tanesi kendi yüklediğiniz için atlandı.');

        $this->assertNull($media->fresh()->approved_by_user_id);
    }

    public function test_another_admin_is_not_blocked_by_the_uploader_rule(): void
    {
        $yukleyen = $this->yonetici();
        $media = $this->yukle($yukleyen);

        $this->actingAs($this->yonetici())
            ->withSession(['_token' => 'test-csrf'])
            ->post(route('admin.media.approve', $media->public_id), ['_token' => 'test-csrf']);

        $this->assertFalse(str_contains((string) session('error', ''), 'Kendi yüklediğiniz'));
    }

    private function yukle(User $yukleyen): Media
    {
        $tarif = Recipe::query()->create([
            'title' => 'Hellimli Börek',
            'slug' => Str::slug('Hellimli Börek').'-'.uniqid(),
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        $this->actingAs($yukleyen)
            ->post(route('admin.media.store'), [
                'file' => UploadedFile::fake()->image('yemek.jpg', 1600, 1350),
                'entity_type' => 'recipe',
                'entity_public_id' => $tarif->public_id,
                'visibility' => 'public',
                'alt_text' => 'Servis edilmiş yemek',
                'telif_beyani' => '1',
                'icerik_beyani' => '1',
            ])
            ->assertRedirect();

        return $tarif->media()->sole();
    }

    private function yonetici(): User
    {
        return User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }
}
