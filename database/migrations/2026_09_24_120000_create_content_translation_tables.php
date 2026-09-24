<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Localised editions of published recipes and blog posts.
 *
 * The Turkish base tables stay the source of identity, timing, media and
 * relations. A translation row only carries reader-facing copy plus its own
 * locale-specific slug, so /en URLs never change the existing Turkish URLs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 8);
            $table->string('slug', 191);
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->text('description')->nullable();
            $table->string('cuisine_label')->nullable();
            $table->string('category_label')->nullable();
            $table->string('difficulty_label')->nullable();
            $table->unsignedSmallInteger('prep_minutes')->nullable();
            $table->unsignedSmallInteger('cook_minutes')->nullable();
            $table->unsignedSmallInteger('rest_minutes')->nullable();
            $table->unsignedSmallInteger('servings')->nullable();
            $table->json('ingredients')->nullable();
            $table->json('steps')->nullable();
            $table->json('equipment')->nullable();
            $table->json('tips')->nullable();
            $table->json('common_mistakes')->nullable();
            $table->json('keywords')->nullable();
            $table->text('serving_suggestions')->nullable();
            $table->text('storage_notes')->nullable();
            $table->text('freezing_notes')->nullable();
            $table->text('reheating_notes')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('status', 24)->default('published');
            $table->string('source_hash', 64)->nullable();
            $table->timestamp('translated_at')->nullable();
            $table->timestamps();

            $table->unique(['recipe_id', 'locale']);
            $table->unique(['locale', 'slug']);
            $table->index(['locale', 'status']);
        });

        Schema::create('blog_post_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 8);
            $table->string('slug', 191);
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->string('category_label')->nullable();
            $table->json('keywords')->nullable();
            $table->json('faq_blocks')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('status', 24)->default('published');
            $table->string('source_hash', 64)->nullable();
            $table->timestamp('translated_at')->nullable();
            $table->timestamps();

            $table->unique(['blog_post_id', 'locale']);
            $table->unique(['locale', 'slug']);
            $table->index(['locale', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_translations');
        Schema::dropIfExists('recipe_translations');
    }
};
