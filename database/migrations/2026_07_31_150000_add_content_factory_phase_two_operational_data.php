<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table): void {
            $table->unsignedTinyInteger('content_factory_readiness_score')->nullable()->index()->after('completeness_score');
            $table->string('content_factory_rule_version', 32)->nullable()->after('content_factory_readiness_score');
            $table->timestamp('content_factory_checked_at')->nullable()->after('content_factory_rule_version');
            $table->json('content_factory_metadata')->nullable()->after('content_factory_checked_at');
        });

        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->unsignedTinyInteger('content_factory_readiness_score')->nullable()->index()->after('content_factory_state');
            $table->string('content_factory_rule_version', 32)->nullable()->after('content_factory_readiness_score');
            $table->timestamp('content_factory_checked_at')->nullable()->after('content_factory_rule_version');
            $table->json('content_factory_metadata')->nullable()->after('content_factory_checked_at');
        });

        Schema::table('content_factory_reviews', function (Blueprint $table): void {
            $table->text('editorial_comment')->nullable()->after('findings');
        });

        Schema::create('content_factory_issues', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->foreignId('content_factory_batch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('entity_type', 120);
            $table->string('entity_public_id', 26)->index();
            $table->string('fingerprint', 64)->unique();
            $table->string('issue_type', 80)->index();
            $table->string('field_name', 80)->nullable();
            $table->text('claim')->nullable();
            $table->string('risk_level', 20)->default('medium')->index();
            $table->string('source_requirement', 120)->nullable();
            $table->unsignedTinyInteger('confidence_score')->default(0);
            $table->string('status', 40)->default('pending')->index();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_note')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['entity_type', 'entity_public_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_factory_issues');
        Schema::table('content_factory_reviews', function (Blueprint $table): void {
            $table->dropColumn('editorial_comment');
        });
        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->dropColumn(['content_factory_readiness_score', 'content_factory_rule_version', 'content_factory_checked_at', 'content_factory_metadata']);
        });
        Schema::table('recipes', function (Blueprint $table): void {
            $table->dropColumn(['content_factory_readiness_score', 'content_factory_rule_version', 'content_factory_checked_at', 'content_factory_metadata']);
        });
    }
};
