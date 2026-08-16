@extends('layouts.mobile')

@section('title', $mission->brand_name.' — AfriBoost')
@section('heading', 'Détails de la mission')

@section('content')
    <a href="{{ route('missions.network', ['reseau' => $mission->social_network]) }}" class="mb-4 inline-flex text-sm font-semibold text-teal-700">← Retour</a>

    <article class="rounded-3xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
        <div class="flex gap-3">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 text-xs font-bold uppercase text-white">
                {{ $mission->brand_name }}
            </div>
            <div>
                <h2 class="text-xl font-extrabold">{{ $mission->brand_name }}</h2>
                <p class="text-sm text-amber-500">★ {{ number_format($mission->rating, 1) }}</p>
                <p class="mt-1 text-lg font-extrabold text-rose-600">{{ number_format($mission->reward_usd, 2) }} USD</p>
            </div>
        </div>

        <h3 class="mt-5 text-sm font-bold uppercase tracking-wide text-slate-400">À propos</h3>
        <p class="mt-1 break-words text-sm leading-relaxed text-slate-700 [overflow-wrap:anywhere] whitespace-pre-wrap">{{ $mission->description }}</p>

        <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
            <div class="rounded-2xl bg-slate-50 p-3"><span class="block text-xs text-slate-400">Réseau</span><strong>{{ $mission->networkLabel() }}</strong></div>
            <div class="rounded-2xl bg-slate-50 p-3"><span class="block text-xs text-slate-400">Type</span><strong>{{ ucfirst($mission->content_type) }}</strong></div>
            <div class="rounded-2xl bg-slate-50 p-3"><span class="block text-xs text-slate-400">Durée min.</span><strong>{{ $mission->min_duration_seconds ? $mission->min_duration_seconds.' s' : '—' }}</strong></div>
            <div class="rounded-2xl bg-slate-50 p-3"><span class="block text-xs text-slate-400">Date limite</span><strong>{{ optional($mission->ends_at)->format('d/m/Y') ?? '—' }}</strong></div>
        </div>

        @if ($mission->instructions)
            <h3 class="mt-5 text-sm font-bold uppercase tracking-wide text-slate-400">Étapes à suivre</h3>
            <pre class="mt-2 whitespace-pre-wrap break-words font-afriboost text-sm leading-relaxed text-slate-700 [overflow-wrap:anywhere]">{{ $mission->instructions }}</pre>
        @endif
    </article>

    @if (! $participation)
        <form method="POST" action="{{ route('missions.participate', $mission->routeParams()) }}" class="mt-4">
            @csrf
            <button class="w-full rounded-2xl bg-rose-600 py-4 text-center text-base font-extrabold text-white shadow-lg shadow-rose-200">Participer</button>
        </form>
    @elseif (in_array($participation->status, ['in_progress', 'submitted', 'under_review', 'rejected'], true))
        <section class="mt-4 rounded-3xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-bold">Soumettre votre participation</h3>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $participation->statusColor() }}">{{ $participation->statusLabel() }}</span>
            </div>

            @if ($participation->status === 'rejected' && $participation->rejection_reason)
                <div class="mb-3 rounded-2xl bg-red-50 px-3 py-2 text-sm text-red-700">
                    Motif du refus : {{ $participation->rejection_reason }}
                </div>
            @endif

            <form method="POST" action="{{ route('missions.submit', $mission->routeParams()) }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-semibold">Lien de votre contenu</label>
                    <input type="url" name="content_url" value="{{ old('content_url', $participation->content_url) }}" required
                           placeholder="Collez ici le lien de votre publication"
                           class="w-full rounded-2xl border-slate-200 px-4 py-3 text-sm focus:border-rose-400 focus:ring-rose-400">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold">Capture d'écran (optionnel)</label>
                    <input type="file" name="screenshot" accept="image/*" class="block w-full text-sm">
                </div>
                <button class="w-full rounded-2xl bg-rose-600 py-4 text-base font-extrabold text-white">Soumettre ma participation</button>
            </form>
        </section>
    @else
        <div class="mt-4 rounded-3xl bg-emerald-50 p-4 text-sm text-emerald-800">
            Participation {{ $participation->statusLabel() }}. Récompense : {{ number_format($mission->reward_usd, 2) }} USD.
        </div>
    @endif
@endsection
