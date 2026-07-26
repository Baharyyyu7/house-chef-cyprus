<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allergens', function (Blueprint $table): void {
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
        });
        Schema::table('dietary_tags', function (Blueprint $table): void {
            $table->string('rule_type', 30)->default('manual');
            $table->json('threshold_config')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
        });
        Schema::table('nutrition_facts', function (Blueprint $table): void {
            $table->boolean('is_active')->default(true);
        });
        Schema::table('recipe_allergens', function (Blueprint $table): void {
            $table->string('source_type', 30)->default('editorial');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('updated_at')->nullable();
        });
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 100);
            $table->nullableMorphs('auditable');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
