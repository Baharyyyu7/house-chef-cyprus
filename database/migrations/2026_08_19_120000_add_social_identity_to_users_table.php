<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sosyal giriş kimliği.
     *
     * Sağlayıcının verdiği kimlik e-postadan ayrı tutulur: kullanıcı Google
     * hesabındaki e-postayı değiştirse bile aynı hesaba girmeye devam eder.
     * Şifre alanı sosyal girişte boş kalabilir, o yüzden nullable yapılır.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('social_provider', 20)->nullable()->after('registration_source');
            $table->string('social_id')->nullable()->after('social_provider');
            $table->index(['social_provider', 'social_id']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['social_provider', 'social_id']);
            $table->dropColumn(['social_provider', 'social_id']);
        });
    }
};
