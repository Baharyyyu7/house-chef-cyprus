<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('account_status', 24)->default('active')->index();
            $table->timestamp('last_login_at')->nullable()->index();
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->string('last_login_ip_masked', 64)->nullable();
            $table->string('last_login_ip_hash', 64)->nullable()->index();
            $table->string('last_country_code', 2)->nullable()->index();
            $table->string('last_city_name', 120)->nullable();
        });

        Schema::create('user_activities', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 80)->index();
            $table->string('route_name', 160)->nullable()->index();
            $table->string('ip_masked', 64)->nullable();
            $table->string('ip_hash', 64)->nullable()->index();
            $table->string('device_summary', 255)->nullable();
            $table->string('status', 24)->default('success')->index();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
            $table->index(['user_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activities');
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['account_status']);
            $table->dropIndex(['last_login_at']);
            $table->dropIndex(['last_activity_at']);
            $table->dropIndex(['last_login_ip_hash']);
            $table->dropIndex(['last_country_code']);
            $table->dropColumn(['account_status', 'last_login_at', 'last_activity_at', 'last_login_ip_masked', 'last_login_ip_hash', 'last_country_code', 'last_city_name']);
        });
    }
};
