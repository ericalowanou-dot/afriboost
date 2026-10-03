<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participations', function (Blueprint $table) {
            // Réseau sur lequel le créateur réalise la mission (missions multi-réseaux)
            $table->string('network')->nullable()->after('mission_id');
            // Montant figé au moment de la validation, pour la traçabilité
            $table->decimal('reward_usd', 10, 2)->nullable()->after('screenshot_path');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payout_method')->nullable()->after('label');
            $table->string('payout_account')->nullable()->after('payout_method');
            $table->text('admin_note')->nullable()->after('payout_account');
            $table->timestamp('processed_at')->nullable()->after('admin_note');
            $table->foreignId('processed_by')->nullable()->after('processed_at')->constrained('users')->nullOnDelete();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // auteur de l'action
            $table->string('action'); // ex : participation.validated
            $table->nullableMorphs('subject');
            $table->string('description');
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('action');
        });

        // Reprise des données existantes
        DB::table('participations')->whereNull('network')->update([
            'network' => DB::raw('(select social_network from missions where missions.id = participations.mission_id)'),
        ]);

        DB::table('transactions')
            ->where('type', 'reward_credit')
            ->whereNotNull('participation_id')
            ->orderBy('id')
            ->each(function ($transaction) {
                DB::table('participations')
                    ->where('id', $transaction->participation_id)
                    ->update(['reward_usd' => $transaction->amount_usd]);
            });

        // pending_usd servait de doublon du solde : il représente désormais les retraits en cours.
        DB::table('wallets')->update(['pending_usd' => 0]);
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
            $table->dropColumn(['payout_method', 'payout_account', 'admin_note', 'processed_at', 'processed_by']);
        });

        Schema::table('participations', function (Blueprint $table) {
            $table->dropColumn(['network', 'reward_usd']);
        });
    }
};
