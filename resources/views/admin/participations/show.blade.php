@extends('layouts.admin')

@section('title', 'Vérifier participation')
@section('heading', 'Vérification participation')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.participations.index') }}" class="inline-flex text-sm font-semibold text-teal-700">← Retour à la file</a>

        <x-admin.verification-modals
            :prefix="'participation-show-'.$participation->id"
            :view-links="$participation->adminViewLinks()"
            :tier-networks="$participation->adminTierNetworks()"
            :tier-action="route('admin.creators.networks.tier', $participation->user_id)"
            :verify-action="route('admin.participations.validate', $participation)"
            :reject-action="route('admin.participations.reject', $participation)"
            verify-label="Valider et créditer"
            reject-label="Refuser la participation"
            reject-field="rejection_reason"
            reject-placeholder="Motif du refus de la participation"
            :can-decide="$participation->isPendingReview()"
        />
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">{{ $participation->mission->brand_name }}</h2>
            <p class="text-slate-500">{{ $participation->mission->title }}</p>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Récompense</dt>
                    <dd class="font-semibold">{{ number_format($participation->mission->rewardFor($participation->user, $participation->mission->social_network), 2) }} USD</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Réseau mission</dt>
                    <dd>{{ $participation->mission->networkLabels() }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Statut</dt>
                    <dd>
                        <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $participation->statusColor() }}">
                            {{ $participation->statusLabel() }}
                        </span>
                    </dd>
                </div>
                @if ($participation->mission->content_retention_days)
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Conservation requise</dt>
                        <dd class="font-semibold">{{ $participation->mission->content_retention_days }} jours</dd>
                    </div>
                @endif
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
            <p class="mt-1 text-sm">Classement : <strong>{{ $participation->user->creatorTierLabel() }}</strong></p>

            <ul class="mt-3 space-y-2 text-sm text-slate-600">
                @foreach ($participation->user->socialNetworks as $network)
                    <li class="flex items-center justify-between gap-2 rounded-lg bg-slate-50 px-3 py-2">
                        <span>{{ $network->platformLabel() }} : {{ $network->handle ?: '—' }} · {{ $network->tierLabel() }}</span>
                        @if ($network->profile_url)
                            <a href="{{ $network->profile_url }}" target="_blank" rel="noopener"
                               class="shrink-0 text-xs font-bold text-teal-700">Voir</a>
                        @endif
                    </li>
                @endforeach
            </ul>

            <div class="mt-5 rounded-xl bg-slate-50 p-4">
                <p class="text-sm font-semibold">Lien soumis</p>
                @if ($participation->content_url)
                    <a href="{{ $participation->content_url }}" target="_blank" rel="noopener"
                       class="mt-1 break-all text-sm font-semibold text-teal-700">{{ $participation->content_url }}</a>
                @else
                    <p class="mt-1 text-sm text-slate-500">Aucun lien</p>
                @endif
            </div>

            @if ($participation->rejection_reason)
                <div class="mt-4 rounded-xl bg-red-50 px-3 py-2 text-sm text-red-700">
                    Motif du refus : {{ $participation->rejection_reason }}
                </div>
            @endif
        </section>
    </div>
@endsection
