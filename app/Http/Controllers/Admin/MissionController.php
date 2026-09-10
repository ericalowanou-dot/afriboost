<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Mission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MissionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'network' => $request->string('network')->toString() ?: 'all',
            'status' => $request->string('status')->toString() ?: 'all',
            'campaign_id' => $request->input('campaign_id'),
            'search' => $request->string('search')->toString(),
            'sort' => $request->string('sort')->toString() ?: 'latest',
        ];

        $missions = Mission::query()
            ->with('campaign')
            ->when($filters['network'] !== 'all', fn ($q) => $q->network($filters['network']))
            ->when($filters['status'] !== 'all', fn ($q) => $q->where('status', $filters['status']))
            ->when($filters['campaign_id'], fn ($q) => $q->where('campaign_id', $filters['campaign_id']))
            ->when($filters['search'], function ($q) use ($filters) {
                $search = '%'.$filters['search'].'%';
                $q->where(function ($inner) use ($search) {
                    $inner->where('brand_name', 'like', $search)
                        ->orWhere('title', 'like', $search)
                        ->orWhere('description', 'like', $search);
                });
            })
            ->when($filters['sort'] === 'reward_desc', fn ($q) => $q->orderByDesc('reward_usd'))
            ->when($filters['sort'] === 'reward_asc', fn ($q) => $q->orderBy('reward_usd'))
            ->when($filters['sort'] === 'ends_soon', fn ($q) => $q->orderBy('ends_at'))
            ->when($filters['sort'] === 'latest', fn ($q) => $q->latest())
            ->paginate(20)
            ->withQueryString();

        $campaigns = Campaign::orderBy('title')->get();

        return view('admin.missions.index', compact('missions', 'filters', 'campaigns'));
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

        if ($request->hasFile('logo')) {
            $data['image_path'] = $request->file('logo')->store('missions/logos', 'public');
        }

        if ($request->hasFile('content_example')) {
            $data['content_example_path'] = $request->file('content_example')->store('missions/examples', 'public');
        }

        unset($data['logo'], $data['content_example']);

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
        $data = $this->validated($request);

        if ($request->hasFile('logo')) {
            if ($mission->image_path) {
                Storage::disk('public')->delete($mission->image_path);
            }
            $data['image_path'] = $request->file('logo')->store('missions/logos', 'public');
        }

        if ($request->hasFile('content_example')) {
            if ($mission->content_example_path) {
                Storage::disk('public')->delete($mission->content_example_path);
            }
            $data['content_example_path'] = $request->file('content_example')->store('missions/examples', 'public');
        }

        if ($request->boolean('remove_content_example') && $mission->content_example_path) {
            Storage::disk('public')->delete($mission->content_example_path);
            $data['content_example_path'] = null;
        }

        unset($data['logo'], $data['content_example'], $data['remove_content_example']);

        $mission->update($data);

        return redirect()->route('admin.missions.index')->with('success', 'Mission mise à jour.');
    }

    public function destroy(Mission $mission)
    {
        if ($mission->image_path) {
            Storage::disk('public')->delete($mission->image_path);
        }
        if ($mission->content_example_path) {
            Storage::disk('public')->delete($mission->content_example_path);
        }

        $mission->delete();

        return redirect()->route('admin.missions.index')->with('success', 'Mission supprimée.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'brand_name' => ['required', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:180'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'objective' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'validation_criteria' => ['nullable', 'string'],
            'social_networks' => ['required', 'array', 'min:1'],
            'social_networks.*' => ['in:facebook,tiktok,instagram,youtube'],
            'content_type' => ['required', 'in:video,post,story'],
            'min_duration_seconds' => ['nullable', 'integer', 'min:1'],
            'network_budgets' => ['required', 'array'],
            'network_budgets.*.basic' => ['required', 'numeric', 'min:0'],
            'network_budgets.*.medium' => ['nullable', 'numeric', 'min:0'],
            'network_budgets.*.top' => ['nullable', 'numeric', 'min:0'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'content_example' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,mp4,mov,webm', 'max:20480'],
            'remove_content_example' => ['nullable', 'boolean'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'content_retention_days' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:draft,published,closed'],
        ]);

        $networks = array_values(array_unique($data['social_networks']));
        $data['social_networks'] = $networks;
        $data['social_network'] = $networks[0];

        $budgets = [];
        foreach ($networks as $network) {
            $budgets[$network] = [
                'basic' => (float) $data['network_budgets'][$network]['basic'],
                'medium' => isset($data['network_budgets'][$network]['medium']) && $data['network_budgets'][$network]['medium'] !== ''
                    ? (float) $data['network_budgets'][$network]['medium']
                    : (float) $data['network_budgets'][$network]['basic'],
                'top' => isset($data['network_budgets'][$network]['top']) && $data['network_budgets'][$network]['top'] !== ''
                    ? (float) $data['network_budgets'][$network]['top']
                    : (float) $data['network_budgets'][$network]['basic'],
            ];
        }
        $data['network_budgets'] = $budgets;

        $primary = $networks[0];
        $data['reward_usd'] = $budgets[$primary]['basic'];
        $data['reward_usd_medium'] = $budgets[$primary]['medium'];
        $data['reward_usd_top'] = $budgets[$primary]['top'];

        return $data;
    }
}
