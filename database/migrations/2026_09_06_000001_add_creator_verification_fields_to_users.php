<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('registration_step')->default(2)->after('status');
            $table->string('verification_status')->default('pending')->after('registration_step');
            $table->string('creator_tier')->nullable()->after('verification_status');
            $table->timestamp('verified_at')->nullable()->after('creator_tier');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
            $table->text('verification_notes')->nullable()->after('verified_by');
        });

        Schema::table('social_networks', function (Blueprint $table) {
            $table->unsignedInteger('follower_count')->nullable()->after('profile_url');
        });
    }

    public function down(): void
    {
        Schema::table('social_networks', function (Blueprint $table) {
            $table->dropColumn('follower_count');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'registration_step',
                'verification_status',
                'creator_tier',
                'verified_at',
                'verified_by',
                'verification_notes',
            ]);
        });
    }
};
