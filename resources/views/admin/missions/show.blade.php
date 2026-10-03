@extends('layouts.admin')

@section('title', 'Suivi — '.$mission->title)
@section('heading', 'Suivi de la mission')
@section('actions')
    @if ($mission->status === 'published')
        <a href="{{ route('missions.show', $mission->routeParams()) }}" target="_blank" class="ab-btn-ghost py-2"><x-icon name="external" class="h-4 w-4" /> <span class="hidden sm:inline">Voir côté créateur</span></a>
    @endif
    <a href="{{ route('admin.missions.edit', $mission) }}" class="ab-btn-brand py-2"><x-icon name="pencil" class="h-4 w-4" /> Modifier</a>
@endsection

@php use App\Support\Money; @endphp

@section('content')
    <a href="{{ route('admin.missions.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-700"><x-icon name="arrow-left" class="h-4 w-4" /> Toutes les missions</a>

    <section class="ab-panel flex flex-col gap-4 p-5 sm:flex-row sm:items-center">
        <x-mission-visual :mission="$mission" class="h-20 w-20" />
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-xl font-extrabold">{{ $mission->brand_name }}</h2>
                <span class="ab-chip {{ $mission->status === 'published' ? 'bg-emerald-100 text-emerald-700' : ($mission->status === 'closed' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-700') }}">{{ $mission->statusLabel() }}</span>
                @if ($mission->hasEnded())
                    <span class="ab-chip bg-slate-100 text-slate-600">Date limite dépassée</span>
                @endif
            </div>
            <p class="text-slate-600">{{ $mission->title }}</p>
            <p class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-500">
                <span class="flex items-center gap-1">@foreach ($mission->selectedNetworks() as $n)<x-network-icon :network="$n" />@endforeach</span>
                @if ($mission->campaign)
                    <a href="{{ route('admin.campaigns.show', $mission->campaign) }}" class="font-semibold text-brand-700">{{ $mission->campaign->title }}</a>
                @endif
                <span>{{ optional($mission->starts_at)->format('d/m/Y') ?? '—' }} → {{ optional($mission->ends_at)->format('d/m/Y') ?? 'sans fin' }}</span>
            </p>
        </div>
        @php [$min, $max] = $mission->rewardRange(); @endphp
        <div class="text-left sm:text-right">
            <p class="text-sm text-slate-500">Récompense</p>
            <p class="text-xl font-extrabold text-cta-600">{{ $min == $max ? Money::usd($min) : Money::usd($min).' – '.Money::usd($max) }}</p>
        </div>
    </section>

    <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        <x-admin.stat label="Participants" :value="$stats['participants'].($mission->max_participants ? ' / '.$mission->max_participants : '')" icon="users" />
        <x-admin.stat label="Contenus soumis" :value="$stats['submitted']" icon="upload" tone="sky" />
        <x-admin.stat label="En attente" :value="$stats['pending']" icon="clock" tone="amber" :href="route('admin.participations.index', ['mission_id' => $mission->id])" />
        <x-admin.stat label="Validés" :value="$stats['validated']" icon="check-circle" tone="brand" />
        <x-admin.stat label="Refusés" :value="$stats['rejected']" icon="x-circle" tone="cta" />
        <x-admin.stat label="Récompenses" :value="Money::usd($stats['rewards'])" icon="dollar" tone="amber" />
    </div>

    <section class="ab-panel mt-4 overflow-hidden">
        <h3 class="px-5 py-4 font-extrabold">Participations</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-y border-slate-100 bg-slate-50">
                    <tr>
                        <th class="ab-th">Créateur</th>
                        <th class="ab-th">Réseau</th>
                        <th class="ab-th">Lien</th>
                        <th class="ab-th">Statut</th>
                        <th class="ab-th">Récompense</th>
                        <th class="ab-th"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($participations as $p)
                        <tr class="hover:bg-slate-50/60">
                            <td class="ab-td font-semibold"><a href="{{ route('admin.creators.show', $p->user_id) }}" class="hover:text-brand-700">{{ $p->user->name }}</a></td>
                            <td class="ab-td"><x-network-icon :network="$p->effectiveNetwork()" /></td>
                            <td class="ab-td max-w-[16rem] truncate">
                                @if ($p->content_url)
                                    <a href="{{ $p->content_url }}" target="_blank" rel="noopener" class="text-brand-700 hover:underline">{{ $p->content_url }}</a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="ab-td"><span class="ab-chip {{ $p->statusColor() }}">{{ $p->statusLabel() }}</span></td>
                            <td class="ab-td font-semibold">{{ Money::usd($p->rewardAmount()) }}</td>
                            <td class="ab-td text-right"><a href="{{ route('admin.participations.show', $p) }}" class="text-xs font-bold text-brand-700">Ouvrir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">Aucune participation pour cette mission.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <div class="mt-4">{{ $participations->links() }}</div>
@endsection
