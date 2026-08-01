<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_quality_profiles', function (Blueprint $table): void {
            $table->string('graduation_stage', 50)->default('draft')->index()->after('content_type');
            $table->foreignId('human_approved_by_user_id')->nullable()->constrained('users')->nullOnDelete()->after('last_scanned_at');
            $table->timestamp('human_approved_at')->nullable()->after('human_approved_by_user_id');
            $table->foreignId('published_by_user_id')->nullable()->constrained('users')->nullOnDelete()->after('human_approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('content_quality_profiles', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('published_by_user_id');
            $table->dropColumn('human_approved_at');
            $table->dropConstrainedForeignId('human_approved_by_user_id');
            $table->dropIndex(['graduation_stage']);
            $table->dropColumn('graduation_stage');
        });
    }
};
