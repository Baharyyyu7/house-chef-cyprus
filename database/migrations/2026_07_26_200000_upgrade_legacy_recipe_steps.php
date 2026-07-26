<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('recipe_steps', 'step_number')) {
            return;
        }

        Schema::table('recipe_steps', function (Blueprint $table): void {
            $table->string('public_id')->nullable()->after('id');
            $table->unsignedSmallInteger('position')->nullable()->after('recipe_id');
            $table->text('instruction')->nullable()->after('title');
            $table->unsignedInteger('duration_seconds')->nullable()->after('instruction');
            $table->unsignedSmallInteger('temperature_value')->nullable()->after('duration_seconds');
            $table->string('temperature_unit', 12)->nullable()->after('temperature_value');
            $table->boolean('timer_required')->default(false)->after('temperature_unit');
            $table->boolean('is_optional')->default(false)->after('timer_required');
            $table->text('chef_tip')->nullable()->after('equipment');
            $table->string('animation_key')->nullable()->after('safety_note');
            $table->string('completion_hint')->nullable()->after('animation_key');
        });

        DB::table('recipe_steps')->orderBy('id')->each(function (object $step): void {
            DB::table('recipe_steps')->where('id', $step->id)->update([
                'public_id' => (string) Str::ulid(),
                'position' => $step->step_number,
                'instruction' => $step->instructions,
                'duration_seconds' => $step->duration_minutes === null ? null : $step->duration_minutes * 60,
                'temperature_value' => $step->temperature_celsius,
                'temperature_unit' => $step->temperature_celsius === null ? null : 'celsius',
                'chef_tip' => $step->tip,
            ]);
        });

        Schema::table('recipe_steps', function (Blueprint $table): void {
            $table->unique('public_id');
            $table->unique(['recipe_id', 'position']);
        });
    }

    public function down(): void
    {
        // Legacy schemas are intentionally not reconstructed on rollback.
    }
};
