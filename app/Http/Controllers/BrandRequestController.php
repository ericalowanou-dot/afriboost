<?php

namespace App\Http\Controllers;

use App\Models\CampaignRequest;
use App\Models\Mission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Page publique « Lancer une campagne » destinée aux marques. */
class BrandRequestController extends Controller
{
    public function create(): View
    {
        return view('brands.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:150'],
            'contact_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:30'],
            'objective' => ['required', 'string', 'max:2000'],
            'networks' => ['nullable', 'array'],
            'networks.*' => [Rule::in(Mission::NETWORKS)],
            'budget_usd' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'desired_start' => ['nullable', 'date', 'after_or_equal:today'],
            'website' => ['prohibited'], // pot de miel anti-robots
        ], [
            'company_name.required' => 'Indiquez le nom de votre marque ou entreprise.',
            'objective.required' => 'Décrivez en quelques mots l\'objectif de votre campagne.',
        ]);

        unset($data['website']);

        CampaignRequest::create($data + ['status' => 'new']);

        return redirect()
            ->route('brands.create')
            ->with('success', 'Merci ! Votre demande a bien été envoyée. L\'équipe AfriBoost vous recontacte sous 48 h.');
    }
}
