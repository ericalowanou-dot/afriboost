@extends('layouts.admin')

@section('title', 'Vérifier participation')
@section('heading', 'Vérification')

@section('content')
    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">{{ $participation->mission->brand_name }}</h2>
            <p class="text-slate-500">{{ $participation->mission->title }}</p>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Récompense</dt><dd class="font-semibold">{{ number_format($participation->mission->reward_usd, 2) }} USD</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Réseau</dt><dd>{{ $participation->mission->networkLabel() }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Statut</dt><dd><span class="rounded-full px-2 py-1 text-xs font-semibold {{ $participation->statusColor() }}">{{ $participation->statusLabel() }}</span></dd></div>
            </dl>
            <div class="mt-4">
                <p class="text-sm font-semibold">Consignes</p>
                <pre class="mt-1 whitespace-pre-wrap font-afriboost text-sm text-slate-600">{{ $participation->mission->instructions }}</pre>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">Créateur</h2>
            <p class="mt-1 font-semibold">{{ $participation->user->name }}</p>
            <p class="text-sm text-slate-500">{{ $participation->user->email }} · {{ $participation->user->phone }}</p>
            <ul class="mt-3 space-y-1 text-sm text-slate-600">
                @foreach ($participation->user->socialNetworks as $network)
                    <li>{{ $network->platformLabel() }} : {{ $network->handle ?: $network->profile_url }}</li>
                @endforeach
            </ul>

            <div class="mt-5 rounded-xl bg-slate-50 p-4">
                <p class="text-sm font-semibold">Lien soumis</p>
                @if ($participation->content_url)
                    <a href="{{ $participation->content_url }}" target="_blank" rel="noopener" class="mt-1 break-all text-sm font-semibold text-teal-700">{{ $participation->content_url }}</a>
                @else
                    <p class="mt-1 text-sm text-slate-500">Aucun lien</p>
                @endif
            </div>

            @if (in_array($participation->status, ['submitted', 'under_review'], true))
                <div class="mt-6 flex flex-col gap-3">
                    <form method="POST" action="{{ route('admin.participations.validate', $participation) }}">
                        @csrf
                        <button class="w-full rounded-xl bg-emerald-600 py-3 font-bold text-white">Valider et créditer</button>
                    </form>
                    <form method="POST" action="{{ route('admin.participations.reject', $participation) }}" class="space-y-2">
                        @csrf
                        <textarea name="rejection_reason" required rows="3" placeholder="Motif du refus" class="w-full rounded-xl border-slate-200"></textarea>
                        <button class="w-full rounded-xl bg-rose-600 py-3 font-bold text-white">Refuser</button>
                    </form>
                </div>
            @endif
        </section>
    </div>
@endsection
