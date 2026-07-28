<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\DataQualityIssue;
use App\Models\Ingredient;
use App\Models\Media;
use App\Models\Recipe;
use App\Models\RecipeNutritionSnapshot;
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
}
