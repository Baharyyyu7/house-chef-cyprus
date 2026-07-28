<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_image_generations', function (Blueprint $table): void {
            $table->string('generation_scope', 32)->default('pilot')->after('provider')->index();
            $table->unsignedSmallInteger('response_status')->nullable()->after('error_message');
            $table->string('provider_request_id', 128)->nullable()->after('response_status');
            $table->string('response_content_type', 128)->nullable()->after('provider_request_id');
            $table->boolean('response_body_present')->default(false)->after('response_content_type');
            $table->unsignedSmallInteger('candidate_count')->default(0)->after('response_body_present');
            $table->unsignedSmallInteger('part_count')->default(0)->after('candidate_count');
            $table->unsignedSmallInteger('image_part_count')->default(0)->after('part_count');
            $table->unsignedSmallInteger('text_part_count')->default(0)->after('image_part_count');
            $table->string('finish_reason', 64)->nullable()->after('text_part_count');
            $table->string('moderation_status', 64)->nullable()->after('finish_reason');
            $table->unsignedInteger('request_bytes')->default(0)->after('moderation_status');
            $table->unsignedInteger('response_bytes')->default(0)->after('request_bytes');
            $table->unsignedInteger('provider_latency_ms')->nullable()->after('response_bytes');
            $table->string('billing_status', 32)->default('unknown')->after('provider_latency_ms');
            $table->string('parser_result', 64)->nullable()->after('billing_status');
            $table->json('diagnostic_snapshot')->nullable()->after('parser_result');
            $table->json('status_transitions')->nullable()->after('diagnostic_snapshot');
        });
        DB::table('ai_image_generations')->whereNull('parser_result')->update([
            'billing_status' => 'unknown',
            'parser_result' => 'historical_response_telemetry_unavailable',
            'diagnostic_snapshot' => json_encode(['historical_record' => true, 'response_telemetry' => 'unavailable', 'raw_payload_retained' => false]),
            'status_transitions' => json_encode([['status' => 'provider_failed', 'at' => now()->toIso8601String(), 'note' => 'historical response telemetry unavailable']]),
        ]);
        Schema::table('ai_image_generations', function (Blueprint $table): void {
            $table->dropUnique('ai_image_generation_recipe_variation_unique');
            $table->unique(['recipe_id', 'variation_number', 'generation_scope'], 'ai_image_generation_recipe_variation_scope_unique');
        });
    }

    public function down(): void
    {
        Schema::table('ai_image_generations', function (Blueprint $table): void {
            $table->dropUnique('ai_image_generation_recipe_variation_scope_unique');
            $table->unique(['recipe_id', 'variation_number'], 'ai_image_generation_recipe_variation_unique');
            $table->dropColumn(['generation_scope', 'response_status', 'provider_request_id', 'response_content_type', 'response_body_present', 'candidate_count', 'part_count', 'image_part_count', 'text_part_count', 'finish_reason', 'moderation_status', 'request_bytes', 'response_bytes', 'provider_latency_ms', 'billing_status', 'parser_result', 'diagnostic_snapshot', 'status_transitions']);
        });
    }
};
