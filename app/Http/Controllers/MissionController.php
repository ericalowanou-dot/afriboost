<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use App\Models\Participation;
use App\Support\SocialProfileUrlParser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MissionController extends Controller
{
    public function index(Request $request, ?string $reseau = null): View
    {
        $network = $reseau ?: ($request->string('network')->toString() ?: 'all');

        if ($network !== 'all' && ! in_array($network, ['facebook', 'tiktok', 'instagram', 'youtube'], true)) {
            abort(404);
        }

        $missions = Mission::query()
            ->published()
            ->active()
            ->network($network === 'all' ? null : $network)
            ->withCount(['participations as taken_slots_count' => fn ($q) => $q->where('status', '!=', Participation::STATUS_REJECTED)])
            ->latest()
            ->get();

        $user = auth()->user()?->loadMissing('socialNetworks');
        $joinedMissionIds = $user
            ? $user->participations()->pluck('status', 'mission_id')
            : collect();

        return view('creator.missions.index', compact('missions', 'network', 'joinedMissionIds'));
    }

    public function show(string $reseau, Mission $missionSlug): View|RedirectResponse
    {
        $mission = $missionSlug;

        abort_unless($mission->status === 'published', 404);

        if (! $mission->supportsNetwork($reseau)) {
            return redirect()->route('missions.show', $mission->routeParams(), 301);
        }

        // Canonical : si le slug titre a changé, rediriger
        $currentSlug = request()->segment(3);
        if ($mission->urlSlug() !== $currentSlug) {
            return redirect()->route('missions.show', $mission->routeParams(), 301);
        }

        $participation = auth()->check()
            ? auth()->user()->participations()->where('mission_id', $mission->id)->first()
            : null;

        // Une participation existante reste sur son réseau d'origine
        if ($participation?->network && $participation->network !== $reseau && $mission->supportsNetwork($participation->network)) {
            return redirect()->route('missions.show', $mission->routeParams($participation->network));
        }

        return view('creator.missions.show', compact('mission', 'participation', 'reseau'));
    }

    public function participate(string $reseau, Mission $missionSlug)
    {
        $mission = $missionSlug;

        abort_unless($mission->status === 'published', 404);
        abort_unless($mission->supportsNetwork($reseau), 404);

        $user = auth()->user();

        if (! $user->isVerified()) {
            return redirect()
                ->route('missions.show', $mission->routeParams($reseau))
                ->with('warning', $user->verification_status === 'rejected'
                    ? 'Votre compte a été refusé. Corrigez vos informations sur votre profil puis redemandez une vérification pour pouvoir participer aux missions.'
                    : 'Votre compte est en cours de vérification par l\'équipe AfriBoost. Vous pourrez participer aux missions une fois votre compte vérifié.');
        }

        if (! $user->hasConnectedNetwork($reseau)) {
            return redirect()
                ->route('missions.show', $mission->routeParams($reseau))
                ->with('warning', 'Connectez d\'abord votre profil '.$mission->networkLabel($reseau).' pour participer à cette mission.');
        }

        $existing = Participation::where('user_id', $user->id)->where('mission_id', $mission->id)->first();

        if (! $existing && ($reason = $mission->closedReason())) {
            return redirect()
                ->route('missions.show', $mission->routeParams($reseau))
                ->with('warning', $reason);
        }

        $participation = $existing ?? Participation::create([
            'user_id' => $user->id,
            'mission_id' => $mission->id,
            'network' => $reseau,
            'status' => Participation::STATUS_IN_PROGRESS,
        ]);

        return redirect()
            ->route('missions.show', $mission->routeParams($reseau))
            ->with('success', $participation->wasRecentlyCreated
                ? 'Mission démarrée. Suivez les consignes puis soumettez votre lien.'
                : 'Vous participez déjà à cette mission.');
    }

    public function submit(Request $request, string $reseau, Mission $missionSlug)
    {
        $mission = $missionSlug;

        abort_unless($mission->status === 'published', 404);
        abort_unless($mission->supportsNetwork($reseau), 404);

        if (! auth()->user()->isVerified()) {
            return redirect()
                ->route('missions.show', $mission->routeParams($reseau))
                ->with('warning', 'Votre compte doit être vérifié par l\'équipe AfriBoost pour soumettre une participation.');
        }

        $data = $request->validate([
            'content_url' => ['required', 'url', 'max:500'],
            'screenshot' => ['nullable', 'image', 'max:4096'],
        ]);

        $participation = Participation::where('user_id', auth()->id())
            ->where('mission_id', $mission->id)
            ->firstOrFail();

        $expectedNetwork = $participation->network ?: $reseau;
        $linkPlatform = SocialProfileUrlParser::detectPlatform($data['content_url']);
        if ($linkPlatform && $linkPlatform !== $expectedNetwork) {
            return back()->withInput()->withErrors([
                'content_url' => 'Ce lien ne correspond pas à une publication '.$mission->networkLabel($expectedNetwork).'.',
            ]);
        }

        if (in_array($participation->status, [
            Participation::STATUS_VALIDATED,
            Participation::STATUS_PAID,
        ], true)) {
            return back()->withErrors(['content_url' => 'Cette participation est déjà validée.']);
        }

        if ($mission->hasEnded() && $participation->status === Participation::STATUS_IN_PROGRESS) {
            return back()->withErrors(['content_url' => 'La date limite de cette mission est dépassée.']);
        }

        $screenshotPath = $participation->screenshot_path;
        if ($request->hasFile('screenshot')) {
            $screenshotPath = $request->file('screenshot')->store('screenshots', 'public');
        }

        $participation->update([
            'content_url' => $data['content_url'],
            'screenshot_path' => $screenshotPath,
            'network' => $participation->network ?: $reseau,
            'status' => Participation::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'rejection_reason' => null,
        ]);

        return redirect()
            ->route('participations.index', ['status' => 'submitted'])
            ->with('success', 'Participation soumise. L\'équipe AfriBoost va la vérifier.');
    }
}
