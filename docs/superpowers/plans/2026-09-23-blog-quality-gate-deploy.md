# Blog Quality Gate and Controlled Deploy Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the permissive blog auto-drip check with an explainable, people-first quality gate, set the production cadence to three human-approved posts per day, and deploy the code without publishing any draft during the deployment.

**Architecture:** A focused `BlogPublicationReadinessService` evaluates one post and returns a typed result containing `passed`, `score`, and explicit issues. The admin publish action, scheduled publisher, and auto-drip command consume the same service so no publication path can bypass the rules. A read-only command summarizes all drafts for editorial batching; deployment uses the existing release script and verifies draft counts before and after.

**Tech Stack:** PHP 8.4, Laravel, Eloquent, PHPUnit/Laravel feature tests, Artisan, Bash release tooling.

**Spec:** `docs/superpowers/specs/2026-09-23-blog-seo-geo-quality-design.md`

## Global Constraints

- No agent changes a blog post to `published` or changes `published_at` as part of this implementation or deploy.
- New and updated content remains draft-only.
- The locale enablement configuration is not modified.
- Content imports are JSON-only and live on the production server, not the local repository.
- Minimum article length is 900 words.
- A ready article requires 3-6 secondary keywords and 3-6 FAQ entries.
- A ready article requires a verified recipe relation, source notes, valid metadata, originality, fact-check, editorial approval, and recorded human approval.
- The production schedule is capped at three posts per day; publishing remains a separate human-owned operation.

---

## File Structure

- Create `app/Services/Seo/BlogPublicationReadinessService.php`: single source of truth for publication eligibility.
- Create `app/Console/Commands/SeoBlogDraftReadiness.php`: read-only JSON/text inventory of draft readiness.
- Modify `app/Http/Controllers/Admin/BlogPostController.php`: use the shared readiness service before manual publication.
- Modify `app/Console/Commands/SeoAutoDrip.php`: report candidates with the shared gate and require explicit human approval; preserve dry-run behavior.
- Modify `app/Console/Commands/PublishScheduledBlogPosts.php`: use the same gate for scheduled posts.
- Modify `routes/console.php`: change the configured daily maximum from five to three.
- Create `tests/Feature/BlogPublicationReadinessTest.php`: focused gate boundary tests.
- Modify `tests/Feature/SeoContentAutomationTest.php`: command and publication-path integration tests.

### Task 1: Central publication-readiness service

**Files:**
- Create: `app/Services/Seo/BlogPublicationReadinessService.php`
- Create: `tests/Feature/BlogPublicationReadinessTest.php`

**Interfaces:**
- Consumes: `App\Models\BlogPost` and its `recipes` relation.
- Produces: `BlogPublicationReadinessService::evaluate(BlogPost $post): array{passed: bool, score: int, issues: list<string>, metrics: array<string, int|bool>}`.

- [ ] **Step 1: Write failing tests for the complete ready state and every blocking family**

```php
public function test_ready_post_passes_every_people_first_rule(): void
{
    $post = $this->readyPost();
    $result = app(BlogPublicationReadinessService::class)->evaluate($post);

    $this->assertTrue($result['passed']);
    $this->assertSame([], $result['issues']);
    $this->assertGreaterThanOrEqual(900, $result['metrics']['word_count']);
}

public function test_899_words_missing_human_approval_and_sources_are_blocked(): void
{
    $post = $this->readyPost([
        'body' => implode(' ', array_fill(0, 899, 'kelime')),
        'human_approved_at' => null,
        'human_approved_by_user_id' => null,
        'source_notes' => null,
    ]);

    $result = app(BlogPublicationReadinessService::class)->evaluate($post);

    $this->assertFalse($result['passed']);
    $this->assertContains('İçerik en az 900 kelime olmalıdır.', $result['issues']);
    $this->assertContains('İnsan yayın onayı kaydı gerekli.', $result['issues']);
    $this->assertContains('Kaynak/fact-check notu gerekli.', $result['issues']);
}
```

