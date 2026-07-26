<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cooking_timers', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id')->unique();
            $table->foreignId('cooking_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipe_step_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label');
            $table->unsignedInteger('duration_seconds');
            $table->unsignedInteger('remaining_seconds')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('status', 20)->default('ready');
            $table->timestamps();
            $table->index(['cooking_session_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cooking_timers');
    }
};
