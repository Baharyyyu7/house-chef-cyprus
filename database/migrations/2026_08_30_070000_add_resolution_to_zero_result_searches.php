<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sıfır sonuçlu arama kaydı kendini düzeltemiyordu.
 *
 * Kayıt yalnız sonuç sıfırken yazılıyordu; katalog büyüyünce ya da arama
 * düzelince satır olduğu yerde kalıyor ve terim sonsuza dek "karşılanmamış
 * talep" görünüyordu. Üretimde tam olarak bu oldu: "kolay tarifler" 24
 * tarif dönerken sistem hâlâ o konuda rehber yazılmasını istiyordu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zero_result_searches', function (Blueprint $tablo): void {
            $tablo->timestamp('resolved_at')->nullable()->after('suggested_action');
            $tablo->unsignedInteger('resolved_result_count')->nullable()->after('resolved_at');
        });
    }

    public function down(): void
    {
        Schema::table('zero_result_searches', function (Blueprint $tablo): void {
            $tablo->dropColumn(['resolved_at', 'resolved_result_count']);
        });
    }
};
