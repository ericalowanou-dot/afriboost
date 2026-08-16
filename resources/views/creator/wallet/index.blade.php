@extends('layouts.mobile')

@section('title', 'Mon wallet — AfriBoost')
@section('heading', 'Mon wallet')

@section('content')
    <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-teal-600 to-emerald-500 p-5 text-white shadow-lg">
        <p class="text-sm font-medium text-teal-50">Solde disponible</p>
        <p class="mt-2 text-4xl font-extrabold tracking-tight">{{ number_format($wallet->balance_usd, 2) }} <span class="text-lg">USD</span></p>
        <div class="mt-5 grid grid-cols-2 gap-2">
            <div class="rounded-2xl bg-white/15 px-3 py-3 text-center text-sm font-semibold">Historique</div>
            <div class="rounded-2xl bg-white/15 px-3 py-3 text-center text-sm font-semibold">Demander un retrait</div>
        </div>
    </section>

    <section class="mt-4 rounded-3xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
        <h2 class="font-bold">Résumé</h2>
        <dl class="mt-3 space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">Gains totaux</dt><dd class="font-semibold">{{ number_format($totalEarned, 2) }} USD</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Montant payé</dt><dd class="font-semibold">{{ number_format($paidOut, 2) }} USD</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">En attente de paiement</dt><dd class="font-semibold">{{ number_format($wallet->pending_usd, 2) }} USD</dd></div>
        </dl>
    </section>

    <section class="mt-4">
        <h2 class="mb-3 font-bold">Dernières transactions</h2>
        <div class="space-y-2">
            @forelse ($transactions as $tx)
                <article class="flex items-center justify-between rounded-2xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $tx->status === 'pending' ? 'bg-orange-100 text-orange-600' : 'bg-emerald-100 text-emerald-600' }}">$</div>
                        <div>
                            <p class="text-sm font-semibold">{{ $tx->label ?: $tx->typeLabel() }}</p>
                            <p class="text-xs text-slate-400">{{ $tx->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    <p class="font-bold {{ $tx->type === 'payout' ? 'text-slate-700' : 'text-emerald-600' }}">
                        {{ $tx->type === 'payout' ? '-' : '+' }}{{ number_format($tx->amount_usd, 2) }}
                    </p>
                </article>
            @empty
                <div class="rounded-2xl bg-white p-6 text-center text-sm text-slate-500 shadow-sm">Aucune transaction pour le moment.</div>
            @endforelse
        </div>
    </section>
@endsection
