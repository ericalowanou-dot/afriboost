<?php

namespace Tests\Feature;

use App\Models\Mission;
use App\Models\Participation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVerificationActionsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'registration_step' => 2,
        ]);
    }

    public function test_admin_can_classify_each_creator_network(): void
    {
        $creator = User::factory()->create(['role' => 'creator', 'registration_step' => 2]);
        $tiktok = $creator->socialNetworks()->create([
            'platform' => 'tiktok',
            'handle' => '@demo',
            'profile_url' => 'https://tiktok.com/@demo',
            'status' => 'active',
        ]);
        $instagram = $creator->socialNetworks()->create([
            'platform' => 'instagram',
            'handle' => '@demo',
            'profile_url' => 'https://instagram.com/demo',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.creators.networks.tier', $creator), [
            'networks' => [
                $tiktok->id => ['creator_tier' => 'top'],
                $instagram->id => ['creator_tier' => 'basic'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertSame('top', $tiktok->fresh()->creator_tier);
        $this->assertSame('basic', $instagram->fresh()->creator_tier);
        $this->assertSame('top', $creator->fresh()->creator_tier);
    }

    public function test_admin_creators_index_shows_verification_action_buttons(): void
    {
        $creator = User::factory()->create([
            'role' => 'creator',
            'verification_status' => User::VERIFICATION_PENDING,
            'registration_step' => 2,
        ]);
        $creator->socialNetworks()->create([
            'platform' => 'tiktok',
            'profile_url' => 'https://tiktok.com/@demo',
            'status' => 'active',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.creators.index', ['verification' => 'pending']))
            ->assertOk()
            ->assertSee('Voir')
            ->assertSee('Classer')
            ->assertSee('Décider');
    }

    public function test_admin_participations_index_shows_verification_action_buttons(): void
    {
        $creator = User::factory()->create(['role' => 'creator', 'registration_step' => 2]);
        $mission = Mission::create([
            'brand_name' => 'Brand',
            'title' => 'Mission',
            'description' => 'Desc',
            'social_network' => 'tiktok',
            'social_networks' => ['tiktok'],
            'content_type' => 'video',
            'reward_usd' => 10,
            'status' => 'published',
        ]);

        Participation::create([
            'user_id' => $creator->id,
            'mission_id' => $mission->id,
            'content_url' => 'https://tiktok.com/@demo/video/1',
            'status' => Participation::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.participations.index'))
            ->assertOk()
            ->assertSee('Voir')
            ->assertSee('Classer')
            ->assertSee('Décider');
    }
}
