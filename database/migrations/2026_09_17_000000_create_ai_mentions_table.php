<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_mentions', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id', 26)->unique();
            $table->string('engine', 30)->comment('chatgpt, gemini, perplexity, claude');
            $table->string('mode', 30)->default('default')->comment('default, web, deep-research');
            $table->text('prompt');
            $table->text('answer_body');
            $table->boolean('mentioned')->default(false);
            $table->string('brand_context', 500)->nullable()->comment('Yanıtta marka ile ilgili geçen ifade');
            $table->boolean('linked')->default(false)->comment('Sitemize link var mı');
            $table->string('linked_url', 500)->nullable();
            $table->json('cited_sources')->nullable()->comment('Yanıttaki kaynak linkleri');
            $table->json('competitor_mentions')->nullable()->comment('Geçen rakip isimleri');
            $table->string('status', 20)->default('new')->comment('new, reviewed, action_needed');
            $table->text('notes')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_mentions');
    }
};
