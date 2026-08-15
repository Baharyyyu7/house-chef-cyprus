<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * media.kind varchar(20) olarak oluşturulmuştu ama sözlük o sınırı aştı:
 * "editorial_synthetic_illustration" 32, "external_hotlinked_media" 24 karakter.
 * SQLite uzunluk kısıtı uygulamadığı için yerelde ve testlerde görünmüyor;
 * MySQL üretimde 1406 "Data too long" ile deploy'u durduruyor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->string('kind', 64)->change();
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->string('kind', 20)->change();
        });
    }
};
