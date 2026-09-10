@extends('layouts.mobile')

@section('title', 'Missions — AfriBoost')
@section('heading', 'Missions')

@section('content')
    <section class="mb-5 overflow-hidden rounded-3xl bg-slate-900 text-white shadow-lg">
        <div class="relative px-5 py-6">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-300">Monétise ton audience</p>
            <h2 class="mt-2 max-w-[16rem] text-2xl font-extrabold leading-tight">Avec AfriBoost</h2>
            <p class="mt-2 max-w-xs text-sm text-slate-300">Choisis une mission, publie ton contenu, gagne en USD.</p>
            <div class="mt-4 flex gap-2 text-[11px] font-semibold">
                <span class="rounded-full bg-white/10 px-3 py-1">TikTok</span>
                <span class="rounded-full bg-white/10 px-3 py-1">Instagram</span>
                <span class="rounded-full bg-white/10 px-3 py-1">Facebook</span>
                <span class="rounded-full bg-white/10 px-3 py-1">YouTube</span>
            </div>
        </div>
    </section>

    <div class="mb-4 flex gap-2 overflow-x-auto pb-1 text-sm font-semibold">
        @php
            $networks = ['all' => 'Toutes', 'facebook' => 'Facebook', 'tiktok' => 'TikTok', 'instagram' => 'Instagram', 'youtube' => 'YouTube'];
        @endphp
        @foreach ($networks as $key => $label)
            <a href="{{ $key === 'all' ? route('missions.index') : route('missions.network', ['reseau' => $key]) }}"
               class="whitespace-nowrap rounded-full px-4 py-2 {{ $network === $key ? 'bg-rose-600 text-white' : 'bg-white text-slate-600 shadow-sm' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse ($missions as $mission)
            <article class="rounded-3xl bg-white p-3 shadow-sm ring-1 ring-slate-100">
                <div class="flex gap-3">
                    @if ($mission->logoUrl())
                        <img src="{{ $mission->logoUrl() }}" alt="{{ $mission->brand_name }}"
                             class="h-20 w-20 shrink-0 rounded-2xl object-cover ring-1 ring-slate-100">
                    @else
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 text-center text-xs font-bold uppercase text-white">
                            {{ $mission->brand_name }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="truncate font-bold">{{ $mission->brand_name }}</h3>
                                <p class="text-xs text-amber-500">★ {{ number_format($mission->rating, 1) }} · {{ $mission->networkLabels() }}</p>
                            </div>
                            @php
                                $displayNetwork = ($network !== 'all' && $mission->supportsNetwork($network))
                                    ? $network
                                    : $mission->social_network;
                            @endphp
                            <p class="shrink-0 text-sm font-extrabold text-rose-600">{{ number_format($mission->rewardFor(auth()->user(), $displayNetwork), 2) }} USD</p>
                        </div>
                        <p class="mt-1 line-clamp-2 break-words text-sm text-slate-500 [overflow-wrap:anywhere]">{{ $mission->short_description }}</p>
                        <div class="mt-3 flex justify-end">
                            <a href="{{ route('missions.show', $mission->routeParams($displayNetwork)) }}" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-bold text-white">Voir</a>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl bg-white p-8 text-center text-slate-500 shadow-sm">
                Aucune mission disponible pour ce filtre.
            </div>
        @endforelse
    </div>
@endsection
