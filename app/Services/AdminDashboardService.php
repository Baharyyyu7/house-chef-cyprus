<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\BlogPost;
use App\Models\ContactRequest;
use App\Models\DataQualityIssue;
use App\Models\Ingredient;
use App\Models\Media;
use App\Models\PersonalizationExportRequest;
use App\Models\Recipe;
use App\Models\RecipeNutritionSnapshot;
use App\Models\RestoreHistory;
use App\Models\User;
use App\Services\Observability\MetricService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class AdminDashboardService
{
    private const PERIODS = [1, 7, 30, 90];

    public function __construct(
        private readonly DataQualityService $quality,
        private readonly HealthService $health,
        private readonly MetricService $metrics,
        private readonly QueueHealthService $queue,
        private readonly SchedulerHealthService $scheduler,
        private readonly BackupService $backups,
        private readonly ProductionOperationsService $productionOperations,
    ) {}

    /** @return array<string, mixed> */
    public function dashboard(int $requestedPeriod): array
    {
        $period = in_array($requestedPeriod, self::PERIODS, true) ? $requestedPeriod : 7;

        return Cache::remember("admin.dashboard.v3.{$period}", now()->addMinutes(3), function () use ($period): array {
            $from = now()->subDays($period);
            $previousFrom = now()->subDays($period * 2);
            $qualityScores = $this->quality->categoryScores();
            $queue = $this->queue->snapshot();
            $scheduler = $this->scheduler->snapshot();
            $health = $this->health->snapshot();
            $nutritionCompleteness = RecipeNutritionSnapshot::query()
                ->whereNull('invalidated_at')
                ->avg('completeness_percentage');
            $ingredientCount = Ingredient::query()->count();
            $allergenLinkedIngredients = Ingredient::query()->whereHas('allergenDetails')->count();
            $allergenCompleteness = $ingredientCount > 0 ? round(($allergenLinkedIngredients / $ingredientCount) * 100) : null;
            $backup = $this->lastBackup();
            $contentOperations = $this->contentOperations();
            $memberOperations = $this->memberOperations($from);
            $communicationOperations = $this->communicationOperations($from);

            $cards = [
                $this->metric('toplam_tarif', 'Toplam tarif', Recipe::query()->count(), Recipe::query()->whereBetween('created_at', [$previousFrom, $from])->count(), Recipe::query()->where('created_at', '>=', $from)->count(), 'Tarif kataloğu'),
                $this->metric('yayinda', 'Yayındaki tarif', Recipe::query()->where('status', 'published')->count(), Recipe::query()->where('status', 'published')->whereBetween('published_at', [$previousFrom, $from])->count(), Recipe::query()->where('status', 'published')->where('published_at', '>=', $from)->count(), 'Yayın durumu'),
                $this->metric('dogrulanmis', 'Doğrulanmış', Recipe::query()->where('verification_status', 'verified')->count(), Recipe::query()->where('verification_status', 'verified')->whereBetween('updated_at', [$previousFrom, $from])->count(), Recipe::query()->where('verification_status', 'verified')->where('updated_at', '>=', $from)->count(), 'Editoryal güven'),
                $this->metric('inceleme', 'İnceleme bekleyen', Recipe::query()->whereIn('verification_status', ['needs_review', 'unreviewed', 'partially_verified'])->count(), 0, 0, 'İçerik kuyruğu', true),
                $this->metric('kullanicilar', 'Toplam kullanıcı', User::query()->count(), User::query()->whereBetween('created_at', [$previousFrom, $from])->count(), User::query()->where('created_at', '>=', $from)->count(), 'Üye büyümesi'),
                $this->metric('malzemeler', 'Toplam malzeme', $ingredientCount, Ingredient::query()->whereBetween('created_at', [$previousFrom, $from])->count(), Ingredient::query()->where('created_at', '>=', $from)->count(), 'Malzeme verisi'),
                $this->metric('kalite', 'Veri kalitesi', $qualityScores['overall'], null, null, 'Açık issue ağırlığına göre', false, '%'),
                $this->metric('beslenme', 'Beslenme tamlığı', $nutritionCompleteness === null ? null : round((float) $nutritionCompleteness), null, null, 'Geçerli snapshot ortalaması', false, '%'),
                $this->metric('alerjen', 'Alerjen kapsamı', $allergenCompleteness, null, null, 'Alerjen ilişkisi olan malzemeler', false, '%'),
                $this->metric('failed_jobs', 'Başarısız işler', (int) $queue['failed_jobs'], null, null, 'Queue sağlık özeti', true),
            ];

            return [
                'period' => $period,
                'periodLabel' => $this->periodLabel($period),
                'updatedAt' => now()->toIso8601String(),
                'cards' => $cards,
                'charts' => [
                    $this->recipeTrend($from, $period),
                    $this->distributionChart('Mutfak dağılımı', 'Tariflerin mutfaklara göre gerçek dağılımı.', 'bar', DB::table('recipes')->leftJoin('cuisines', 'recipes.cuisine_id', '=', 'cuisines.id')->selectRaw("COALESCE(cuisines.name, 'Belirsiz') as label, COUNT(recipes.id) as total")->groupBy('label')->orderByDesc('total')->limit(8)->pluck('total', 'label')->all()),
                    $this->distributionChart('Doğrulama durumu', 'Tariflerin editoryal doğrulama görünümü.', 'doughnut', Recipe::query()->selectRaw("COALESCE(verification_status, 'Belirsiz') as label, COUNT(*) as total")->groupBy('label')->orderByDesc('total')->pluck('total', 'label')->all()),
                ],
                'qualityScores' => $qualityScores,
                'operations' => [
                    'content' => $contentOperations,
                    'members' => $memberOperations,
                    'communication' => $communicationOperations,
                    'restore' => $this->restoreOperations(),
                ],
                'system' => [
                    'health' => $health,
                    'queue' => $queue,
                    'scheduler' => $scheduler,
                    'backup' => $backup,
                    'media' => [
                        'failed_processing' => Media::query()->where('processing_status', 'failed')->count(),
                        'pending_thumbnails' => Media::query()->whereIn('thumbnail_status', ['pending', 'processing'])->count(),
                    ],
                    'requestMetrics' => $this->metrics->summary($period),
                    'production' => [
                        'release_state' => (string) (config('production.release_state') ?: 'development'),
                        'environment' => app()->environment(),
                        'environment_readiness' => app()->environment('production') ? 'configured' : 'not_production',
                        'mail' => $this->productionOperations->mailHealth()['status'],
                        'security' => $this->productionOperations->securityReadiness()['status'],
                        'analytics' => filled(config('production.analytics.ga_measurement_id')) ? 'configured' : 'not_configured',
                        'search_console' => filled(config('production.analytics.search_console_verification')) ? 'configured' : 'not_configured',
                    ],
                ],
                'activity' => $this->activity(),
                'attention' => $this->attention($queue, $backup),
                'quickActions' => [
                    ['label' => 'Yeni tarif', 'route' => 'admin.recipes.create', 'icon' => 'plus'],
                    ['label' => 'İçerik doğrulama', 'route' => 'admin.recipe-verification.index', 'icon' => 'check'],
                    ['label' => 'Malzemeleri yönet', 'route' => 'admin.ingredients.index', 'icon' => 'search'],
                    ['label' => 'Medya yükle', 'route' => 'admin.media.create', 'icon' => 'image'],
                    ['label' => 'Veri kalitesi', 'route' => 'admin.data-quality.index', 'icon' => 'sparkles'],
                    ['label' => 'Yedekler', 'route' => 'admin.backups.index', 'icon' => 'archive'],
                ],
            ];
        });
    }

    /** @return array<string, mixed> */
    private function metric(string $key, string $label, int|float|null $current, ?int $previous, ?int $absoluteChange, string $description, bool $inverse = false, string $suffix = ''): array
    {
        $percentage = $previous !== null && $previous > 0 && $absoluteChange !== null ? round(($absoluteChange / $previous) * 100, 1) : null;
        $direction = $absoluteChange === null || $absoluteChange === 0 ? 'neutral' : ($absoluteChange > 0 ? 'up' : 'down');

        return [
            'key' => $key,
            'label' => $label,
            'current_value' => $current,
            'previous_value' => $previous,
            'absolute_change' => $absoluteChange,
            'percentage_change' => $percentage,
            'trend_direction' => $direction,
            'data_status' => $current === null ? 'insufficient_data' : 'available',
            'description' => $description,
            'inverse' => $inverse,
            'suffix' => $suffix,
        ];
    }

    /** @return array<string, mixed> */
    private function recipeTrend(Carbon $from, int $period): array
    {
        $rows = Recipe::query()->where('created_at', '>=', $from)->selectRaw('DATE(created_at) as bucket, COUNT(*) as total')->groupBy('bucket')->pluck('total', 'bucket');
        $labels = [];
        $values = [];
        for ($offset = $period - 1; $offset >= 0; $offset--) {
            $date = now()->subDays($offset)->toDateString();
            $labels[] = now()->subDays($offset)->format('d M');
            $values[] = (int) ($rows[$date] ?? 0);
        }

        return $this->chart('Tarif eklenme trendi', 'Seçilen dönemde oluşturulan tarif sayısı.', 'line', $labels, $values);
    }

    /** @param array<string, int|string> $distribution @return array<string, mixed> */
    private function distributionChart(string $title, string $description, string $type, array $distribution): array
    {
        return $this->chart($title, $description, $type, array_keys($distribution), array_map('intval', array_values($distribution)));
    }

    /** @param list<string> $labels @param list<int> $values @return array<string, mixed> */
    private function chart(string $title, string $description, string $type, array $labels, array $values): array
    {
        $total = array_sum($values);

        return [
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'labels' => $labels,
            'values' => $values,
            'data_status' => $total > 0 ? 'available' : 'empty',
            'summary' => $total > 0 ? implode(', ', array_map(fn (string $label, int $value): string => "{$label}: {$value}", $labels, $values)) : 'Veri henüz oluşmadı.',
        ];
    }

    /** @return list<array<string, string>> */
    private function activity(): array
    {
        return AuditLog::query()->with('user:id,name,public_id')->latest()->limit(8)->get()->map(function (AuditLog $log): array {
            return [
                'action' => str($log->action)->replace('.', ' ')->headline()->value(),
                'actor' => $log->user_id === null ? 'Sistem' : $log->user->name,
                'at' => $log->created_at?->diffForHumans() ?? '—',
                'entity_public_id' => (string) data_get($log->metadata, 'recipe_public_id', data_get($log->metadata, 'entity_public_id', '')),
            ];
        })->all();
    }

    /** @param array<string, mixed> $queue @param array<string, mixed>|null $backup @return list<array<string, string>> */
    private function attention(array $queue, ?array $backup): array
    {
        $alerts = [];
        if ((int) $queue['failed_jobs'] > 0) {
            $alerts[] = ['severity' => 'warning', 'label' => 'Başarısız işler', 'detail' => (string) $queue['failed_jobs'].' iş incelenmeli.', 'route' => 'admin.failed-jobs.index'];
        }
        $critical = DataQualityIssue::query()->where('resolution_status', 'open')->where('severity', 'critical')->count();
        if ($critical > 0) {
            $alerts[] = ['severity' => 'danger', 'label' => 'Kritik veri kalitesi sorunu', 'detail' => $critical.' açık kritik issue var.', 'route' => 'admin.data-quality.index'];
        }
        $review = Recipe::query()->whereIn('verification_status', ['needs_review', 'unreviewed', 'partially_verified'])->count();
        if ($review > 0) {
            $alerts[] = ['severity' => 'warning', 'label' => 'İçerik doğrulama kuyruğu', 'detail' => $review.' tarif gözden geçirmeyi bekliyor.', 'route' => 'admin.recipe-verification.index'];
        }
        if ($backup === null) {
            $alerts[] = ['severity' => 'info', 'label' => 'Yedek kaydı yok', 'detail' => 'Henüz listelenebilir bir yedek kaydı bulunmuyor.', 'route' => 'admin.backups.index'];
        }
        foreach ([
            ['recipe_uses_placeholder_image', 'warning', 'Yer tutucu tarif görselleri', 'Yer tutucu görsellerin doğrulanmış görsellerle değiştirilmesi gerekiyor.'],
            ['conversion_low_confidence', 'warning', 'Düşük güvenli ölçü dönüşümleri', 'Ölçü dönüşümlerini kaynakla doğrulayın.'],
            ['nutrition_low_confidence', 'warning', 'Tahmini nutrition verisi', 'Tahmine dayalı nutrition kayıtlarını doğrulayın.'],
            ['member_browser_qa_fixture_missing', 'info', 'Member browser QA fixture eksik', 'Yerel QA fixture komutu henüz çalıştırılmamış.'],
        ] as [$type, $severity, $label, $fallback]) {
            $count = DataQualityIssue::query()->where('resolution_status', 'open')->where('issue_type', $type)->count();
            if ($count > 0) {
                $alerts[] = ['severity' => $severity, 'label' => $label, 'detail' => $count === 1 ? $fallback : $count.' açık kayıt var.', 'url' => route('admin.data-quality.index', ['issue_type' => $type, 'status' => 'open'])];
            }
        }

        return $alerts;
    }

    /** @return array<string, mixed>|null */
    private function lastBackup(): ?array
    {
        try {
            $backup = $this->backups->list()[0] ?? null;

            return is_array($backup) ? ['status' => (string) ($backup['status'] ?? 'unknown'), 'created_at' => (string) ($backup['created_at'] ?? ''), 'verified_at' => (string) ($backup['verified_at'] ?? '')] : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function periodLabel(int $period): string
    {
        return match ($period) {
            1 => 'Son 24 saat',
            7 => 'Son 7 gün',
            30 => 'Son 30 gün',
            default => 'Son 90 gün',
        };
    }

    /** @return array<string, int> */
    private function contentOperations(): array
    {
        $approvedPublicMedia = static fn ($query): mixed => $query
            ->where('visibility', 'public')
            ->where('is_placeholder', false)
            ->where('verification_status', 'verified')
            ->where('media_status', 'approved_real_media')
            ->where('license_review_status', 'approved')
            ->where('attribution_review_status', 'approved');

        return [
            'recipes_total' => Recipe::query()->count(),
            'recipes_published' => Recipe::query()->where('status', 'published')->count(),
            'recipes_draft' => Recipe::query()->where('status', 'draft')->count(),
            'recipes_review_required' => Recipe::query()->whereIn('verification_status', ['needs_review', 'unreviewed', 'partially_verified'])->count(),
            'recipes_missing_approved_media' => Recipe::query()->where('status', 'published')->whereDoesntHave('media', $approvedPublicMedia)->count(),
            'blogs_total' => BlogPost::query()->count(),
            'blogs_published' => BlogPost::query()->where('status', 'published')->count(),
            'blogs_review_required' => BlogPost::query()->whereIn('editorial_status', ['needs_review', 'unreviewed', 'changes_requested'])->count(),
            'blogs_missing_approved_media' => BlogPost::query()->where('status', 'published')->where(function ($query) use ($approvedPublicMedia): void {
                $query->whereNull('hero_media_id')->orWhereDoesntHave('heroMedia', $approvedPublicMedia);
            })->count(),
            'scheduled' => Recipe::query()->where('status', 'scheduled')->count() + BlogPost::query()->whereNotNull('scheduled_at')->where('scheduled_at', '>', now())->count(),
        ];
    }

    /** @return array<string, int> */
    private function memberOperations(Carbon $from): array
    {
        return [
            'total' => User::query()->count(),
            'recent_registrations' => User::query()->where('created_at', '>=', $from)->count(),
            'unverified' => User::query()->whereNull('email_verified_at')->count(),
            'suspended' => User::query()->where('account_status', 'suspended')->count(),
            'deletion_requests' => User::query()->where('account_status', 'deletion_requested')->count(),
            'export_requests' => PersonalizationExportRequest::query()->where('status', 'requested')->count(),
        ];
    }

    /** @return array<string, int> */
    private function communicationOperations(Carbon $from): array
    {
        return [
            'new_contact_requests' => ContactRequest::query()->where('submitted_at', '>=', $from)->count(),
            'open_contact_requests' => ContactRequest::query()->whereNotIn('status', ['resolved', 'closed', 'anonymized'])->count(),
            'pending_mail_jobs' => (int) DB::table('jobs')->where('payload', 'like', '%Mail%')->count(),
            'failed_mail_jobs' => (int) DB::table('failed_jobs')->where('payload', 'like', '%Mail%')->count(),
        ];
    }

    /** @return array<string, string|null> */
    private function restoreOperations(): array
    {
        $latest = RestoreHistory::query()->latest('created_at')->first(['status', 'finished_at']);

        return [
            'status' => $latest?->status,
            'finished_at' => $latest?->finished_at instanceof \DateTimeInterface ? $latest->finished_at->toIso8601String() : null,
        ];
    }
}
