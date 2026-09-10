@extends('layouts.admin')

@section('title', 'Vérifications')
@section('heading', 'File de vérification')

@section('content')
    @php
        $tabs = [
            'queue' => 'À vérifier',
            'validated' => 'Validées',
            'rejected' => 'Refusées',
            'all' => 'Toutes',
        ];
    @endphp
    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('admin.participations.index', ['status' => $key]) }}"
               class="rounded-full px-4 py-2 text-sm font-semibold {{ $status === $key ? 'bg-rose-600 text-white' : 'bg-white text-slate-600 shadow-sm' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3">Créateur</th>
                    <th class="px-4 py-3">Mission</th>
                    <th class="px-4 py-3">Réseau</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3">Soumise</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($participations as $participation)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $participation->user->name }}</p>
                            <p class="text-xs text-slate-400">{{ $participation->user->creatorTierLabel() }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $participation->mission->brand_name }}</td>
                        <td class="px-4 py-3">{{ $participation->mission->networkLabel() }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $participation->statusColor() }}">
                                {{ $participation->statusLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ optional($participation->submitted_at)->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <x-admin.verification-modals
                                :prefix="'participation-'.$participation->id"
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
                            <a href="{{ route('admin.participations.show', $participation) }}"
                               class="mt-1 inline-flex text-xs font-semibold text-teal-700 hover:underline">
                                Détail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-slate-500">Aucune participation.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $participations->links() }}</div>
@endsection
