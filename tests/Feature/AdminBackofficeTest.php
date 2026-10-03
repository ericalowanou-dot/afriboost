<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\CampaignRequest;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBackofficeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_pages_render(): void
    {
        $campaign = Campaign::create(['client_name' => 'Gozem', 'title' => 'Rentrée', 'status' => 'active']);
        $mission = Mission::create([
            'campaign_id' => $campaign->id,
            'brand_name' => 'GOZEM',
            'title' => 'Vidéo',
            'social_network' => 'tiktok',
            'content_type' => 'video',
            'reward_usd' => 10,
            'status' => 'published',
        ]);

        $this->actingAs($this->admin);

        foreach ([
            route('admin.dashboard'),
            route('admin.missions.index'),
            route('admin.missions.show', $mission),
            route('admin.campaigns.index'),
            route('admin.campaigns.show', $campaign),
            route('admin.campaigns.create'),
            route('admin.payouts.index'),
            route('admin.activity.index'),
            route('admin.campaign-requests.index'),
            route('admin.participations.index'),
            route('admin.creators.index'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_creators_cannot_access_admin(): void
    {
        $creator = User::factory()->create(['role' => 'creator']);

        $this->actingAs($creator)->get(route('admin.payouts.index'))->assertForbidden();
        $this->actingAs($creator)->get(route('admin.campaigns.index'))->assertForbidden();
    }

    public function test_campaign_can_be_created_from_brand_request(): void
    {
        $request = CampaignRequest::create([
            'company_name' => 'Kinkeliba Bio',
            'contact_name' => 'Awa',
            'email' => 'contact@example.com',
            'objective' => 'Notoriété',
            'budget_usd' => 400,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.campaigns.create', ['demande' => $request->id]))
            ->assertOk()
            ->assertSee('Kinkeliba Bio');

        $this->post(route('admin.campaigns.store'), [
            'campaign_request_id' => $request->id,
            'client_name' => 'Kinkeliba Bio',
            'title' => 'Lancement',
            'budget_usd' => 400,
            'status' => 'active',
        ])->assertRedirect();

        $campaign = Campaign::firstOrFail();
        $this->assertSame('converted', $request->fresh()->status);
        $this->assertSame($campaign->id, $request->fresh()->campaign_id);
        $this->assertDatabaseHas('activity_logs', ['action' => 'campaign.created']);
    }

    public function test_suspending_a_creator_requires_a_reason_and_is_logged(): void
    {
        $creator = User::factory()->create(['role' => 'creator']);

        $this->actingAs($this->admin)
            ->patch(route('admin.creators.status', $creator), ['status' => 'suspended'])
            ->assertSessionHasErrors('status_reason');
        $this->assertSame('active', $creator->fresh()->status);

        $this->patch(route('admin.creators.status', $creator), ['status' => 'suspended', 'status_reason' => 'Faux abonnés'])
            ->assertSessionHas('success');

        $this->assertSame('suspended', $creator->fresh()->status);
        $this->assertDatabaseHas('activity_logs', ['action' => 'creator.status_changed', 'subject_id' => $creator->id]);
    }
}
