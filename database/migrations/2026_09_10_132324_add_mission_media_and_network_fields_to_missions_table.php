<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->json('social_networks')->nullable()->after('social_network');
            $table->json('network_budgets')->nullable()->after('reward_usd_top');
            $table->unsignedInteger('content_retention_days')->nullable()->after('ends_at');
            $table->string('content_example_path')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn([
                'social_networks',
                'network_budgets',
                'content_retention_days',
                'content_example_path',
            ]);
        });
    }
};
