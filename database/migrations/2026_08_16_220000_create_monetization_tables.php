<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Users tablosuna premium alanları ekleme
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_premium')) {
                $table->boolean('is_premium')->default(false)->after('role');
            }
            if (! Schema::hasColumn('users', 'premium_plan')) {
                $table->string('premium_plan')->nullable()->after('is_premium'); // monthly, yearly, lifetime
            }
            if (! Schema::hasColumn('users', 'premium_expires_at')) {
                $table->timestamp('premium_expires_at')->nullable()->after('premium_plan');
            }
            if (! Schema::hasColumn('users', 'stripe_customer_id')) {
                $table->string('stripe_customer_id')->nullable()->index()->after('premium_expires_at');
            }
            if (! Schema::hasColumn('users', 'ai_daily_usage_count')) {
                $table->unsignedInteger('ai_daily_usage_count')->default(0)->after('stripe_customer_id');
            }
            if (! Schema::hasColumn('users', 'ai_usage_last_reset_at')) {
                $table->date('ai_usage_last_reset_at')->nullable()->after('ai_daily_usage_count');
            }
        });

        // 2. Abonelik / Ödeme Geçmişi Tablosu
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan'); // monthly, yearly
            $table->decimal('amount', 8, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('payment_provider')->default('stripe'); // stripe, iyzico, paytr, manual
            $table->string('provider_subscription_id')->nullable()->index();
            $table->string('status')->default('active'); // active, cancelled, expired, pending
            $table->timestamp('started_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 3. Affiliate Ürün ve Linkleri Tablosu
        Schema::create('affiliate_links', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->default('equipment'); // equipment, ingredient, book, gadget
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('target_url'); // Gerçek affiliate linki
            $table->string('provider')->default('amazon'); // amazon, trendyol, hepsiburada, local_market
            $table->decimal('price', 8, 2)->nullable();
            $table->string('currency', 3)->default('TRY');
            $table->unsignedBigInteger('click_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Dijital Ürünler (E-Kitap, Özel Menü Paketleri) Tablosu
        Schema::create('digital_products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('features')->nullable(); // JSON or text
            $table->decimal('price', 8, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('cover_image')->nullable();
            $table->string('file_path')->nullable(); // Protected digital file
            $table->unsignedInteger('page_count')->nullable();
            $table->unsignedInteger('sales_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Dijital Ürün Siparişleri Tablosu
        Schema::create('digital_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('order_uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('digital_product_id')->constrained()->cascadeOnDelete();
            $table->string('buyer_email');
            $table->string('buyer_name')->nullable();
            $table->decimal('amount', 8, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('payment_provider')->default('stripe');
            $table->string('provider_payment_id')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed, refunded
            $table->string('download_token')->unique();
            $table->unsignedInteger('download_count')->default(0);
            $table->unsignedInteger('max_downloads')->default(5);
            $table->timestamps();
        });

        // 6. Kahve Ismarla / Destek (Tip Jar) Tablosu
        Schema::create('tip_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('supporter_name')->default('Anonim Gurme');
            $table->string('supporter_email')->nullable();
            $table->decimal('amount', 8, 2);
            $table->string('currency', 3)->default('EUR');
            $table->text('message')->nullable();
            $table->string('payment_provider')->default('stripe');
            $table->string('provider_payment_id')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tip_contributions');
        Schema::dropIfExists('digital_orders');
        Schema::dropIfExists('digital_products');
        Schema::dropIfExists('affiliate_links');
        Schema::dropIfExists('user_subscriptions');

        Schema::table('users', function (Blueprint $table) {
            // stripe_customer_id indeksli oluşturuldu; SQLite indeksi duran
            // sütunu düşüremiyor, önce indeks kaldırılmalı.
            $table->dropIndex(['stripe_customer_id']);
            $table->dropColumn([
                'is_premium',
                'premium_plan',
                'premium_expires_at',
                'stripe_customer_id',
                'ai_daily_usage_count',
                'ai_usage_last_reset_at',
            ]);
        });
    }
};
