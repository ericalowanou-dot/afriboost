@extends('layouts.admin')

@section('title', 'Modifier la campagne')
@section('heading', 'Modifier la campagne')

@section('content')
    <a href="{{ route('admin.campaigns.show', $campaign) }}" class="mb-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-700"><x-icon name="arrow-left" class="h-4 w-4" /> {{ $campaign->title }}</a>

    <form method="POST" action="{{ route('admin.campaigns.update', $campaign) }}" class="ab-panel max-w-3xl p-6">
        @method('PUT')
        @include('admin.campaigns.form')
        <div class="mt-6 flex justify-end gap-2">
            <a href="{{ route('admin.campaigns.show', $campaign) }}" class="ab-btn-ghost">Annuler</a>
            <button class="ab-btn-brand">Enregistrer</button>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.campaigns.destroy', $campaign) }}" class="mt-4 max-w-3xl"
          onsubmit="return confirm('Supprimer cette campagne ? Les missions seront conservées sans campagne.')">
        @csrf
        @method('DELETE')
        <button class="text-sm font-semibold text-cta-600 hover:underline">Supprimer la campagne</button>
    </form>
@endsection
