<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Mission;
use App\Models\Participation;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Données de démonstration. Comptes (mot de passe « password ») :
 *  - admin@afriboost.test    : équipe AfriBoost
 *  - createur@afriboost.test : créatrice vérifiée avec historique
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $wallets = app(WalletService::class);

        $admin = User::create([
            'name' => 'Équipe AfriBoost',
            'email' => 'admin@afriboost.test',
            'phone' => '+22890000000',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'verification_status' => User::VERIFICATION_VERIFIED,
            'registration_step' => 2,
            'email_verified_at' => now(),
        ]);
        $wallets->ensureWallet($admin);

        $creators = collect([
            ['Awa Créatrice', 'createur@afriboost.test', User::VERIFICATION_VERIFIED, User::TIER_MEDIUM, [
                ['tiktok', '@awa.boost', 15000, User::TIER_MEDIUM],
                ['instagram', 'awa.boost', 4200, User::TIER_BASIC],
            ]],
            ['Kofi Mensah', 'kofi@afriboost.test', User::VERIFICATION_VERIFIED, User::TIER_TOP, [
                ['youtube', '@kofitech', 82000, User::TIER_TOP],
                ['tiktok', '@kofi.tech', 31000, User::TIER_MEDIUM],
            ]],
            ['Fatou Diallo', 'fatou@afriboost.test', User::VERIFICATION_VERIFIED, User::TIER_BASIC, [
                ['instagram', 'fatou.lifestyle', 6800, User::TIER_BASIC],
                ['facebook', 'fatou.diallo.officiel', 9100, User::TIER_BASIC],
            ]],
            ['Yao Kouassi', 'yao@afriboost.test', User::VERIFICATION_PENDING, null, [
                ['tiktok', '@yao.comedy', 12500, null],
            ]],
            ['Aminata Traoré', 'aminata@afriboost.test', User::VERIFICATION_PENDING, null, [
                ['instagram', 'amina.cuisine', 3400, null],
                ['facebook', 'aminata.cuisine', 2100, null],
            ]],
        ])->map(function (array $row) use ($admin, $wallets) {
            [$name, $email, $verification, $tier, $networks] = $row;

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'phone' => '+2289'.random_int(1000000, 9999999),
                'password' => Hash::make('password'),
                'role' => 'creator',
                'status' => 'active',
                'verification_status' => $verification,
                'creator_tier' => $tier,
                'registration_step' => 2,
                'email_verified_at' => now(),
                'verified_at' => $verification === User::VERIFICATION_VERIFIED ? now()->subDays(10) : null,
                'verified_by' => $verification === User::VERIFICATION_VERIFIED ? $admin->id : null,
            ]);
            $wallets->ensureWallet($user);

            foreach ($networks as [$platform, $handle, $followers, $networkTier]) {
                $user->socialNetworks()->create([
                    'platform' => $platform,
                    'handle' => $handle,
                    'public_name' => $name,
                    'profile_url' => $this->profileUrl($platform, $handle),
                    'follower_count' => $followers,
                    'creator_tier' => $networkTier,
                    'status' => 'active',
                ]);
            }

            return $user;
        });

        $gozem = Campaign::create([
            'client_name' => 'Gozem',
            'title' => 'Gozem — rentrée 2026',
            'objective' => 'Faire connaître les services Gozem auprès des jeunes urbains de Lomé et Cotonou.',
            'budget_usd' => 2500,
            'starts_at' => now()->subWeek()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

        $local = Campaign::create([
            'client_name' => 'Marques locales',
            'title' => 'Consommer local',
            'objective' => 'Mettre en avant les restaurants et produits bio de la région.',
            'budget_usd' => 800,
            'starts_at' => now()->subDays(3)->toDateString(),
            'ends_at' => now()->addWeeks(3)->toDateString(),
            'status' => 'active',
        ]);

        $missions = collect([
            [
                'campaign_id' => $gozem->id,
                'brand_name' => 'GOZEM',
                'title' => 'Présente Gozem en vidéo',
                'short_description' => 'Fais la promotion de l\'application Gozem sur tes réseaux sociaux.',
                'description' => 'Crée une vidéo originale pour présenter Gozem, ses services et ses avantages. Mentionne @gozemapp et utilise le hashtag #Gozem.',
                'objective' => 'Générer de la notoriété et des téléchargements de l\'application.',
                'instructions' => "Crée ton contenu.\nPublie-le sur ton réseau.\nCopie le lien de la publication.\nSoumets ta participation ici.",
                'validation_criteria' => "Vidéo publique d'au moins 30 secondes.\nMarque Gozem clairement visible.\nHashtag #Gozem dans la description.",
                'social_network' => 'tiktok',
                'social_networks' => ['tiktok', 'instagram'],
                'network_budgets' => [
                    'tiktok' => ['basic' => 15, 'medium' => 22, 'top' => 35],
                    'instagram' => ['basic' => 12, 'medium' => 18, 'top' => 28],
                ],
                'content_type' => 'video',
                'min_duration_seconds' => 30,
                'max_participants' => 40,
                'rating' => 4.8,
                'ends_at' => now()->addDays(20)->toDateString(),
                'content_retention_days' => 30,
                'visual' => ['#1a9c3e', '#0e6b2a', 'GOZEM', 'AFRICA\'S SUPER APP'],
            ],
            [
                'campaign_id' => $local->id,
                'brand_name' => 'RESTO AKIFF',
                'title' => 'Fais découvrir Resto Akiff',
                'short_description' => 'Fais découvrir les plats de Resto Akiff à ta communauté.',
                'description' => 'Montre l\'ambiance du restaurant et goûte un plat signature. Tague @resto.akiff dans ta story.',
                'objective' => 'Attirer de nouveaux clients locaux.',
                'instructions' => "Rends-toi au restaurant ou utilise le visuel fourni.\nPublie une story Instagram.\nTague @resto.akiff.\nSoumets le lien ou une capture.",
                'validation_criteria' => "Story publiée.\nTag @resto.akiff présent.\nVisuel net et appétissant.",
                'social_network' => 'instagram',
                'social_networks' => ['instagram'],
                'network_budgets' => ['instagram' => ['basic' => 8, 'medium' => 12, 'top' => 18]],
                'content_type' => 'story',
                'rating' => 4.5,
                'ends_at' => now()->addDays(14)->toDateString(),
                'visual' => ['#c2410c', '#7c2d12', 'AKIFF', 'RESTO · LOMÉ'],
            ],
            [
                'campaign_id' => null,
                'brand_name' => 'PUP APPLI',
                'title' => 'Monétise ton audience avec Pup',
                'short_description' => 'Promouvez Pup Appli et gagnez de l\'argent à chaque mission validée.',
                'description' => 'Explique en moins d\'une minute comment Pup aide les créateurs à monétiser leur audience.',
                'objective' => 'Acquérir de nouveaux créateurs sur Pup.',
                'instructions' => "Crée un Short YouTube de moins de 60 secondes.\nMontre l'écran de l'application.\nTermine par un appel à l'action clair.\nSoumets le lien.",
                'validation_criteria' => "Short public.\nApplication visible à l'écran.\nAppel à l'action présent.",
                'social_network' => 'youtube',
                'social_networks' => ['youtube', 'tiktok'],
                'network_budgets' => [
                    'youtube' => ['basic' => 12, 'medium' => 20, 'top' => 40],
                    'tiktok' => ['basic' => 10, 'medium' => 16, 'top' => 30],
                ],
                'content_type' => 'video',
                'min_duration_seconds' => 20,
                'rating' => 4.7,
                'ends_at' => now()->addDays(30)->toDateString(),
                'visual' => ['#1e1b4b', '#4338ca', 'PUP', 'MONÉTISE TON AUDIENCE'],
            ],
            [
                'campaign_id' => $local->id,
                'brand_name' => 'KINKELIBA BIO',
                'title' => 'Les bienfaits du Kinkeliba Bio',
                'short_description' => 'Boostez la visibilité du jus Kinkeliba Bio et ses bienfaits 100 % naturels.',
                'description' => 'Rédige un post authentique avec une photo du produit et parle de ses bienfaits.',
                'objective' => 'Sensibiliser à la marque bio.',
                'instructions' => "Rédige un post Facebook.\nAjoute une photo du produit.\nUtilise le hashtag #KinkelibaBio.\nColle le lien du post.",
                'validation_criteria' => "Post public.\nHashtag #KinkelibaBio présent.\nPhoto nette du produit.",
                'social_network' => 'facebook',
                'social_networks' => ['facebook', 'instagram'],
                'network_budgets' => [
                    'facebook' => ['basic' => 7.5, 'medium' => 10, 'top' => 15],
                    'instagram' => ['basic' => 7.5, 'medium' => 10, 'top' => 15],
                ],
                'content_type' => 'post',
                'rating' => 4.6,
                'ends_at' => now()->addDays(21)->toDateString(),
                'visual' => ['#65a30d', '#3f6212', 'KINKELIBA', '100 % BIO · NATUREL'],
            ],
        ])->map(function (array $data) {
            [$from, $to, $wordmark, $tagline] = $data['visual'];
            unset($data['visual']);

            $slug = Str::slug($data['brand_name']);
            $path = 'missions/logos/demo-'.$slug.'.svg';
            Storage::disk('public')->put($path, $this->brandVisual($from, $to, $wordmark, $tagline));

            $primary = $data['social_network'];

            return Mission::create($data + [
                'reward_usd' => $data['network_budgets'][$primary]['basic'],
                'reward_usd_medium' => $data['network_budgets'][$primary]['medium'],
                'reward_usd_top' => $data['network_budgets'][$primary]['top'],
                'image_path' => $path,
                'starts_at' => now()->subWeek()->toDateString(),
                'status' => 'published',
            ]);
        });

        [$awa, $kofi, $fatou] = [$creators[0], $creators[1], $creators[2]];
        [$gozemMission, $akiff, $pup, $kinkeliba] = $missions->all();

        // Historique de démonstration couvrant tous les statuts (journalisé au nom de l'admin)
        auth()->setUser($admin);
        $this->participation($awa, $kinkeliba, 'instagram', Participation::STATUS_VALIDATED, 9, $admin);
        $this->participation($awa, $akiff, 'instagram', Participation::STATUS_VALIDATED, 7, $admin);
        $this->participation($awa, $gozemMission, 'tiktok', Participation::STATUS_SUBMITTED, 1);
        $this->participation($awa, $pup, 'tiktok', Participation::STATUS_IN_PROGRESS, 0);

        $this->participation($kofi, $pup, 'youtube', Participation::STATUS_VALIDATED, 6, $admin);
        $this->participation($kofi, $gozemMission, 'tiktok', Participation::STATUS_UNDER_REVIEW, 2);

        $this->participation($fatou, $kinkeliba, 'facebook', Participation::STATUS_REJECTED, 4, $admin,
            'Le hashtag #KinkelibaBio est absent de la publication.');
        $this->participation($fatou, $akiff, 'instagram', Participation::STATUS_SUBMITTED, 0);

        // Un retrait payé et un retrait en attente
        auth()->setUser($kofi);
        $payout = $wallets->requestPayout($kofi, 20, 'mobile_money', '+228 90 12 34 56');
        auth()->setUser($admin);
        $wallets->completePayout($payout, $admin, 'Payé via T-Money');
        auth()->setUser($awa);
        $wallets->requestPayout($awa, 10, 'mobile_money', '+228 91 11 11 11');
        auth()->forgetUser();
    }

    private function participation(User $user, Mission $mission, string $network, string $status, int $daysAgo, ?User $admin = null, ?string $reason = null): void
    {
        $participation = Participation::create([
            'user_id' => $user->id,
            'mission_id' => $mission->id,
            'network' => $network,
            'status' => $status === Participation::STATUS_IN_PROGRESS ? $status : Participation::STATUS_SUBMITTED,
            'content_url' => $status === Participation::STATUS_IN_PROGRESS ? null : $this->contentUrl($network, $user->id.$mission->id),
            'submitted_at' => $status === Participation::STATUS_IN_PROGRESS ? null : now()->subDays($daysAgo)->subHours(3),
        ]);

        match ($status) {
            Participation::STATUS_VALIDATED => app(WalletService::class)->validateParticipation($participation, $admin),
            Participation::STATUS_REJECTED => app(WalletService::class)->rejectParticipation($participation, $admin, $reason),
            Participation::STATUS_UNDER_REVIEW => $participation->update(['status' => $status]),
            default => null,
        };
    }

    private function profileUrl(string $platform, string $handle): string
    {
        $handle = ltrim($handle, '@');

        return match ($platform) {
            'tiktok' => 'https://www.tiktok.com/@'.$handle,
            'instagram' => 'https://www.instagram.com/'.$handle,
            'facebook' => 'https://www.facebook.com/'.$handle,
            'youtube' => 'https://www.youtube.com/@'.$handle,
        };
    }

    private function contentUrl(string $platform, string $id): string
    {
        return match ($platform) {
            'tiktok' => 'https://www.tiktok.com/@demo/video/73'.$id.'0912',
            'instagram' => 'https://www.instagram.com/p/C'.$id.'demo/',
            'facebook' => 'https://www.facebook.com/demo/posts/10'.$id,
            'youtube' => 'https://www.youtube.com/shorts/demo'.$id,
        };
    }

    private function brandVisual(string $from, string $to, string $wordmark, string $tagline): string
    {
        $size = strlen($wordmark) > 6 ? 46 : 64;

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="{$from}"/><stop offset="1" stop-color="{$to}"/>
    </linearGradient>
  </defs>
  <rect width="400" height="400" fill="url(#g)"/>
  <circle cx="340" cy="60" r="120" fill="#fff" opacity=".08"/>
  <circle cx="40" cy="380" r="90" fill="#fff" opacity=".06"/>
  <text x="200" y="210" text-anchor="middle" font-family="Arial Black, Arial, sans-serif" font-weight="900" font-size="{$size}" fill="#fff" letter-spacing="2">{$wordmark}</text>
  <text x="200" y="250" text-anchor="middle" font-family="Arial, sans-serif" font-weight="700" font-size="16" fill="#fff" opacity=".85" letter-spacing="3">{$tagline}</text>
</svg>
SVG;
    }
}
