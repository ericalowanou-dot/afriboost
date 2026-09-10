<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->decimal('reward_usd_medium', 10, 2)->nullable()->after('reward_usd');
            $table->decimal('reward_usd_top', 10, 2)->nullable()->after('reward_usd_medium');
        });
    }

    public function down(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn(['reward_usd_medium', 'reward_usd_top']);
        });
    }
};