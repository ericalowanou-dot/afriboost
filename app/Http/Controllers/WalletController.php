<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class WalletController extends Controller
{
    public function __construct(private WalletService $wallets) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $wallet = $this->wallets->ensureWallet($user);
        $showAll = $request->boolean('tout');

        $transactions = $user->transactions()
            ->with('mission')
            ->latest()
            ->when(! $showAll, fn ($q) => $q->limit(10))
            ->get();

        $summary = [
            'earned' => $wallet->totalEarned(),
            'paid_out' => $wallet->totalPaidOut(),
            'payout_pending' => (float) $wallet->pending_usd,
            'awaiting_review' => $wallet->rewardsAwaitingReview(),
        ];

        $hasPendingPayout = $wallet->hasPendingPayout();
        $minPayout = Wallet::MIN_PAYOUT_USD;
        $payoutMethods = Transaction::PAYOUT_METHODS;

        return view('creator.wallet.index', compact(
            'wallet', 'transactions', 'summary', 'showAll', 'hasPendingPayout', 'minPayout', 'payoutMethods'
        ));
    }

    public function requestPayout(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('payout', [
            'amount' => ['required', 'numeric', 'min:'.Wallet::MIN_PAYOUT_USD],
            'payout_method' => ['required', Rule::in(array_keys(Transaction::PAYOUT_METHODS))],
            'payout_account' => ['required', 'string', 'max:120'],
        ], [
            'amount.min' => 'Le retrait minimum est de '.Wallet::MIN_PAYOUT_USD.' $.',
            'payout_account.required' => 'Indiquez le numéro ou le compte qui recevra le paiement.',
        ]);

        try {
            $this->wallets->requestPayout(
                auth()->user(),
                round((float) $data['amount'], 2),
                $data['payout_method'],
                $data['payout_account'],
            );
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['amount' => $e->getMessage()], 'payout');
        }

        return redirect()
            ->route('wallet.index')
            ->with('success', 'Demande de retrait envoyée. L\'équipe AfriBoost va traiter votre paiement.');
    }
}
