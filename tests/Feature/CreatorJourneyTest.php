<?php

namespace Tests\Feature;

use App\Models\Mission;
use App\Models\Participation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatorJourneyTest extends TestCase
{
    use RefreshDatabase;

    private function creator(array $networks = ['tiktok'], array $attributes = []): User
    {
        $user = User::factory()->create($attributes + ['role' => 'creator']);
        foreach ($networks as $platform) {
            $user->socialNetworks()->create([
                'platform' => $platform,
                'handle' => '@demo',
                'profile_url' => 'https://www.'.$platform.'.com/@demo',
                'status' => 'active',
            ]);
        }

        return $user;
    }

    private function mission(array $attributes = []): Mission
    {
        return Mission::create($attributes + [
            'brand_name' => 'GOZEM',
            'title' => 'Présente Gozem',
            'short_description' => 'Vidéo courte',
            'social_network' => 'tiktok',
            'social_networks' => ['tiktok', 'instagram'],
            'network_budgets' => [
                'tiktok' => ['basic' => 10, 'medium' => 15, 'top' => 25],
                'instagram' => ['basic' => 8, 'medium' => 12, 'top' => 20],
            ],
            'content_type' => 'video',
            'reward_usd' => 10,
            'status' => 'published',
        ]);
    }

    public function test_creator_pages_render(): void
    {
        $user = $this->creator();
        $mission = $this->mission();

        $this->get(route('missions.index'))->assertOk()->assertSee('GOZEM')->assertSee('Publie')->assertSee('Lancez votre');
        $this->get(route('missions.show', $mission->routeParams()))->assertOk()->assertSee('À propos de la mission')->assertSee('Se connecter');

        $this->actingAs($user);
        $this->get(route('missions.show', $mission->routeParams()))->assertOk()->assertSee('Participer à la mission');
        $this->get(route('participations.index'))->assertOk();
        $this->get(route('wallet.index'))->assertOk()->assertSee('Solde disponible');
        $this->get(route('creator.profile'))->assertOk()->assertSee('Mes réseaux sociaux');
    }

    public function test_participation_records_network_and_submission_is_checked(): void
    {
        $user = $this->creator(['instagram']);
        $mission = $this->mission();

        $this->actingAs($user)
            ->post(route('missions.participate', $mission->routeParams('instagram')))
            ->assertRedirect();

        $participation = Participation::firstOrFail();
        $this->assertSame('instagram', $participation->network);

        // Un lien TikTok ne correspond pas à une participation Instagram
        $this->post(route('missions.submit', $mission->routeParams('instagram')), [
            'content_url' => 'https://www.tiktok.com/@demo/video/1',
        ])->assertSessionHasErrors('content_url');

        $this->post(route('missions.submit', $mission->routeParams('instagram')), [
            'content_url' => 'https://www.instagram.com/p/abc/',
        ])->assertRedirect(route('participations.index', ['status' => 'submitted']));

        $this->assertSame(Participation::STATUS_SUBMITTED, $participation->fresh()->status);
        $this->assertSame(8.0, $participation->fresh()->rewardAmount());
    }

    public function test_full_mission_cannot_be_joined(): void
    {
        $mission = $this->mission(['max_participants' => 1]);
        $first = $this->creator();
        Participation::create(['user_id' => $first->id, 'mission_id' => $mission->id, 'network' => 'tiktok', 'status' => 'in_progress']);

        $second = $this->creator();
        $this->actingAs($second)
            ->post(route('missions.participate', $mission->routeParams()))
            ->assertSessionHas('warning', 'Toutes les places de cette mission sont prises.');

        $this->assertSame(1, Participation::count());
    }

    public function test_unverified_creator_cannot_participate(): void
    {
        $user = $this->creator(['tiktok'], ['verification_status' => User::VERIFICATION_PENDING]);
        $mission = $this->mission();

        $this->actingAs($user)
            ->post(route('missions.participate', $mission->routeParams()))
            ->assertSessionHas('warning');

        $this->assertSame(0, Participation::count());
    }

    public function test_creator_can_update_profile_and_password(): void
    {
        $user = $this->creator();

        $this->actingAs($user)
            ->patch(route('creator.profile.update'), ['name' => 'Nouveau Nom', 'phone' => '+22890000001'])
            ->assertSessionHas('success');
        $this->assertSame('Nouveau Nom', $user->fresh()->name);

        $this->from(route('creator.profile'))
            ->put(route('password.update'), [
                'current_password' => 'password',
                'password' => 'un-nouveau-mot-de-passe',
                'password_confirmation' => 'un-nouveau-mot-de-passe',
            ])
            ->assertRedirect(route('creator.profile'))
            ->assertSessionHasNoErrors();
    }

    public function test_brand_can_send_campaign_request(): void
    {
        $this->get(route('brands.create'))->assertOk();

        $this->post(route('brands.store'), [
            'company_name' => 'Kinkeliba Bio',
            'contact_name' => 'Awa',
            'email' => 'contact@example.com',
            'objective' => 'Faire connaître notre jus.',
            'networks' => ['instagram', 'facebook'],
            'budget_usd' => 500,
        ])->assertRedirect(route('brands.create'))->assertSessionHas('success');

        $this->assertDatabaseHas('campaign_requests', ['company_name' => 'Kinkeliba Bio', 'status' => 'new']);
    }

    public function test_brand_request_honeypot_blocks_bots(): void
    {
        $this->post(route('brands.store'), [
            'company_name' => 'Spam',
            'contact_name' => 'Bot',
            'email' => 'bot@example.com',
            'objective' => 'Spam',
            'website' => 'http://spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertDatabaseCount('campaign_requests', 0);
    }
}
