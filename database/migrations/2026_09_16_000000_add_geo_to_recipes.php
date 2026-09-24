<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table): void {
            $table->decimal('geo_latitude', 10, 7)->nullable()->after('country');
            $table->decimal('geo_longitude', 10, 7)->nullable()->after('geo_latitude');
            $table->string('geo_region', 100)->nullable()->after('geo_longitude');
            $table->string('geo_country', 100)->nullable()->after('geo_region');
            $table->string('geo_city', 100)->nullable()->after('geo_country');
        });
    }

    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table): void {
            $table->dropColumn(['geo_latitude', 'geo_longitude', 'geo_region', 'geo_country', 'geo_city']);
        });
    }
};
