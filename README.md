# FreelanceScope — Laravel API

Backend API pour la gestion de freelance : clients, projets, fonctionnalités, estimation IA (Groq), devis PDF.

**Stack :** Laravel 13 · PHP 8.4 · Sanctum · MySQL · Docker

---

## Démarrage rapide (Docker)

```bash
docker-compose up -d
```

L'API est servie sur `http://localhost:80/api`.

Configuration :

- `APP_URL=http://localhost:80`
- `FRONTEND_URL=http://localhost:4000` (Angular)
- `DB_HOST=db`, `DB_DATABASE=freelancescope`
- `QUEUE_CONNECTION=redis`

Lancer le worker pour les jobs IA asynchrones :

```bash
docker exec freelancescope-app php artisan queue:work
```

---

## Installation locale (sans Docker)

```bash
cp .env.example .env
# configurer DB_HOST=127.0.0.1, DB_DATABASE=...
composer install
php artisan key:generate
php artisan migrate
php artisan serve --port=8000
```

---

## Structure API

Toutes les routes sont préfixées par `/api`.

### Auth

| Méthode | URL | Description |
|---------|-----|-------------|
| POST | `/api/auth/register` | Inscription |
| POST | `/api/auth/login` | Connexion |
| POST | `/api/auth/forgot-password` | Mot de passe oublié |
| POST | `/api/auth/reset-password` | Réinitialisation |
| GET | `/api/auth/me` | Profil connecté |
| POST | `/api/auth/logout` | Déconnexion |

### Ressources

| Méthode | URL | Description |
|---------|-----|-------------|
| GET/POST | `/api/clients` | Lister / Créer |
| GET/PUT/DELETE | `/api/clients/{client}` | Voir / Modifier / Supprimer |
| GET/POST | `/api/projects` | Lister / Créer |
| GET/PUT/DELETE | `/api/projects/{project}` | Voir / Modifier / Supprimer |
| GET/POST | `/api/projects/{project}/features` | Fonctionnalités lister / créer |
| GET/PUT/DELETE | `/api/features/{feature}` | Voir / Modifier / Supprimer |
| GET | `/api/features/{feature}/estimate` | Estimation d'une fonctionnalité |
| PUT | `/api/estimates/{estimate}` | Modifier une estimation |
| GET/POST | `/api/projects/{project}/devis` | Devis lister / créer |
| GET/PUT/DELETE | `/api/projects/{project}/devis/{devis}` | Voir / Modifier / Supprimer |
| GET | `/api/projects/{project}/devis/{devis}/pdf` | Télécharger PDF |

### IA

| Méthode | URL | Description |
|---------|-----|-------------|
| POST | `/api/projects/{project}/ai-estimate` | Lancer estimation IA (asynchrone) |
| GET | `/api/projects/{project}/ai-analyses` | Historique des analyses |

### Dashboard & Profil

| Méthode | URL | Description |
|---------|-----|-------------|
| GET | `/api/dashboard/stats` | Stats du freelance connecté |
| GET/PUT | `/api/freelance/profile` | Profil freelance |

### Admin

| Méthode | URL | Description |
|---------|-----|-------------|
| GET | `/api/admin/dashboard` | Dashboard admin |
| GET/POST | `/api/admin/freelances` | Lister / Créer un freelance |
| GET/PUT/DELETE | `/api/admin/freelances/{user}` | Voir / Modifier / Supprimer |
| PATCH | `/api/admin/freelances/{user}/statut` | Activer / désactiver |

---

## Tests

```bash
php artisan test --compact
```

58 tests (Pest) couvrant auth, CRUD, policies, services, AI, devis.

---

## Documentation complète

- [Schémas API détaillés](docs/api-schemas.md)
- [Postman collection](docs/FreelanceScope.postman_collection.json)
- [Spécification Angular](docs/FreelanceScope_Angular_Spec.md)
