@extends('layouts.admin')

@section('title', 'Vérifier participation')
@section('heading', 'Vérification participation')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.participations.index') }}" class="inline-flex text-sm font-semibold text-brand-700">← Retour à la file</a>

        <x-admin.verification-modals
            :prefix="'participation-show-'.$participation->id"
            :creator-id="$participation->user_id"
            :view-links="$participation->adminViewLinks()"
            :tier-networks="$participation->adminTierNetworks()"
            :tier-action="route('admin.creators.networks.tier', $participation->user_id)"
            :global-tier="$participation->user->creator_tier"
            :global-tier-action="route('admin.creators.tier', $participation->user_id)"
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
        <section class="ab-panel p-6">
            <h2 class="text-lg font-bold">{{ $participation->mission->brand_name }}</h2>
            <p class="text-slate-500">{{ $participation->mission->title }}</p>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Récompense</dt>
                    <dd class="font-semibold">{{ \App\Support\Money::usd($participation->rewardAmount()) }}@if ($participation->reward_usd === null) <span class="text-xs font-normal text-slate-400">(estimation)</span>@endif</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Réseau utilisé</dt>
                    <dd class="flex items-center gap-1.5 font-semibold"><x-network-icon :network="$participation->effectiveNetwork()" /> {{ $participation->mission->networkLabel($participation->effectiveNetwork()) }}</dd>
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
            @if ($participation->mission->validation_criteria)
                <div class="mt-4 rounded-xl bg-brand-50 p-3">
                    <p class="text-sm font-semibold text-brand-800">Critères de validation</p>
                    <pre class="mt-1 whitespace-pre-wrap font-afriboost text-sm text-brand-900">{{ $participation->mission->validation_criteria }}</pre>
                </div>
            @endif
            <div class="mt-4">
                <p class="text-sm font-semibold">Consignes</p>
                <pre class="mt-1 whitespace-pre-wrap font-afriboost text-sm text-slate-600">{{ $participation->mission->instructions }}</pre>
            </div>
        </section>

        <section class="ab-panel p-6">
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
                               class="shrink-0 text-xs font-bold text-brand-700">Voir</a>
                        @endif
                    </li>
                @endforeach
            </ul>

            <div class="mt-5 rounded-xl bg-slate-50 p-4">
                <p class="text-sm font-semibold">Lien soumis</p>
                @if ($participation->content_url)
                    <a href="{{ $participation->content_url }}" target="_blank" rel="noopener"
                       class="mt-1 break-all text-sm font-semibold text-brand-700">{{ $participation->content_url }}</a>
                @else
                    <p class="mt-1 text-sm text-slate-500">Aucun lien</p>
                @endif
            </div>

            @if ($participation->screenshotUrl())
                <div class="mt-4">
                    <p class="text-sm font-semibold">Capture d'écran</p>
                    <a href="{{ $participation->screenshotUrl() }}" target="_blank" rel="noopener" class="mt-2 block overflow-hidden rounded-xl ring-1 ring-slate-200">
                        <img src="{{ $participation->screenshotUrl() }}" alt="Capture d'écran soumise" class="max-h-80 w-full object-contain bg-slate-50">
                    </a>
                </div>
            @endif

            @php
                $history = $participation->user->participations->countBy('status');
            @endphp
            <div class="mt-4 grid grid-cols-3 gap-2 text-center text-xs">
                <div class="rounded-xl bg-slate-50 p-2"><p class="text-lg font-extrabold">{{ $participation->user->participations->count() }}</p>Participations</div>
                <div class="rounded-xl bg-emerald-50 p-2 text-emerald-800"><p class="text-lg font-extrabold">{{ ($history['validated'] ?? 0) + ($history['paid'] ?? 0) }}</p>Validées</div>
                <div class="rounded-xl bg-red-50 p-2 text-red-800"><p class="text-lg font-extrabold">{{ $history['rejected'] ?? 0 }}</p>Refusées</div>
            </div>

            @if ($participation->reviewer)
                <p class="mt-4 text-xs text-slate-500">Traitée par {{ $participation->reviewer->name }} le {{ $participation->reviewed_at?->format('d/m/Y H:i') }}</p>
            @endif

            @if ($participation->rejection_reason)
                <div class="mt-4 rounded-xl bg-red-50 px-3 py-2 text-sm text-red-700">
                    Motif du refus : {{ $participation->rejection_reason }}
                </div>
            @endif
        </section>
    </div>
@endsection
