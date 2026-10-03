@extends('layouts.mobile')

@section('title', 'Mes participations — AfriBoost')
@section('heading', 'Mes participations')

@php
    use App\Support\Money;

    $tabs = [
        '' => 'Toutes',
        'in_progress' => 'À compléter',
        'submitted' => 'En attente',
        'validated' => 'Validées',
        'rejected' => 'Refusées',
    ];
    $statusText = [
        'in_progress' => 'text-amber-600',
        'submitted' => 'text-orange-600',
        'under_review' => 'text-orange-600',
        'validated' => 'text-brand-600',
        'paid' => 'text-brand-600',
        'rejected' => 'text-cta-600',
    ];
    $statusSentence = [
        'in_progress' => 'À publier puis soumettre',
        'submitted' => 'En attente de validation',
        'under_review' => 'En cours de vérification',
        'validated' => 'Validée',
        'paid' => 'Payée',
        'rejected' => 'Refusée',
    ];
@endphp

@section('content')
    <div class="-mx-4 mb-4 flex gap-1 overflow-x-auto px-4 pb-1 scrollbar-none">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('participations.index', array_filter(['status' => $key])) }}"
               class="ab-tab {{ $status === $key ? 'ab-tab-active' : '' }}">
                {{ $label }}
                @if ($key !== '' && ($counts[$key] ?? 0) > 0)
                    <span class="rounded-full bg-slate-900/5 px-1.5 text-[11px] font-bold">{{ $counts[$key] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse ($participations as $participation)
            @php $mission = $participation->mission; @endphp
            <a href="{{ route('missions.show', $mission->routeParams($participation->effectiveNetwork())) }}"
               class="ab-card flex items-center gap-3 p-3 transition hover:shadow-lg">
                <x-mission-visual :mission="$mission" class="h-24 w-24" />
                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="truncate text-[15px] font-extrabold uppercase">{{ $mission->brand_name }}</h2>
                        <span class="ab-chip {{ $participation->statusColor() }}">{{ $participation->statusLabel() }}</span>
                    </div>
                    <p class="mt-1 flex items-center gap-1.5 text-xs text-slate-600">
                        <x-network-icon :network="$participation->effectiveNetwork()" class="h-3.5 w-3.5" />
                        @if ($participation->submitted_at)
                            Publié le : {{ $participation->submitted_at->format('d/m/Y') }}
                        @else
                            Démarrée le : {{ $participation->created_at->format('d/m/Y') }}
                        @endif
                    </p>
                    <p class="mt-0.5 text-xs text-slate-600">
                        <span class="font-semibold text-slate-800">Statut :</span>
                        <span class="font-semibold {{ $statusText[$participation->status] ?? '' }}">{{ $statusSentence[$participation->status] ?? $participation->statusLabel() }}</span>
                    </p>
                    @if ($participation->status === 'rejected' && $participation->rejection_reason)
                        <p class="mt-1 line-clamp-2 rounded-lg bg-cta-50 px-2 py-1 text-xs text-cta-700">{{ $participation->rejection_reason }}</p>
                    @endif
                    <p class="mt-1.5 text-[15px] font-extrabold text-cta-600">{{ Money::usd($participation->rewardAmount()) }}</p>
                </div>
                <x-icon name="chevron-right" class="h-5 w-5 text-slate-400" stroke="2.4" />
            </a>
        @empty
            <div class="ab-card flex flex-col items-center px-6 py-10 text-center">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600"><x-icon name="clipboard" class="h-7 w-7" /></span>
                <p class="mt-3 font-bold">{{ $status ? 'Rien dans cet onglet' : 'Aucune participation' }}</p>
                <p class="mt-1 text-sm text-slate-500">Rejoins une mission pour commencer à gagner en USD.</p>
                <a href="{{ route('missions.index') }}" class="ab-btn-cta mt-4">Voir les missions</a>
            </div>
        @endforelse
    </div>
@endsection
