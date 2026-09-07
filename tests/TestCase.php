<?php

namespace Tests;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    private bool $publicTestDiskIsReady = false;

    /** Build the complete public-media contract used by production. */
    protected function makeRecipePubliclyEligible(Recipe $recipe): void
    {
        if (! $this->publicTestDiskIsReady) {
            Storage::fake('public');
            $this->publicTestDiskIsReady = true;
        }
        $reviewer = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $source = 'media/tests/'.$recipe->id.'/source.jpg';
        Storage::disk('public')->put($source, 'source');
        $variants = collect(['thumbnail', 'square', 'card', 'open_graph', 'hero'])->map(function (string $name) use ($recipe): array {
            $jpeg = 'media/tests/'.$recipe->id.'/'.$name.'.jpg';
            $webp = 'media/tests/'.$recipe->id.'/'.$name.'.webp';
            Storage::disk('public')->put($jpeg, 'jpeg');
            Storage::disk('public')->put($webp, 'webp');

            return ['name' => $name, 'width' => 1200, 'height' => 800, 'jpeg' => $jpeg, 'webp' => $webp];
        })->all();

        $recipe->media()->create([
            'kind' => 'recipe_synthetic', 'disk' => 'public', 'path' => $source, 'mime_type' => 'image/jpeg',
            'visibility' => 'public', 'media_status' => 'approved_real_media', 'verification_status' => 'verified',
            'license_review_status' => 'approved', 'attribution_review_status' => 'approved',
            'fidelity_status' => 'strong_match', 'fidelity_confidence' => 95,
            'media_reviewer_user_id' => $reviewer->id, 'approved_by_user_id' => $reviewer->id,
            'media_reviewed_at' => now(), 'approved_at' => now(), 'responsive_variants' => $variants,
            'checksum_sha256' => hash('sha256', $source), 'alt_text' => $recipe->title.' servis edilmiş hâlde',
        ]);
    }
}
