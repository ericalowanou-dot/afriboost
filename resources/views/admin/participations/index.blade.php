@extends('layouts.admin')

@section('title', 'Vérifications')
@section('heading', 'File de vérification')

@php
    use App\Support\Money;

    $tabs = [
        'queue' => 'À vérifier',
        'in_progress' => 'En cours',
        'validated' => 'Validées',
        'rejected' => 'Refusées',
        'all' => 'Toutes',
    ];
@endphp

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-1 rounded-2xl bg-white p-1 shadow-sm ring-1 ring-slate-900/5">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('admin.participations.index', array_filter(['status' => $key, 'mission_id' => $missionId, 'search' => $search])) }}"
                   class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-sm font-semibold {{ $status === $key ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                    {{ $label }}
                    @if ($key === 'queue' && $queueCount)
                        <span class="rounded-full px-1.5 text-[11px] font-bold {{ $status === $key ? 'bg-cta-600 text-white' : 'bg-cta-100 text-cta-700' }}">{{ $queueCount }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input type="search" name="search" value="{{ $search }}" placeholder="Créateur…" class="rounded-xl border-slate-200 py-2 pl-9 text-sm">
            </div>
            <select name="mission_id" class="rounded-xl border-slate-200 py-2 text-sm" onchange="this.form.submit()">
                <option value="">Toutes les missions</option>
                @foreach ($missions as $m)
                    <option value="{{ $m->id }}" @selected($missionId === $m->id)>{{ $m->brand_name }} — {{ \Illuminate\Support\Str::limit($m->title, 30) }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="ab-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-100 bg-slate-50">
                    <tr>
                        <th class="ab-th">Créateur</th>
                        <th class="ab-th">Mission</th>
                        <th class="ab-th">Récompense</th>
                        <th class="ab-th">Statut</th>
                        <th class="ab-th">Soumise</th>
                        <th class="ab-th">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($participations as $participation)
                        <tr class="hover:bg-slate-50/60">
                            <td class="ab-td">
                                <a href="{{ route('admin.creators.show', $participation->user_id) }}" class="font-semibold hover:text-brand-700">{{ $participation->user->name }}</a>
                                <p class="text-xs text-slate-400">{{ $participation->user->creatorTierLabel() }}</p>
                            </td>
                            <td class="ab-td">
                                <div class="flex items-center gap-2">
                                    <x-network-icon :network="$participation->effectiveNetwork()" />
                                    <div>
                                        <p class="font-semibold">{{ $participation->mission->brand_name }}</p>
                                        <p class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($participation->mission->title, 40) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="ab-td font-semibold">{{ Money::usd($participation->rewardAmount()) }}</td>
                            <td class="ab-td"><span class="ab-chip {{ $participation->statusColor() }}">{{ $participation->statusLabel() }}</span></td>
                            <td class="ab-td whitespace-nowrap text-slate-500">{{ optional($participation->submitted_at)->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="ab-td">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-admin.verification-modals
                                        :prefix="'participation-'.$participation->id"
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
                                    <a href="{{ route('admin.participations.show', $participation) }}" class="text-xs font-bold text-brand-700 hover:underline">Détail</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">Aucune participation.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $participations->links() }}</div>
@endsection
