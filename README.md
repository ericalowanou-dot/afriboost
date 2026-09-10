# AfriBoost

Plateforme de missions de promotion pour créateurs de contenu (PWA Laravel).

## Prérequis

- PHP 8.2+
- Composer
- Node.js 18+
- Extension PHP `sqlite` (local) ou MySQL (LWS)

## Démarrage local

```bash
cd afriboost
composer install
cp .env.example .env   # si besoin
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan storage:link
php artisan serve
```

Ouvre `http://127.0.0.1:8000`.

## Comptes de démo

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Admin | `admin@afriboost.test` | `password` |
| Créateur | `createur@afriboost.test` | `password` |

## Fonctionnalités v1

- Auth créateur / admin
- Missions (liste, filtres réseau, détail, participation, soumission de lien)
- Suivi des participations
- Wallet USD + historique
- Profil + réseaux sociaux
- Admin : dashboard, CRUD missions, file de vérification (valider/refuser + crédit wallet), gestion créateurs

## URLs principales

| Page | URL |
|------|-----|
| Connexion | `/connexion` |
| Inscription | `/inscription` |
| Missions | `/missions` |
| Missions TikTok | `/missions/tiktok` |
| Détail mission | `/missions/tiktok/presente-gozem-en-video-1` |
| Mes participations | `/mes-participations` |
| Wallet | `/mon-wallet` |
| Profil | `/mon-profil` |
| Admin | `/admin` |
| Vérifications | `/admin/verifications` |
| Créateurs | `/admin/createurs` |

## Déploiement LWS

1. Auto-installeur Laravel (ou upload FTP du projet)
2. Configurer `.env` avec MySQL LWS
3. `composer install --no-dev`
4. `php artisan migrate --seed` (ou sans seed en prod)
5. `npm run build` en local puis uploader `public/build`
6. Pointer le domaine vers le dossier `public`
