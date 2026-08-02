<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_metadata', function (Blueprint $table): void {
            $table->id();
            $table->string('seoable_type', 120);
            $table->unsignedBigInteger('seoable_id');
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->string('canonical_override', 2048)->nullable();
            $table->string('robots', 120)->default('index,follow');
            $table->string('og_title', 70)->nullable();
            $table->string('og_description', 160)->nullable();
            $table->string('og_media_public_id', 26)->nullable();
            $table->string('x_title', 70)->nullable();
            $table->string('x_description', 160)->nullable();
            $table->string('x_media_public_id', 26)->nullable();
            $table->string('focus_topic', 160)->nullable();
            $table->string('primary_query', 160)->nullable();
            $table->string('search_intent', 40)->nullable();
            $table->string('index_status', 30)->default('pending');
            $table->string('schema_status', 30)->default('pending');
            $table->unsignedTinyInteger('quality_score')->nullable();
            $table->timestamp('last_audited_at')->nullable();
            $table->timestamp('last_refreshed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['seoable_type', 'seoable_id']);
        });

        Schema::create('keyword_topics', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->string('primary_keyword', 160);
            $table->string('normalized_keyword', 160);
            $table->string('locale', 10)->default('tr');
            $table->string('country', 4)->default('CY');
            $table->string('intent', 40);
            $table->string('topic_cluster', 120);
            $table->foreignId('parent_topic_id')->nullable()->constrained('keyword_topics')->nullOnDelete();
            $table->unsignedTinyInteger('priority')->default(50);
            $table->string('difficulty_source', 80)->nullable();
            $table->string('volume_source', 80)->nullable();
            $table->string('trend_direction', 20)->nullable();
            $table->decimal('data_confidence', 5, 2)->default(0);
            $table->string('status', 30)->default('idea');
            $table->timestamps();
            $table->unique(['normalized_keyword', 'locale', 'country']);
        });

        Schema::create('content_briefs', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->foreignId('keyword_topic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('proposed_title', 255);
            $table->string('target_audience', 255)->nullable();
            $table->string('search_intent', 40);
            $table->text('user_question')->nullable();
            $table->text('direct_answer')->nullable();
            $table->json('required_sections')->nullable();
            $table->json('required_facts')->nullable();
            $table->json('related_recipe_public_ids')->nullable();
            $table->json('internal_links')->nullable();
            $table->json('prohibited_claims')->nullable();
            $table->json('source_notes')->nullable();
            $table->string('freshness_requirement', 80)->nullable();
            $table->string('risk_level', 20)->default('low');
            $table->string('status', 30)->default('idea');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('content_opportunities', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->string('kind', 40);
            $table->string('title', 255);
            $table->string('normalized_key', 255)->unique();
            $table->string('search_intent', 40)->nullable();
            $table->string('topic_cluster', 120)->nullable();
            $table->json('evidence')->nullable();
            $table->json('related_public_ids')->nullable();
            $table->string('risk_level', 20)->default('low');
            $table->unsignedTinyInteger('priority')->default(50);
            $table->string('status', 30)->default('open');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'priority']);
        });

        Schema::create('zero_result_searches', function (Blueprint $table): void {
            $table->id();
            $table->string('normalized_query', 180)->unique();
            $table->string('locale', 10)->default('tr');
            $table->unsignedInteger('search_count')->default(0);
            $table->timestamp('last_searched_at')->useCurrent();
            $table->string('suggested_action', 40)->nullable();
            $table->timestamps();
        });

        Schema::create('seo_issues', function (Blueprint $table): void {
            $table->id();
            $table->string('fingerprint', 64)->unique();
            $table->string('category', 50);
            $table->string('entity_type', 120)->nullable();
            $table->string('entity_public_id', 80)->nullable();
            $table->string('url', 2048)->nullable();
            $table->string('severity', 20);
            $table->string('message', 255);
            $table->json('safe_metadata')->nullable();
            $table->timestamp('first_detected_at')->useCurrent();
            $table->timestamp('last_detected_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('reopened_at')->nullable();
            $table->timestamps();
            $table->index(['severity', 'resolved_at']);
            $table->index(['entity_type', 'entity_public_id']);
        });

        Schema::create('search_performance_daily', function (Blueprint $table): void {
            $table->id();
            $table->date('performance_date');
            $table->string('page', 2048);
            $table->string('query_hash', 64)->nullable();
            $table->string('country', 4)->nullable();
            $table->string('device', 30)->nullable();
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('impressions')->default(0);
            $table->decimal('ctr', 7, 4)->default(0);
            $table->decimal('average_position', 8, 2)->nullable();
            $table->timestamps();
            $table->unique(['performance_date', 'page', 'query_hash', 'country', 'device'], 'seo_performance_daily_unique');
        });

        Schema::create('indexing_events', function (Blueprint $table): void {
            $table->id();
            $table->string('url', 2048);
            $table->string('event_type', 40);
            $table->string('provider', 40);
            $table->string('status', 30);
            $table->string('safe_error_code', 80)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->index(['provider', 'status']);
        });

        Schema::create('content_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('revision_number');
            $table->string('reason', 255);
            $table->longText('previous_body')->nullable();
            $table->longText('proposed_body')->nullable();
            $table->json('changed_seo_fields')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['blog_post_id', 'revision_number']);
        });

        Schema::create('social_distribution_drafts', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->foreignId('blog_post_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel', 30);
            $table->text('body');
            $table->json('safe_metadata')->nullable();
            $table->string('status', 30)->default('draft');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['channel', 'status']);
        });

        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->foreignId('content_brief_id')->nullable()->after('hero_media_id')->constrained('content_briefs')->nullOnDelete();
            $table->string('content_purpose', 120)->nullable()->after('body');
            $table->string('search_intent', 40)->nullable()->after('content_purpose');
            $table->string('primary_keyword', 160)->nullable()->after('search_intent');
            $table->json('secondary_keywords')->nullable()->after('primary_keyword');
            $table->json('source_notes')->nullable()->after('secondary_keywords');
            $table->json('faq_blocks')->nullable()->after('source_notes');
            $table->string('fact_check_status', 30)->default('pending')->after('status');
            $table->string('editorial_status', 30)->default('draft')->after('fact_check_status');
            $table->string('originality_status', 30)->default('pending')->after('editorial_status');
            $table->string('freshness_class', 40)->nullable()->after('originality_status');
            $table->timestamp('review_due_at')->nullable()->after('scheduled_at');
            $table->timestamp('last_refreshed_at')->nullable()->after('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('content_brief_id');
            $table->dropColumn(['content_purpose', 'search_intent', 'primary_keyword', 'secondary_keywords', 'source_notes', 'faq_blocks', 'fact_check_status', 'editorial_status', 'originality_status', 'freshness_class', 'review_due_at', 'last_refreshed_at']);
        });
        Schema::dropIfExists('content_revisions');
        Schema::dropIfExists('social_distribution_drafts');
        Schema::dropIfExists('indexing_events');
        Schema::dropIfExists('search_performance_daily');
        Schema::dropIfExists('seo_issues');
        Schema::dropIfExists('zero_result_searches');
        Schema::dropIfExists('content_opportunities');
        Schema::dropIfExists('content_briefs');
        Schema::dropIfExists('keyword_topics');
        Schema::dropIfExists('seo_metadata');
    }
};
