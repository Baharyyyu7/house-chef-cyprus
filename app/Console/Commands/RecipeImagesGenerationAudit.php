<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AiImageGeneration;
use Illuminate\Console\Command;

final class RecipeImagesGenerationAudit extends Command
{
    protected $signature = 'recipe-images:generation-audit {--json : Makine okunabilir çıktı}';

    protected $description = 'AI görsel çağrılarının gizli veri içermeyen tanı özetini gösterir';

    public function handle(): int
    {
        $rows = AiImageGeneration::query()->with('recipe:id,public_id')->orderBy('created_at')->get()->map(fn (AiImageGeneration $item): array => [
            'generation_public_id' => $item->public_id, 'recipe_public_id' => $item->recipe?->public_id, 'provider' => $item->provider, 'model' => $item->model,
            'scope' => $item->generation_scope, 'status' => $item->status, 'response_status' => $item->response_status, 'provider_request_id' => $item->provider_request_id,
            'response_content_type' => $item->response_content_type, 'response_body_present' => $item->response_body_present, 'candidate_count' => $item->candidate_count,
            'part_count' => $item->part_count, 'image_part_count' => $item->image_part_count, 'text_part_count' => $item->text_part_count,
            'finish_reason' => $item->finish_reason, 'moderation_status' => $item->moderation_status, 'request_bytes' => $item->request_bytes,
            'response_bytes' => $item->response_bytes, 'provider_latency_ms' => $item->provider_latency_ms, 'billing_status' => $item->billing_status,
            'parser_result' => $item->parser_result, 'safe_error_code' => $item->error_code, 'safe_error_message' => $item->error_message,
        ])->all();
        if ($this->option('json')) {
            $this->line((string) json_encode(['generations' => $rows], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(['Generation', 'Tarif', 'Durum', 'HTTP', 'Parser', 'Hata'], collect($rows)->map(fn (array $row): array => [$row['generation_public_id'], $row['recipe_public_id'], $row['status'], $row['response_status'] ?? '—', $row['parser_result'] ?? '—', $row['safe_error_code'] ?? '—'])->all());
        }

        return self::SUCCESS;
    }
}
