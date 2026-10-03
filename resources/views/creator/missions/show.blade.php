@extends('layouts.mobile')

@section('title', $mission->brand_name.' — '.$mission->title.' — AfriBoost')
@section('heading', 'Détails de la mission')
@section('back', url()->previous() !== url()->current() ? url()->previous() : route('missions.index'))

@php
    use App\Support\Money;

    $user = auth()->user();
    $reward = $mission->rewardFor($user, $reseau);
    $steps = collect(preg_split('/\r\n|\r|\n/', (string) $mission->instructions))
        ->map(fn ($line) => trim(preg_replace('/^\s*(\d+[\.\)]|[-•*])\s*/u', '', $line)))
        ->filter()->values();
    $criteria = collect(preg_split('/\r\n|\r|\n/', (string) $mission->validation_criteria))
        ->map(fn ($line) => trim(preg_replace('/^\s*(\d+[\.\)]|[-•*])\s*/u', '', $line)))
        ->filter()->values();
    $remaining = $mission->remainingSlots();
    $closedReason = $mission->closedReason();
    $canEdit = $participation && in_array($participation->status, ['in_progress', 'submitted', 'under_review', 'rejected'], true);
@endphp

@section('content')
    {{-- En-tête de la mission --}}
    <article class="ab-card flex gap-3 p-3">
        <x-mission-visual :mission="$mission" class="h-28 w-28" />
        <div class="min-w-0 flex-1 py-0.5">
            <div class="flex items-start justify-between gap-2">
                <h2 class="truncate text-base font-extrabold uppercase">{{ $mission->brand_name }}</h2>
                <span class="ab-chip bg-amber-100 text-amber-800"><x-icon name="star" class="h-3 w-3 text-amber-500" /> {{ number_format($mission->rating, 1) }}</span>
            </div>
            <p class="mt-1 text-[13px] leading-snug text-slate-600 [overflow-wrap:anywhere]">{{ $mission->short_description ?: $mission->title }}</p>
            <p class="mt-2 text-lg font-extrabold text-cta-600">{{ Money::usd($reward) }}</p>
        </div>
    </article>

    {{-- Choix du réseau pour les missions multi-réseaux --}}
    @if (count($mission->selectedNetworks()) > 1 && ! $participation)
        <div class="mt-3">
            <p class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">Choisis ton réseau</p>
            <div class="grid grid-cols-2 gap-2">
                @foreach ($mission->selectedNetworks() as $n)
                    <a href="{{ route('missions.show', $mission->routeParams($n)) }}"
                       class="flex items-center justify-between rounded-2xl px-3 py-2.5 text-sm font-bold ring-1 transition {{ $n === $reseau ? 'bg-white text-slate-900 ring-2 ring-brand-500' : 'bg-white/60 text-slate-600 ring-slate-200 hover:bg-white' }}">
                        <span class="flex items-center gap-2"><x-network-icon :network="$n" /> {{ $mission->networkLabel($n) }}</span>
                        <span class="text-cta-600">{{ Money::usd($mission->rewardFor($user, $n)) }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- À propos --}}
    <section class="ab-card mt-3 p-4">
        <h3 class="font-extrabold">À propos de la mission</h3>
        @if ($mission->description)
            <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-600 [overflow-wrap:anywhere]">{{ $mission->description }}</p>
        @endif

        <dl class="mt-4 grid grid-cols-[auto_1fr] gap-x-4 gap-y-3 text-sm">
            @php
                $facts = array_filter([
                    ['share', 'Réseau', $mission->networkLabel($reseau), $reseau],
                    ['video', 'Type de contenu', $mission->contentTypeLabel(), null],
                    $mission->min_duration_seconds ? ['clock', 'Durée minimale', $mission->min_duration_seconds.' secondes', null] : null,
                    $mission->ends_at ? ['calendar', 'Date limite', $mission->ends_at->format('d/m/Y'), null] : null,
                    ['dollar', 'Rémunération', Money::usd($reward), 'reward'],
                    $remaining !== null ? ['users', 'Places restantes', $remaining.' / '.$mission->max_participants, null] : null,
                    $mission->content_retention_days ? ['shield', 'Conservation', $mission->content_retention_days.' jours en ligne', null] : null,
                ]);
            @endphp
            @foreach ($facts as [$icon, $label, $value, $extra])
                <dt class="flex items-center gap-2 text-slate-600"><x-icon :name="$icon" class="h-[18px] w-[18px] text-slate-700" /> {{ $label }}</dt>
                <dd class="flex items-center gap-1.5 font-semibold {{ $extra === 'reward' ? 'text-cta-600' : 'text-slate-800' }}">
                    @if ($extra && $extra !== 'reward')
                        <x-network-icon :network="$extra" />
                    @endif
                    {{ $value }}
                </dd>
            @endforeach
        </dl>

        @if ($mission->objective)
            <div class="mt-5 rounded-2xl bg-brand-50 p-3">
                <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-brand-700"><x-icon name="target" class="h-4 w-4" /> Objectif</p>
                <p class="mt-1 text-sm text-brand-900">{{ $mission->objective }}</p>
            </div>
        @endif

        @if ($steps->isNotEmpty())
            <h4 class="mt-5 font-extrabold">Étapes à suivre</h4>
            <ol class="mt-2 space-y-2">
                @foreach ($steps as $i => $step)
                    <li class="flex gap-3 text-sm text-slate-600">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">{{ $i + 1 }}</span>
                        <span class="pt-0.5 [overflow-wrap:anywhere]">{{ $step }}</span>
                    </li>
                @endforeach
            </ol>
        @endif

        @if ($criteria->isNotEmpty())
            <h4 class="mt-5 font-extrabold">Conditions à respecter</h4>
            <ul class="mt-2 space-y-1.5">
                @foreach ($criteria as $criterion)
                    <li class="flex gap-2 text-sm text-slate-600">
                        <x-icon name="check" class="mt-0.5 h-4 w-4 text-brand-600" stroke="2.6" />
                        <span class="[overflow-wrap:anywhere]">{{ $criterion }}</span>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($mission->contentExampleUrl())
            <h4 class="mt-5 font-extrabold">Exemple de contenu</h4>
            <div class="mt-2 overflow-hidden rounded-2xl ring-1 ring-slate-100">
                @if (preg_match('/\.(mp4|mov|webm)$/i', $mission->content_example_path ?? ''))
                    <video src="{{ $mission->contentExampleUrl() }}" controls playsinline class="max-h-72 w-full bg-black"></video>
                @else
                    <img src="{{ $mission->contentExampleUrl() }}" alt="Exemple de contenu" class="w-full object-cover" loading="lazy">
                @endif
            </div>
        @endif
    </section>

    {{-- Action --}}
    @guest
        <section class="ab-card mt-3 p-4 text-center">
            <p class="font-extrabold">Envie de participer ?</p>
            <p class="mt-1 text-sm text-slate-500">Connecte-toi ou crée ton compte créateur pour rejoindre cette mission.</p>
        </section>
    @else
        @if (! $participation)
            @if ($closedReason)
                <div class="mt-3 flex items-start gap-3 rounded-3xl bg-slate-100 p-4 text-sm text-slate-700">
                    <x-icon name="info" class="mt-0.5" /> <p>{{ $closedReason }}</p>
                </div>
            @elseif (! $user->isVerified())
                <div class="mt-3 rounded-3xl bg-amber-50 p-4 text-sm text-amber-900 ring-1 ring-amber-200">
                    <p class="flex items-center gap-2 font-bold"><x-icon name="clock" class="h-4 w-4" />
                        {{ $user->verification_status === 'rejected' ? 'Compte refusé' : 'Compte en cours de vérification' }}</p>
                    @if ($user->verification_status === 'rejected')
                        @if ($user->status_reason)
                            <p class="mt-1">Motif : {{ $user->status_reason }}</p>
                        @endif
                        <p class="mt-1">Corrige tes informations sur ton profil puis redemande une vérification.</p>
                    @else
                        <p class="mt-1">L'équipe AfriBoost vérifie ton compte. Tu pourras participer dès qu'il sera validé.</p>
                    @endif
                    <a href="{{ route('creator.profile') }}" class="ab-btn mt-3 w-full bg-amber-500 text-white hover:bg-amber-600">Voir mon profil</a>
                </div>
            @elseif (! $user->hasConnectedNetwork($reseau))
                <div class="mt-3 rounded-3xl bg-amber-50 p-4 text-sm text-amber-900 ring-1 ring-amber-200">
                    <p>Connecte d'abord ton profil <strong>{{ $mission->networkLabel($reseau) }}</strong> pour participer à cette mission.</p>
                    <a href="{{ route('creator.profile') }}#reseaux" class="ab-btn mt-3 w-full bg-amber-500 text-white hover:bg-amber-600">
                        <x-network-icon :network="$reseau" :colored="false" /> Connecter mon {{ $mission->networkLabel($reseau) }}
                    </a>
                </div>
            @else
                <form method="POST" action="{{ route('missions.participate', $mission->routeParams($reseau)) }}" class="mt-4">
                    @csrf
                    <button class="ab-btn-cta w-full py-4 text-base">Participer à la mission</button>
                    <p class="mt-2 text-center text-xs text-slate-500">Tu pourras ensuite publier ton contenu et soumettre son lien.</p>
                </form>
            @endif
        @elseif ($canEdit)
            <section class="ab-card mt-3 p-4" x-data="{ fileName: '' }">
                <div class="mb-3 flex items-center justify-between gap-2">
                    <h3 class="font-extrabold">Soumettre votre participation</h3>
                    <span class="ab-chip {{ $participation->statusColor() }}">{{ $participation->statusLabel() }}</span>
                </div>

                @if ($participation->status === 'rejected' && $participation->rejection_reason)
                    <div class="mb-3 rounded-2xl bg-cta-50 px-3 py-2.5 text-sm text-cta-700">
                        <p class="font-bold">Motif du refus</p>
                        <p class="mt-0.5">{{ $participation->rejection_reason }}</p>
                        <p class="mt-1 text-xs">Corrige ton contenu puis soumets à nouveau.</p>
                    </div>
                @elseif ($participation->isPendingReview())
                    <div class="mb-3 rounded-2xl bg-orange-50 px-3 py-2.5 text-sm text-orange-800">
                        Ta participation est en cours de vérification. Tu peux encore modifier le lien si besoin.
                    </div>
                @endif

                <form method="POST" action="{{ route('missions.submit', $mission->routeParams($reseau)) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="content_url" class="ab-label">Lien de votre {{ $mission->contentTypeLabel() === 'Vidéo' ? 'vidéo' : 'publication' }} {{ $mission->networkLabel($reseau) }}</label>
                        <div class="relative">
                            <input id="content_url" type="url" name="content_url" value="{{ old('content_url', $participation->content_url) }}" required
                                   placeholder="Collez ici le lien de votre publication" inputmode="url"
                                   class="ab-input pr-11">
                            <x-icon name="link" class="pointer-events-none absolute right-4 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-slate-400" />
                        </div>
                    </div>

                    <div>
                        <span class="ab-label">Capture d'écran <span class="font-normal text-slate-400">(optionnel)</span></span>
                        <label class="flex cursor-pointer items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-sm text-slate-600 transition hover:border-brand-400 hover:bg-brand-50/50">
                            <x-icon name="upload" class="h-6 w-6 text-slate-700" />
                            <span x-text="fileName || 'Ajouter une capture d’écran (statistiques, vues…)'">Ajouter une capture d'écran (statistiques, vues…)</span>
                            <input type="file" name="screenshot" accept="image/*" class="sr-only" @change="fileName = $event.target.files[0]?.name || ''">
                        </label>
                        @if ($participation->screenshotUrl())
                            <p class="mt-1.5 text-xs text-slate-500">Une capture a déjà été envoyée. En ajouter une nouvelle la remplacera.</p>
                        @endif
                    </div>

                    <button class="ab-btn-cta w-full py-4 text-base">
                        {{ $participation->status === 'in_progress' ? 'Soumettre ma participation' : 'Mettre à jour ma participation' }}
                    </button>
                </form>
            </section>
        @else
            <div class="mt-3 flex items-center gap-3 rounded-3xl bg-brand-50 p-4 text-sm text-brand-900 ring-1 ring-brand-200">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-600 text-white"><x-icon name="check" stroke="2.6" /></span>
                <div>
                    <p class="font-bold">Participation {{ mb_strtolower($participation->statusLabel()) }}</p>
                    <p>{{ Money::usd($participation->rewardAmount()) }} crédités sur ton wallet.</p>
                </div>
            </div>
        @endif
    @endguest
@endsection
