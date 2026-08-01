<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/**
 * Static, repeatable UX guardrails. This service intentionally does not claim
 * browser, assistive-technology, or Core Web Vitals measurements: those need
 * a real browser and production telemetry.
 */
final class ProductExperienceAuditService
{
    public const RULE_VERSION = '2026-08-01';

    /** @var array<string, list<string>> */
    private const SURFACES = [
        'public' => ['welcome.blade.php', 'recipes/index.blade.php', 'recipes/show.blade.php', 'blog/index.blade.php', 'blog/show.blade.php'],
        'member' => ['pantry/index.blade.php', 'favorites/index.blade.php', 'ingredients/explore.blade.php', 'cooking-sessions/show.blade.php', 'ai-kitchen/index.blade.php', 'profile.blade.php'],
        'admin' => ['admin/dashboard.blade.php', 'components/admin-sidebar.blade.php', 'components/admin-layout.blade.php'],
    ];

    /** @return array<string, mixed> */
    public function audit(string $scope = 'all'): array
    {
        $views = collect(self::SURFACES)
            ->when($scope !== 'all', fn ($surfaces) => $surfaces->only($scope))
            ->flatten()
            ->map(fn (string $file) => resource_path('views/'.$file))
            ->filter(fn (string $path) => File::exists($path))
            ->values();

        $contents = $views->mapWithKeys(fn (string $path) => [$path => File::get($path)]);
        $css = File::get(resource_path('css/app.css'));
        $findings = [
            'missing_skip_link' => ! str_contains(File::get(resource_path('views/layouts/site.blade.php')), 'İçeriğe geç'),
            'missing_reduced_motion' => ! str_contains($css, 'prefers-reduced-motion'),
            'missing_theme_tokens' => ! str_contains($css, '--hc-background-page') || ! str_contains($css, 'html.dark'),
            'missing_empty_state' => $contents->filter(fn (string $view) => str_contains($view, 'empty') || str_contains($view, 'boş'))->isEmpty(),
            'missing_error_state' => $contents->filter(fn (string $view) => str_contains($view, 'alert') || str_contains($view, '$errors'))->isEmpty(),
            'hard_coded_view_colors' => $contents->sum(fn (string $view) => preg_match_all('/(?:bg|text|border)-\[#|#[0-9a-fA-F]{3,8}\b/', $view)),
        ];
        $critical = collect($findings)->filter(fn (mixed $value, string $key) => $key !== 'hard_coded_view_colors' && $value === true)->keys()->values()->all();
        $scores = [
            'public' => $this->score($contents, ['recipe', 'blog', 'welcome']),
            'member' => $this->score($contents, ['pantry', 'favorites', 'cooking']),
            'admin' => $this->score($contents, ['admin']),
        ];
        $scores['overall'] = (int) floor(array_sum($scores) / 3);

        $report = [
            'rule_version' => self::RULE_VERSION,
            'generated_at' => now()->toIso8601String(),
            'scope' => $scope,
            'status' => $critical === [] ? 'needs_browser_qa' : 'attention_required',
            'scores' => $scores,
            'findings' => $findings,
            'critical_blockers' => $critical,
            'surface_files_checked' => $views->map(fn (string $path) => str_replace(resource_path('views').'/', '', $path))->all(),
            'manual_qa_required' => [
                'screen_reader_navigation', 'keyboard_focus_order', 'color_contrast_measurement',
                'responsive_viewports', '200_percent_zoom', 'core_web_vitals_field_data',
            ],
            'measurement_note' => 'Skorlar yalnız statik arayüz sözleşmesi ön kontrolüdür; canlı kullanıcı, tarayıcı veya CWV ölçümü değildir.',
        ];

        Cache::forever('ux.audit.latest', $report);

        return $report;
    }

    /** @return array<string, mixed> */
    public function latest(): array
    {
        $latest = Cache::get('ux.audit.latest');

        return is_array($latest) ? $latest : $this->audit();
    }

    /** @param Collection<string, string> $contents @param list<string> $hints */
    private function score(Collection $contents, array $hints): int
    {
        $joined = $contents->implode("\n");
        $signals = [
            str_contains($joined, 'hc-v3-card'),
            str_contains($joined, 'hc-v3-btn'),
            str_contains($joined, 'aria-'),
            str_contains($joined, 'dark:'),
            str_contains($joined, 'sm:') || str_contains($joined, 'md:') || str_contains($joined, 'lg:'),
            collect($hints)->contains(fn (string $hint) => str_contains($joined, $hint)),
        ];

        // A static check never reaches 100: real browser and assistive-tech QA remains required.
        return min(85, 40 + (collect($signals)->filter()->count() * 7));
    }
}
