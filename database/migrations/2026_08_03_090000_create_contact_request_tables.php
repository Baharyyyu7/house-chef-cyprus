<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 254)->index();
            $table->string('phone', 32)->nullable()->index();
            $table->string('subject', 60)->index();
            $table->text('message');
            $table->string('status', 24)->default('new')->index();
            $table->string('priority', 16)->default('normal')->index();
            $table->foreignId('assigned_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source_page', 2048)->nullable();
            $table->string('referrer_url', 2048)->nullable();
            $table->string('utm_source', 120)->nullable()->index();
            $table->string('utm_medium', 120)->nullable();
            $table->string('utm_campaign', 160)->nullable()->index();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('privacy_consent_at');
            $table->timestamp('first_responded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('submitted_at')->index();
            $table->timestamp('anonymized_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contact_request_replies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contact_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->timestamp('sent_at')->index();
            $table->timestamps();
        });

        Schema::create('contact_request_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contact_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('contact_request_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contact_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 24)->nullable();
            $table->string('to_status', 24);
            $table->string('note', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_request_status_histories');
        Schema::dropIfExists('contact_request_notes');
        Schema::dropIfExists('contact_request_replies');
        Schema::dropIfExists('contact_requests');
    }
};
