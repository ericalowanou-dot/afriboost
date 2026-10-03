@extends('layouts.admin')

@section('title', 'Demandes de marques')
@section('heading', 'Demandes de marques')

@php use App\Support\Money; @endphp

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="inline-flex flex-wrap gap-1 rounded-2xl bg-white p-1 shadow-sm ring-1 ring-slate-900/5">
            @foreach (['open' => 'À traiter'] + \App\Models\CampaignRequest::STATUSES + ['all' => 'Toutes'] as $key => $label)
                <a href="{{ route('admin.campaign-requests.index', ['status' => $key]) }}"
                   class="rounded-xl px-3 py-1.5 text-sm font-semibold {{ $status === $key ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">{{ $label }}</a>
            @endforeach
        </div>
        <a href="{{ route('brands.create') }}" target="_blank" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700">
            Formulaire public <x-icon name="external" class="h-4 w-4" />
        </a>
    </div>

    <div class="space-y-3">
        @forelse ($requests as $req)
            <article class="ab-panel p-5">
                <div class="flex flex-wrap items-start gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-extrabold">{{ $req->company_name }}</h2>
                            <span class="ab-chip {{ $req->statusColor() }}">{{ $req->statusLabel() }}</span>
                            <span class="text-xs text-slate-400">{{ $req->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-600">
                            <span class="flex items-center gap-1"><x-icon name="user" class="h-4 w-4 text-slate-400" /> {{ $req->contact_name }}</span>
                            <a href="mailto:{{ $req->email }}" class="flex items-center gap-1 hover:text-brand-700"><x-icon name="mail" class="h-4 w-4 text-slate-400" /> {{ $req->email }}</a>
                            @if ($req->phone)
                                <a href="tel:{{ $req->phone }}" class="flex items-center gap-1 hover:text-brand-700"><x-icon name="phone" class="h-4 w-4 text-slate-400" /> {{ $req->phone }}</a>
                            @endif
                        </p>
                        <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $req->objective }}</p>
                        <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-500">
                            @if ($req->networks)
                                <span class="flex items-center gap-1">@foreach ($req->networks as $n)<x-network-icon :network="$n" />@endforeach</span>
                            @endif
                            @if ($req->budget_usd)
                                <span>Budget : <strong class="text-slate-700">{{ Money::usd($req->budget_usd) }}</strong></span>
                            @endif
                            @if ($req->desired_start)
                                <span>Démarrage : {{ $req->desired_start->format('d/m/Y') }}</span>
                            @endif
                            @if ($req->campaign)
                                <a href="{{ route('admin.campaigns.show', $req->campaign) }}" class="font-semibold text-brand-700">→ {{ $req->campaign->title }}</a>
                            @endif
                        </div>
                    </div>
                    <div class="flex w-full flex-col gap-2 sm:w-56">
                        @unless ($req->status === 'converted')
                            <a href="{{ route('admin.campaigns.create', ['demande' => $req->id]) }}" class="ab-btn-brand py-2">Créer la campagne</a>
                        @endunless
                        <form method="POST" action="{{ route('admin.campaign-requests.update', $req) }}" class="flex gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="ab-input py-2 text-sm" onchange="this.form.submit()" aria-label="Statut de la demande">
                                @foreach (\App\Models\CampaignRequest::STATUSES as $value => $label)
                                    <option value="{{ $value }}" @selected($req->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="ab-panel px-6 py-12 text-center">
                <x-icon name="inbox" class="mx-auto h-8 w-8 text-slate-300" />
                <p class="mt-2 font-semibold">Aucune demande</p>
                <p class="text-sm text-slate-500">Les marques peuvent vous écrire depuis la page « Lancer une campagne ».</p>
            </div>
        @endforelse
    </div>
    <div class="mt-4">{{ $requests->links() }}</div>
@endsection
