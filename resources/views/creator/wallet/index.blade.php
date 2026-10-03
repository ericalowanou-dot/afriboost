@extends('layouts.mobile')

@section('title', 'Mon wallet — AfriBoost')
@section('heading', 'Mon wallet')

@php
    use App\Support\Money;

    $payoutErrors = $errors->getBag('payout');
    $balance = (float) $wallet->balance_usd;
@endphp

@section('content')
<div x-data="{ payout: {{ $payoutErrors->any() ? 'true' : 'false' }} }">
    {{-- Solde --}}
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-500 to-brand-700 p-5 text-white shadow-card">
        <div class="absolute -right-6 -top-8 h-36 w-36 rounded-full bg-white/10"></div>
        <div class="relative flex items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-brand-50">Solde disponible</p>
                <p class="mt-1 text-[34px] font-extrabold leading-none tracking-tight">{{ Money::usd($balance) }}</p>
                <p class="mt-2 text-xs font-medium text-brand-100">Devise : USD</p>
            </div>
            {{-- Illustration portefeuille --}}
            <svg class="h-24 w-24 shrink-0 drop-shadow-lg" viewBox="0 0 96 96" aria-hidden="true">
                <rect x="20" y="14" width="44" height="26" rx="4" fill="#9be3b4" transform="rotate(-12 42 27)"/>
                <rect x="26" y="10" width="44" height="26" rx="4" fill="#c7f0d4" transform="rotate(-4 48 23)"/>
                <rect x="10" y="28" width="66" height="50" rx="12" fill="#8b5a3c"/>
                <rect x="10" y="28" width="66" height="14" rx="7" fill="#a06b48"/>
                <rect x="52" y="46" width="28" height="18" rx="9" fill="#6f4430"/>
                <circle cx="62" cy="55" r="4" fill="#f5c542"/>
                <circle cx="78" cy="78" r="11" fill="#f5c542"/><circle cx="78" cy="78" r="7" fill="#e7a91c"/>
                <circle cx="66" cy="84" r="9" fill="#f5c542"/><circle cx="66" cy="84" r="5.5" fill="#e7a91c"/>
            </svg>
        </div>
    </section>

    <div class="mt-3 grid grid-cols-2 gap-3">
        <a href="{{ route('wallet.index', $showAll ? [] : ['tout' => 1]) }}#transactions" class="ab-card flex items-center justify-center gap-2 px-3 py-3.5 text-sm font-bold text-slate-800">
            <x-icon name="history" class="h-[18px] w-[18px]" /> {{ $showAll ? 'Récentes' : 'Historique' }}
        </a>
        <button type="button" @click="payout = true" class="ab-card flex items-center justify-center gap-2 px-3 py-3.5 text-sm font-bold text-slate-800 disabled:cursor-not-allowed disabled:text-slate-400"
                @disabled($hasPendingPayout)>
            <x-icon name="download" class="h-[18px] w-[18px]" /> Demander un retrait
        </button>
    </div>

    @if ($hasPendingPayout)
        <p class="mt-2 flex items-center gap-2 rounded-2xl bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 ring-1 ring-amber-200">
            <x-icon name="clock" class="h-4 w-4" /> Un retrait de {{ Money::usd($summary['payout_pending']) }} est en cours de paiement.
        </p>
    @endif

    {{-- Résumé --}}
    <section class="ab-card mt-3 p-4">
        <h2 class="font-extrabold">Résumé</h2>
        <dl class="mt-2 divide-y divide-slate-100 text-sm">
            <div class="flex justify-between py-2.5"><dt class="text-slate-600">Gains totaux</dt><dd class="font-bold text-brand-600">{{ Money::usd($summary['earned']) }}</dd></div>
            <div class="flex justify-between py-2.5"><dt class="text-slate-600">Montant payé</dt><dd class="font-bold text-brand-600">{{ Money::usd($summary['paid_out']) }}</dd></div>
            <div class="flex justify-between py-2.5"><dt class="text-slate-600">Retrait en cours de paiement</dt><dd class="font-bold text-orange-600">{{ Money::usd($summary['payout_pending']) }}</dd></div>
            <div class="flex justify-between py-2.5">
                <dt class="text-slate-600">Récompenses en attente de validation</dt>
                <dd class="font-bold text-orange-600">{{ Money::usd($summary['awaiting_review']) }}</dd>
            </div>
        </dl>
    </section>

    {{-- Transactions --}}
    <section id="transactions" class="ab-card mt-3 p-4">
        <h2 class="font-extrabold">{{ $showAll ? 'Historique des mouvements' : 'Dernières transactions' }}</h2>
        <div class="mt-2 divide-y divide-slate-100">
            @forelse ($transactions as $tx)
                @php
                    [$icon, $iconTone] = match (true) {
                        $tx->status === 'cancelled' => ['x-circle', 'bg-slate-100 text-slate-500'],
                        $tx->status === 'pending' => ['clock', 'bg-orange-100 text-orange-600'],
                        $tx->isPayout() => ['upload', 'bg-sky-100 text-sky-600'],
                        default => ['download', 'bg-brand-100 text-brand-600'],
                    };
                @endphp
                <article class="flex items-center gap-3 py-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $iconTone }}"><x-icon :name="$icon" class="h-[18px] w-[18px]" stroke="2.2" /></span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold">{{ $tx->label ?: $tx->typeLabel() }}</p>
                        <p class="text-xs text-slate-500">
                            {{ $tx->statusLabel() }}
                            @if ($tx->isPayout() && $tx->payoutMethodLabel())
                                · {{ $tx->payoutMethodLabel() }}
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-extrabold {{ $tx->status === 'cancelled' ? 'text-slate-400 line-through' : ($tx->isDebit() ? 'text-slate-800' : 'text-brand-600') }}">
                            {{ $tx->isDebit() ? '−' : '+' }}{{ Money::usd(abs((float) $tx->amount_usd)) }}
                        </p>
                        <p class="text-xs text-slate-500">{{ $tx->created_at->format('d/m/Y') }}</p>
                    </div>
                </article>
            @empty
                <div class="py-8 text-center">
                    <p class="text-sm font-semibold text-slate-700">Aucune transaction pour le moment</p>
                    <p class="mt-1 text-xs text-slate-500">Tes récompenses apparaîtront ici dès qu'une participation sera validée.</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- Feuille de demande de retrait --}}
    <div x-cloak x-show="payout" class="fixed inset-0 z-50 flex items-end justify-center sm:items-center" role="dialog" aria-modal="true" aria-labelledby="payout-title">
        <div x-show="payout" x-transition.opacity class="absolute inset-0 bg-slate-900/50" @click="payout = false"></div>
        <div x-show="payout" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-full sm:translate-y-4 sm:opacity-0" x-transition:enter-end="translate-y-0 sm:opacity-100"
             class="relative w-full max-w-lg rounded-t-3xl bg-white p-5 pb-safe shadow-2xl sm:rounded-3xl" @keydown.escape.window="payout = false">
            <div class="mx-auto mb-4 h-1.5 w-12 rounded-full bg-slate-200 sm:hidden"></div>
            <div class="flex items-center justify-between">
                <h2 id="payout-title" class="text-lg font-extrabold">Demander un retrait</h2>
                <button type="button" @click="payout = false" class="rounded-xl p-1.5 text-slate-500 hover:bg-slate-100" aria-label="Fermer"><x-icon name="x" /></button>
            </div>
            <p class="mt-1 text-sm text-slate-500">Solde disponible : <strong class="text-slate-900">{{ Money::usd($balance) }}</strong> · minimum {{ Money::usd($minPayout) }}</p>

            @if ($payoutErrors->any())
                <div class="mt-3 rounded-2xl bg-cta-50 px-3 py-2 text-sm text-cta-700">
                    @foreach ($payoutErrors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if ($balance < $minPayout)
                <div class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                    Ton solde doit atteindre au moins {{ Money::usd($minPayout) }} pour demander un retrait. Continue tes missions !
                </div>
            @else
                <form method="POST" action="{{ route('wallet.payout') }}" class="mt-4 space-y-4" x-data="{ amount: '{{ old('amount', number_format($balance, 2, '.', '')) }}' }">
                    @csrf
                    <div>
                        <label for="amount" class="ab-label">Montant (USD)</label>
                        <div class="relative">
                            <input id="amount" name="amount" type="number" step="0.01" min="{{ $minPayout }}" max="{{ $balance }}" x-model="amount" required class="ab-input pr-20 text-lg font-bold">
                            <button type="button" @click="amount = '{{ number_format($balance, 2, '.', '') }}'" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-xl bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-700">Tout</button>
                        </div>
                    </div>
                    <div>
                        <span class="ab-label">Moyen de paiement</span>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach ($payoutMethods as $value => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="payout_method" value="{{ $value }}" class="peer sr-only" @checked(old('payout_method', 'mobile_money') === $value)>
                                    <span class="flex h-full items-center justify-center rounded-2xl px-2 py-3 text-center text-xs font-bold text-slate-600 ring-1 ring-slate-200 peer-checked:bg-brand-50 peer-checked:text-brand-700 peer-checked:ring-2 peer-checked:ring-brand-500">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label for="payout_account" class="ab-label">Numéro ou compte de réception</label>
                        <input id="payout_account" name="payout_account" type="text" value="{{ old('payout_account', auth()->user()->phone) }}" required maxlength="120"
                               placeholder="+228 90 00 00 00, IBAN ou e-mail PayPal" class="ab-input">
                    </div>
                    <button class="ab-btn-brand w-full py-4 text-base">Envoyer la demande</button>
                    <p class="text-center text-xs text-slate-500">Le montant est réservé jusqu'au paiement par l'équipe AfriBoost.</p>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
