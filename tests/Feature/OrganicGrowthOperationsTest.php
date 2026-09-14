<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ContentOpportunity;
use App\Models\IntegrityReport;
use App\Models\Recipe;
use App\Models\SeoBacklinkCandidate;
use App\Models\SeoIssue;
use App\Models\User;
use App\Services\Seo\OrganicGrowthService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class OrganicGrowthOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'https://housechefcyprus.com']);
        Http::preventStrayRequests();
    }

    public function test_growth_pages_and_mutations_require_verified_admin(): void
    {
        $this->get(route('admin.seo.growth'))->assertRedirect();
        $this->post(route('admin.seo.growth.refresh'))->assertRedirect();
        $this->actingAs(User::factory()->create(['role' => 'member', 'email_verified_at' => now()]));
        $this->get(route('admin.seo.growth'))->assertForbidden();
        $this->post(route('admin.seo.growth.refresh'))->assertForbidden();
        $this->post(route('admin.seo.growth.import'))->assertForbidden();
        $this->actingAs($this->admin())
            ->get(route('admin.seo.growth'))
            ->assertOk()
            ->assertSee('Araştırma adayı')
            ->assertSee('7 Dilde Otonom Büyüme')
            ->assertSee('GEO Korpusları');
    }

    public function test_refresh_is_bounded_idempotent_and_preserves_review_decisions(): void
    {
        foreach (range(1, 3) as $index) {
            $this->issue($index);
            $this->recipe($index);
        }
        $service = app(OrganicGrowthService::class);
        $first = $service->refresh(2);
        $this->assertSame(2, $first['opportunities_created']);
        $this->assertSame(2, $first['candidates_created']);
        $candidate = SeoBacklinkCandidate::first();
        $candidate->update(['status' => 'rejected', 'review_note' => 'Alakasız', 'reviewed_at' => now()]);
        ContentOpportunity::first()->update(['status' => 'dismissed', 'reviewed_at' => now()]);
        $second = $service->refresh(2);
        $this->assertSame(1, $second['opportunities_created']);
        $this->assertSame(1, $second['candidates_created']);
        $third = $service->refresh(1000);
        $this->assertSame(0, $third['opportunities_created']);
        $this->assertSame(0, $third['candidates_created']);
        $this->assertSame('rejected', $candidate->fresh()->status);
        $this->assertSame('dismissed', ContentOpportunity::first()->status);
        $this->assertSame(3, SeoBacklinkCandidate::count());
        $this->assertSame(3, Recipe::where('status', 'published')->count());
        $this->assertFalse($third['publishes_content']);
        $this->assertSame(0, $third['external_http_requests']);
        $this->assertSame(0, $third['outreach_sent']);
    }

    public function test_sources_map_to_real_recipe_target_and_exclude_deleted_or_draft_recipes(): void
    {
        $recipe = $this->recipe(1);
        $this->recipe(2)->delete();
        $this->recipe(3)->update(['status' => 'draft']);
        $result = app(OrganicGrowthService::class)->refresh();
        $this->assertSame(1, $result['candidates_created']);
        $this->assertSame('https://housechefcyprus.com/tarifler/recipe-1', SeoBacklinkCandidate::first()->target_url);
        $this->assertSame($recipe->public_id, SeoBacklinkCandidate::first()->evidence['recipe_public_id']);
    }

    public function test_issue_work_tracks_resolution_and_recurrence_without_overwriting_human_decisions(): void
    {
        $issue = $this->issue(21);
        $service = app(OrganicGrowthService::class);
        $service->refresh(1);
        $work = ContentOpportunity::where('normalized_key', 'growth-issue-'.$issue->fingerprint)->firstOrFail();

        $issue->update(['resolved_at' => now()]);
        $result = $service->refresh(1);
        $this->assertSame(1, $result['opportunities_resolved']);
        $this->assertSame('resolved', $work->fresh()->status);

        $issue->update(['resolved_at' => null, 'reopened_at' => now(), 'severity' => 'critical']);
        $result = $service->refresh(1);
        $this->assertSame(1, $result['opportunities_reopened']);
        $this->assertSame('open', $work->fresh()->status);
        $this->assertSame(100, $work->fresh()->priority);
        $this->assertSame(1, ContentOpportunity::where('normalized_key', $work->normalized_key)->count());

        $work->update(['status' => 'declined', 'reviewed_at' => now()]);
        $issue->update(['resolved_at' => now()]);
        $service->refresh(1);
        $this->assertSame('declined', $work->fresh()->status);
        Http::assertNothingSent();
    }

    public function test_deleted_issue_is_not_assumed_fixed(): void
    {
        $issue = $this->issue(22);
        $service = app(OrganicGrowthService::class);
        $service->refresh(1);
        $issue->delete();
        $service->refresh(1);
        $this->assertSame('open', ContentOpportunity::first()->status);
    }

    public function test_rotating_sources_do_not_get_stuck_on_unsafe_records(): void
    {
        $this->recipe(1)->update(['source_url' => 'http://127.0.0.1/private']);
        $this->recipe(2);
        $service = app(OrganicGrowthService::class);
        $this->assertSame(0, $service->refresh(1)['candidates_created']);
        $this->assertSame(1, $service->refresh(1)['candidates_created']);
    }

    public function test_unsafe_sources_and_foreign_targets_are_rejected(): void
    {
        $service = app(OrganicGrowthService::class);
        foreach (['http://127.0.0.1/a', 'http://169.254.169.254/a', 'http://[::1]/a', 'http://2130706433/a', 'http://x.internal/a', 'file:///tmp/a', 'https://u:p@source.org/a', 'https://housechefcyprus.com/a', 'https://www.housechefcyprus.com/a', 'https://source.org/%0aheader', 'https://source.org:8080/a'] as $url) {
            $this->assertNull($service->candidateUrls($url, 'https://housechefcyprus.com/tarifler/a'), $url);
        }
        $this->assertNull($service->candidateUrls('https://source.org/a', 'https://other.org/a'));
        $this->assertNull($service->candidateUrls('https://source.org/a', 'http://housechefcyprus.com/a'));
        $this->assertNotNull($service->candidateUrls('https://source.org/a#fragment', 'https://housechefcyprus.com/tarifler/a'));
    }

    public function test_csv_import_deduplicates_and_cannot_set_observed_status(): void
    {
        $admin = $this->admin();
        $row = "https://source.org/a,https://housechefcyprus.com/tarifler/a,Kaynak araştırması\n";
        $file = UploadedFile::fake()->createWithContent('candidates.csv', "source_url,target_url,evidence\n".$row.$row);
        $result = app(OrganicGrowthService::class)->importCsv($file, $admin->public_id);
        $this->assertSame(['rows' => 2, 'created' => 1, 'duplicates' => 1], $result);
        $candidate = SeoBacklinkCandidate::first();
        $this->assertSame('candidate', $candidate->status);
        $candidate->update(['status' => 'rejected', 'reviewed_at' => now()]);
        app(OrganicGrowthService::class)->importCsv($file, $admin->public_id);
        $this->assertSame('rejected', $candidate->fresh()->status);
        $this->actingAs($admin)->post(route('admin.seo.growth.import'), ['csv' => UploadedFile::fake()->createWithContent('bad.csv', "source_url,target_url,status\nhttps://source.org/a,https://housechefcyprus.com/a,observed")])->assertSessionHasErrors('csv');
    }

    public function test_invalid_late_csv_row_rolls_back_entire_import(): void
    {
        $file = UploadedFile::fake()->createWithContent('candidates.csv', "source_url,target_url\nhttps://source.org/a,https://housechefcyprus.com/a\nhttp://localhost/a,https://housechefcyprus.com/a\n");
        try {
            app(OrganicGrowthService::class)->importCsv($file, $this->admin()->public_id);
            $this->fail('Unsafe row accepted');
        } catch (ValidationException) {
            $this->assertSame(0, SeoBacklinkCandidate::count());
        }
    }

    public function test_csv_row_and_file_size_limits_are_enforced(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.seo.growth.import'), ['csv' => UploadedFile::fake()->create('large.csv', 10241, 'text/csv')])->assertSessionHasErrors('csv');
        $file = UploadedFile::fake()->createWithContent('rows.csv', "source_url,target_url\n".str_repeat("https://source.org/a,https://housechefcyprus.com/a\n", 10001));
        try {
            app(OrganicGrowthService::class)->importCsv($file, $admin->public_id);
            $this->fail('Row limit not enforced');
        } catch (ValidationException) {
            $this->assertSame(0, SeoBacklinkCandidate::count());
        }
    }

    public function test_observed_links_require_explicit_review_and_evidence_and_counts_stay_separate(): void
    {
        $this->recipe(1);
        $service = app(OrganicGrowthService::class);
        $service->refresh();
        $candidate = SeoBacklinkCandidate::first();
        $url = route('admin.seo.growth.review', $candidate->public_id);
        $this->actingAs($this->admin())->post($url, ['status' => 'observed', 'review_note' => 'Bağlantı kaynakta görüldü'])->assertSessionHasErrors('status');
        $this->post($url, ['status' => 'reviewing'])->assertSessionHasNoErrors();
        $this->post($url, ['status' => 'observed'])->assertSessionHasErrors('review_note');
        $this->post($url, ['status' => 'observed', 'review_note' => 'Kaynak sayfada hedef bağlantı görüldü.'])->assertSessionHasNoErrors();
        $this->assertSame(1, $service->snapshot()['observed_links']);
        $this->assertSame(0, $service->snapshot()['research_candidates']);
        $this->assertNull($service->snapshot()['organic_metrics']['google_clicks']);
    }

    public function test_refresh_logs_outcome_and_releases_lock_after_failure(): void
    {
        $this->issue(1);
        ContentOpportunity::creating(function (): void {
            throw new \RuntimeException('Controlled failure');
        });
        try {
            app(OrganicGrowthService::class)->refresh();
            $this->fail('Expected failure');
        } catch (\RuntimeException) {
            $this->assertSame('failed', IntegrityReport::latest('id')->first()->status);
            $lock = Cache::lock('seo:growth-refresh', 300);
            $this->assertTrue($lock->get());
            $lock->release();
        } finally {
            ContentOpportunity::flushEventListeners();
        }
    }

    public function test_schedule_is_daily_and_commands_validate_bounds(): void
    {
        $this->artisan('seo:growth-refresh', ['--limit' => 0])->assertExitCode(2);
        $this->artisan('seo:growth-refresh', ['--limit' => 1001])->assertExitCode(2);
        $this->artisan('seo:growth-refresh', ['--limit' => 1, '--json' => true])->assertSuccessful();
        $events = app(Schedule::class)->events();
        $event = collect($events)->first(fn ($event) => $event->description === 'seo_growth_refresh');
        $this->assertNotNull($event);
        $this->assertSame('49 1 * * *', $event->expression);
        $this->assertTrue($event->withoutOverlapping);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }

    private function recipe(int $index): Recipe
    {
        return Recipe::create(['title' => 'Recipe '.$index, 'slug' => 'recipe-'.$index, 'status' => 'published', 'verification_status' => 'verified', 'source_url' => 'https://source.org/recipe-'.$index]);
    }

    private function issue(int $index): SeoIssue
    {
        return SeoIssue::create(['fingerprint' => hash('sha256', 'issue-'.$index), 'category' => 'technical', 'severity' => 'error', 'message' => 'Test issue '.$index, 'first_detected_at' => now(), 'last_detected_at' => now()]);
    }
}
