<?php

namespace App\Services;

use App\Models\Participation;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
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
        if (! in_array($participation->status, [
            Participation::STATUS_SUBMITTED,
            Participation::STATUS_UNDER_REVIEW,
        ], true)) {
            throw new RuntimeException('Cette participation ne peut pas être validée.');
        }

        DB::transaction(function () use ($participation, $admin) {
            $wallet = $this->ensureWallet($participation->user);
            $amount = $participation->mission->reward_usd;

            $participation->update([
                'status' => Participation::STATUS_VALIDATED,
                'rejection_reason' => null,
                'reviewed_at' => now(),
                'reviewed_by' => $admin->id,
            ]);

            $wallet->increment('balance_usd', $amount);
            $wallet->increment('pending_usd', $amount);

            Transaction::create([
                'user_id' => $participation->user_id,
                'wallet_id' => $wallet->id,
                'participation_id' => $participation->id,
                'mission_id' => $participation->mission_id,
                'amount_usd' => $amount,
                'type' => 'reward_credit',
                'status' => 'completed',
                'label' => 'Récompense — '.$participation->mission->brand_name,
            ]);
        });
    }

    public function rejectParticipation(Participation $participation, User $admin, string $reason): void
    {
        if (! in_array($participation->status, [
            Participation::STATUS_SUBMITTED,
            Participation::STATUS_UNDER_REVIEW,
        ], true)) {
            throw new RuntimeException('Cette participation ne peut pas être refusée.');
        }

        $participation->update([
            'status' => Participation::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'reviewed_at' => now(),
            'reviewed_by' => $admin->id,
        ]);
    }
}
