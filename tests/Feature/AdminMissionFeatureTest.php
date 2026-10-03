<?php

namespace Tests\Feature;

use App\Models\Mission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMissionFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'registration_step' => 2,
        ]);
    }

    public function test_admin_can_create_mission_with_all_requested_fields(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin)->post(route('admin.missions.store'), [
            'brand_name' => 'Entreprise Demo',
            'title' => 'Mission Demo',
            'description' => 'Description complète de la mission.',
            'social_networks' => ['tiktok', 'instagram'],
            'network_budgets' => [
                'tiktok' => ['basic' => '10', 'medium' => '12', 'top' => '15'],
                'instagram' => ['basic' => '8', 'medium' => '10', 'top' => '12'],
            ],
            'content_type' => 'video',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addDays(30)->toDateString(),
            'content_retention_days' => '14',
            'status' => 'published',
            'logo' => UploadedFile::fake()->image('logo.jpg'),
            'content_example' => UploadedFile::fake()->image('example.jpg'),
        ]);

        $response->assertRedirect(route('admin.missions.show', Mission::latest('id')->first()));

        $mission = Mission::first();
        $this->assertNotNull($mission);
        $this->assertSame('Entreprise Demo', $mission->brand_name);
        $this->assertSame(['tiktok', 'instagram'], $mission->social_networks);
        $this->assertSame(14, $mission->content_retention_days);
        $this->assertNotNull($mission->image_path);
        $this->assertNotNull($mission->content_example_path);
        $this->assertSame(8.0, $mission->rewardFor(null, 'instagram'));
    }

    public function test_admin_missions_index_supports_filters(): void
    {
        Mission::create([
            'brand_name' => 'Alpha',
            'title' => 'Alpha Mission',
            'description' => 'Desc',
            'social_network' => 'tiktok',
            'social_networks' => ['tiktok'],
            'content_type' => 'video',
            'reward_usd' => 20,
            'status' => 'published',
        ]);

        Mission::create([
            'brand_name' => 'Beta',
            'title' => 'Beta Mission',
            'description' => 'Desc',
            'social_network' => 'instagram',
            'social_networks' => ['instagram'],
            'content_type' => 'video',
            'reward_usd' => 5,
            'status' => 'draft',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.missions.index', ['network' => 'tiktok', 'status' => 'published']))
            ->assertOk()
            ->assertSee('Alpha')
            ->assertDontSee('Beta');
    }

    public function test_admin_creators_index_supports_tier_and_network_filters(): void
    {
        $topCreator = User::factory()->create([
            'role' => 'creator',
            'creator_tier' => User::TIER_TOP,
            'name' => 'Top Creator',
            'registration_step' => 2,
        ]);
        $topCreator->socialNetworks()->create(['platform' => 'tiktok', 'status' => 'active']);

        User::factory()->create([
            'role' => 'creator',
            'creator_tier' => User::TIER_BASIC,
            'name' => 'Basic Creator',
            'registration_step' => 2,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.creators.index', ['tier' => 'top', 'network' => 'tiktok']))
            ->assertOk()
            ->assertSee('Top Creator')
            ->assertDontSee('Basic Creator');
    }

    public function test_published_missions_disappear_after_end_date(): void
    {
        Mission::create([
            'brand_name' => 'Expired',
            'title' => 'Expired Mission',
            'description' => 'Desc',
            'social_network' => 'tiktok',
            'social_networks' => ['tiktok'],
            'content_type' => 'video',
            'reward_usd' => 10,
            'ends_at' => now()->subDay(),
            'status' => 'published',
        ]);

        $this->get(route('missions.index'))
            ->assertOk()
            ->assertDontSee('Expired');
    }
}
