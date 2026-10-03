@extends('layouts.admin')

@section('title', $campaign->title)
@section('heading', $campaign->title)
@section('actions')
    <a href="{{ route('admin.missions.create', ['campaign_id' => $campaign->id]) }}" class="ab-btn-ghost py-2"><x-icon name="plus" class="h-4 w-4" /> <span class="hidden sm:inline">Mission</span></a>
    <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="ab-btn-brand py-2"><x-icon name="pencil" class="h-4 w-4" /> Modifier</a>
@endsection

@php use App\Support\Money; @endphp

@section('content')
    <a href="{{ route('admin.campaigns.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-700"><x-icon name="arrow-left" class="h-4 w-4" /> Campagnes</a>

    <div class="grid gap-4 lg:grid-cols-3">
        <section class="ab-panel p-5 lg:col-span-2">
            <div class="flex flex-wrap items-center gap-2">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $campaign->client_name }}</p>
                <span class="ab-chip {{ $campaign->statusColor() }}">{{ $campaign->statusLabel() }}</span>
            </div>
            <p class="mt-2 text-slate-700">{{ $campaign->objective ?: 'Aucun objectif renseigné.' }}</p>
            <p class="mt-3 flex items-center gap-2 text-sm text-slate-500"><x-icon name="calendar" class="h-4 w-4" />
                {{ optional($campaign->starts_at)->format('d/m/Y') ?? '—' }} → {{ optional($campaign->ends_at)->format('d/m/Y') ?? '—' }}</p>
        </section>
        <section class="ab-panel p-5">
            <p class="text-sm font-semibold text-slate-500">Budget consommé</p>
            <p class="mt-1 text-2xl font-extrabold">{{ Money::usd($stats['spent']) }}</p>
            <p class="text-sm text-slate-500">sur {{ $campaign->budget_usd ? Money::usd($campaign->budget_usd) : 'budget non défini' }}</p>
            @if ($stats['budget_used'] !== null)
                <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full {{ $stats['budget_used'] >= 90 ? 'bg-cta-500' : 'bg-brand-500' }}" style="width: {{ $stats['budget_used'] }}%"></div>
                </div>
                <p class="mt-1 text-xs font-semibold text-slate-500">{{ $stats['budget_used'] }} % utilisé</p>
            @endif
        </section>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        <x-admin.stat label="Missions" :value="$stats['missions']" icon="flag" />
        <x-admin.stat label="Créateurs" :value="$stats['creators']" icon="users" tone="sky" />
        <x-admin.stat label="Participations" :value="$stats['participants']" icon="clipboard" />
        <x-admin.stat label="À vérifier" :value="$stats['pending']" icon="clock" tone="amber" />
        <x-admin.stat label="Validées" :value="$stats['validated']" icon="check-circle" tone="brand" />
        <x-admin.stat label="Refusées" :value="$stats['rejected']" icon="x-circle" tone="cta" />
    </div>

    <section class="ab-panel mt-4 overflow-hidden">
        <h3 class="px-5 py-4 font-extrabold">Missions de la campagne</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-y border-slate-100 bg-slate-50">
                    <tr>
                        <th class="ab-th">Mission</th>
                        <th class="ab-th">Réseaux</th>
                        <th class="ab-th">Statut</th>
                        <th class="ab-th">Participants</th>
                        <th class="ab-th">À vérifier</th>
                        <th class="ab-th">Validées</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($campaign->missions as $mission)
                        <tr class="hover:bg-slate-50/60">
                            <td class="ab-td">
                                <a href="{{ route('admin.missions.show', $mission) }}" class="font-semibold hover:text-brand-700">{{ $mission->brand_name }}</a>
                                <p class="text-xs text-slate-500">{{ $mission->title }}</p>
                            </td>
                            <td class="ab-td"><span class="flex gap-1">@foreach ($mission->selectedNetworks() as $n)<x-network-icon :network="$n" />@endforeach</span></td>
                            <td class="ab-td">{{ $mission->statusLabel() }}</td>
                            <td class="ab-td font-semibold">{{ $mission->participations_count }}</td>
                            <td class="ab-td {{ $mission->pending_count ? 'font-bold text-orange-600' : 'text-slate-400' }}">{{ $mission->pending_count }}</td>
                            <td class="ab-td text-emerald-700">{{ $mission->validated_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">Aucune mission rattachée à cette campagne.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
