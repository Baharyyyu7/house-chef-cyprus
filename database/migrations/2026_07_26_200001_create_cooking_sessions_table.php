<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cooking_sessions', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('current_step_id')->nullable()->constrained('recipe_steps')->nullOnDelete();
            $table->unsignedSmallInteger('servings_snapshot');
            $table->string('status', 20)->default('in_progress');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'recipe_id', 'status']);
        });

        Schema::create('cooking_session_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cooking_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipe_step_id')->constrained()->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['cooking_session_id', 'recipe_step_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cooking_session_steps');
        Schema::dropIfExists('cooking_sessions');
    }
};
