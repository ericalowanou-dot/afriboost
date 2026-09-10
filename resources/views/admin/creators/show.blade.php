@extends('layouts.admin')

@section('title', $creator->name.' — Créateur')
@section('heading', 'Fiche créateur')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.creators.index') }}" class="inline-flex text-sm font-semibold text-teal-700">← Retour à la liste</a>

        <x-admin.verification-modals
            :prefix="'creator-show-'.$creator->id"
            :creator-id="$creator->id"
            :view-links="$creator->adminViewLinks()"
            :tier-networks="$creator->adminTierNetworks()"
            :tier-action="route('admin.creators.networks.tier', $creator)"
            :global-tier="$creator->creator_tier"
            :global-tier-action="route('admin.creators.tier', $creator)"
            :verify-action="route('admin.creators.verify', $creator)"
            :reject-action="route('admin.creators.reject', $creator)"
            verify-label="Vérifier le compte"
            reject-label="Refuser le compte"
            reject-field="status_reason"
            reject-placeholder="Motif du refus du compte"
            :can-decide="$creator->verification_status === 'pending'"
        />
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h2 class="text-lg font-bold">Informations du compte</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Nom</dt>
                    <dd class="font-semibold text-right">{{ $creator->name }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Email</dt>
                    <dd class="font-semibold text-right">{{ $creator->email }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">WhatsApp</dt>
                    <dd class="font-semibold text-right">{{ $creator->phone ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Inscription</dt>
                    <dd class="font-semibold text-right">{{ $creator->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Vérification</dt>
                    <dd>
                        <span class="rounded-full px-2 py-1 text-xs font-semibold
                            {{ $creator->verification_status === 'verified' ? 'bg-emerald-100 text-emerald-700' : ($creator->verification_status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ $creator->verificationStatusLabel() }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Classement global</dt>
                    <dd class="font-semibold">{{ $creator->creatorTierLabel() }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Solde wallet</dt>
                    <dd class="font-semibold">{{ number_format(optional($creator->wallet)->balance_usd ?? 0, 2) }} USD</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Participations</dt>
                    <dd class="font-semibold">{{ $creator->participations->count() }}</dd>
                </div>
            </dl>

            @if ($creator->status_reason)
                <div class="mt-4 rounded-xl bg-red-50 px-3 py-2 text-sm text-red-700">
                    Motif : {{ $creator->status_reason }}
                </div>
            @endif
        </section>

        <section class="space-y-4">
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold">Classement global manuel</h2>
                <p class="mt-1 text-sm text-slate-500">Raccourci si tous les comptes ont le même niveau.</p>

                <form method="POST" action="{{ route('admin.creators.tier', $creator) }}" class="mt-4 flex flex-wrap items-end gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="creator_tier" class="rounded-xl border-slate-200 text-sm">
                        <option value="">Non classé</option>
                        @foreach (['top' => 'Top', 'medium' => 'Medium', 'basic' => 'Basique'] as $value => $label)
                            <option value="{{ $value }}" @selected($creator->creator_tier === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white">Enregistrer</button>
                </form>
            </div>

            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold">Statut du compte</h2>
                <form method="POST" action="{{ route('admin.creators.status', $creator) }}" class="mt-4 flex flex-wrap items-end gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="rounded-xl border-slate-200 text-sm">
                        @foreach (['active','suspended','blocked'] as $status)
                            <option value="{{ $status }}" @selected($creator->status === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="status_reason" placeholder="Motif (optionnel)" value="{{ $creator->status_reason }}"
                           class="min-w-[160px] flex-1 rounded-xl border-slate-200 text-sm">
                    <button class="rounded-xl bg-slate-700 px-4 py-2 text-sm font-bold text-white">Mettre à jour</button>
                </form>
            </div>
        </section>
    </div>

    <section class="mt-6 rounded-2xl bg-white p-5 shadow-sm">
        <h2 class="text-lg font-bold">Réseaux sociaux</h2>
        <p class="mt-1 text-sm text-slate-500">Utilisez le bouton « Classer » en haut pour catégoriser chaque compte (Top, Medium, Basique).</p>

        @forelse ($creator->socialNetworks as $network)
            <div class="mt-4 rounded-xl border border-slate-100 p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-bold">{{ $network->platformLabel() }}</p>
                        <p class="text-sm text-slate-500">{{ $network->handle ?? '—' }}</p>
                        <p class="text-xs text-teal-700">{{ $network->tierLabel() }}</p>
                        @if ($network->profile_url)
                            <a href="{{ $network->profile_url }}" target="_blank" rel="noopener"
                               class="mt-1 inline-block text-sm font-semibold text-teal-700 break-all">
                                {{ $network->profile_url }}
                            </a>
                        @endif
                    </div>
                    @if ($network->profile_url)
                        <a href="{{ $network->profile_url }}" target="_blank" rel="noopener"
                           class="rounded-lg bg-teal-700 px-3 py-1.5 text-xs font-bold text-white">
                            Voir le profil
                        </a>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.creators.networks.update', [$creator, $network]) }}"
                      class="mt-3 flex flex-wrap items-end gap-2 border-t border-slate-100 pt-3">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-500">Abonnés</label>
                        <input type="number" name="follower_count" min="0" value="{{ $network->follower_count }}"
                               class="w-32 rounded-lg border-slate-200 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-500">Handle</label>
                        <input type="text" name="handle" value="{{ $network->handle }}"
                               class="rounded-lg border-slate-200 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-500">Classement</label>
                        <select name="creator_tier" class="rounded-lg border-slate-200 text-sm">
                            <option value="">Non classé</option>
                            @foreach (['top' => 'Top', 'medium' => 'Medium', 'basic' => 'Basique'] as $value => $label)
                                <option value="{{ $value }}" @selected($network->creator_tier === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white">Mettre à jour</button>
                </form>
            </div>
        @empty
            <p class="mt-3 text-sm text-slate-500">Aucun réseau social enregistré.</p>
        @endforelse
    </section>

    @if ($creator->participations->isNotEmpty())
        <section class="mt-6 rounded-2xl bg-white p-5 shadow-sm">
            <h2 class="text-lg font-bold">Participations récentes</h2>
            <ul class="mt-3 divide-y divide-slate-100 text-sm">
                @foreach ($creator->participations->take(10) as $participation)
                    <li class="flex justify-between gap-4 py-2">
                        <span>{{ $participation->mission->brand_name ?? 'Mission #'.$participation->mission_id }}</span>
                        <span class="font-semibold">{{ $participation->statusLabel() }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
@endsection
