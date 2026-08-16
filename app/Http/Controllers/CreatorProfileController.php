<?php

namespace App\Http\Controllers;

use App\Models\SocialNetwork;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreatorProfileController extends Controller
{
    public function show(): View
    {
        $user = auth()->user()->load('socialNetworks');

        return view('creator.profile.show', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user->update($data);

        return back()->with('success', 'Profil mis à jour.');
    }

    public function storeNetwork(Request $request)
    {
        $data = $request->validate([
            'platform' => ['required', 'in:facebook,tiktok,instagram,youtube'],
            'handle' => ['nullable', 'string', 'max:100'],
            'public_name' => ['nullable', 'string', 'max:100'],
            'profile_url' => ['nullable', 'url', 'max:500'],
        ]);

        SocialNetwork::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'platform' => $data['platform'],
            ],
            [
                'handle' => $data['handle'] ?? null,
                'public_name' => $data['public_name'] ?? null,
                'profile_url' => $data['profile_url'] ?? null,
                'status' => 'active',
            ]
        );

        return back()->with('success', 'Réseau social enregistré.');
    }

    public function destroyNetwork(SocialNetwork $network)
    {
        abort_unless($network->user_id === auth()->id(), 403);
        $network->delete();

        return back()->with('success', 'Réseau social supprimé.');
    }
}
