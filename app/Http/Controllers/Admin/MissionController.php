<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Mission;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MissionController extends Controller
{
    public function index(): View
    {
        $missions = Mission::with('campaign')->latest()->paginate(20);

        return view('admin.missions.index', compact('missions'));
    }

    public function create(): View
    {
        $campaigns = Campaign::orderBy('title')->get();

        return view('admin.missions.create', compact('campaigns'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['status'] = $request->input('status', 'draft');

        Mission::create($data);

        return redirect()->route('admin.missions.index')->with('success', 'Mission créée.');
    }

    public function edit(Mission $mission): View
    {
        $campaigns = Campaign::orderBy('title')->get();

        return view('admin.missions.edit', compact('mission', 'campaigns'));
    }

    public function update(Request $request, Mission $mission)
    {
        $mission->update($this->validated($request));

        return redirect()->route('admin.missions.index')->with('success', 'Mission mise à jour.');
    }

    public function destroy(Mission $mission)
    {
        $mission->delete();

        return redirect()->route('admin.missions.index')->with('success', 'Mission supprimée.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'brand_name' => ['required', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:180'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'objective' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'validation_criteria' => ['nullable', 'string'],
            'social_network' => ['required', 'in:facebook,tiktok,instagram,youtube'],
            'content_type' => ['required', 'in:video,post,story'],
            'min_duration_seconds' => ['nullable', 'integer', 'min:1'],
            'reward_usd' => ['required', 'numeric', 'min:0'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', 'in:draft,published,closed'],
        ]);
    }
}
