<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class MediaIntakeSkipsInsteadOfAbortingTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_mode_is_the_default_and_writes_nothing(): void
    {
        // Varsayılan davranış yazmak değil raporlamak olmalı: medyayla ilgili
        // her komutta olduğu gibi.
        $this->artisan('media:intake')
            ->expectsOutputToContain('Rapor modu')
            ->assertExitCode(0);
    }

    public function test_an_unpublished_recipe_is_skipped_with_its_reason_not_aborting_the_batch(): void
    {
        // Toplu içe aktarmada tek bir reddedilen kayıt bütün partiyi
        // durduruyordu: Antigravity yayında olmayan bir tarife görsel
        // ürettiğinde o partideki kusursuz görseller de içeri girmiyordu.
        $taslak = Recipe::query()->create([
            'title' => 'Taslak Tarif',
            'slug' => 'taslak-tarif-'.uniqid(),
            'status' => 'draft',
            'verification_status' => 'verified',
        ]);

        $this->manifestYaz([['public_id' => $taslak->public_id]]);

        $this->artisan('media:intake', ['--apply' => true])
            ->expectsOutputToContain('yayında değil')
            ->assertExitCode(0);
    }

    public function test_a_missing_recipe_is_reported_rather_than_crashing(): void
    {
        $this->manifestYaz([['public_id' => '01OLMAYANKAYITXXXXXXXXXXXX']]);

        $this->artisan('media:intake', ['--apply' => true])
            ->expectsOutputToContain('tarif kaydı yok')
            ->assertExitCode(0);
    }

    public function test_nothing_is_published_without_human_approval(): void
    {
        $this->manifestYaz([]);

        // Komutun sonu her zaman onay kuyruğunu işaret etmeli; hiçbir görsel
        // kendiliğinden yayına çıkmaz.
        $this->artisan('media:intake', ['--apply' => true])
            ->expectsOutputToContain('Hiçbir görsel onaysız yayına çıkmaz.')
            ->assertExitCode(0);
    }

    /** @param list<array<string,string>> $kayitlar */
    private function manifestYaz(array $kayitlar): void
    {
        Storage::disk('local')->put(
            'media-import/approved-media-manifest.json',
            json_encode(['recipes' => $kayitlar], JSON_UNESCAPED_UNICODE)
        );
    }
}
