@extends('layouts.admin')

@section('title', 'Campagnes')
@section('heading', 'Campagnes')
@section('actions')
    <a href="{{ route('admin.campaigns.create') }}" class="ab-btn-cta py-2"><x-icon name="plus" class="h-4 w-4" stroke="2.6" /> <span class="hidden sm:inline">Nouvelle campagne</span></a>
@endsection

@php use App\Support\Money; @endphp

@section('content')
    <div class="mb-4 flex flex-wrap gap-1 rounded-2xl bg-white p-1 shadow-sm ring-1 ring-slate-900/5 sm:inline-flex">
        @foreach (['all' => 'Toutes'] + \App\Models\Campaign::STATUSES as $key => $label)
            <a href="{{ route('admin.campaigns.index', ['status' => $key]) }}"
               class="rounded-xl px-3 py-1.5 text-sm font-semibold {{ $status === $key ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($campaigns as $campaign)
            @php
                $used = (float) ($spent[$campaign->id] ?? 0);
                $pct = $campaign->budget_usd > 0 ? min(100, round($used / (float) $campaign->budget_usd * 100)) : null;
            @endphp
            <a href="{{ route('admin.campaigns.show', $campaign) }}" class="ab-panel block p-5 transition hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $campaign->client_name }}</p>
                        <h2 class="mt-0.5 truncate text-lg font-extrabold">{{ $campaign->title }}</h2>
                    </div>
                    <span class="ab-chip {{ $campaign->statusColor() }}">{{ $campaign->statusLabel() }}</span>
                </div>
                @if ($campaign->objective)
                    <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ $campaign->objective }}</p>
                @endif
                <div class="mt-4 flex items-center justify-between text-sm">
                    <span class="flex items-center gap-1.5 text-slate-500"><x-icon name="flag" class="h-4 w-4" /> {{ $campaign->missions_count }} mission(s)</span>
                    <span class="text-slate-500">{{ optional($campaign->starts_at)->format('d/m') ?? '—' }} → {{ optional($campaign->ends_at)->format('d/m/Y') ?? '—' }}</span>
                </div>
                <div class="mt-3">
                    <div class="flex justify-between text-xs font-semibold">
                        <span class="text-slate-600">{{ Money::usd($used) }} versés</span>
                        <span class="text-slate-400">{{ $campaign->budget_usd ? 'budget '.Money::usd($campaign->budget_usd) : 'budget non défini' }}</span>
                    </div>
                    <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full {{ ($pct ?? 0) >= 90 ? 'bg-cta-500' : 'bg-brand-500' }}" style="width: {{ $pct ?? 0 }}%"></div>
                    </div>
                </div>
            </a>
        @empty
            <div class="ab-panel col-span-full px-6 py-12 text-center">
                <x-icon name="layers" class="mx-auto h-8 w-8 text-slate-300" />
                <p class="mt-2 font-semibold">Aucune campagne</p>
                <p class="text-sm text-slate-500">Regroupez les missions d'un même client dans une campagne pour suivre son budget.</p>
            </div>
        @endforelse
    </div>
    <div class="mt-4">{{ $campaigns->links() }}</div>
@endsection
