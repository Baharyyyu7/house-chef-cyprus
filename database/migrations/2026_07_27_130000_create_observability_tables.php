<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_events', function (Blueprint $table): void {
            $table->id();
            $table->string('event_type', 80);
            $table->string('severity', 20);
            $table->string('message', 255);
            $table->string('correlation_id', 32)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['severity', 'created_at']);
        });
        Schema::create('system_metrics', function (Blueprint $table): void {
            $table->id();
            $table->string('metric', 80);
            $table->string('dimension', 160)->default('all');
            $table->decimal('value', 14, 2);
            $table->timestamp('recorded_at');
            $table->timestamps();
            $table->unique(['metric', 'dimension']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_metrics');
        Schema::dropIfExists('system_events');
    }
};
