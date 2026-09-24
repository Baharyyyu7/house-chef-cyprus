<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ziyaretçi ülkesinin günlük toplamı.
     *
     * Yalnız sayaç tutulur: IP, şehir, oturum kimliği ya da kullanıcıya
     * bağlanabilecek herhangi bir alan yazılmaz. Bir gün ve bir ülke için tek
     * satır vardır, o satır artar.
     */
    public function up(): void
    {
        Schema::create('audience_country_days', function (Blueprint $table): void {
            $table->id();
            $table->date('day');
            $table->string('country_code', 2);
            $table->unsignedInteger('hits')->default(0);
            $table->timestamps();
            $table->unique(['day', 'country_code']);
            $table->index('day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audience_country_days');
    }
};
