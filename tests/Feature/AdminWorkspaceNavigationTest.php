<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\IntegrityReport;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminWorkspaceNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_numbered_placeholder_domains_are_not_publisher_prospects(): void
    {
        foreach (app(\App\Services\Seo\BacklinkGrowthService::class)->prospects() as $prospect) {
            $this->assertDoesNotMatchRegularExpression('/^foodblog[0-9]+\\.com$/i', $prospect['domain']);
        }
    }

    public function test_existing_pages_share_three_workspaces_and_keep_the_correct_tab_selected(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]));
        foreach (config('admin_workspaces') as $area) {
            foreach ($area['tabs'] as $tab) {
                $response = $this->get(route($tab['route']))->assertOk();
                $document = new DOMDocument;
                @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent(), LIBXML_NONET);
                $xpath = new DOMXPath($document);
                $sidebars = $xpath->query('//nav[contains(@class,"admin-sidebar")]//section[@aria-label="Veri ve SEO"]');
                $this->assertGreaterThan(0, $sidebars->length);
                foreach ($sidebars as $sidebar) {
                    $this->assertSame(3, $xpath->query('.//a', $sidebar)->length);
                    $this->assertSame(1, $xpath->query('.//a[@aria-current="page"]', $sidebar)->length);
                }
                $this->assertSame(count($area['tabs']), $xpath->query('//nav[@data-admin-workspace-tabs]//a')->length);
                $this->assertSame(1, $xpath->query('//nav[@data-admin-workspace-tabs]//a[@aria-current="page"]')->length);
            }
        }
    }

    public function test_seo_overview_uses_stored_measurements_instead_of_static_disconnected_copy(): void
    {
        config(['seo.search_console.site_url' => 'https://housechefcyprus.com/']);
        IntegrityReport::query()->create(['guard' => 'seo:search-console-measurement', 'status' => 'success', 'payload' => [
            'property' => 'https://housechefcyprus.com/', 'search_type' => 'web', 'source' => 'ui_import',
            'observed_at' => now()->toIso8601String(), 'start_date' => now()->subDays(28)->toDateString(), 'end_date' => now()->subDay()->toDateString(),
            'clicks' => 13, 'impressions' => 130, 'position' => 12, 'queries' => [],
        ]]);
        $this->actingAs(User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]))
            ->get(route('admin.seo.index'))->assertOk()->assertSee('13 tıklama, 130 gösterim')
            ->assertDontSee('Bağımsız backlink doğrulaması ve Search Console ölçümü bağlı değil.');
        $this->get(route('admin.seo.opportunities'))->assertOk()
            ->assertDontSee('Günlük 100 SEO')->assertDontSee('Otorite Skoru');
    }
}
