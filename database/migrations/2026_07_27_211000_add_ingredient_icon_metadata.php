<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table): void {
            $table->string('icon_key', 64)->nullable()->index();
            $table->string('icon_label', 120)->nullable();
            $table->string('icon_status', 24)->default('missing')->index();
        });
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table): void {
            $table->dropColumn(['icon_key', 'icon_label', 'icon_status']);
        });
    }
};
