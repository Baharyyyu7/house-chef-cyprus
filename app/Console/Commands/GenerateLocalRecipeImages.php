<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Recipe;
use App\Services\Media\LocalComfyUiImageGenerationService;
use Illuminate\Console\Command;

final class GenerateLocalRecipeImages extends Command
{
    protected $signature = 'recipe-images:comfyui-batch {--limit=5 : En fazla kaç doğrulanmış tarif işleneceği} {--dry-run : Yerel ComfyUI çağrısı yapmadan planı göster}';

    protected $description = 'Onay gerektiren private ComfyUI tarif görseli batchi oluşturur';

    public function handle(LocalComfyUiImageGenerationService $generator): int
    {
        $limit = max(1, min((int) $this->option('limit'), 5));
        $recipes = Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->with(['ingredients', 'media'])
            ->orderBy('id')
            ->limit($limit)
            ->get();
        if ($this->option('dry-run')) {
            $this->table(['Tarif', 'Public ID', 'İşlem'], $recipes->map(fn (Recipe $recipe): array => [$recipe->title, $recipe->public_id, 'Private review-only local generation'])->all());

            return self::SUCCESS;
        }
        foreach ($recipes as $recipe) {
            $result = $generator->generate($recipe);
            $this->line($recipe->title.': '.($result['status'] ?? 'unknown'));
            if (($result['status'] ?? null) === 'not_configured') {
                $this->warn((string) ($result['safe_message'] ?? 'ComfyUI yapılandırılmadı.'));

                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
