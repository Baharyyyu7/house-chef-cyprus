<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

/**
 * 2891 tarif ve yazı, dosyası olmayan "onaylı" medya satırlarıyla işaretlenmişti:
 * görseller Unsplash'ten hotlink ediliyordu ve 2891 satır 23 fotoğrafı
 * paylaşıyordu. Onay artık dışarıdan doğrulanabilir koşullara bağlıdır.
 */
final class HotlinkedMediaCannotBeApprovedTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_hotlink_without_a_human_approver_cannot_be_marked_approved(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/MEDIA_HOTLINK_NEEDS_HUMAN_APPROVAL/');

        $this->medya(['kind' => 'external_hotlinked_media', 'path' => '', 'approved_by_user_id' => null]);
    }

    public function test_an_editor_approved_hotlink_is_still_allowed(): void
    {
        // Unsplash akışı bir editör onayıyla hotlink yayınlar; bu tasarlanmıştır.
        $media = $this->medya([
            'kind' => 'external_hotlinked_media',
            'path' => '',
            'approved_by_user_id' => User::factory()->create()->id,
        ]);

        $this->assertSame('approved_real_media', $media->media_status);
    }

    public function test_an_approved_row_without_a_file_is_rejected(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/MEDIA_APPROVED_WITHOUT_FILE/');

        $this->medya(['path' => '']);
    }

    public function test_a_genuine_imported_image_still_saves(): void
    {
        $media = $this->medya(['path' => 'media/recipes/adana.jpg']);

        $this->assertSame('approved_real_media', $media->media_status);
    }

    public function test_an_unapproved_hotlink_is_left_alone(): void
    {
        // Aday havuzunda hotlink tutmak serbesttir; yasak olan onaylı saymaktır.
        $media = $this->medya(['kind' => 'external_hotlinked_media', 'path' => '', 'media_status' => 'no_media']);

        $this->assertSame('no_media', $media->media_status);
    }

    /** @param array<string,mixed> $ozellikler */
    private function medya(array $ozellikler): Media
    {
        $recipe = Recipe::query()->create([
            'title' => 'Medya Kapısı Tarifi',
            'slug' => 'medya-kapisi-tarifi-'.uniqid(),
            'status' => 'published',
        ]);

        return $recipe->media()->create(array_merge([
            'public_id' => (string) Str::ulid(),
            'kind' => 'image',
            'disk' => 'public',
            'visibility' => 'public',
            'media_status' => 'approved_real_media',
        ], $ozellikler));
    }
}
