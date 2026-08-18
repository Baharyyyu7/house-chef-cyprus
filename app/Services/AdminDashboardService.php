<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AudienceCountryDay;
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
use Illuminate\Support\Str;

final class AdminDashboardService
{
    private const PERIODS = [1, 7, 30, 90];

    /**
     * Şematik harita koordinatları yalnız ülke bazlı toplulaştırılmış üye
     * etkinliğini anlatmak içindir; siyasi sınır veya hassas konum verisi
     * temsil etmez.
     *
     * @var array<string, array{label: string, x: int, y: int}>
     */
    private const AUDIENCE_COUNTRIES = [
        'AU' => ['label' => 'Avustralya', 'x' => 806, 'y' => 354],
        'CA' => ['label' => 'Kanada', 'x' => 271, 'y' => 139],
        'CY' => ['label' => 'Kıbrıs', 'x' => 546, 'y' => 222],
        'DE' => ['label' => 'Almanya', 'x' => 500, 'y' => 182],
        'ES' => ['label' => 'İspanya', 'x' => 467, 'y' => 212],
        'FR' => ['label' => 'Fransa', 'x' => 480, 'y' => 200],
        'GB' => ['label' => 'Birleşik Krallık', 'x' => 462, 'y' => 166],
        'GR' => ['label' => 'Yunanistan', 'x' => 530, 'y' => 215],
        'IT' => ['label' => 'İtalya', 'x' => 512, 'y' => 211],
        'NL' => ['label' => 'Hollanda', 'x' => 490, 'y' => 174],
        'SA' => ['label' => 'Suudi Arabistan', 'x' => 580, 'y' => 251],
        'TR' => ['label' => 'Türkiye', 'x' => 563, 'y' => 208],
        'UA' => ['label' => 'Ukrayna', 'x' => 551, 'y' => 177],
        'AE' => ['label' => 'Birleşik Arap Emirlikleri', 'x' => 604, 'y' => 242],
        'US' => ['label' => 'Amerika Birleşik Devletleri', 'x' => 244, 'y' => 202],
    ];

    /** @var array<string, string> */
    private const COUNTRY_PROFILE_ALIASES = [
        'almanya' => 'DE',
        'america' => 'US',
        'amerika' => 'US',
        'amerikabirlesikdevletleri' => 'US',
        'australia' => 'AU',
        'avustralya' => 'AU',
        'birlesikarapemirlikleri' => 'AE',
        'birlesikkrallik' => 'GB',
        'canada' => 'CA',
        'cyprus' => 'CY',
        'england' => 'GB',
        'france' => 'FR',
        'germany' => 'DE',
        'greece' => 'GR',
        'hollanda' => 'NL',
        'ingiltere' => 'GB',
        'italia' => 'IT',
        'italy' => 'IT',
        'ispanya' => 'ES',
        'kibris' => 'CY',
        'kktc' => 'CY',
        'kanada' => 'CA',
        'netherlands' => 'NL',
        'northcyprus' => 'CY',
        'saudiarabia' => 'SA',
        'suudiarabistan' => 'SA',
        'turkey' => 'TR',
        'turkiye' => 'TR',
        'uae' => 'AE',
        'uk' => 'GB',
        'ukraine' => 'UA',
        'unitedarabemirates' => 'AE',
        'unitedkingdom' => 'GB',
        'unitedstates' => 'US',
        'yunanistan' => 'GR',
    ];

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
            // Tarama hiç çalışmadıysa açık issue yoktur ve her kategori %100
            // çıkar. Bu, "sorun bulunamadı" değil "hiç bakılmadı" demektir;
            // panel ikisini aynı şekilde gösteremez.
            $qualityScanned = $this->quality->hasEverScanned();
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
                $this->metric('kalite', 'Veri kalitesi', $qualityScanned ? $qualityScores['overall'] : null, null, null, $qualityScanned ? 'Açık issue ağırlığına göre' : 'Henüz taranmadı', false, '%'),
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
                'audienceMap' => $this->audienceMap($from),
                'qualityScores' => $qualityScores,
                'qualityScanned' => $qualityScanned,
                'qualityScannedAt' => $this->quality->lastScannedAt()?->format(DATE_ATOM),
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

    /** @return array{data_status: string, total_members: int, countries: list<array<string, int|string>>, summary: string, source: string} */
    private function audienceMap(Carbon $from): array
    {
        // Ziyaretçi ülkesi asıl kaynaktır: harita "kim geliyor" sorusunu
        // yanıtlıyor, "kim üye oldu" sorusunu değil. Üye profili yalnız
        // ziyaretçi sayacı boşken devreye girer.
        $visitorTotals = [];

        AudienceCountryDay::query()
            ->where('day', '>=', $from->toDateString())
            ->get(['country_code', 'hits'])
            ->each(function (AudienceCountryDay $gun) use (&$visitorTotals): void {
                $code = strtoupper((string) $gun->country_code);

                if (isset(self::AUDIENCE_COUNTRIES[$code])) {
                    $visitorTotals[$code] = ($visitorTotals[$code] ?? 0) + (int) $gun->hits;
                }
            });

        if ($visitorTotals !== []) {
            return $this->audienceMapFrom($visitorTotals, 'visitor_country_aggregate');
        }

        $totals = [];

        User::query()
            ->whereNotNull('last_activity_at')
            ->where('last_activity_at', '>=', $from)
            ->get(['last_country_code', 'country'])
            ->each(function (User $member) use (&$totals): void {
                $countryCode = $this->countryCodeForMember($member);

                if ($countryCode !== null) {
                    $totals[$countryCode] = ($totals[$countryCode] ?? 0) + 1;
                }
            });

        return $this->audienceMapFrom($totals, 'member_profile_aggregate');
    }

