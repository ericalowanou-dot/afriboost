<?php

namespace App\Http\Controllers;

use App\Models\Participation;
use App\Models\SocialNetwork;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreatorProfileController extends Controller
{
    public function show(): View
    {
        $user = auth()->user()->load(['socialNetworks', 'wallet']);

        $stats = [
            'participations' => $user->participations()->count(),
            'validated' => $user->participations()
                ->whereIn('status', [Participation::STATUS_VALIDATED, Participation::STATUS_PAID])
                ->count(),
            'earned' => $user->wallet?->totalEarned() ?? 0,
        ];

        return view('creator.profile.show', compact('user', 'stats'));
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

    /**
     * Permet à un créateur dont le compte a été refusé de redemander
     * une vérification après avoir corrigé ses informations.
     */
    public function requestVerification()
    {
        $user = auth()->user();

        abort_unless($user->verification_status === User::VERIFICATION_REJECTED, 403);

        $user->update([
            'verification_status' => User::VERIFICATION_PENDING,
            'status_reason' => null,
            'verified_at' => null,
            'verified_by' => null,
        ]);

        return back()->with('success', 'Votre demande de vérification a été envoyée. L\'équipe AfriBoost va réexaminer votre compte.');
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
