<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->foreignId('uploaded_by_user_id')->nullable()->after('media_reviewer_user_id')->constrained('users')->nullOnDelete();
        });

        // Onaylanmamış görselde media_reviewer_user_id yükleyenin (ya da sistem
        // beyanının) kimliğidir; onay bu alanı ezdiği için yükleyen buradan alınır.
        // Onaylananlar yayında, dokunulmaz.
        DB::table('media')
            ->whereNull('approved_by_user_id')
            ->whereNotNull('media_reviewer_user_id')
            ->update(['uploaded_by_user_id' => DB::raw('media_reviewer_user_id')]);
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('uploaded_by_user_id');
        });
    }
};