    /**
     * @param  array<string, int>  $totals
     * @return array{data_status: string, total_members: int, countries: list<array<string, int|string>>, summary: string, source: string}
     */
    private function audienceMapFrom(array $totals, string $source): array
    {
        arsort($totals);
        $countries = [];

        foreach (array_slice($totals, 0, 8, true) as $countryCode => $members) {
            $country = self::AUDIENCE_COUNTRIES[$countryCode];
            $countries[] = [
                'country_code' => $countryCode,
                'label' => $country['label'],
                'members' => $members,
                'x' => $country['x'],
                'y' => $country['y'],
            ];
        }

        $totalMembers = array_sum(array_column($countries, 'members'));

        return [
            'data_status' => $countries === [] ? 'empty' : 'available',
            'total_members' => $totalMembers,
            'countries' => $countries,
            'summary' => $countries === []
                ? 'Konum özeti için yeterli ziyaretçi verisi yok.'
                : $totalMembers.($source === 'visitor_country_aggregate' ? ' ziyaret, ' : ' etkin üye, ').count($countries).' ülke üzerinden toplulaştırıldı.',
            'source' => $source,
        ];
    }

    private function countryCodeForMember(User $member): ?string
    {
        $recordedCode = strtoupper(trim((string) $member->last_country_code));

        if (isset(self::AUDIENCE_COUNTRIES[$recordedCode])) {
            return $recordedCode;
        }

        $profileCountry = Str::of(Str::ascii(trim((string) $member->country)))
            ->lower()
            ->replaceMatches('/[^a-z]/', '')
            ->value();

        return self::COUNTRY_PROFILE_ALIASES[$profileCountry] ?? null;
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

    /** @return list<array{action: string, actor: string, at: string, entity_public_id: string, repeat: int}> */
    private function activity(): array
    {
        // Toplu işler aynı eylemi arka arkaya yüzlerce kez yazıyor; sekiz satırın
        // sekizi de "Media Import Approved" olunca liste hiçbir şey anlatmıyordu.
        // Peş peşe tekrarlar tek satırda sayıyla toplanır.
        $logs = AuditLog::query()->with('user:id,name,public_id')->latest()->limit(120)->get();
        $satirlar = [];

        foreach ($logs as $log) {
            $eylem = str($log->action)->replace('.', ' ')->headline()->value();
            $aktor = $log->user_id === null ? 'Sistem' : $log->user->name;
            $son = $satirlar === [] ? null : array_key_last($satirlar);

            if ($son !== null && $satirlar[$son]['action'] === $eylem && $satirlar[$son]['actor'] === $aktor) {
                $satirlar[$son]['repeat']++;

                continue;
            }

            if (count($satirlar) === 8) {
                break;
            }

            $satirlar[] = [
                'action' => $eylem,
                'actor' => $aktor,
                'at' => $log->created_at?->diffForHumans() ?? '—',
                'entity_public_id' => (string) data_get($log->metadata, 'recipe_public_id', data_get($log->metadata, 'entity_public_id', '')),
                'repeat' => 1,
            ];
        }

        return $satirlar;
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

        // Aşağıdaki iki kontrol DataQualityIssue kayıtlarına bağlı değildir. Panel yalnız
        // açılmış issue'lara baksaydı, kimse kaydetmediği sürece "her şey yolunda" derdi;
        // oysa aynı fotoğrafın onlarca tarifte kullanılması doğrudan sayılabilir bir durum.
        $paylasilan = DB::table('media')
            ->where('media_status', 'approved_real_media')
            ->where('mediable_type', Recipe::class)
            ->whereNotNull('path')
            ->select('path', DB::raw('count(*) as adet'))
            ->groupBy('path')
            ->having('adet', '>', 1)
            ->get();

        if ($paylasilan->isNotEmpty()) {
            $etkilenen = (int) $paylasilan->sum('adet') - $paylasilan->count();
            $alerts[] = [
                'severity' => 'danger',
                'label' => 'Aynı görsel birden fazla tarifte',
                'detail' => $etkilenen.' tarif, başka bir tarife ait fotoğrafı gösteriyor ('.$paylasilan->count().' görsel paylaşılıyor).',
                'route' => 'admin.media.index',
            ];
        }

        $gorselsiz = Recipe::query()
            ->where('status', 'published')
            ->whereDoesntHave('media', fn ($query) => $query->where('media_status', 'approved_real_media'))
            ->count();

        if ($gorselsiz > 0) {
            $alerts[] = [
                'severity' => 'warning',
                'label' => 'Onaylı görseli olmayan yayındaki tarif',
                'detail' => $gorselsiz.' yayındaki tarif yer tutucu ile gösteriliyor.',
                'route' => 'admin.media.index',
            ];
        }
        // Yayındaki tarif sayısı tek başına iyi haber gibi okunuyor. Sayının
        // büyük bölümü "{Mutfak} Usulü Limonlu {Malzeme}" kalıbıyla toplu
        // üretilmiş, adı bir yemeği tarif etmeyen kayıtlardan geliyor; bunlar
        // arama motorları için scaled content abuse desenidir.
        $sablon = Recipe::query()
            ->where('status', 'published')
            ->get(['title'])
            ->reject(fn (Recipe $recipe): bool => Recipe::titleDescribesADish((string) $recipe->title))
            ->count();

        if ($sablon > 0) {
            $alerts[] = [
                'severity' => 'danger',
                'label' => 'Şablonla üretilmiş yayındaki tarif',
                'detail' => $sablon.' tarifin adı bir yemeği tarif etmiyor (örn. "Akdeniz Mutfağı Usulü Limonlu").',
                'route' => 'admin.recipes.index',
            ];
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
