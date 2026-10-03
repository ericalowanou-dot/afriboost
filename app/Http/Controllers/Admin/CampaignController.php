<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignRequest;
use App\Models\Participation;
use App\Models\Transaction;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: 'all';

        $campaigns = Campaign::query()
            ->withCount('missions')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $spent = Transaction::query()
            ->join('missions', 'missions.id', '=', 'transactions.mission_id')
            ->whereIn('missions.campaign_id', $campaigns->pluck('id'))
            ->where('transactions.type', Transaction::TYPE_REWARD)
            ->where('transactions.status', Transaction::STATUS_COMPLETED)
            ->groupBy('missions.campaign_id')
            ->selectRaw('missions.campaign_id, sum(transactions.amount_usd) as total')
            ->pluck('total', 'campaign_id');

        return view('admin.campaigns.index', compact('campaigns', 'status', 'spent'));
    }

    public function create(Request $request): View
    {
        // Pré-remplissage depuis une demande de marque
        $source = $request->integer('demande')
            ? CampaignRequest::find($request->integer('demande'))
            : null;

        $campaign = new Campaign([
            'status' => 'draft',
            'client_name' => $source?->company_name,
            'objective' => $source?->objective,
            'budget_usd' => $source?->budget_usd,
            'starts_at' => $source?->desired_start,
        ]);

        return view('admin.campaigns.create', compact('campaign', 'source'));
    }

    public function store(Request $request)
    {
        $campaign = Campaign::create($this->validated($request));

        ActivityLogger::log('campaign.created', 'Campagne « '.$campaign->title.' » créée.', $campaign);

        if ($source = CampaignRequest::find($request->integer('campaign_request_id'))) {
            $source->update(['status' => 'converted', 'campaign_id' => $campaign->id]);
        }

        return redirect()->route('admin.campaigns.show', $campaign)->with('success', 'Campagne créée.');
    }

    /** Statistiques d'une campagne, de sa création à sa validation. */
    public function show(Campaign $campaign): View
    {
        $campaign->load(['missions' => fn ($q) => $q->withCount([
            'participations',
            'participations as validated_count' => fn ($p) => $p->whereIn('status', [Participation::STATUS_VALIDATED, Participation::STATUS_PAID]),
            'participations as pending_count' => fn ($p) => $p->whereIn('status', [Participation::STATUS_SUBMITTED, Participation::STATUS_UNDER_REVIEW]),
        ])]);

        $missionIds = $campaign->missions->pluck('id');

        $byStatus = Participation::whereIn('mission_id', $missionIds)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $spent = (float) Transaction::whereIn('mission_id', $missionIds)
            ->where('type', Transaction::TYPE_REWARD)
            ->where('status', Transaction::STATUS_COMPLETED)
            ->sum('amount_usd');

        $stats = [
            'missions' => $campaign->missions->count(),
            'participants' => $byStatus->sum(),
            'creators' => Participation::whereIn('mission_id', $missionIds)->distinct('user_id')->count('user_id'),
            'pending' => $byStatus->only([Participation::STATUS_SUBMITTED, Participation::STATUS_UNDER_REVIEW])->sum(),
            'validated' => $byStatus->only([Participation::STATUS_VALIDATED, Participation::STATUS_PAID])->sum(),
            'rejected' => (int) ($byStatus[Participation::STATUS_REJECTED] ?? 0),
            'spent' => $spent,
            'budget_used' => $campaign->budget_usd > 0 ? min(100, round($spent / (float) $campaign->budget_usd * 100)) : null,
        ];

        return view('admin.campaigns.show', compact('campaign', 'stats'));
    }

    public function edit(Campaign $campaign): View
    {
        return view('admin.campaigns.edit', compact('campaign'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        $campaign->update($this->validated($request));

        ActivityLogger::log('campaign.updated', 'Campagne « '.$campaign->title.' » modifiée.', $campaign, [
            'changes' => array_keys($campaign->getChanges()),
        ]);

        return redirect()->route('admin.campaigns.show', $campaign)->with('success', 'Campagne mise à jour.');
    }

    public function destroy(Campaign $campaign)
    {
        ActivityLogger::log('campaign.deleted', 'Campagne « '.$campaign->title.' » supprimée.', null, [
            'campaign_id' => $campaign->id,
        ]);

        // Les missions sont conservées (campaign_id passe à null)
        $campaign->delete();

        return redirect()->route('admin.campaigns.index')->with('success', 'Campagne supprimée.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'client_name' => ['required', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:180'],
            'objective' => ['nullable', 'string', 'max:2000'],
            'budget_usd' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', 'in:'.implode(',', array_keys(Campaign::STATUSES))],
        ]);
    }
}
