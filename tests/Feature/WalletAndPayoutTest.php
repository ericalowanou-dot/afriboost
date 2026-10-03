<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Mission;
use App\Models\Participation;
use App\Models\Transaction;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletAndPayoutTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $creator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->creator = User::factory()->create(['role' => 'creator']);
        $this->creator->socialNetworks()->create([
            'platform' => 'instagram',
            'handle' => '@demo',
            'creator_tier' => User::TIER_MEDIUM,
            'status' => 'active',
        ]);
    }

    private function submittedParticipation(): Participation
    {
        $mission = Mission::create([
            'brand_name' => 'AKIFF',
            'title' => 'Story Akiff',
            'social_network' => 'tiktok',
            'social_networks' => ['tiktok', 'instagram'],
            'network_budgets' => [
                'tiktok' => ['basic' => 10, 'medium' => 15, 'top' => 25],
                'instagram' => ['basic' => 8, 'medium' => 12, 'top' => 20],
            ],
            'content_type' => 'story',
            'reward_usd' => 10,
            'status' => 'published',
        ]);

        return Participation::create([
            'user_id' => $this->creator->id,
            'mission_id' => $mission->id,
            'network' => 'instagram',
            'content_url' => 'https://www.instagram.com/p/abc/',
            'status' => Participation::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
    }

    public function test_validation_credits_reward_for_the_network_used_once(): void
    {
        $participation = $this->submittedParticipation();

        $this->actingAs($this->admin)
            ->post(route('admin.participations.validate', $participation))
            ->assertRedirect(route('admin.participations.index'));

        $participation->refresh();
        $wallet = $this->creator->wallet()->first();

        // Instagram, niveau medium → 12 $
        $this->assertSame('12.00', $participation->reward_usd);
        $this->assertSame('12.00', $wallet->balance_usd);
        $this->assertSame('0.00', $wallet->pending_usd);
        $this->assertSame(1, Transaction::where('type', 'reward_credit')->count());
        $this->assertTrue(ActivityLog::where('action', 'participation.validated')->exists());

        // Une seconde validation est refusée proprement (pas d'erreur 500, pas de double crédit)
        $this->post(route('admin.participations.validate', $participation))
            ->assertSessionHasErrors('participation');
        $this->assertSame('12.00', $wallet->fresh()->balance_usd);
    }

    public function test_opening_a_submission_marks_it_under_review(): void
    {
        $participation = $this->submittedParticipation();

        $this->actingAs($this->admin)->get(route('admin.participations.show', $participation))->assertOk();

        $this->assertSame(Participation::STATUS_UNDER_REVIEW, $participation->fresh()->status);
    }

    public function test_payout_request_reserves_balance_and_can_be_paid(): void
    {
        $wallet = app(WalletService::class)->ensureWallet($this->creator);
        $wallet->update(['balance_usd' => 30]);

        $this->actingAs($this->creator)
            ->post(route('wallet.payout'), ['amount' => 20, 'payout_method' => 'mobile_money', 'payout_account' => '+22890000000'])
            ->assertRedirect(route('wallet.index'))
            ->assertSessionHas('success');

        $wallet->refresh();
        $this->assertSame('10.00', $wallet->balance_usd);
        $this->assertSame('20.00', $wallet->pending_usd);

        // Une seule demande en cours à la fois
        $this->post(route('wallet.payout'), ['amount' => 5, 'payout_method' => 'mobile_money', 'payout_account' => '+22890000000'])
            ->assertSessionHasErrors('amount', null, 'payout');

        $payout = Transaction::where('type', 'payout')->firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('admin.payouts.complete', $payout), ['admin_note' => 'Réf. 123'])
            ->assertSessionHas('success');

        $this->assertSame('completed', $payout->fresh()->status);
        $this->assertSame('0.00', $wallet->fresh()->pending_usd);
        $this->assertSame('10.00', $wallet->fresh()->balance_usd);
    }

    public function test_cancelled_payout_is_refunded(): void
    {
        $wallet = app(WalletService::class)->ensureWallet($this->creator);
        $wallet->update(['balance_usd' => 15]);
        $payout = app(WalletService::class)->requestPayout($this->creator, 15, 'paypal', 'demo@example.com');

        $this->actingAs($this->admin)
            ->post(route('admin.payouts.cancel', $payout), ['admin_note' => 'Compte PayPal invalide'])
            ->assertSessionHas('success');

        $this->assertSame('cancelled', $payout->fresh()->status);
        $this->assertSame('15.00', $wallet->fresh()->balance_usd);
        $this->assertSame('0.00', $wallet->fresh()->pending_usd);
    }

    public function test_payout_cannot_exceed_balance(): void
    {
        app(WalletService::class)->ensureWallet($this->creator)->update(['balance_usd' => 6]);

        $this->actingAs($this->creator)
            ->post(route('wallet.payout'), ['amount' => 50, 'payout_method' => 'mobile_money', 'payout_account' => '+22890000000'])
            ->assertSessionHasErrors('amount', null, 'payout');

        $this->assertSame(0, Transaction::where('type', 'payout')->count());
    }
}
