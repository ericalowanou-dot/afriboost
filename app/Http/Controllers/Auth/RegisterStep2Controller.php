<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialNetwork;
use App\Support\SocialProfileUrlParser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegisterStep2Controller extends Controller
{
    public function create(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->hasCompletedRegistration()) {
            return redirect()->route('missions.index');
        }

        return view('auth.register-step2');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user->hasCompletedRegistration()) {
            return redirect()->route('missions.index');
        }

        $data = $request->validate([
            'profile_url' => ['required', 'url', 'max:500'],
        ], [
            'profile_url.required' => 'Collez le lien de votre profil social.',
            'profile_url.url' => 'Le lien du profil doit être une URL valide.',
        ]);

        $platform = SocialProfileUrlParser::detectPlatform($data['profile_url']);

        if (! $platform) {
            return back()
                ->withInput()
                ->withErrors(['profile_url' => 'Lien non reconnu. Utilisez un profil TikTok, Instagram, Facebook ou YouTube.']);
        }

        $handle = SocialProfileUrlParser::extractHandle($data['profile_url'], $platform);

        SocialNetwork::updateOrCreate(
            [
                'user_id' => $user->id,
                'platform' => $platform,
            ],
            [
                'handle' => $handle,
                'profile_url' => $data['profile_url'],
                'status' => 'active',
            ]
        );

        $user->update(['registration_step' => 2]);

        return redirect()
            ->route('missions.index')
            ->with('success', 'Compte créé ! Votre profil sera vérifié par l\'équipe AfriBoost.');
    }
}
