@extends('layouts.mobile')

@section('title', 'Missions — AfriBoost')
@section('heading', 'Missions')

@php
    use App\Support\Money;

    $networks = ['all' => 'Toutes'] + \App\Models\Mission::NETWORK_LABELS;
    $statusChips = [
        'in_progress' => ['En cours', 'bg-amber-100 text-amber-800'],
        'submitted' => ['En attente', 'bg-orange-100 text-orange-700'],
        'under_review' => ['En vérification', 'bg-orange-100 text-orange-700'],
        'validated' => ['Validée', 'bg-brand-100 text-brand-700'],
        'paid' => ['Payée', 'bg-brand-100 text-brand-700'],
        'rejected' => ['Refusée', 'bg-cta-100 text-cta-700'],
    ];
@endphp

@section('content')
    {{-- Bannières (maquette q1 / q2) --}}
    <section x-data="{ slide: 0, total: 2, timer: null,
                       start() { this.timer = setInterval(() => this.slide = (this.slide + 1) % this.total, 6000) },
                       go(i) { this.slide = i; clearInterval(this.timer); this.start() } }"
             x-init="start()" class="relative mb-5" aria-roledescription="carrousel">
        <div class="overflow-hidden rounded-3xl shadow-card">
            <div class="flex transition-transform duration-500 ease-out" :style="`transform: translateX(-${slide * 100}%)`">
                <div class="relative min-w-full overflow-hidden bg-gradient-to-br from-brand-700 via-brand-800 to-slate-900 px-5 py-6 text-white">
                    <div class="absolute -right-8 -top-10 h-40 w-40 rounded-full bg-brand-400/20"></div>
                    <div class="absolute -bottom-12 right-10 h-32 w-32 rounded-full bg-amber-300/10"></div>
                    <div class="absolute right-4 top-4 flex gap-1.5">
                        @foreach (['tiktok', 'instagram', 'facebook', 'youtube'] as $n)
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-white shadow"><x-network-icon :network="$n" class="h-4 w-4" /></span>
                        @endforeach
                    </div>
                    <p class="relative mt-6 text-[26px] font-extrabold leading-tight">Monétise<br><span class="text-amber-300">ton audience</span></p>
                    <p class="relative mt-1 text-sm font-semibold text-brand-100">avec AfriBoost</p>
                    <p class="relative mt-3 max-w-[15rem] text-sm text-white/75">Choisis une mission, publie ton contenu et gagne en USD.</p>
                </div>
                <div class="relative min-w-full overflow-hidden bg-gradient-to-br from-orange-500 to-cta-600 px-5 py-6 text-white">
                    <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
                    <span class="ab-chip relative bg-white/20 text-white"><x-icon name="sparkles" class="h-3.5 w-3.5" /> Vous avez une marque ?</span>
                    <p class="relative mt-3 text-[22px] font-extrabold leading-tight">Lancez aussi votre<br>campagne d'influence</p>
                    <p class="relative mt-1 text-sm text-white/85">Faites promouvoir vos produits par nos créateurs.</p>
                    <a href="{{ route('brands.create') }}" class="relative mt-4 inline-flex items-center gap-2 rounded-full bg-lime-300 px-5 py-2.5 text-sm font-extrabold text-slate-900 shadow-lg hover:bg-lime-200">
                        Créer une campagne <x-icon name="arrow-right" class="h-4 w-4" stroke="2.4" />
                    </a>
                </div>
            </div>
        </div>
        <div class="mt-2.5 flex justify-center gap-2">
            <template x-for="i in total" :key="i">
                <button type="button" @click="go(i - 1)" class="h-2.5 rounded-full transition-all"
                        :class="slide === i - 1 ? 'w-6 bg-cta-600' : 'w-2.5 bg-white ring-1 ring-slate-300'"
                        :aria-label="`Bannière ${i}`"></button>
            </template>
        </div>
    </section>

    {{-- Filtres par réseau --}}
    <div class="-mx-4 mb-4 flex gap-1 overflow-x-auto px-4 pb-1 scrollbar-none">
        @foreach ($networks as $key => $label)
            <a href="{{ $key === 'all' ? route('missions.index') : route('missions.network', ['reseau' => $key]) }}"
               class="ab-tab {{ $network === $key ? 'ab-tab-active' : '' }}">
                @if ($key !== 'all')
                    <x-network-icon :network="$key" class="h-3.5 w-3.5" />
                @endif
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse ($missions as $mission)
            @php
                $displayNetwork = ($network !== 'all' && $mission->supportsNetwork($network)) ? $network : $mission->social_network;
                $joinedStatus = $joinedMissionIds[$mission->id] ?? null;
                $remaining = $mission->max_participants ? max(0, $mission->max_participants - $mission->taken_slots_count) : null;
                $url = route('missions.show', $mission->routeParams($displayNetwork));
            @endphp
            <article class="ab-card relative flex gap-3 p-3 transition hover:shadow-lg">
                <a href="{{ $url }}" class="shrink-0" tabindex="-1" aria-hidden="true">
                    <x-mission-visual :mission="$mission" class="h-[104px] w-[104px]" />
                </a>
                <div class="flex min-w-0 flex-1 flex-col">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="truncate text-[15px] font-extrabold uppercase tracking-tight">
                            <a href="{{ $url }}" class="after:absolute after:inset-0">{{ $mission->brand_name }}</a>
                        </h2>
                        @if ($joinedStatus && isset($statusChips[$joinedStatus]))
                            <span class="ab-chip {{ $statusChips[$joinedStatus][1] }}">{{ $statusChips[$joinedStatus][0] }}</span>
                        @else
                            <span class="ab-chip bg-amber-100 text-amber-800"><x-icon name="star" class="h-3 w-3 text-amber-500" /> {{ number_format($mission->rating, 1) }}</span>
                        @endif
                    </div>
                    <p class="mt-1 line-clamp-2 text-[13px] leading-snug text-slate-600 [overflow-wrap:anywhere]">{{ $mission->short_description ?: $mission->title }}</p>
                    <div class="mt-1.5 flex items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                        @foreach ($mission->selectedNetworks() as $n)
                            <x-network-icon :network="$n" class="h-3.5 w-3.5" />
                        @endforeach
                        @if ($mission->ends_at)
                            <span class="ml-1 inline-flex items-center gap-1"><x-icon name="clock" class="h-3.5 w-3.5" /> jusqu'au {{ $mission->ends_at->format('d/m') }}</span>
                        @endif
                        @if ($remaining !== null && $remaining <= 10)
                            <span class="ml-1 text-cta-600">{{ $remaining }} place{{ $remaining > 1 ? 's' : '' }}</span>
                        @endif
                    </div>
                    <div class="mt-auto flex items-end justify-between gap-2 pt-2">
                        <p class="text-base font-extrabold text-cta-600">{{ Money::usd($mission->rewardFor(auth()->user(), $displayNetwork)) }}</p>
                        <span class="relative z-10 rounded-xl bg-cta-600 px-4 py-1.5 text-xs font-extrabold text-white shadow-cta">
                            {{ $joinedStatus ? 'Voir' : 'Participer' }}
                        </span>
                    </div>
                </div>
            </article>
        @empty
            <div class="ab-card flex flex-col items-center px-6 py-10 text-center">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600"><x-icon name="flag" class="h-7 w-7" /></span>
                <p class="mt-3 font-bold">Aucune mission pour le moment</p>
                <p class="mt-1 text-sm text-slate-500">De nouvelles missions arrivent régulièrement. Reviens bientôt !</p>
                @if ($network !== 'all')
                    <a href="{{ route('missions.index') }}" class="mt-4 text-sm font-bold text-brand-700">Voir toutes les missions</a>
                @endif
            </div>
        @endforelse
    </div>
@endsection
