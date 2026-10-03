@extends('layouts.admin')

@section('title', 'Missions')
@section('heading', 'Missions')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.missions.index') }}" class="flex flex-1 flex-wrap items-end gap-3">
            <div class="min-w-[180px]">
                <label class="mb-1 block text-xs font-semibold text-slate-500">Recherche</label>
                <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Marque, titre…"
                       class="w-full rounded-xl border-slate-200 text-sm">
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">Réseau</label>
                <select name="network" class="rounded-xl border-slate-200 text-sm">
                    <option value="all">Tous</option>
                    @foreach (\App\Models\Mission::NETWORK_LABELS as $key => $label)
                        <option value="{{ $key }}" @selected($filters['network'] === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">Statut</label>
                <select name="status" class="rounded-xl border-slate-200 text-sm">
                    <option value="all">Tous</option>
                    @foreach (['draft' => 'Brouillon', 'published' => 'Publiée', 'closed' => 'Fermée'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">Campagne</label>
                <select name="campaign_id" class="rounded-xl border-slate-200 text-sm">
                    <option value="">Toutes</option>
                    @foreach ($campaigns as $campaign)
                        <option value="{{ $campaign->id }}" @selected($filters['campaign_id'] == $campaign->id)>{{ $campaign->title }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">Tri</label>
                <select name="sort" class="rounded-xl border-slate-200 text-sm">
                    <option value="latest" @selected($filters['sort'] === 'latest')>Plus récentes</option>
                    <option value="reward_desc" @selected($filters['sort'] === 'reward_desc')>Budget ↓</option>
                    <option value="reward_asc" @selected($filters['sort'] === 'reward_asc')>Budget ↑</option>
                    <option value="ends_soon" @selected($filters['sort'] === 'ends_soon')>Fin proche</option>
                </select>
            </div>

            <button type="submit" class="rounded-xl bg-brand-700 px-4 py-2 text-sm font-bold text-white">Filtrer</button>
            @if (array_filter($filters))
                <a href="{{ route('admin.missions.index') }}" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600">Réinitialiser</a>
            @endif
        </form>

        <a href="{{ route('admin.missions.create') }}" class="shrink-0 rounded-xl bg-cta-600 px-4 py-2 text-sm font-bold text-white">Nouvelle mission</a>
    </div>

    <div class="ab-panel overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3">Entreprise</th>
                    <th class="px-4 py-3">Réseaux</th>
                    <th class="px-4 py-3">Budget (Basique)</th>
                    <th class="px-4 py-3">Suivi</th>
                    <th class="px-4 py-3">Dates</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($missions as $mission)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($mission->logoUrl())
                                    <img src="{{ $mission->logoUrl() }}" alt="" class="h-10 w-10 rounded-lg object-cover">
                                @else
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-100 text-xs font-bold text-brand-700">
                                        {{ strtoupper(substr($mission->brand_name, 0, 2)) }}
                                    </div>
                                @endif
                                <a href="{{ route('admin.missions.show', $mission) }}" class="group">
                                    <div class="font-semibold group-hover:text-brand-700">{{ $mission->brand_name }}</div>
                                    <div class="text-slate-500">{{ $mission->title }}</div>
                                    @if ($mission->campaign)
                                        <div class="text-xs text-slate-400">{{ $mission->campaign->title }}</div>
                                    @endif
                                </a>
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $mission->networkLabels() }}</td>
                        <td class="px-4 py-3 font-semibold">{{ \App\Support\Money::usd($mission->reward_usd) }}</td>
                        <td class="px-4 py-3 text-xs">
                            <div class="font-semibold text-slate-700">{{ $mission->participations_count }}{{ $mission->max_participants ? ' / '.$mission->max_participants : '' }} participant(s)</div>
                            <div class="mt-0.5 flex gap-2">
                                @if ($mission->pending_count)
                                    <a href="{{ route('admin.participations.index', ['mission_id' => $mission->id]) }}" class="font-semibold text-orange-600">{{ $mission->pending_count }} à vérifier</a>
                                @endif
                                <span class="text-emerald-700">{{ $mission->validated_count }} validée(s)</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500">
                            @if ($mission->starts_at)
                                <div>Début : {{ $mission->starts_at->format('d/m/Y') }}</div>
                            @endif
                            @if ($mission->ends_at)
                                <div>Fin : {{ $mission->ends_at->format('d/m/Y') }}</div>
                            @endif
                            @if ($mission->content_retention_days)
                                <div>Conservation : {{ $mission->content_retention_days }} j</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-1 text-xs font-semibold
                                {{ $mission->status === 'published' ? 'bg-emerald-100 text-emerald-700' : ($mission->status === 'closed' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-700') }}">
                                {{ $mission->statusLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.missions.show', $mission) }}" class="mr-3 font-semibold text-slate-600 hover:text-slate-900">Suivi</a>
                            <a href="{{ route('admin.missions.edit', $mission) }}" class="font-semibold text-brand-700">Modifier</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-500">Aucune mission trouvée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $missions->links() }}</div>
@endsection
