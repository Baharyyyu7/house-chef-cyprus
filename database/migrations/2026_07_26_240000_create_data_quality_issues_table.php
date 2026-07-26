<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_quality_issues', function (Blueprint $table): void {
            $table->id();
            $table->string('issue_type');
            $table->string('severity');
            $table->string('entity_type');
            $table->string('entity_public_id')->nullable();
            $table->string('message');
            $table->timestamp('detected_at');
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolution_status')->default('open');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['resolution_status', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_quality_issues');
    }
};
