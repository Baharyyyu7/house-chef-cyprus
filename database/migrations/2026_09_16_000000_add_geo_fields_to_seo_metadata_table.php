<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_metadata', function (Blueprint $table): void {
            $table->string('geo_region')->nullable()->after('primary_query');
            $table->string('geo_country')->nullable()->after('geo_region');
        });
    }

    public function down(): void
    {
        Schema::table('seo_metadata', function (Blueprint $table): void {
            $table->dropColumn(['geo_region', 'geo_country']);
        });
    }
};
