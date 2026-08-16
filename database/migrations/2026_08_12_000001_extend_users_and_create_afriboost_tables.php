<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('role')->default('creator')->after('phone'); // creator | admin
            $table->string('status')->default('active')->after('role'); // active | suspended | blocked
            $table->text('status_reason')->nullable()->after('status');
            $table->string('avatar_path')->nullable()->after('status_reason');
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('title');
            $table->text('objective')->nullable();
            $table->decimal('budget_usd', 12, 2)->nullable();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->string('status')->default('draft'); // draft | active | completed | archived
            $table->timestamps();
        });

        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->string('brand_name');
            $table->string('title');
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->text('objective')->nullable();
            $table->text('instructions')->nullable();
            $table->text('validation_criteria')->nullable();
            $table->string('social_network'); // facebook | tiktok | instagram | youtube
            $table->string('content_type')->default('video'); // video | post | story
            $table->unsignedInteger('min_duration_seconds')->nullable();
            $table->decimal('reward_usd', 10, 2);
            $table->unsignedInteger('max_participants')->nullable();
            $table->string('image_path')->nullable();
            $table->decimal('rating', 2, 1)->default(4.8);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->string('status')->default('draft'); // draft | published | closed
            $table->timestamps();
        });

        Schema::create('social_networks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('platform'); // facebook | tiktok | instagram | youtube
            $table->string('handle')->nullable();
            $table->string('public_name')->nullable();
            $table->string('profile_url')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['user_id', 'platform']);
        });

        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('balance_usd', 12, 2)->default(0);
            $table->decimal('pending_usd', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mission_id')->constrained()->cascadeOnDelete();
            $table->string('content_url')->nullable();
            $table->string('screenshot_path')->nullable();
            $table->string('status')->default('in_progress');
            // in_progress | submitted | under_review | validated | rejected | paid
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'mission_id']);
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('mission_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount_usd', 12, 2);
            $table->string('type'); // reward_credit | reward_pending | payout | adjustment
            $table->string('status')->default('completed'); // pending | completed | cancelled
            $table->string('label')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('participations');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('social_networks');
        Schema::dropIfExists('missions');
        Schema::dropIfExists('campaigns');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'role', 'status', 'status_reason', 'avatar_path']);
        });
    }
};
