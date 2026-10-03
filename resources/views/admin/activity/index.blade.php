@extends('layouts.admin')

@section('title', 'Historique des actions')
@section('heading', 'Historique des actions')

@section('content')
    <div class="mb-4 inline-flex flex-wrap gap-1 rounded-2xl bg-white p-1 shadow-sm ring-1 ring-slate-900/5">
        <a href="{{ route('admin.activity.index') }}" class="rounded-xl px-3 py-1.5 text-sm font-semibold {{ $family === '' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Tout</a>
        @foreach ($families as $key => $label)
            <a href="{{ route('admin.activity.index', ['type' => $key]) }}"
               class="rounded-xl px-3 py-1.5 text-sm font-semibold {{ $family === $key ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="ab-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-100 bg-slate-50">
                    <tr>
                        <th class="ab-th">Date</th>
                        <th class="ab-th">Action</th>
                        <th class="ab-th">Détail</th>
                        <th class="ab-th">Auteur</th>
                        <th class="ab-th">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr class="align-top hover:bg-slate-50/60">
                            <td class="ab-td whitespace-nowrap text-slate-500">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td class="ab-td"><span class="ab-chip font-mono {{ $log->tone() }}">{{ $log->action }}</span></td>
                            <td class="ab-td">
                                <p class="text-slate-700">{{ $log->description }}</p>
                                @if (! empty($log->properties['reason']))
                                    <p class="mt-0.5 text-xs text-slate-500">Motif : {{ $log->properties['reason'] }}</p>
                                @endif
                            </td>
                            <td class="ab-td whitespace-nowrap">{{ $log->user?->name ?? 'Système' }}</td>
                            <td class="ab-td font-mono text-xs text-slate-400">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">Aucune action enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
