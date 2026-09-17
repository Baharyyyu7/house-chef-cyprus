<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Recipe;
use App\Models\User;
use App\Services\Media\PublicMediaEligibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class DuplicateMediaDoesNotDeadlockApprovalTest extends TestCase
{
    use RefreshDatabase;

    private PublicMediaEligibilityService $kapi;

    private User $editor;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::disk('public')->put('media/approved-synthetic/recipe/AAA/source.png', 'png');

        // Kapı beş boy varyantın hem jpeg hem webp dosyasını diskte arıyor.
        foreach (['thumbnail', 'square', 'card', 'open_graph', 'hero'] as $boy) {
            foreach (['jpeg' => 'jpg', 'webp' => 'webp'] as $uzanti) {
                Storage::disk('public')->put('media/optimized/AAA/'.$boy.'.'.$uzanti, 'x');
            }
        }

        $this->kapi = app(PublicMediaEligibilityService::class);
        $this->editor = $this->yonetici();
    }

    public function test_two_pending_twins_do_not_block_each_other(): void
    {
        // İçe aktarma iki kez çalıştığında aynı dosya için iki kayıt oluşuyor.
        // Her biri diğerini kopya sayınca ikisi de hiçbir zaman
        // onaylanamıyordu: onay ekranındaki sekiz görselin sekizi de
        // "duplicate_media_usage" veriyordu ve düğme çalışmıyor görünüyordu.
        [$birinci, $ikinci] = $this->ikizler();

        $this->onayUygula($birinci);

        $this->assertNull(
            $this->kapi->reason($birinci),
            'Onay bekleyen ikizi olan bir görsel onaylanabilmeli.'
        );

        $this->onayUygula($ikinci);

        $this->assertNull(
            $this->kapi->reason($ikinci),
            'İkizlerden hiçbiri henüz yayında değilken ikisi de engellenmemeli.'
        );
    }

    public function test_a_copy_is_still_blocked_once_the_other_is_live(): void
    {
        // Daralma kuralı zayıflatmamalı: aynı dosya iki yerde birden yayına
        // çıkamaz. Bu, kuralın var olma sebebi.
        [$yayindaki, $ikinci] = $this->ikizler();

        $this->onayUygula($yayindaki);
        $yayindaki->save();

        $this->onayUygula($ikinci);

        $this->assertSame(
            'duplicate_media_usage',
            // fresh() bellekteki onayı atardı; kontrol edilen şey tam da o onay.
            $this->kapi->reason($ikinci),
            'Kopyalardan biri yayına girdiyse diğeri engellenmeli.'
        );
    }

    public function test_the_admin_screen_can_actually_approve_a_twinned_image(): void
    {
        [$birinci] = $this->ikizler();

        $this->actingAs($this->editor)
            ->post(route('admin.media.approve', $birinci->public_id))
            ->assertRedirect()
            ->assertSessionMissing('error');

        $this->assertNotNull($birinci->fresh()->approved_by_user_id);
    }

    public function test_a_copy_that_can_never_be_approved_leaves_the_queue(): void
    {
        // Kapı ilk engeli döndürür: eşi yayında olan ikiz "insan onayı eksik"
        // diyordu ve kuyrukta onaylanmayı bekliyor görünüyordu. Onaylandığında
        // kopya kontrolüne takılıp geri dönüyor, kuyrukta kalmaya devam
        // ediyordu. Onaylayan kişi aynı görseli tekrar tekrar onaylamayı
        // deniyordu.
        [$yayindaki, $ikiz] = $this->ikizler();

        $this->onayUygula($yayindaki);
        $yayindaki->save();

        $icerik = (string) $this->actingAs($this->editor)
            ->get(route('admin.media.index', ['durum' => 'onay']))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString(
            $ikiz->public_id,
            $icerik,
            'Onaylanması imkânsız bir kayıt onay kuyruğunda durmamalı.'
        );
    }

    public function test_an_approvable_image_does_stay_in_the_queue(): void
    {
        // Daralma kuyruğu boşaltmamalı: onayın yayına çıkaracağı kayıt durur.
        [$birinci] = $this->ikizler();

        $this->actingAs($this->editor)
            ->get(route('admin.media.index', ['durum' => 'onay']))
            ->assertOk()
            ->assertSee($birinci->public_id, false);
    }

    /** @return array{0: Media, 1: Media} */
    private function ikizler(): array
    {
        $tarif = Recipe::query()->create([
            'title' => 'İkiz Kayıtlı Tarif',
            'slug' => 'ikiz-'.uniqid(),
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        // Dosya yolundaki klasör tarifin kendi kimliği olmalı: başka bir
        // tarifin klasöründe duran görsel artık kimlik uyuşmazlığı sayılıyor
        // ve onaya girmiyor (eaf1311).
        $klasor = (string) $tarif->public_id;
        Storage::disk('public')->put('media/approved-synthetic/recipe/'.$klasor.'/source.png', 'png');

        $alanlar = [
            'kind' => 'recipe_synthetic',
            'disk' => 'public',
            'path' => 'media/approved-synthetic/recipe/'.$klasor.'/source.png',
            'mime_type' => 'image/png',
            'visibility' => 'public',
            'media_status' => 'approved_real_media',
            'verification_status' => 'verified',
            'license_review_status' => 'approved',
            'attribution_review_status' => 'approved',
            'is_placeholder' => false,
            'fidelity_status' => 'strong_match',
            'fidelity_confidence' => 95,
            'checksum_sha256' => str_repeat('a', 64),
            // Varyantlar eşleme değil, 'name' alanı taşıyan bir liste.
            'responsive_variants' => collect(['thumbnail', 'square', 'card', 'open_graph', 'hero'])
                ->map(fn (string $boy): array => [
                    'name' => $boy,
                    'jpeg' => 'media/optimized/AAA/'.$boy.'.jpg',
                    'webp' => 'media/optimized/AAA/'.$boy.'.webp',
                ])->all(),
        ];

        return [$tarif->media()->create($alanlar), $tarif->media()->create($alanlar)];
    }

    private function onayUygula(Media $media): void
    {
        $media->forceFill([
            'media_reviewer_user_id' => $this->editor->id,
            'media_reviewed_at' => now(),
            'approved_by_user_id' => $this->editor->id,
            'approved_at' => now(),
        ]);
    }

    private function yonetici(): User
    {
        return User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }
}
