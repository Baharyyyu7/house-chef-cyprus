<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Claude Coni'nin iş defteri.
 *
 * Her satır canlıya alınmış tek bir değişiklik: hangi dilde, hangi sayfada,
 * neden, değişiklikten önceki ölçüm ve sonradan ölçülen sonuç. Satır
 * silinmez; sonuç geldiğinde durumu değişir. Üç hafta sonra "bu başlığı
 * neden değiştirdik" sorusunun cevabı buradadır.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coni_actions', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 8);
            $table->string('page', 512);
            $table->char('page_hash', 64);
            $table->string('kind', 32);
            $table->string('status', 20)->default('uygulandi');
            $table->text('reason');
            $table->json('evidence')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->json('baseline')->nullable();
            $table->json('result')->nullable();
            $table->timestamp('applied_at');
            $table->date('evaluate_after');
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();
            $table->index(['locale', 'status']);
            $table->index(['kind', 'status']);
            $table->index('page_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coni_actions');
    }
};