- [ ] **Step 2: Run the focused tests and verify failure**

Run: `php artisan test tests/Feature/BlogPublicationReadinessTest.php`

Expected: FAIL because `BlogPublicationReadinessService` does not exist.

- [ ] **Step 3: Implement the service with explicit metrics and issues**

```php
final class BlogPublicationReadinessService
{
    /** @return array{passed:bool,score:int,issues:list<string>,metrics:array<string,int|bool>} */
    public function evaluate(BlogPost $post): array
    {
        $plain = trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $post->body)));
        preg_match_all('/[\p{L}\p{N}]+/u', $plain, $words);
        $wordCount = count($words[0]);
        $secondaryCount = count($post->secondary_keywords ?? []);
        $faqCount = count($post->faq_blocks ?? []);
        $issues = [];

        if ($wordCount < 900) $issues[] = 'İçerik en az 900 kelime olmalıdır.';
        if ($secondaryCount < 3 || $secondaryCount > 6) $issues[] = 'Üç ila altı ikincil anahtar kelime gerekli.';
        if ($faqCount < 3 || $faqCount > 6) $issues[] = 'Üç ila altı gerçek SSS gerekli.';
        if (blank($post->source_notes)) $issues[] = 'Kaynak/fact-check notu gerekli.';
        if ($post->recipes()->count() === 0) $issues[] = 'En az bir doğrulanmış tarif bağlantısı gerekli.';
        if (blank($post->human_approved_at) || blank($post->human_approved_by_user_id)) $issues[] = 'İnsan yayın onayı kaydı gerekli.';

        return [
            'passed' => $issues === [],
            'score' => max(0, 100 - count($issues) * 8),
            'issues' => $issues,
            'metrics' => ['word_count' => $wordCount, 'secondary_keyword_count' => $secondaryCount, 'faq_count' => $faqCount],
        ];
    }
}
```

The final implementation must also preserve the existing purpose, intent, primary keyword, metadata, fact-check, originality, and editorial checks from `ContentQualityService` and validate meta title at 60 characters maximum and meta description at 140-158 characters.

- [ ] **Step 4: Run the focused tests**

Run: `php artisan test tests/Feature/BlogPublicationReadinessTest.php`

Expected: PASS.

- [ ] **Step 5: Commit the service and tests**

```bash
git add app/Services/Seo/BlogPublicationReadinessService.php tests/Feature/BlogPublicationReadinessTest.php
git commit -m "feat(seo): enforce blog publication readiness"
```

### Task 2: Apply one gate to every publication path

**Files:**
- Modify: `app/Http/Controllers/Admin/BlogPostController.php`
- Modify: `app/Console/Commands/SeoAutoDrip.php`
- Modify: `app/Console/Commands/PublishScheduledBlogPosts.php`
- Modify: `tests/Feature/SeoContentAutomationTest.php`

**Interfaces:**
- Consumes: `BlogPublicationReadinessService::evaluate()` from Task 1.
- Produces: identical blocking behavior for manual, scheduled, and auto-drip publication paths.

- [ ] **Step 1: Add failing integration tests**

```php
public function test_all_publication_paths_reject_a_short_post_without_human_approval(): void
{
    $post = $this->makePost([
        'body' => implode(' ', array_fill(0, 899, 'kelime')),
        'status' => 'scheduled',
        'scheduled_at' => now()->subMinute(),
        'editorial_status' => 'approved',
        'fact_check_status' => 'passed',
        'originality_status' => 'passed',
    ]);

    $this->artisan('blog:publish-scheduled --json')->assertSuccessful();
    $this->assertSame('scheduled', $post->fresh()->status);
}

public function test_auto_drip_dry_run_never_changes_status(): void
{
    $post = $this->fullyReadyPost();
    $this->artisan('seo:auto-drip --dry-run --limit=3')->assertSuccessful();
    $this->assertSame('draft', $post->fresh()->status);
    $this->assertNull($post->fresh()->published_at);
}
```

