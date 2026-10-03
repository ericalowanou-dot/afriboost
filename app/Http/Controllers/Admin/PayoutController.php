<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class PayoutController extends Controller
{
    public function __construct(private WalletService $wallets) {}

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: Transaction::STATUS_PENDING;

        $payouts = Transaction::query()
            ->with(['user', 'processor'])
            ->where('type', Transaction::TYPE_PAYOUT)
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $totals = [
            'pending' => (float) Transaction::where('type', Transaction::TYPE_PAYOUT)->where('status', Transaction::STATUS_PENDING)->sum('amount_usd'),
            'paid' => (float) Transaction::where('type', Transaction::TYPE_PAYOUT)->where('status', Transaction::STATUS_COMPLETED)->sum('amount_usd'),
            'rewards' => (float) Transaction::where('type', Transaction::TYPE_REWARD)->where('status', Transaction::STATUS_COMPLETED)->sum('amount_usd'),
        ];

        return view('admin.payouts.index', compact('payouts', 'status', 'totals'));
    }

    public function complete(Request $request, Transaction $payout)
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:500']]);

        try {
            $this->wallets->completePayout($payout, auth()->user(), $data['admin_note'] ?? null);
        } catch (RuntimeException $e) {
            return back()->withErrors(['payout' => $e->getMessage()]);
        }

        return back()->with('success', 'Retrait marqué comme payé.');
    }

    public function cancel(Request $request, Transaction $payout)
    {
        $data = $request->validate(['admin_note' => ['required', 'string', 'max:500']], [
            'admin_note.required' => 'Indiquez la raison de l\'annulation.',
        ]);

        try {
            $this->wallets->cancelPayout($payout, auth()->user(), $data['admin_note']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['payout' => $e->getMessage()]);
        }

        return back()->with('success', 'Retrait annulé, le solde du créateur a été recrédité.');
    }
}
