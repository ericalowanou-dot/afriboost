<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_networks', function (Blueprint $table) {
            $table->string('creator_tier')->nullable()->after('follower_count');
        });
    }

    public function down(): void
    {
        Schema::table('social_networks', function (Blueprint $table) {
            $table->dropColumn('creator_tier');
        });
    }
};
