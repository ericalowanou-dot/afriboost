@extends('layouts.mobile')

@section('title', 'Mes participations — AfriBoost')
@section('heading', 'Mes participations')

@section('content')
    @php
        $tabs = [
            '' => 'Toutes',
            'in_progress' => 'En cours',
            'submitted' => 'En attente',
            'validated' => 'Validées',
            'rejected' => 'Refusées',
        ];
    @endphp

    <div class="mb-4 flex gap-2 overflow-x-auto pb-1 text-sm font-semibold">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('participations.index', array_filter(['status' => $key])) }}"
               class="whitespace-nowrap rounded-full px-4 py-2 {{ ($status ?: '') === $key ? 'bg-rose-600 text-white' : 'bg-white text-slate-600 shadow-sm' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse ($participations as $participation)
            <article class="rounded-3xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="font-bold">{{ $participation->mission->brand_name }}</h3>
                        <p class="text-sm text-slate-500">{{ $participation->mission->title }}</p>
                        <p class="mt-2 text-xs text-slate-400">
                            @if ($participation->submitted_at)
                                Publié le : {{ $participation->submitted_at->format('d/m/Y') }}
                            @else
                                Démarrée le : {{ $participation->created_at->format('d/m/Y') }}
                            @endif
                        </p>
                        <p class="text-xs text-slate-500">Statut : {{ $participation->statusLabel() }}</p>
                        @if ($participation->rejection_reason)
                            <p class="mt-1 text-xs text-red-600">{{ $participation->rejection_reason }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $participation->statusColor() }}">{{ $participation->statusLabel() }}</span>
                        <p class="mt-3 text-sm font-extrabold text-rose-600">{{ number_format($participation->mission->rewardFor($participation->user), 2) }} USD</p>
                    </div>
                </div>
                <div class="mt-3 flex justify-end">
                    <a href="{{ route('missions.show', $participation->mission->routeParams()) }}" class="text-sm font-semibold text-teal-700">Ouvrir →</a>
                </div>
            </article>
        @empty
            <div class="rounded-3xl bg-white p-8 text-center text-slate-500 shadow-sm">Aucune participation pour ce filtre.</div>
        @endforelse
    </div>
@endsection
