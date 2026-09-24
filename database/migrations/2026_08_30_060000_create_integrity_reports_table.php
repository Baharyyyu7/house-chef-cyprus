<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Otonom denetimlerin koşu geçmişi.
 *
 * Muhafızlar günlük koşuyordu ama sonuçları yalnız log'a yazıyordu: kimse
 * görmüyordu ve iki koşuyu karşılaştırmak mümkün değildi. "Eksik görsel 59"
 * tek başına bir sayı; "dün 62'ydi" ise ilerleme.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrity_reports', function (Blueprint $tablo): void {
            $tablo->id();
            $tablo->string('guard', 64);
            $tablo->string('status', 32);
            // Manşet sayı: her muhafızın tek bir "kaç tane" göstergesi var
            // (eksik görsel, engelli çeviri, bulgu sayısı). Trend bunun
            // üzerinden okunuyor, bu yüzden payload içinde gömülü değil.
            $tablo->unsignedInteger('headline')->nullable();
            $tablo->json('payload');
            $tablo->timestamps();

            $tablo->index(['guard', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrity_reports');
    }
};
