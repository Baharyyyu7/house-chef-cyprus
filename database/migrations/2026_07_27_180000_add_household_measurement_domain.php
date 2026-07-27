<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table): void {
            $table->string('singular_name')->nullable()->after('name');
            $table->string('plural_name')->nullable()->after('singular_name');
            $table->string('short_label', 20)->nullable()->after('symbol');
            $table->boolean('supports_fractions')->default(false)->after('precision');
            $table->boolean('is_user_visible')->default(true)->after('is_active');
            $table->boolean('is_canonical')->default(false)->after('is_user_visible');
        });

        Schema::create('ingredient_unit_conversions', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->string('household_measure_code', 50);
            $table->decimal('household_quantity', 12, 4)->default(1);
            $table->decimal('canonical_quantity', 14, 4);
            $table->string('canonical_unit', 20);
            $table->decimal('density_g_per_ml', 14, 6)->nullable();
            $table->string('source_type', 30);
            $table->string('source_name')->nullable();
            $table->string('source_url', 2048)->nullable();
            $table->string('verification_status', 20)->default('unknown');
            $table->unsignedTinyInteger('confidence_score')->default(0);
            $table->string('conversion_method', 30)->default('ingredient_specific');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();
            $table->unique(['ingredient_id', 'household_measure_code', 'household_quantity', 'canonical_unit', 'is_active'], 'ingredient_unit_conversion_unique');
            $table->index(['ingredient_id', 'household_measure_code', 'is_active']);
        });

        Schema::table('recipe_ingredients', function (Blueprint $table): void {
            $table->decimal('display_quantity', 12, 4)->nullable()->after('quantity');
            $table->string('display_measure_code', 50)->nullable()->after('unit');
            $table->string('display_note')->nullable()->after('display_measure_code');
            $table->decimal('canonical_quantity', 14, 4)->nullable()->after('grams');
            $table->string('canonical_unit', 20)->nullable()->after('canonical_quantity');
            $table->foreignId('ingredient_unit_conversion_id')->nullable()->after('unit_id')->constrained('ingredient_unit_conversions')->nullOnDelete();
            $table->string('conversion_confidence', 20)->default('unknown')->after('ingredient_unit_conversion_id');
            $table->string('conversion_method', 30)->default('not_convertible')->after('conversion_confidence');
            $table->string('scaling_behavior', 30)->default('scalable')->after('can_be_substituted');
            $table->string('scaling_note')->nullable()->after('scaling_behavior');
            $table->string('manual_override_reason')->nullable()->after('scaling_note');
            $table->index(['recipe_id', 'display_measure_code']);
        });

        Schema::table('pantry_items', function (Blueprint $table): void {
            $table->decimal('canonical_quantity', 14, 4)->nullable()->after('quantity');
            $table->string('canonical_unit', 20)->nullable()->after('canonical_quantity');
            $table->string('conversion_confidence', 20)->default('unknown')->after('canonical_unit');
        });
    }

    public function down(): void
    {
        Schema::table('pantry_items', function (Blueprint $table): void {
            $table->dropColumn(['canonical_quantity', 'canonical_unit', 'conversion_confidence']);
        });
        Schema::table('recipe_ingredients', function (Blueprint $table): void {
            $table->dropForeign(['ingredient_unit_conversion_id']);
            $table->dropIndex(['recipe_id', 'display_measure_code']);
            $table->dropColumn(['display_quantity', 'display_measure_code', 'display_note', 'canonical_quantity', 'canonical_unit', 'ingredient_unit_conversion_id', 'conversion_confidence', 'conversion_method', 'scaling_behavior', 'scaling_note', 'manual_override_reason']);
        });
        Schema::dropIfExists('ingredient_unit_conversions');
        Schema::table('units', function (Blueprint $table): void {
            $table->dropColumn(['singular_name', 'plural_name', 'short_label', 'supports_fractions', 'is_user_visible', 'is_canonical']);
        });
    }
};
