@extends('layouts.mobile')

@section('title', 'Missions — AfriBoost')
@section('heading', 'Missions')
@section('hide_heading', true)

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

    // Meilleure récompense affichée dans la bannière d'accueil
    $topReward = $missions->map(fn ($m) => $m->rewardFor(auth()->user(), $m->social_network))->max();
@endphp

@section('content')
    {{-- Bannières d'accueil --}}
    <section x-data="{ slide: 0, total: 2, timer: null,
                       start() { this.timer = setInterval(() => this.slide = (this.slide + 1) % this.total, 6000) },
                       go(i) { this.slide = i; clearInterval(this.timer); this.start() } }"
             x-init="start()" class="relative mb-6 mt-1" aria-roledescription="carrousel">
        <div class="overflow-hidden rounded-[28px] shadow-2xl shadow-black/30 ring-1 ring-white/10">
            <div class="flex transition-transform duration-500 ease-out" :style="`transform: translateX(-${slide * 100}%)`">
                {{-- 1. Publie et gagne --}}
                <div class="relative flex min-h-[188px] min-w-full overflow-hidden bg-gradient-to-br from-[#0f5a3a] via-[#0c4530] to-[#0a2c22] text-white">
                    <div class="relative z-10 flex w-[58%] flex-col justify-between py-5 pl-5">
                        <div>
                            @if ($topReward)
                                <span class="inline-flex items-center rounded-full bg-amber-400 px-2.5 py-1 text-[11px] font-extrabold text-slate-900 shadow">
                                    jusqu'à {{ Money::usd($topReward) }} / mission
                                </span>
                            @endif
                            <p class="mt-3 text-[30px] font-extrabold leading-[1.02] tracking-tight">Publie<br><span class="text-amber-400">et gagne</span></p>
                        </div>
                        <a href="#missions" class="mt-4 inline-flex w-fit items-center gap-2 rounded-full bg-white px-4 py-2.5 text-sm font-extrabold text-slate-900 shadow-lg transition hover:bg-amber-50 active:scale-[.98]">
                            Commencer <x-icon name="arrow-right" class="h-4 w-4 text-cta-600" stroke="2.6" />
                        </a>
                    </div>
                    {{-- Visuel : téléphone + réseaux en orbite --}}
                    <div class="pointer-events-none absolute inset-y-0 right-0 w-[48%]" aria-hidden="true">
                        <div class="absolute -right-10 top-1/2 h-56 w-56 -translate-y-1/2 rounded-full bg-amber-400/15 blur-2xl"></div>
                        <div class="absolute -left-8 -top-12 h-44 w-44 rounded-full border-2 border-amber-400/40"></div>
                        <div class="absolute right-7 top-1/2 h-36 w-[74px] -translate-y-1/2 rotate-6 rounded-[18px] border-[3px] border-slate-900 bg-gradient-to-b from-slate-700 to-slate-900 shadow-2xl">
                            <div class="mx-auto mt-1.5 h-1 w-6 rounded-full bg-slate-950"></div>
                            <div class="mx-1.5 mt-2 h-[62px] rounded-lg bg-gradient-to-br from-amber-300 via-orange-400 to-cta-500"></div>
                            <div class="mx-1.5 mt-1.5 h-1.5 w-10 rounded-full bg-white/40"></div>
                            <div class="mx-1.5 mt-1 h-1.5 w-7 rounded-full bg-white/25"></div>
                            <div class="absolute -left-9 bottom-3 -rotate-6 rounded-xl bg-white px-2 py-1 text-[11px] font-extrabold text-brand-700 shadow-lg">+ $</div>
                        </div>
                        <span class="absolute left-1 top-[50%] flex h-9 w-9 -rotate-6 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 via-pink-500 to-purple-600 shadow-lg">
                            <x-network-icon network="instagram" :colored="false" class="h-5 w-5 text-white" />
                        </span>
                        <span class="absolute left-10 top-5 flex h-8 w-8 items-center justify-center rounded-full bg-[#1877f2] shadow-lg">
                            <x-network-icon network="facebook" :colored="false" class="h-5 w-5 text-white" />
                        </span>
                        <span class="absolute right-3 top-4 flex h-8 w-8 items-center justify-center rounded-xl bg-slate-950 shadow-lg ring-1 ring-white/20">
                            <x-network-icon network="tiktok" :colored="false" class="h-4 w-4 text-white" />
                        </span>
                        <span class="absolute bottom-4 right-3 flex h-8 w-8 items-center justify-center rounded-xl bg-[#ff0000] shadow-lg">
                            <x-network-icon network="youtube" :colored="false" class="h-5 w-5 text-white" />
                        </span>
                    </div>
                </div>
                {{-- 2. Marques --}}
                <div class="relative flex min-h-[188px] min-w-full flex-col justify-between overflow-hidden bg-gradient-to-br from-orange-500 to-cta-600 px-5 py-5 text-white">
                    <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
                    <div class="absolute -bottom-14 right-8 h-32 w-32 rounded-full border-2 border-white/20"></div>
                    <div class="relative">
                        <span class="ab-chip bg-white/20 text-white"><x-icon name="sparkles" class="h-3.5 w-3.5" /> Vous avez une marque ?</span>
                        <p class="mt-3 text-[22px] font-extrabold leading-tight">Lancez votre<br>campagne d'influence</p>
                    </div>
                    <a href="{{ route('brands.create') }}" class="relative mt-4 inline-flex w-fit items-center gap-2 rounded-full bg-white px-4 py-2.5 text-sm font-extrabold text-slate-900 shadow-lg hover:bg-orange-50">
                        Créer une campagne <x-icon name="arrow-right" class="h-4 w-4 text-cta-600" stroke="2.6" />
                    </a>
                </div>
            </div>
        </div>
        <div class="mt-3 flex justify-center gap-1.5">
            <template x-for="i in total" :key="i">
                <button type="button" @click="go(i - 1)" class="h-2 rounded-full transition-all"
                        :class="slide === i - 1 ? 'w-6 bg-cta-500' : 'w-2 bg-white/50 hover:bg-white/80'"
                        :aria-label="`Bannière ${i}`"></button>
            </template>
        </div>
    </section>

    <div id="missions" class="scroll-mt-24">
        <h1 class="text-xl font-extrabold tracking-tight text-white">Missions</h1>

        {{-- Filtres par réseau --}}
        <div class="-mx-4 mb-4 mt-3 flex gap-2 overflow-x-auto px-4 pb-1 scrollbar-none">
            @foreach ($networks as $key => $label)
                <a href="{{ $key === 'all' ? route('missions.index') : route('missions.network', ['reseau' => $key]) }}#missions"
                   class="ab-tab py-1.5 text-[13px] {{ $network === $key ? 'ab-tab-active' : '' }}">
                    @if ($key !== 'all')
                        <x-network-icon :network="$key" :colored="$network === $key" class="h-3.5 w-3.5" />
                    @endif
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="space-y-3.5">
        @forelse ($missions as $mission)
            @php
                $displayNetwork = ($network !== 'all' && $mission->supportsNetwork($network)) ? $network : $mission->social_network;
                $joinedStatus = $joinedMissionIds[$mission->id] ?? null;
                $remaining = $mission->max_participants ? max(0, $mission->max_participants - $mission->taken_slots_count) : null;
                $url = route('missions.show', $mission->routeParams($displayNetwork));
            @endphp
            <article class="relative flex gap-4 rounded-[26px] bg-white p-3.5 shadow-lg shadow-black/15 transition active:scale-[.99]">
                <a href="{{ $url }}" class="flex h-[112px] w-[112px] shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-slate-50 ring-1 ring-slate-900/5" tabindex="-1" aria-hidden="true">
                    <x-mission-visual :mission="$mission" class="h-full w-full" />
                </a>
                <div class="flex min-w-0 flex-1 flex-col py-0.5">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="truncate text-base font-extrabold uppercase tracking-tight text-slate-900">
                            <a href="{{ $url }}" class="after:absolute after:inset-0">{{ $mission->brand_name }}</a>
                        </h2>
                        @if ($joinedStatus && isset($statusChips[$joinedStatus]))
                            <span class="ab-chip {{ $statusChips[$joinedStatus][1] }}">{{ $statusChips[$joinedStatus][0] }}</span>
                        @endif
                    </div>
                    <p class="mt-1 line-clamp-2 text-[13px] leading-snug text-slate-500 [overflow-wrap:anywhere]">{{ $mission->short_description ?: $mission->title }}</p>
                    <div class="mt-2 flex items-center gap-1">
                        @foreach ($mission->selectedNetworks() as $n)
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-100">
                                <x-network-icon :network="$n" class="h-3.5 w-3.5" />
                            </span>
                        @endforeach
                        @if ($remaining !== null && $remaining <= 10)
                            <span class="ml-1 text-[11px] font-bold text-cta-600">{{ $remaining }} place{{ $remaining > 1 ? 's' : '' }}</span>
                        @endif
                    </div>
                    <div class="mt-auto flex items-end justify-between gap-2 pt-2">
                        <p class="text-[17px] font-extrabold tracking-tight text-cta-600">{{ Money::usd($mission->rewardFor(auth()->user(), $displayNetwork)) }}</p>
                        <span class="relative z-10 inline-flex items-center gap-1 rounded-full bg-cta-600 py-2 pl-4 pr-3 text-[13px] font-extrabold text-white shadow-cta">
                            Voir <x-icon name="chevron-right" class="h-4 w-4" stroke="2.8" />
                        </span>
                    </div>
                </div>
            </article>
        @empty
            <div class="flex flex-col items-center rounded-[26px] bg-white px-6 py-10 text-center shadow-lg shadow-black/15">
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
