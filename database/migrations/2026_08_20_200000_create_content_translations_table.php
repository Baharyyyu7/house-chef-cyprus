<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * İçerik çevirileri.
 *
 * Site dört dilde arayüz sunuyor ama içeriği saklayacak yer yoktu: recipes
 * tablosunda tek bir title, slug, excerpt var. Bir dili açmak, İngilizce
 * menülerin altında Türkçe tarif göstermek anlamına geliyordu. Google bir
 * sayfanın dilini görünen içerikten belirler; sonuç aynı içeriğin ikinci bir
 * kopyası ve zayıf dil sayfaları olurdu. Yardımcı içerik sistemi site geneli
 * çalıştığı için bu, güçlü Türkçe sayfaların sıralamasını da düşürürdü.
 *
 * Alan bazlı satır tutuluyor: her yeni çevrilebilir alan için tabloya kolon
 * eklemek gerekmiyor ve kısmi çeviri doğal olarak temsil edilebiliyor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_translations', function (Blueprint $table): void {
            $table->id();
            $table->morphs('translatable');
            $table->string('locale', 8);
            $table->string('field', 64);
            $table->longText('value')->nullable();
            $table->timestamps();

            // Bir kayıt, bir dil, bir alan için tek satır.
            $table->unique(['translatable_type', 'translatable_id', 'locale', 'field'], 'content_translations_unique');

            // "Bu dilde hangi kayıtlar var" sorgusu listeleme, sitemap ve
            // hreflang üretiminin hepsinde çalışıyor.
            $table->index(['translatable_type', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_translations');
    }
};
