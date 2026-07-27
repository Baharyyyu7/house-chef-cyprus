<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->string('visibility', 10)->default('public')->index();
            $table->string('alt_text', 255)->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('processing_status', 20)->default('ready')->index();
            $table->string('thumbnail_status', 20)->default('pending')->index();
            $table->json('thumbnail_paths')->nullable();
            $table->string('processing_error_code', 64)->nullable();
            $table->string('processing_error_message', 255)->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->index(['disk', 'created_at'], 'media_disk_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->dropIndex('media_disk_created_index');
            $table->dropIndex(['visibility']);
            $table->dropIndex(['processing_status']);
            $table->dropIndex(['thumbnail_status']);
            $table->dropColumn(['visibility', 'alt_text', 'width', 'height', 'processing_status', 'thumbnail_status', 'thumbnail_paths', 'processing_error_code', 'processing_error_message', 'processed_at']);
        });
    }
};
