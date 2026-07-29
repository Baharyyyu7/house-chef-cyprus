<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->string('name', 120);
            $table->string('slug', 160)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('blog_tags', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->string('name', 80);
            $table->string('slug', 120)->unique();
            $table->timestamps();
        });
        Schema::create('blog_posts', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('blog_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('hero_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('excerpt');
            $table->longText('body');
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->string('canonical_url', 2048)->nullable();
            $table->timestamps();
            $table->index(['status', 'published_at']);
        });
        Schema::create('blog_post_blog_tag', function (Blueprint $table): void {
            $table->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blog_tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['blog_post_id', 'blog_tag_id']);
        });
        Schema::create('blog_post_recipe', function (Blueprint $table): void {
            $table->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->primary(['blog_post_id', 'recipe_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_recipe');
        Schema::dropIfExists('blog_post_blog_tag');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_tags');
        Schema::dropIfExists('blog_categories');
    }
};