- [ ] **Step 2: Run tests and verify the existing paths fail the new expectations**

Run: `php artisan test tests/Feature/SeoContentAutomationTest.php`

Expected: FAIL because the publication paths use different and weaker gates.

- [ ] **Step 3: Inject and call the shared service**

```php
$assessment = $readiness->evaluate($post);
abort_unless($assessment['passed'], 422, implode(' ', $assessment['issues']));
```

For command paths, skip a failing post and include its issue list in JSON output. Do not change status while `--dry-run` is active.

- [ ] **Step 4: Run the integration tests**

Run: `php artisan test tests/Feature/SeoContentAutomationTest.php tests/Feature/BlogPublicationReadinessTest.php`

Expected: PASS.

- [ ] **Step 5: Commit unified publication behavior**

```bash
git add app/Http/Controllers/Admin/BlogPostController.php app/Console/Commands/SeoAutoDrip.php app/Console/Commands/PublishScheduledBlogPosts.php tests/Feature/SeoContentAutomationTest.php
git commit -m "fix(seo): unify blog publication gates"
```

### Task 3: Add a read-only draft readiness report

**Files:**
- Create: `app/Console/Commands/SeoBlogDraftReadiness.php`
- Modify: `tests/Feature/SeoContentAutomationTest.php`

**Interfaces:**
- Consumes: `BlogPublicationReadinessService::evaluate()`.
- Produces: `seo:blog-draft-readiness {--json} {--limit=0}` with totals and per-post failures; it performs no database writes.

- [ ] **Step 1: Write an idempotence test**

```php
public function test_draft_readiness_report_is_read_only_and_explainable(): void
{
    $post = $this->makePost(['status' => 'draft']);
    $before = $post->fresh()->getAttributes();

    $this->artisan('seo:blog-draft-readiness --json')
        ->expectsOutputToContain('"drafts":1')
        ->expectsOutputToContain('"ready":0')
        ->assertSuccessful();

    $this->assertSame($before, $post->fresh()->getAttributes());
}
```

- [ ] **Step 2: Run the test and verify failure**

Run: `php artisan test tests/Feature/SeoContentAutomationTest.php --filter=draft_readiness`

Expected: FAIL because the command is undefined.

- [ ] **Step 3: Implement the read-only command**

```php
$rows = BlogPost::query()->where('status', 'draft')->orderBy('id')->get()
    ->map(fn (BlogPost $post): array => [
        'id' => $post->id,
        'slug' => $post->slug,
        ...$readiness->evaluate($post),
    ]);

$result = [
    'drafts' => $rows->count(),
    'ready' => $rows->where('passed', true)->count(),
    'blocked' => $rows->where('passed', false)->count(),
    'posts' => $limit > 0 ? $rows->take($limit)->values() : $rows->values(),
];
```

The command must not call `save`, `update`, `create`, `delete`, or dispatch jobs.

- [ ] **Step 4: Run the feature test**

Run: `php artisan test tests/Feature/SeoContentAutomationTest.php --filter=draft_readiness`

Expected: PASS.

- [ ] **Step 5: Commit the report command**

```bash
git add app/Console/Commands/SeoBlogDraftReadiness.php tests/Feature/SeoContentAutomationTest.php
git commit -m "feat(seo): report blog draft readiness"
```

### Task 4: Set the controlled daily cadence to three

**Files:**
- Modify: `routes/console.php`
- Modify: `tests/Feature/SeoContentAutomationTest.php`

**Interfaces:**
- Consumes: `seo:auto-drip --limit=3`.
- Produces: one daily scheduler invocation capped at three eligible, human-approved posts.

- [ ] **Step 1: Add a scheduler configuration assertion**

