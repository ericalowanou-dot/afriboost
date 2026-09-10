<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(private WalletService $wallets) {}

    public function index(): View
    {
        $user = auth()->user();
        $wallet = $this->wallets->ensureWallet($user);

        $transactions = $user->transactions()
            ->with('mission')
            ->latest()
            ->limit(20)
            ->get();

        $totalEarned = $wallet->totalEarned();
        $paidOut = max(0, $totalEarned - (float) $wallet->balance_usd);

        return view('creator.wallet.index', compact('wallet', 'transactions', 'totalEarned', 'paidOut'));
    }
}
