<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restore_histories', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('backup_id', 26);
            $table->string('target');
            $table->foreignId('triggered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('triggered_by_type')->default('system');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->string('status', 20);
            $table->boolean('checksum_verified')->default(false);
            $table->boolean('database_opened')->default(false);
            $table->boolean('migration_table_verified')->default(false);
            $table->unsignedInteger('user_count_before')->nullable();
            $table->unsignedInteger('user_count_after')->nullable();
            $table->unsignedInteger('recipe_count_before')->nullable();
            $table->unsignedInteger('recipe_count_after')->nullable();
            $table->unsignedInteger('ingredient_count_before')->nullable();
            $table->unsignedInteger('ingredient_count_after')->nullable();
            $table->boolean('relations_verified')->default(false);
            $table->string('health_check_status', 20)->nullable();
            $table->string('safe_error_code', 64)->nullable();
            $table->string('safe_error_message', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restore_histories');
    }
};
