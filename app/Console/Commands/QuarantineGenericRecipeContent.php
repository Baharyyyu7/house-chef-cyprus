<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Recipe;
use Illuminate\Console\Command;

final class QuarantineGenericRecipeContent extends Command
{
    protected $signature = 'recipes:quarantine-generic-content
        {--apply : SEO karantinasını yaz, aksi hâlde yalnız raporla}
        {--limit=0 : Kaç kayıt işlensin, 0 = tümü}';

    protected $description = 'Genel şablon adımları taşıyan yayınları silmeden ve yayından kaldırmadan indeks dışına alır.';

    /** @var list<string> */
    private const GENERIC_STEPS = [
        'Malzemeleri yıkayın, doğrayın ve tezgâhta sıralayın.',
        'Tencereyi veya tavayı orta ısıda ısıtın; malzemeleri sırayla ekleyip kıvamını takip edin.',
        'Tadını kontrol edin, kısa süre dinlendirip servis edin.',
    ];

    public function handle(): int
    {
        $query = Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->orderBy('id');

        foreach (self::GENERIC_STEPS as $instruction) {
            $query->whereHas('steps', fn ($steps) => $steps->where('instruction', $instruction));
        }

        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $recipes = $query->get();
        $this->line(sprintf('  SEO karantinası adayı: %d', $recipes->count()));

        foreach ($recipes->take(8) as $recipe) {
            $this->line('    - '.$recipe->title);
        }

        if (! (bool) $this->option('apply')) {
            $this->line('Yazmak için --apply ekleyin. Tarif silinmez ve yayın durumu değişmez.');

            return self::SUCCESS;
        }

        foreach ($recipes as $recipe) {
            $recipe->seoMetadata()->updateOrCreate([], [
                'robots' => 'noindex,nofollow',
                'index_status' => 'blocked_content_quality',
                'schema_status' => 'blocked_content_quality',
                'last_audited_at' => now(),
            ]);
        }

        $this->info(sprintf('%d tarif SEO karantinasına alındı.', $recipes->count()));

        return self::SUCCESS;
    }
}
