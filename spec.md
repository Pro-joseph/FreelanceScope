# FreelanceScope — Spec de build (commandes)

Ordre d'exécution recommandé : Setup → Migrations → Models → Controllers → Form Requests → Policies → Resources → Jobs → Services → PDF → Queue → IA (Groq) → Tests.

---

## 1. Setup projet

```bash
composer create-project laravel/laravel freelancescope
cd freelancescope
composer require laravel/sanctum
php artisan install:api          # publie la config Sanctum + migration personal_access_tokens
```

---

## 2. Migrations

Copier les 7 fichiers fournis dans `database/migrations/` :

```bash
php artisan migrate
```

Pour repartir de zéro pendant le dev si besoin :

```bash
php artisan migrate:fresh
```

---

## 3. Models

```bash
php artisan make:model Client
php artisan make:model Project
php artisan make:model ProjectFeature
php artisan make:model AiAnalysis
php artisan make:model Estimate
php artisan make:model Devis
# User existe deja par defaut dans app/Models/
```

---

## 4. Controllers

```bash
php artisan make:controller ClientController --api --model=Client
php artisan make:controller ProjectController --api --model=Project
php artisan make:controller AIController --invokable
php artisan make:controller DevisController --api --model=Devis
```

---

## 5. Form Requests (validation)

```bash
php artisan make:request StoreClientRequest
php artisan make:request UpdateClientRequest
php artisan make:request StoreProjectRequest
php artisan make:request UpdateProjectRequest
php artisan make:request GenerateEstimationRequest
php artisan make:request StoreDevisRequest
```

---

## 6. Policies (autorisation — freelance ne voit que ses données)

```bash
php artisan make:policy ClientPolicy --model=Client
php artisan make:policy ProjectPolicy --model=Project
php artisan make:policy DevisPolicy --model=Devis
```

---

## 7. API Resources (format JSON de réponse)

```bash
php artisan make:resource ClientResource
php artisan make:resource ProjectResource
php artisan make:resource ProjectFeatureResource
php artisan make:resource EstimateResource
php artisan make:resource DevisResource
```

---

## 8. Jobs (traitement asynchrone IA)

```bash
php artisan make:job GenerateEstimationJob
php artisan make:job ParseEstimationJob
```

---

## 9. Services (logique métier — pas de commande artisan, dossier manuel)

```bash
mkdir -p app/Services
touch app/Services/ClientService.php
touch app/Services/ProjectService.php
touch app/Services/AIEstimationService.php
touch app/Services/DevisService.php
```

---

## 10. PDF (pour DevisService)

```bash
composer require barryvdh/laravel-dompdf
```

---

## 11. Queue (jobs IA en asynchrone)

Dans `.env` :

```
QUEUE_CONNECTION=redis   # ou database si pas de Redis en local
```

```bash
php artisan queue:table       # si QUEUE_CONNECTION=database
php artisan migrate
php artisan queue:work        # lance le worker en local pour tester
```

---

## 12. Appel Groq (compatible API OpenAI)

```bash
composer require openai-php/client
```

Groq expose une API compatible OpenAI : on pointe simplement le client vers `https://api.groq.com/openai/v1` au lieu de `api.openai.com`, à configurer dans `config/services.php` + `.env`.

---

## 13. Tests (Pest)

```bash
composer require pestphp/pest --dev --with-all-dependencies
composer require pestphp/pest-plugin-laravel --dev
php artisan pest:install
```

---

## Points ouverts à confirmer avant les models

- `estimates.feature_id` — pas de `project_id` en plus (schéma normalisé, sans redondance).
- `clients.user_id` — renommé depuis `freelance_id` pour matcher le screenshot drawSQL.