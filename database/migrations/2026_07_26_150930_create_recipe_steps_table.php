<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recipe_steps', function (Blueprint $table) {
            $table->id();
            $table->string('public_id')->unique();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->string('title');
            $table->text('instruction');
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedSmallInteger('temperature_value')->nullable();
            $table->string('temperature_unit', 12)->nullable();
            $table->boolean('timer_required')->default(false);
            $table->boolean('is_optional')->default(false);
            $table->string('equipment')->nullable();
            $table->text('chef_tip')->nullable();
            $table->text('safety_note')->nullable();
            $table->string('animation_key')->nullable();
            $table->string('completion_hint')->nullable();
            $table->timestamps();
            $table->unique(['recipe_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_steps');
    }
};
