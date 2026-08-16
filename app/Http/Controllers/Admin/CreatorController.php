<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreatorController extends Controller
{
    public function index(): View
    {
        $creators = User::where('role', 'creator')
            ->with('wallet')
            ->withCount('participations')
            ->latest()
            ->paginate(20);

        return view('admin.creators.index', compact('creators'));
    }

    public function updateStatus(Request $request, User $creator)
    {
        abort_unless($creator->isCreator(), 404);

        $data = $request->validate([
            'status' => ['required', 'in:active,suspended,blocked'],
            'status_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $creator->update($data);

        return back()->with('success', 'Statut du créateur mis à jour.');
    }
}
