@extends('layouts.admin')

@section('title', 'Vérifications')
@section('heading', 'File de vérification')

@section('content')
    @php
        $tabs = [
            'queue' => 'À vérifier',
            'validated' => 'Validées',
            'rejected' => 'Refusées',
            'all' => 'Toutes',
        ];
    @endphp
    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('admin.participations.index', ['status' => $key]) }}"
               class="rounded-full px-4 py-2 text-sm font-semibold {{ $status === $key ? 'bg-rose-600 text-white' : 'bg-white text-slate-600 shadow-sm' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3">Créateur</th>
                    <th class="px-4 py-3">Mission</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3">Soumise</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($participations as $participation)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium">{{ $participation->user->name }}</td>
                        <td class="px-4 py-3">{{ $participation->mission->brand_name }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-xs font-semibold {{ $participation->statusColor() }}">{{ $participation->statusLabel() }}</span></td>
                        <td class="px-4 py-3">{{ optional($participation->submitted_at)->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('admin.participations.show', $participation) }}" class="font-semibold text-teal-700">Ouvrir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-slate-500">Aucune participation.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $participations->links() }}</div>
@endsection
