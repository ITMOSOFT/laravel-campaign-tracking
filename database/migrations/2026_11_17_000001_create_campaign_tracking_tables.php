<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('itmosoft-campaign-tracking.table_prefix', 'campaign_tracking_');
        $touches = $prefix.config('itmosoft-campaign-tracking.tables.touches', 'touches');
        $conversions = $prefix.config('itmosoft-campaign-tracking.tables.conversions', 'conversions');

        Schema::create($touches, function (Blueprint $table): void {
            $table->id();
            $table->string('campaign')->nullable()->index();
            $table->string('source')->nullable()->index();
            $table->string('medium')->nullable();
            $table->string('term')->nullable();
            $table->string('content')->nullable();
            $table->string('landing_url', 2048)->nullable();
            $table->string('visitor_id', 191)->nullable()->index();
            $table->string('session_id', 191)->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamp('touched_at')->index();
            $table->timestamps();
        });

        Schema::create($conversions, function (Blueprint $table) use ($touches): void {
            $table->id();
            $table->foreignId('touch_id')->nullable()->constrained($touches)->nullOnDelete();
            $table->string('event', 100)->index();
            $table->string('external_id', 191)->nullable();
            $table->string('idempotency_key', 191)->nullable()->unique();
            $table->decimal('value', 14, 4)->nullable();
            $table->char('currency', 3)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('converted_at')->index();
            $table->timestamps();
            $table->index(['event', 'converted_at']);
        });
    }

    public function down(): void
    {
        $prefix = config('itmosoft-campaign-tracking.table_prefix', 'campaign_tracking_');
        Schema::dropIfExists($prefix.config('itmosoft-campaign-tracking.tables.conversions', 'conversions'));
        Schema::dropIfExists($prefix.config('itmosoft-campaign-tracking.tables.touches', 'touches'));
    }
};
