<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Mission;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

        $creator = User::create([
            'name' => 'Awa Créatrice',
            'email' => 'createur@afriboost.test',
            'phone' => '+22891111111',
            'password' => Hash::make('password'),
            'role' => 'creator',
            'status' => 'active',
            'verification_status' => User::VERIFICATION_VERIFIED,
            'creator_tier' => User::TIER_MEDIUM,
            'registration_step' => 2,
            'email_verified_at' => now(),
        ]);
        $wallets->ensureWallet($creator);

        $creator->socialNetworks()->create([
            'platform' => 'tiktok',
            'handle' => '@awa.boost',
            'public_name' => 'Awa Boost',
            'profile_url' => 'https://www.tiktok.com/@awa.boost',
            'follower_count' => 15000,
        ]);

        $campaign = Campaign::create([
            'client_name' => 'Gozem',
            'title' => 'Campagne Gozem été 2026',
            'objective' => 'Faire connaître les services Gozem auprès des jeunes urbains.',
            'budget_usd' => 5000,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ]);

        $missions = [
            [
                'campaign_id' => $campaign->id,
                'brand_name' => 'GOZEM',
                'title' => 'Présente Gozem en vidéo TikTok',
                'short_description' => 'Crée une vidéo de 30s présentant les services Gozem.',
                'description' => 'Présente clairement l\'application Gozem et ses avantages au quotidien.',
                'objective' => 'Générer de la notoriété sur TikTok.',
                'instructions' => "1. Ouvre TikTok et crée une vidéo verticale.\n2. Présente au moins 2 services Gozem.\n3. Mentionne AfriBoost dans la description.\n4. Publie puis colle le lien ici.",
                'validation_criteria' => 'Vidéo publique, durée min. 30s, marque visible, consignes respectées.',
                'social_network' => 'tiktok',
                'content_type' => 'video',
                'min_duration_seconds' => 30,
                'reward_usd' => 15.00,
                'rating' => 4.8,
                'starts_at' => now()->toDateString(),
                'ends_at' => now()->addDays(20)->toDateString(),
                'status' => 'published',
            ],
            [
                'campaign_id' => null,
                'brand_name' => 'RESTO AKIFF',
                'title' => 'Story Instagram Resto Akiff',
                'short_description' => 'Partage une story Instagram du restaurant.',
                'description' => 'Montre l\'ambiance et un plat signature.',
                'objective' => 'Attirer des clients locaux.',
                'instructions' => "1. Visite ou utilise le visuel fourni.\n2. Publie une story Instagram.\n3. Tag @resto.akiff.\n4. Soumets le lien/capture.",
                'validation_criteria' => 'Story publiée, tag présent, visuel clair.',
                'social_network' => 'instagram',
                'content_type' => 'story',
                'min_duration_seconds' => null,
                'reward_usd' => 8.00,
                'rating' => 4.9,
                'starts_at' => now()->toDateString(),
                'ends_at' => now()->addDays(14)->toDateString(),
                'status' => 'published',
            ],
            [
                'campaign_id' => null,
                'brand_name' => 'KINKELIBA BIO',
                'title' => 'Post Facebook Kinkeliba Bio',
                'short_description' => 'Publie un post Facebook sur les bienfaits du kinkeliba.',
                'description' => 'Rédige un post authentique avec une photo produit.',
                'objective' => 'Sensibiliser à la marque bio.',
                'instructions' => "1. Rédige un post Facebook.\n2. Ajoute une photo produit.\n3. Utilise le hashtag #KinkelibaBio.\n4. Colle le lien du post.",
                'validation_criteria' => 'Post public, hashtag présent, photo claire.',
                'social_network' => 'facebook',
                'content_type' => 'post',
                'min_duration_seconds' => null,
                'reward_usd' => 7.50,
                'rating' => 4.7,
                'starts_at' => now()->toDateString(),
                'ends_at' => now()->addDays(21)->toDateString(),
                'status' => 'published',
            ],
            [
                'campaign_id' => null,
                'brand_name' => 'PUP APPLI',
                'title' => 'Vidéo YouTube Shorts Pup Appli',
                'short_description' => 'Crée un Short YouTube pour présenter Pup Appli.',
                'description' => 'Explique comment monétiser son audience avec Pup.',
                'objective' => 'Acquérir des créateurs.',
                'instructions' => "1. Crée un Short YouTube (< 60s).\n2. Montre l'écran de l'app.\n3. CTA clair en fin de vidéo.\n4. Soumets le lien.",
                'validation_criteria' => 'Short public, app visible, CTA présent.',
                'social_network' => 'youtube',
                'content_type' => 'video',
                'min_duration_seconds' => 20,
                'reward_usd' => 12.00,
                'rating' => 4.9,
                'starts_at' => now()->toDateString(),
                'ends_at' => now()->addDays(30)->toDateString(),
                'status' => 'published',
            ],
        ];

        foreach ($missions as $mission) {
            Mission::create($mission);
        }
    }
}
