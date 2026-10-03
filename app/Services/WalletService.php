<?php

namespace App\Services;

use App\Models\Participation;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Support\ActivityLogger;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletService
{
    public function ensureWallet(User $user): Wallet
    {
        return $user->wallet()->firstOrCreate([], [
            'balance_usd' => 0,
            'pending_usd' => 0,
            'status' => 'active',
        ]);
    }

    public function validateParticipation(Participation $participation, User $admin): void
    {
        DB::transaction(function () use ($participation, $admin) {
            $participation = Participation::query()->lockForUpdate()->findOrFail($participation->id);

            if (! $participation->isPendingReview()) {
                throw new RuntimeException('Cette participation ne peut pas être validée.');
            }

            $wallet = $this->ensureWallet($participation->user);
            $amount = $participation->mission->rewardFor($participation->user, $participation->effectiveNetwork());

            $participation->update([
                'status' => Participation::STATUS_VALIDATED,
                'reward_usd' => $amount,
                'rejection_reason' => null,
                'reviewed_at' => now(),
                'reviewed_by' => $admin->id,
            ]);

            $wallet->increment('balance_usd', $amount);

            Transaction::create([
                'user_id' => $participation->user_id,
                'wallet_id' => $wallet->id,
                'participation_id' => $participation->id,
                'mission_id' => $participation->mission_id,
                'amount_usd' => $amount,
                'type' => Transaction::TYPE_REWARD,
                'status' => Transaction::STATUS_COMPLETED,
                'label' => 'Mission '.$participation->mission->brand_name,
                'processed_at' => now(),
                'processed_by' => $admin->id,
            ]);

            ActivityLogger::log(
                'participation.validated',
                'Participation de '.$participation->user->name.' à « '.$participation->mission->title.' » validée ('.Money::usd($amount).').',
                $participation,
                ['amount_usd' => $amount, 'network' => $participation->effectiveNetwork()]
            );
        });
    }

    public function rejectParticipation(Participation $participation, User $admin, string $reason): void
    {
        if (! $participation->isPendingReview()) {
            throw new RuntimeException('Cette participation ne peut pas être refusée.');
        }

        $participation->update([
            'status' => Participation::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'reviewed_at' => now(),
            'reviewed_by' => $admin->id,
        ]);

        ActivityLogger::log(
            'participation.rejected',
            'Participation de '.$participation->user->name.' à « '.$participation->mission->title.' » refusée.',
            $participation,
            ['reason' => $reason]
        );
    }

    /**
     * Le créateur demande un retrait : le montant quitte le solde disponible
     * et reste « en cours de paiement » jusqu'au traitement par l'équipe.
     */
    public function requestPayout(User $user, float $amount, string $method, string $account): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $method, $account) {
            $wallet = Wallet::query()->lockForUpdate()->findOrFail($this->ensureWallet($user)->id);

            if ($wallet->status !== 'active') {
                throw new RuntimeException('Votre wallet est bloqué. Contactez l\'équipe AfriBoost.');
            }

            if ($amount < Wallet::MIN_PAYOUT_USD) {
                throw new RuntimeException('Le retrait minimum est de '.Money::usd(Wallet::MIN_PAYOUT_USD).'.');
            }

            if ($amount > (float) $wallet->balance_usd) {
                throw new RuntimeException('Le montant demandé dépasse votre solde disponible.');
            }

            if ($wallet->hasPendingPayout()) {
                throw new RuntimeException('Vous avez déjà une demande de retrait en cours.');
            }

            $wallet->decrement('balance_usd', $amount);
            $wallet->increment('pending_usd', $amount);

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'amount_usd' => $amount,
                'type' => Transaction::TYPE_PAYOUT,
                'status' => Transaction::STATUS_PENDING,
                'label' => 'Demande de retrait',
                'payout_method' => $method,
                'payout_account' => $account,
            ]);

            ActivityLogger::log(
                'payout.requested',
                $user->name.' a demandé un retrait de '.Money::usd($amount).'.',
                $transaction,
                ['amount_usd' => $amount, 'method' => $method]
            );

            return $transaction;
        });
    }

    public function completePayout(Transaction $payout, User $admin, ?string $note = null): void
    {
        DB::transaction(function () use ($payout, $admin, $note) {
            $payout = $this->lockPendingPayout($payout);

            $payout->wallet->decrement('pending_usd', (float) $payout->amount_usd);

            $payout->update([
                'status' => Transaction::STATUS_COMPLETED,
                'label' => 'Retrait payé',
                'admin_note' => $note,
                'processed_at' => now(),
                'processed_by' => $admin->id,
            ]);

            ActivityLogger::log(
                'payout.completed',
                'Retrait de '.Money::usd($payout->amount_usd).' payé à '.$payout->user->name.'.',
                $payout,
                ['amount_usd' => (float) $payout->amount_usd]
            );
        });
    }

    /** Annule une demande de retrait et recrédite le solde disponible. */
    public function cancelPayout(Transaction $payout, User $admin, string $note): void
    {
        DB::transaction(function () use ($payout, $admin, $note) {
            $payout = $this->lockPendingPayout($payout);
            $amount = (float) $payout->amount_usd;

            $payout->wallet->decrement('pending_usd', $amount);
            $payout->wallet->increment('balance_usd', $amount);

            $payout->update([
                'status' => Transaction::STATUS_CANCELLED,
                'label' => 'Retrait annulé',
                'admin_note' => $note,
                'processed_at' => now(),
                'processed_by' => $admin->id,
            ]);

            ActivityLogger::log(
                'payout.cancelled',
                'Retrait de '.Money::usd($amount).' de '.$payout->user->name.' annulé.',
                $payout,
                ['amount_usd' => $amount, 'reason' => $note]
            );
        });
    }

    private function lockPendingPayout(Transaction $payout): Transaction
    {
        $payout = Transaction::query()->with(['wallet', 'user'])->lockForUpdate()->findOrFail($payout->id);

        if (! $payout->isPayout() || $payout->status !== Transaction::STATUS_PENDING) {
            throw new RuntimeException('Ce retrait a déjà été traité.');
        }

        return $payout;
    }
}
