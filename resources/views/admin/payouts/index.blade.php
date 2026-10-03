@extends('layouts.admin')

@section('title', 'Paiements')
@section('heading', 'Paiements des créateurs')

@php use App\Support\Money; @endphp

@section('content')
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <x-admin.stat label="Retraits à payer" :value="Money::usd($totals['pending'])" icon="clock" tone="amber" />
        <x-admin.stat label="Total payé" :value="Money::usd($totals['paid'])" icon="banknote" tone="brand" />
        <x-admin.stat label="Récompenses créditées" :value="Money::usd($totals['rewards'])" icon="dollar" tone="sky" hint="depuis le lancement" />
    </div>

    <div class="my-4 inline-flex flex-wrap gap-1 rounded-2xl bg-white p-1 shadow-sm ring-1 ring-slate-900/5">
        @foreach (['pending' => 'À payer', 'completed' => 'Payés', 'cancelled' => 'Annulés', 'all' => 'Tous'] as $key => $label)
            <a href="{{ route('admin.payouts.index', ['status' => $key]) }}"
               class="rounded-xl px-3 py-1.5 text-sm font-semibold {{ $status === $key ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse ($payouts as $payout)
            <article class="ab-panel p-4 sm:p-5" x-data="{ mode: null }">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('admin.creators.show', $payout->user_id) }}" class="font-extrabold hover:text-brand-700">{{ $payout->user->name }}</a>
                            <span class="ab-chip {{ $payout->statusColor() }}">{{ $payout->statusLabel() }}</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-600">
                            <span class="font-semibold">{{ $payout->payoutMethodLabel() }}</span> · <span class="font-mono">{{ $payout->payout_account }}</span>
                        </p>
                        <p class="text-xs text-slate-400">
                            Demandé le {{ $payout->created_at->format('d/m/Y à H:i') }}
                            @if ($payout->processed_at)
                                · traité le {{ $payout->processed_at->format('d/m/Y') }} par {{ $payout->processor?->name ?? '—' }}
                            @endif
                        </p>
                        @if ($payout->admin_note)
                            <p class="mt-1 text-xs text-slate-500">Note : {{ $payout->admin_note }}</p>
                        @endif
                    </div>
                    <p class="text-2xl font-extrabold">{{ Money::usd($payout->amount_usd) }}</p>
                    @if ($payout->status === 'pending')
                        <div class="flex gap-2">
                            <button type="button" @click="mode = mode === 'pay' ? null : 'pay'" class="ab-btn-brand py-2">Marquer payé</button>
                            <button type="button" @click="mode = mode === 'cancel' ? null : 'cancel'" class="ab-btn-ghost py-2 text-cta-600">Annuler</button>
                        </div>
                    @endif
                </div>

                @if ($payout->status === 'pending')
                    <form x-cloak x-show="mode === 'pay'" method="POST" action="{{ route('admin.payouts.complete', $payout) }}" class="mt-4 flex flex-col gap-2 border-t border-slate-100 pt-4 sm:flex-row">
                        @csrf
                        <input name="admin_note" placeholder="Référence du paiement (optionnel)" class="ab-input py-2">
                        <button class="ab-btn-brand shrink-0 py-2">Confirmer le paiement de {{ Money::usd($payout->amount_usd) }}</button>
                    </form>
                    <form x-cloak x-show="mode === 'cancel'" method="POST" action="{{ route('admin.payouts.cancel', $payout) }}" class="mt-4 flex flex-col gap-2 border-t border-slate-100 pt-4 sm:flex-row">
                        @csrf
                        <input name="admin_note" required placeholder="Raison de l'annulation (visible dans l'historique)" class="ab-input py-2">
                        <button class="ab-btn shrink-0 bg-cta-600 py-2 text-white hover:bg-cta-700">Annuler et recréditer</button>
                    </form>
                @endif
            </article>
        @empty
            <div class="ab-panel px-6 py-12 text-center">
                <x-icon name="banknote" class="mx-auto h-8 w-8 text-slate-300" />
                <p class="mt-2 font-semibold">Aucun retrait {{ $status === 'pending' ? 'à payer' : '' }}</p>
            </div>
        @endforelse
    </div>
    <div class="mt-4">{{ $payouts->links() }}</div>
@endsection
