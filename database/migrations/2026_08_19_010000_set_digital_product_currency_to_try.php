<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mağazanın para birimi TL olur.
     *
     * Tablo EUR varsayılanıyla kurulmuştu ama mağaza Kıbrıs ve Türkiye'deki
     * okura satış yapıyor. Varsayılanı değiştirmek yetmez: sonradan eklenen
     * ürünler doğru olur, mevcutlar euro kalırdı - o yüzden mevcut satırlar
     * da taşınır. Fiyat rakamına dokunulmaz; dönüşüm bir iş kararıdır ve
     * göz kararı yapılmaz.
     */
    public function up(): void
    {
        Schema::table('digital_products', function ($table): void {
            $table->string('currency', 3)->default('TRY')->change();
        });

        DB::table('digital_products')->where('currency', 'EUR')->update(['currency' => 'TRY']);
    }

    public function down(): void
    {
        Schema::table('digital_products', function ($table): void {
            $table->string('currency', 3)->default('EUR')->change();
        });
    }
};
