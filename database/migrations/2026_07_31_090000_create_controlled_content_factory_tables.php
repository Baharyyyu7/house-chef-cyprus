<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_factory_batches', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->string('content_type', 20)->index();
            $table->unsignedSmallInteger('sequence');
            $table->string('status', 40)->default('planned')->index();
            $table->unsignedSmallInteger('planned_items')->default(0);
            $table->unsignedSmallInteger('created_items')->default(0);
            $table->unsignedSmallInteger('technical_passed_items')->default(0);
            $table->unsignedSmallInteger('failed_items')->default(0);
            $table->json('distribution')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->unique(['content_type', 'sequence']);
        });

        Schema::create('content_factory_reviews', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->foreignId('content_factory_batch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('content_type', 20)->index();
            $table->string('entity_type', 120);
            $table->string('entity_public_id', 26)->index();
            $table->string('stage', 40)->index();
            $table->string('status', 40)->default('pending')->index();
            $table->boolean('fact_check_required')->default(false);
            $table->json('findings')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['entity_type', 'entity_public_id', 'stage']);
        });

        Schema::create('content_topic_clusters', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->string('name', 160);
            $table->string('slug', 180)->unique();
            $table->string('search_intent', 60);
            $table->string('pillar_title', 255);
            $table->string('review_interval', 40)->default('6 ay');
            $table->json('internal_link_plan')->nullable();
            $table->timestamps();
        });

        Schema::table('recipes', function (Blueprint $table): void {
            $table->foreignId('content_factory_batch_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
            $table->string('content_factory_state', 40)->nullable()->index()->after('editorial_status');
            $table->boolean('fact_check_required')->default(false)->after('content_factory_state');
            $table->foreignId('human_approved_by_user_id')->nullable()->after('editorial_reviewed_by_user_id')->constrained('users')->nullOnDelete();
            $table->timestamp('human_approved_at')->nullable()->after('human_approved_by_user_id');
        });

        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->foreignId('content_factory_batch_id')->nullable()->after('content_brief_id')->constrained()->nullOnDelete();
            $table->string('content_factory_state', 40)->nullable()->index()->after('editorial_status');
            $table->boolean('fact_check_required')->default(false)->after('content_factory_state');
            $table->foreignId('human_approved_by_user_id')->nullable()->after('author_id')->constrained('users')->nullOnDelete();
            $table->timestamp('human_approved_at')->nullable()->after('human_approved_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('content_factory_batch_id');
            $table->dropConstrainedForeignId('human_approved_by_user_id');
            $table->dropColumn(['content_factory_state', 'fact_check_required', 'human_approved_at']);
        });
        Schema::table('recipes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('content_factory_batch_id');
            $table->dropConstrainedForeignId('human_approved_by_user_id');
            $table->dropColumn(['content_factory_state', 'fact_check_required', 'human_approved_at']);
        });
        Schema::dropIfExists('content_topic_clusters');
        Schema::dropIfExists('content_factory_reviews');
        Schema::dropIfExists('content_factory_batches');
    }
};
