@extends('layouts.admin')

@section('title', isset($mission) ? 'Modifier mission' : 'Nouvelle mission')
@section('heading', isset($mission) ? 'Modifier la mission' : 'Nouvelle mission')

@section('content')
    @php $m = $mission ?? null; @endphp
    <form method="POST"
          action="{{ $m ? route('admin.missions.update', $m) : route('admin.missions.store') }}"
          class="max-w-3xl space-y-4 rounded-2xl bg-white p-6 shadow-sm">
        @csrf
        @if ($m) @method('PUT') @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold">Marque / client</label>
                <input name="brand_name" value="{{ old('brand_name', $m->brand_name ?? '') }}" required class="w-full rounded-xl border-slate-200">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold">Campagne</label>
                <select name="campaign_id" class="w-full rounded-xl border-slate-200">
                    <option value="">—</option>
                    @foreach ($campaigns as $campaign)
                        <option value="{{ $campaign->id }}" @selected(old('campaign_id', $m->campaign_id ?? '') == $campaign->id)>{{ $campaign->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">Titre</label>
            <input name="title" value="{{ old('title', $m->title ?? '') }}" required class="w-full rounded-xl border-slate-200">
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">Description courte</label>
            <input name="short_description" value="{{ old('short_description', $m->short_description ?? '') }}" class="w-full rounded-xl border-slate-200">
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">Description</label>
            <textarea name="description" rows="3" class="w-full rounded-xl border-slate-200">{{ old('description', $m->description ?? '') }}</textarea>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">Consignes</label>
            <textarea name="instructions" rows="4" class="w-full rounded-xl border-slate-200">{{ old('instructions', $m->instructions ?? '') }}</textarea>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-semibold">Réseau</label>
                <select name="social_network" class="w-full rounded-xl border-slate-200">
                    @foreach (['tiktok','instagram','facebook','youtube'] as $n)
                        <option value="{{ $n }}" @selected(old('social_network', $m->social_network ?? 'tiktok') === $n)>{{ ucfirst($n) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold">Type de contenu</label>
                <select name="content_type" class="w-full rounded-xl border-slate-200">
                    @foreach (['video','post','story'] as $t)
                        <option value="{{ $t }}" @selected(old('content_type', $m->content_type ?? 'video') === $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold">Récompense USD</label>
                <input type="number" step="0.01" name="reward_usd" value="{{ old('reward_usd', $m->reward_usd ?? '10') }}" required class="w-full rounded-xl border-slate-200">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-semibold">Durée min. (s)</label>
                <input type="number" name="min_duration_seconds" value="{{ old('min_duration_seconds', $m->min_duration_seconds ?? '') }}" class="w-full rounded-xl border-slate-200">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold">Début</label>
                <input type="date" name="starts_at" value="{{ old('starts_at', optional($m->starts_at ?? null)->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-200">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold">Fin</label>
                <input type="date" name="ends_at" value="{{ old('ends_at', optional($m->ends_at ?? null)->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-200">
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold">Statut</label>
            <select name="status" class="w-full rounded-xl border-slate-200">
                @foreach (['draft'=>'Brouillon','published'=>'Publiée','closed'=>'Fermée'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $m->status ?? 'draft') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-3">
            <button class="rounded-xl bg-rose-600 px-5 py-2.5 font-bold text-white">Enregistrer</button>
            <a href="{{ route('admin.missions.index') }}" class="rounded-xl bg-slate-100 px-5 py-2.5 font-semibold text-slate-600">Annuler</a>
        </div>
    </form>
@endsection
