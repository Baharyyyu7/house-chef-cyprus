<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_conversations', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('purpose', 48);
            $table->json('memory')->nullable();
            $table->timestamp('last_interacted_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'purpose', 'last_interacted_at']);
        });

        Schema::create('ai_interactions', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ai_conversation_id')->nullable()->constrained('ai_conversations')->nullOnDelete();
            $table->string('purpose', 48);
            $table->string('provider', 48);
            $table->string('model', 120)->nullable();
            $table->string('prompt_version', 48);
            $table->string('request_fingerprint', 64)->index();
            $table->text('safe_request_summary');
            $table->text('safe_response_summary')->nullable();
            $table->json('response_payload')->nullable();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->unsignedInteger('cost_cents')->default(0);
            $table->unsignedInteger('latency_ms')->default(0);
            $table->boolean('cache_hit')->default(false);
            $table->boolean('fallback_used')->default(false);
            $table->string('status', 32)->default('completed');
            $table->string('safe_error_code', 80)->nullable();
            $table->json('safety_events')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['purpose', 'status', 'created_at']);
            $table->index(['provider', 'created_at']);
        });

        Schema::create('ai_meal_plans', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_interaction_id')->nullable()->constrained('ai_interactions')->nullOnDelete();
            $table->unsignedTinyInteger('days');
            $table->json('constraints')->nullable();
            $table->json('plan');
            $table->string('status', 32)->default('draft');
            $table->timestamps();
            $table->index(['user_id', 'status', 'created_at']);
        });

        Schema::create('ai_shopping_lists', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_meal_plan_id')->nullable()->constrained('ai_meal_plans')->nullOnDelete();
            $table->foreignId('ai_interaction_id')->nullable()->constrained('ai_interactions')->nullOnDelete();
            $table->json('items');
            $table->string('status', 32)->default('draft');
            $table->timestamps();
            $table->index(['user_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_shopping_lists');
        Schema::dropIfExists('ai_meal_plans');
        Schema::dropIfExists('ai_interactions');
        Schema::dropIfExists('ai_conversations');
    }
};
