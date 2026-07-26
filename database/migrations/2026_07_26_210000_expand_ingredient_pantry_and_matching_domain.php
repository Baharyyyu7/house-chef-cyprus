<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table): void {
            $table->string('canonical_name')->nullable()->after('name');
            $table->string('category')->nullable()->after('slug');
            $table->foreignId('default_unit_id')->nullable()->after('default_unit')->constrained('units')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_allergen')->default(false);
            $table->boolean('is_perishable')->default(false);
            $table->unsignedSmallInteger('typical_shelf_life_days')->nullable();
            $table->string('storage_type', 20)->nullable();
            $table->text('notes')->nullable();
        });

        Schema::create('ingredient_aliases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->string('alias');
            $table->string('normalized_alias');
            $table->string('locale', 8)->default('tr');
            $table->string('source_type', 30)->default('editorial');
            $table->decimal('confidence_score', 5, 2)->default(100);
            $table->timestamps();
            $table->unique(['ingredient_id', 'normalized_alias', 'locale']);
            $table->index(['normalized_alias', 'locale']);
        });

        Schema::create('ingredient_substitutions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('substitute_ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->string('substitution_type', 30);
            $table->decimal('ratio', 10, 3)->default(1);
            $table->string('unit_compatibility', 30)->nullable();
            $table->string('context')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('confidence_score', 5, 2)->default(100);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['ingredient_id', 'substitute_ingredient_id', 'substitution_type']);
        });

        Schema::table('recipe_ingredients', function (Blueprint $table): void {
            $table->boolean('is_required')->default(true)->after('is_optional');
            $table->string('group_name')->nullable()->after('is_required');
            $table->string('preparation_note')->nullable()->after('preparation');
            $table->boolean('can_be_substituted')->default(true)->after('substitution');
        });
        Schema::table('recipe_ingredients', function (Blueprint $table): void {
            $table->dropUnique(['recipe_id', 'ingredient_id']);
            $table->index(['recipe_id', 'sort_order']);
        });

        Schema::table('pantry_items', function (Blueprint $table): void {
            $table->foreignId('unit_id')->nullable()->after('unit')->constrained('units')->nullOnDelete();
            $table->timestamp('opened_at')->nullable()->after('expires_at');
            $table->string('storage_location', 20)->default('pantry')->after('opened_at');
            $table->text('notes')->nullable()->after('storage_location');
            $table->boolean('is_available')->default(true)->after('notes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredient_substitutions');
        Schema::dropIfExists('ingredient_aliases');
    }
};
