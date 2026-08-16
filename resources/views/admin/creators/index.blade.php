@extends('layouts.admin')

@section('title', 'Créateurs')
@section('heading', 'Créateurs')

@section('content')
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3">Nom</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Participations</th>
                    <th class="px-4 py-3">Solde</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($creators as $creator)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium">{{ $creator->name }}</td>
                        <td class="px-4 py-3">{{ $creator->email }}</td>
                        <td class="px-4 py-3">{{ $creator->participations_count }}</td>
                        <td class="px-4 py-3">{{ number_format(optional($creator->wallet)->balance_usd ?? 0, 2) }} USD</td>
                        <td class="px-4 py-3">{{ $creator->status }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.creators.status', $creator) }}" class="flex flex-wrap gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="rounded-lg border-slate-200 text-sm">
                                    @foreach (['active','suspended','blocked'] as $status)
                                        <option value="{{ $status }}" @selected($creator->status === $status)>{{ $status }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="status_reason" placeholder="Motif" class="rounded-lg border-slate-200 text-sm">
                                <button class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-bold text-white">OK</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $creators->links() }}</div>
@endsection
