<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipe_media_candidates', function (Blueprint $table): void {
            $table->text('description')->nullable()->after('alt_text');
            $table->text('alt_description')->nullable()->after('description');
            $table->text('external_page_url')->nullable()->after('pexels_page_url');
            $table->text('download_location')->nullable()->after('external_page_url');
            $table->text('small_url')->nullable()->after('source_url');
            $table->text('thumb_url')->nullable()->after('small_url');
            $table->string('orientation', 16)->nullable()->after('height');
            $table->string('color', 16)->nullable()->after('orientation');
            $table->string('blur_hash', 128)->nullable()->after('color');
            $table->string('utm_source', 100)->nullable()->after('blur_hash');
            $table->string('utm_medium', 100)->nullable()->after('utm_source');
            $table->timestamp('download_tracked_at')->nullable()->after('selected_at');
            $table->string('tracking_status', 32)->nullable()->after('download_tracked_at');
            $table->string('tracking_error_code', 64)->nullable()->after('tracking_status');
        });
    }

    public function down(): void
    {
        Schema::table('recipe_media_candidates', function (Blueprint $table): void {
            $table->dropColumn(['description', 'alt_description', 'external_page_url', 'download_location', 'small_url', 'thumb_url', 'orientation', 'color', 'blur_hash', 'utm_source', 'utm_medium', 'download_tracked_at', 'tracking_status', 'tracking_error_code']);
        });
    }
};
