<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('editorial_review_batches', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->string('planning_key', 120)->unique();
            $table->string('title', 180);
            $table->string('category', 60)->index();
            $table->json('severity_mix');
            $table->json('risk_mix');
            $table->unsignedSmallInteger('total_issues')->default(0);
            $table->unsignedSmallInteger('source_required_count')->default(0);
            $table->unsignedSmallInteger('evidence_ready_count')->default(0);
            $table->unsignedSmallInteger('human_review_count')->default(0);
            $table->unsignedSmallInteger('duplicate_group_count')->default(0);
            $table->unsignedSmallInteger('estimated_review_minutes')->default(0);
            $table->unsignedTinyInteger('priority_score')->default(0)->index();
            $table->string('status', 40)->default('planned')->index();
            $table->foreignId('assigned_reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->json('blocking_reasons')->nullable();
            $table->timestamps();
        });

        Schema::create('editorial_review_batch_issue', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('editorial_review_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('content_factory_issue_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique('content_factory_issue_id');
            $table->unique(['editorial_review_batch_id', 'content_factory_issue_id'], 'editorial_batch_issue_unique');
        });

        Schema::create('source_research_queue_items', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->foreignId('content_factory_issue_id')->constrained()->cascadeOnDelete()->unique();
            $table->text('claim');
            $table->string('risk_level', 20)->index();
            $table->string('risk_domain', 40)->nullable()->index();
            $table->string('required_source_type', 120)->nullable();
            $table->json('suggested_search_terms')->nullable();
            $table->string('candidate_source_title')->nullable();
            $table->string('publisher', 180)->nullable();
            $table->string('url', 2048)->nullable();
            $table->date('publication_date')->nullable();
            $table->timestamp('accessed_at')->nullable();
            $table->string('source_type', 60)->nullable();
            $table->text('evidence_summary')->nullable();
            $table->text('supported_claim')->nullable();
            $table->text('conflicting_evidence')->nullable();
            $table->unsignedTinyInteger('confidence_score')->default(0);
            $table->string('freshness_requirement', 80)->nullable();
            $table->text('copyright_safe_notes')->nullable();
            $table->string('reviewer_status', 40)->default('source_required')->index();
            $table->foreignId('assigned_reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('editorial_review_packs', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->foreignId('content_factory_batch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('content_type', 20)->index();
            $table->string('entity_type', 120);
            $table->string('entity_public_id', 26);
            $table->json('snapshot');
            $table->unsignedTinyInteger('readiness_score')->default(0)->index();
            $table->json('blocking_reasons')->nullable();
            $table->string('status', 40)->default('blocked')->index();
            $table->foreignId('assigned_reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['entity_type', 'entity_public_id'], 'editorial_review_pack_entity_unique');
        });

        Schema::create('editorial_review_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('editorial_review_pack_id')->constrained()->cascadeOnDelete();
            $table->string('section_key', 80);
            $table->string('status', 40)->default('pending')->index();
            $table->text('note')->nullable();
            $table->foreignId('decided_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->unique(['editorial_review_pack_id', 'section_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('editorial_review_sections');
        Schema::dropIfExists('editorial_review_packs');
        Schema::dropIfExists('source_research_queue_items');
        Schema::dropIfExists('editorial_review_batch_issue');
        Schema::dropIfExists('editorial_review_batches');
    }
};
