@extends('layouts.admin')

@section('title', 'Créateurs')
@section('heading', 'Créateurs')

@section('content')
    <form method="GET" action="{{ route('admin.creators.index') }}" class="mb-4 space-y-3">
        <div class="flex flex-wrap gap-2">
            @php
                $verificationFilters = [
                    'all' => 'Tous',
                    'pending' => 'En attente',
                    'verified' => 'Vérifiés',
                    'rejected' => 'Refusés',
                ];
            @endphp
            @foreach ($verificationFilters as $key => $label)
                <button type="submit" name="verification" value="{{ $key }}"
                        class="rounded-full px-4 py-2 text-sm font-semibold {{ $filters['verification'] === $key ? 'bg-teal-700 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200' }}">
                    {{ $label }}
                    @if ($key === 'pending' && $pendingCount > 0)
                        <span class="ml-1 rounded-full bg-rose-500 px-2 py-0.5 text-xs text-white">{{ $pendingCount }}</span>
                    @endif
                </button>
            @endforeach
        </div>

        <div class="flex flex-wrap items-end gap-3 rounded-2xl bg-white p-4 shadow-sm">
            <input type="hidden" name="verification" value="{{ $filters['verification'] }}">

            <div class="min-w-[180px] flex-1">
                <label class="mb-1 block text-xs font-semibold text-slate-500">Recherche</label>
                <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Nom, email, WhatsApp…"
                       class="w-full rounded-xl border-slate-200 text-sm">
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">Classement</label>
                <select name="tier" class="rounded-xl border-slate-200 text-sm">
                    <option value="all">Tous</option>
                    <option value="top" @selected($filters['tier'] === 'top')>Top</option>
                    <option value="medium" @selected($filters['tier'] === 'medium')>Medium</option>
                    <option value="basic" @selected($filters['tier'] === 'basic')>Basique</option>
                    <option value="none" @selected($filters['tier'] === 'none')>Non classé</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">Réseau connecté</label>
                <select name="network" class="rounded-xl border-slate-200 text-sm">
                    <option value="all">Tous</option>
                    @foreach (\App\Models\Mission::NETWORK_LABELS as $key => $label)
                        <option value="{{ $key }}" @selected($filters['network'] === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">Statut compte</label>
                <select name="status" class="rounded-xl border-slate-200 text-sm">
                    <option value="all">Tous</option>
                    <option value="active" @selected($filters['status'] === 'active')>Actif</option>
                    <option value="suspended" @selected($filters['status'] === 'suspended')>Suspendu</option>
                    <option value="blocked" @selected($filters['status'] === 'blocked')>Bloqué</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">Tri</label>
                <select name="sort" class="rounded-xl border-slate-200 text-sm">
                    <option value="latest" @selected($filters['sort'] === 'latest')>Plus récents</option>
                    <option value="participations_desc" @selected($filters['sort'] === 'participations_desc')>Participations ↓</option>
                    <option value="name_asc" @selected($filters['sort'] === 'name_asc')>Nom A→Z</option>
                </select>
            </div>

            <button type="submit" class="rounded-xl bg-teal-700 px-4 py-2 text-sm font-bold text-white">Filtrer</button>
            @if (array_filter($filters, fn ($v) => $v !== 'all' && $v !== 'latest' && $v !== ''))
                <a href="{{ route('admin.creators.index') }}" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600">Réinitialiser</a>
            @endif
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3">Nom</th>
                    <th class="px-4 py-3">WhatsApp</th>
                    <th class="px-4 py-3">Vérification</th>
                    <th class="px-4 py-3">Classement</th>
                    <th class="px-4 py-3">Réseaux</th>
                    <th class="px-4 py-3">Participations</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($creators as $creator)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $creator->name }}</p>
                            <p class="text-xs text-slate-400">{{ $creator->email }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $creator->phone ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-1 text-xs font-semibold
                                {{ $creator->verification_status === 'verified' ? 'bg-emerald-100 text-emerald-700' : ($creator->verification_status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                {{ $creator->verificationStatusLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3"
                            x-data="{ label: @js($creator->creatorTierLabel()) }"
                            x-text="label"
                            @tier-updated.window="if ($event.detail.creatorId === {{ $creator->id }}) label = $event.detail.creatorTierLabel">
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500">
                            {{ $creator->socialNetworks->map->platformLabel()->implode(', ') ?: '—' }}
                        </td>
                        <td class="px-4 py-3">{{ $creator->participations_count }}</td>
                        <td class="px-4 py-3">
                            <x-admin.verification-modals
                                :prefix="'creator-'.$creator->id"
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
                            <a href="{{ route('admin.creators.show', $creator) }}"
                               class="mt-1 inline-flex text-xs font-semibold text-teal-700 hover:underline">
                                Fiche complète
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-500">Aucun créateur trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $creators->links() }}</div>
@endsection
