<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampaignRequest;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CampaignRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: 'open';

        $requests = CampaignRequest::query()
            ->with('campaign')
            ->when($status === 'open', fn ($q) => $q->whereIn('status', ['new', 'contacted']))
            ->when(array_key_exists($status, CampaignRequest::STATUSES), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.campaign-requests.index', compact('requests', 'status'));
    }

    public function update(Request $request, CampaignRequest $campaignRequest)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(CampaignRequest::STATUSES))],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $campaignRequest->update($data);

        ActivityLogger::log(
            'campaign.request_updated',
            'Demande de '.$campaignRequest->company_name.' : '.$campaignRequest->statusLabel().'.',
            $campaignRequest
        );

        return back()->with('success', 'Demande mise à jour.');
    }
}
