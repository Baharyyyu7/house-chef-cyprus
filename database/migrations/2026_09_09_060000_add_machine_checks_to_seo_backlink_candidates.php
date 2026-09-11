<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_backlink_candidates', function (Blueprint $table): void {
            $table->string('check_status', 30)->default('unchecked');
            $table->timestamp('checked_at')->nullable();
            $table->timestamp('next_check_at')->nullable()->index();
            $table->unsignedSmallInteger('last_http_status')->nullable();
            $table->string('link_rel', 120)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('seo_backlink_candidates', function (Blueprint $table): void {
            $table->dropIndex(['next_check_at']);
            $table->dropColumn(['check_status', 'checked_at', 'next_check_at', 'last_http_status', 'link_rel']);
        });
    }
};
