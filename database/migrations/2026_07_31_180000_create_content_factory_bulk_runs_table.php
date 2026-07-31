<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_factory_bulk_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->string('group_key', 80)->index();
            $table->string('fingerprint', 64)->index();
            $table->json('result');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at')->index();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_factory_bulk_runs');
    }
};