```php
public function test_auto_drip_schedule_uses_daily_limit_of_three(): void
{
    $source = file_get_contents(base_path('routes/console.php'));
    $this->assertStringContainsString("Artisan::call('seo:auto-drip', ['--limit' => 3])", $source);
}
```

- [ ] **Step 2: Run the assertion and verify failure**

Run: `php artisan test tests/Feature/SeoContentAutomationTest.php --filter=daily_limit`

Expected: FAIL because the current limit is five.

- [ ] **Step 3: Change only the configured limit**

```php
Schedule::call(fn () => app(SchedulerHealthService::class)->run(
    'seo_auto_drip',
    fn () => Artisan::call('seo:auto-drip', ['--limit' => 3])
))->name('seo_auto_drip')->dailyAt('08:00')->withoutOverlapping();
```

- [ ] **Step 4: Run the focused and complete test suites**

Run: `php artisan test tests/Feature/SeoContentAutomationTest.php tests/Feature/BlogPublicationReadinessTest.php`

Expected: PASS.

Run: `php artisan test`

Expected: PASS with no failures.

- [ ] **Step 5: Commit the cadence change**

```bash
git add routes/console.php tests/Feature/SeoContentAutomationTest.php
git commit -m "chore(seo): cap daily blog cadence at three"
```

### Task 5: Production deploy without publishing drafts

**Files:**
- Verify: `scripts/release.sh`
- Verify: `scripts/deploy-hostinger-release.sh`
- No content file is created locally.

**Interfaces:**
- Consumes: committed code on `visual-redesign-v2` and the existing release scripts.
- Produces: active production release SHA plus before/after draft and published counts.

- [ ] **Step 1: Capture production invariants before deployment**

Run:

```bash
ssh -p 65002 u584361532@147.93.93.105 \
  'cd ~/domains/housechefcyprus.com/current && php artisan tinker --execute="echo json_encode([\"drafts\"=>App\\Models\\BlogPost::where(\"status\",\"draft\")->count(),\"published\"=>App\\Models\\BlogPost::publiclyVisible()->count()]);"'
```

Expected: JSON with numeric `drafts` and `published` values; save them in the deployment transcript, not the repository.

- [ ] **Step 2: Run release validation without side effects**

Run: `scripts/release.sh --dry-run`

Expected: branch is `visual-redesign-v2`, remote comparison succeeds, and the script prints the planned artifact and release SHA.

- [ ] **Step 3: Run the full release**

Run: `scripts/release.sh`

Expected: complete test suite passes, artifact checksum verifies, active release changes to the committed SHA, and `/`, `/tarifler`, `/blog`, and `/health` return HTTP 200.

- [ ] **Step 4: Verify no draft was published by deployment**

Run the Step 1 count command again and compare results.

Expected: `drafts` and `published` are unchanged. Also run:

```bash
ssh -p 65002 u584361532@147.93.93.105 \
  'cd ~/domains/housechefcyprus.com/current && php artisan seo:blog-draft-readiness --json --limit=5'
```

Expected: valid JSON, no database mutation, and explicit blocking reasons for non-ready drafts.

- [ ] **Step 5: Verify the live scheduler configuration and active SHA**

Run:

```bash
ssh -p 65002 u584361532@147.93.93.105 \
  'cd ~/domains/housechefcyprus.com/current && php artisan schedule:list | grep seo_auto_drip && readlink -f ~/domains/housechefcyprus.com/current'
```

Expected: the daily job is present and the active directory corresponds to the deployed commit.

## Post-deploy operating rule

Keep the configured ceiling at three new articles per day for at least four weeks. Publish only posts that pass the complete gate and receive explicit human approval. Review Search Console weekly; increase toward five per day only when valid indexed-page growth, impressions, and clicks rise without duplicate/crawled-not-indexed warnings. Reduce to one per day or pause when indexation quality or site-wide visibility deteriorates.
