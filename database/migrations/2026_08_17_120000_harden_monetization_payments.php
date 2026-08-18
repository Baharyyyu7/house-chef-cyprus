<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table): void {
            $table->unique('provider_subscription_id', 'user_subscriptions_provider_subscription_unique');
        });

        Schema::table('digital_orders', function (Blueprint $table): void {
            $table->string('checkout_session_id')->nullable()->unique()->after('payment_provider');
            $table->index('provider_payment_id');
        });

        Schema::table('tip_contributions', function (Blueprint $table): void {
            $table->string('checkout_session_id')->nullable()->unique()->after('payment_provider');
            $table->index('provider_payment_id');
        });

        Schema::create('stripe_webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->string('event_id')->unique();
            $table->string('event_type');
            $table->string('payload_hash', 64);
            $table->timestamp('processed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_webhook_events');

        // checkout_session_id benzersiz indeksle oluşturuldu. SQLite'ta
        // indeksi duran bir sütun düşürülemez; indeks önce kaldırılmalı.
        // Aksi hâlde geri alma yolu hata veriyordu, yani deploy ters
        // gittiğinde başvurulacak emniyet çalışmıyordu.
        Schema::table('tip_contributions', function (Blueprint $table): void {
            $table->dropUnique(['checkout_session_id']);
            $table->dropIndex(['provider_payment_id']);
            $table->dropColumn('checkout_session_id');
        });

        Schema::table('digital_orders', function (Blueprint $table): void {
            $table->dropUnique(['checkout_session_id']);
            $table->dropIndex(['provider_payment_id']);
            $table->dropColumn('checkout_session_id');
        });

        Schema::table('user_subscriptions', function (Blueprint $table): void {
            $table->dropUnique('user_subscriptions_provider_subscription_unique');
        });
    }
};
