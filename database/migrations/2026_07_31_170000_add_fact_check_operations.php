<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_factory_issues', function (Blueprint $table): void {
            $table->string('category', 60)->nullable()->index()->after('issue_type');
            $table->string('risk_domain', 30)->nullable()->index()->after('risk_level');
            $table->unsignedTinyInteger('priority_score')->default(0)->index()->after('confidence_score');
            $table->string('resolution_mode', 30)->default('human_required')->index()->after('status');
            $table->json('evidence_pack')->nullable()->after('resolution_note');
            $table->string('group_key', 80)->nullable()->index()->after('fingerprint');
            $table->timestamp('triaged_at')->nullable()->after('resolved_at');
        });

        Schema::create('content_factory_evidences', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->foreignId('content_factory_issue_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('publisher', 160);
            $table->string('url', 2048);
            $table->date('published_at')->nullable();
            $table->timestamp('accessed_at');
            $table->string('source_type', 40);
            $table->string('trust_level', 20);
            $table->string('supported_claim', 180)->nullable();
            $table->unsignedTinyInteger('confidence_score')->default(0);
            $table->text('conflicting_evidence')->nullable();
            $table->string('freshness_requirement', 80)->nullable();
            $table->string('review_status', 30)->default('source_required')->index();
            $table->text('summary');
            $table->text('usage_note')->nullable();
            $table->timestamps();
        });

        Schema::create('content_factory_revisions', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->foreignId('content_factory_issue_id')->constrained()->cascadeOnDelete();
            $table->string('entity_type', 120);
            $table->string('entity_public_id', 26)->index();
            $table->string('field_name', 80)->nullable();
            $table->json('before_value')->nullable();
            $table->json('proposed_value')->nullable();
            $table->text('reason');
            $table->json('source_evidence')->nullable();
            $table->string('issue_public_id', 26)->index();
            $table->json('evidence_reference_ids')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('proposed')->index();
            $table->string('conflict_status', 30)->default('none')->index();
            $table->string('current_value_hash', 64)->nullable();
            $table->string('before_value_hash', 64)->nullable();
            $table->json('audit_metadata')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('applied_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('applied_at')->nullable();
            $table->foreignId('rolled_back_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rolled_back_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_factory_revisions');
        Schema::dropIfExists('content_factory_evidences');
        Schema::table('content_factory_issues', function (Blueprint $table): void {
            $table->dropColumn(['category', 'risk_domain', 'priority_score', 'resolution_mode', 'evidence_pack', 'group_key', 'triaged_at']);
        });
    }
};
