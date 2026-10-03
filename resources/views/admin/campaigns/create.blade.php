@extends('layouts.admin')

@section('title', 'Nouvelle campagne')
@section('heading', 'Nouvelle campagne')

@section('content')
    <a href="{{ route('admin.campaigns.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-700"><x-icon name="arrow-left" class="h-4 w-4" /> Campagnes</a>

    @if ($source)
        <div class="mb-4 flex items-start gap-3 rounded-2xl bg-sky-50 p-4 text-sm text-sky-900 ring-1 ring-sky-200">
            <x-icon name="inbox" class="mt-0.5" />
            <p>Pré-rempli depuis la demande de <strong>{{ $source->company_name }}</strong> ({{ $source->contact_name }}, {{ $source->email }}). La demande sera marquée comme convertie.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.campaigns.store') }}" class="ab-panel max-w-3xl p-6">
        @if ($source)
            <input type="hidden" name="campaign_request_id" value="{{ $source->id }}">
        @endif
        @include('admin.campaigns.form')
        <div class="mt-6 flex justify-end gap-2">
            <a href="{{ route('admin.campaigns.index') }}" class="ab-btn-ghost">Annuler</a>
            <button class="ab-btn-brand">Créer la campagne</button>
        </div>
    </form>
@endsection
