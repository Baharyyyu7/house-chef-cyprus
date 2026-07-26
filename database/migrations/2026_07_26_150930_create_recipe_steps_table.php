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
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('step_number');
            $table->string('title');
            $table->text('instructions');
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->unsignedSmallInteger('temperature_celsius')->nullable();
            $table->string('equipment')->nullable();
            $table->string('tip')->nullable();
            $table->string('safety_note')->nullable();
            $table->timestamps();
            $table->unique(['recipe_id', 'step_number']);
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
