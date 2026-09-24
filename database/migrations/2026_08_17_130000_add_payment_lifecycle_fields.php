<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_orders', function (Blueprint $table): void {
            $table->timestamp('checkout_expires_at')->nullable()->index()->after('checkout_session_id');
            $table->string('dispute_id')->nullable()->index()->after('status');
            $table->timestamp('disputed_at')->nullable()->after('dispute_id');
        });

        Schema::table('tip_contributions', function (Blueprint $table): void {
            $table->timestamp('checkout_expires_at')->nullable()->index()->after('checkout_session_id');
            $table->string('dispute_id')->nullable()->index()->after('status');
            $table->timestamp('disputed_at')->nullable()->after('dispute_id');
        });
    }

    public function down(): void
    {
        Schema::table('tip_contributions', function (Blueprint $table): void {
            $table->dropIndex(['checkout_expires_at']);
            $table->dropIndex(['dispute_id']);
            $table->dropColumn(['checkout_expires_at', 'dispute_id', 'disputed_at']);
        });

        Schema::table('digital_orders', function (Blueprint $table): void {
            $table->dropIndex(['checkout_expires_at']);
            $table->dropIndex(['dispute_id']);
            $table->dropColumn(['checkout_expires_at', 'dispute_id', 'disputed_at']);
        });
    }
};
