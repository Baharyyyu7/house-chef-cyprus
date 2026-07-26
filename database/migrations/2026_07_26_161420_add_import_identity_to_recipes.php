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
        Schema::table('recipes', function (Blueprint $table) { $table->foreignId('data_source_id')->nullable()->constrained()->nullOnDelete(); $table->string('external_id')->nullable(); $table->string('content_origin', 30)->default('editorial'); $table->string('original_hash', 64)->nullable(); $table->timestamp('first_imported_at')->nullable(); $table->timestamp('last_verified_at')->nullable(); $table->unique(['data_source_id','external_id']); });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) { $table->dropUnique(['data_source_id','external_id']); $table->dropConstrainedForeignId('data_source_id'); $table->dropColumn(['external_id','content_origin','original_hash','first_imported_at','last_verified_at']); });
    }
};
