<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('first_name', 100)->nullable()->after('name');
            $table->string('last_name', 100)->nullable()->after('first_name');
            $table->string('phone', 32)->nullable()->after('email');
            $table->string('country', 120)->nullable()->after('phone');
            $table->string('city_or_region', 120)->nullable()->after('country');
            $table->string('preferred_language', 10)->default('tr')->after('city_or_region');
            $table->string('registration_source', 80)->nullable()->after('preferred_language');
            $table->timestamp('terms_accepted_at')->nullable()->after('registration_source');
            $table->timestamp('marketing_consent_at')->nullable()->after('terms_accepted_at');
            $table->index('country', 'users_country_idx');
            $table->index('registration_source', 'users_reg_source_idx');
        });

        Schema::create('member_registration_notifications', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id')->unique('member_reg_notif_public_unique');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('idempotency_key', 64)->unique('member_reg_notif_idem_unique');
            $table->string('recipient');
            $table->string('status', 24)->default('pending')->index('member_reg_notif_status_idx');
            $table->unsignedSmallInteger('attempt_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('failure_code', 80)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_registration_notifications');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_country_idx');
            $table->dropIndex('users_reg_source_idx');
            $table->dropColumn([
                'first_name', 'last_name', 'phone', 'country', 'city_or_region',
                'preferred_language', 'registration_source', 'terms_accepted_at',
                'marketing_consent_at',
            ]);
        });
    }
};
