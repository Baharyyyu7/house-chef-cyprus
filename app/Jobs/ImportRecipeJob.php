<?php

namespace App\Jobs;

use App\DTOs\Importing\NormalizedRecipeData;
use App\Models\DataSource;
use App\Services\Importing\RecipeImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ImportRecipeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    public function __construct(public int $sourceId, public array $record) {}

    public function handle(RecipeImportService $service): void
    {
        $source = DataSource::findOrFail($this->sourceId);
        $service->import($source, new NormalizedRecipeData((string) $this->record['external_id'], (string) $this->record['title'], (string) $this->record['slug'], $this->record));
    }
}
