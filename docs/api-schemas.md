# FreelanceScope — API Schemas pour Postman

Base URL : `http://localhost:8000/api`

---

## Sommaire

1. [Auth](#-auth)
2. [Freelance](#-freelance)
3. [Admin](#-admin)
4. [Clients](#-clients)
5. [Projects](#-projects)
6. [Project Features](#-project-features)
7. [Estimates](#-estimates)
8. [IA — Génération d'estimation](#-ia--génération-destimation)
9. [Devis](#-devis)

---

## 🔐 Auth *(token-based — pour Postman)*

### POST /api/register  *(guest)*

**Body :**
```json
{
  "nom": "Doe",
  "prenom": "John",
  "email": "john@example.com",
  "password": "password"
}
```

**Response 201 :**
```json
{
  "user": { "id": 1, "nom": "Doe", "prenom": "John", "email": "john@example.com", "role": "freelance" },
  "token": "1|abc123..."
}
```
> ⚠️ Copie le `token` dans Postman → Auth type: Bearer Token

---

### POST /api/login  *(guest)*

**Body :**
```json
{
  "email": "john@example.com",
  "password": "password"
}
```

**Response 200 :**
```json
{
  "user": { "id": 1, "nom": "Doe", "prenom": "John", "email": "john@example.com", "role": "freelance", "statut": "actif" },
  "token": "2|xyz789..."
}
```

---

### POST /api/logout  *(auth)*

**Body :** *Aucun*

**Response 200 :**
```json
{ "message": "Déconnecté." }
```

---

### GET /api/user  *(auth)*

**Response 200 :**
```json
{
  "id": 1,
  "nom": "Doe",
  "prenom": "John",
  "email": "john@example.com",
  "role": "freelance",
  "statut": "actif",
  "taux_horaire": null,
  "telephone": null
}
```

---

## 👤 Freelance

### GET /api/freelance/profile  *(auth)*

**Response 200 :**
```json
{
  "id": 1,
  "nom": "Doe",
  "prenom": "John",
  "email": "john@example.com",
  "telephone": "+212600000000",
  "taux_horaire": 50,
  "statut": "actif"
}
```

### PUT /api/freelance/profile  *(auth)*

**Body** *(tous optionnels)* :
```json
{
  "nom": "Doe",
  "prenom": "John",
  "email": "john@example.com",
  "telephone": "+212600000000",
  "taux_horaire": 65
}
```

**Response 200 :** *Même format que GET*

### GET /api/freelance/dashboard  *(auth)*

**Response 200 :**
```json
{
  "clients_count": 5,
  "projects_count": 12,
  "devis_count": 3
}
```

---

## 🛡️ Admin *(role=admin requis)*

### GET /api/admin/dashboard

**Response 200 :**
```json
{ "freelances_count": 10, "clients_count": 25, "projects_count": 40, "devis_count": 8 }
```

### GET /api/admin/freelances

**Response 200 :**
```json
[
  {
    "id": 2, "nom": "Jane", "prenom": "Doe",
    "email": "jane@example.com", "telephone": "+212600000001",
    "statut": "actif", "taux_horaire": 75
  }
]
```

### POST /api/admin/freelances

**Body :**
```json
{
  "nom": "Doe", "prenom": "Jane",
  "email": "jane@example.com", "password": "password123",
  "telephone": "+212600000001", "taux_horaire": 75
}
```

**Response 201**

### PUT /api/admin/freelances/{user}

**Body :**
```json
{ "nom": "Doe Updated", "taux_horaire": 80 }
```

### PATCH /api/admin/freelances/{user}/statut

**Body :** *Aucun — bascule actif/inactif*

### DELETE /api/admin/freelances/{user}

**Response 204**

---

## 👥 Clients

### GET /api/clients  *(auth)*

**Response 200 :**
```json
{
  "data": [
    {
      "id": 1,
      "company_name": "Acme Corp",
      "email": "contact@acme.com",
      "phone": "+212600000000",
      "projects_count": 3,
      "created_at": "2026-07-21T09:00:00.000000Z"
    }
  ],
  "meta": { "current_page": 1, "per_page": 15, "total": 1 }
}
```

### POST /api/clients  *(auth)*

**Body :**
```json
{
  "company_name": "Acme Corp",
  "email": "contact@acme.com",
  "phone": "+212600000000"
}
```

**Response 201 :**
```json
{
  "data": {
    "id": 1, "company_name": "Acme Corp",
    "email": "contact@acme.com", "phone": "+212600000000",
    "projects_count": 0, "created_at": "..."
  }
}
```

### GET /api/clients/{client}  *(auth)*

**Response 200 :** *Même format*

### PUT /api/clients/{client}  *(auth)*

**Body :**
```json
{ "company_name": "Acme Corp Updated", "email": "new@acme.com" }
```

### DELETE /api/clients/{client}  *(auth)*

**Response 204**

---

## 📁 Projects

### GET /api/projects  *(auth)*

**Response 200 :**
```json
{
  "data": [
    {
      "id": 1, "client_id": 1,
      "name": "Site e-commerce",
      "description": "Site de vente en ligne...",
      "status": "draft",
      "features_count": 5,
      "created_at": "2026-07-21T09:00:00.000000Z"
    }
  ],
  "meta": { "current_page": 1, "per_page": 15, "total": 1 }
}
```

### POST /api/projects  *(auth)*

**Body :**
```json
{
  "client_id": 1,
  "name": "Site e-commerce",
  "description": "Site de vente en ligne avec catalogue et paiement"
}
```

**Response 201**

### GET /api/projects/{project}  *(auth)*

**Response 200**

### PUT /api/projects/{project}  *(auth)*

**Body :**
```json
{
  "name": "Site e-commerce v2",
  "status": "in_progress"
}
```
*Status : `draft`, `in_progress`, `completed`, `cancelled`*

### DELETE /api/projects/{project}  *(auth)*

**Response 204**

---

## 📋 Project Features

### GET /api/projects/{project}/features  *(auth)*

**Response 200 :**
```json
[
  {
    "id": 1, "project_id": 1,
    "name": "Page d'accueil",
    "description": "Page d'accueil avec présentation",
    "complexity": "moyen",
    "created_at": "..."
  }
]
```

### POST /api/projects/{project}/features  *(auth)*

**Body :**
```json
{
  "name": "Page d'accueil",
  "description": "Page d'accueil avec présentation",
  "complexity": "moyen"
}
```
*Complexity : `simple`, `moyen`, `complexe`*

**Response 201**

### GET /api/features/{feature}  *(auth)*

### PUT /api/features/{feature}  *(auth)*

**Body :**
```json
{ "name": "Page d'accueil v2", "complexity": "simple" }
```

### DELETE /api/features/{feature}  *(auth)*

**Response 204**

---

## 📊 Estimates

### GET /api/features/{feature}/estimate  *(auth)*

**Response 200 :**
```json
{
  "data": {
    "id": 1, "feature_id": 1,
    "hourly_rate": 50, "total_hours": 16, "total_amount": 800,
    "created_at": "..."
  }
}
```
*Response 404 si pas d'estimate*

### PUT /api/estimates/{estimate}  *(auth)*

**Body :**
```json
{ "hourly_rate": 65, "total_hours": 20 }
```
*total_amount recalculé automatiquement*

**Response 200**

---

## 🤖 IA — Génération d'estimation

### POST /api/projects/{project}/generate-estimate  *(auth)*

**Body :**
```json
{
  "prompt": "Je veux un site e-commerce complet avec catalogue produits, panier, paiement Stripe, dashboard admin, et espace client. Le site doit être responsive et optimise SEO."
}
```

**Response 202 :**
```json
{ "message": "Estimation en cours de generation." }
```

> ⚠️ **Important :** Le job est asynchrone. Tu dois avoir le worker qui tourne :
> ```bash
> php artisan queue:work
> ```
> Ensuite vérifie les resultats avec :
> - `GET /api/projects/{project}/features`
> - `GET /api/features/{feature}/estimate`

---

## 📄 Devis

### GET /api/devis  *(auth)*

**Response 200 :**
```json
{
  "data": [
    {
      "id": 1,
      "client": {
        "company_name": "Acme Corp",
        "email": "contact@acme.com",
        "phone": "+212600000000"
      },
      "project": {
        "name": "Site e-commerce",
        "description": "Site de vente en ligne..."
      },
      "features": null,
      "total_amount": 3200,
      "conditions": null,
      "status": "draft",
      "pdf_path": null,
      "created_at": "..."
    }
  ],
  "meta": { "current_page": 1, "per_page": 15, "total": 1 }
}
```

### POST /api/devis  *(auth)*

**Body :**
```json
{
  "client_id": 1,
  "project_id": 1,
  "conditions": "Paiement : 50% a la commande, 50% a la livraison"
}
```

**Response 201**

### GET /api/devis/{devis}  *(auth)*

**Response 200 :**
```json
{
  "data": {
    "id": 1,
    "client": { "company_name": "Acme Corp", "email": "contact@acme.com", "phone": "+212600000000" },
    "project": { "name": "Site e-commerce", "description": "..." },
    "features": [
      {
        "name": "Page d'accueil", "description": "...",
        "complexity": "moyen",
        "hourly_rate": 50, "total_hours": 16, "total_amount": 800
      }
    ],
    "total_amount": 3200,
    "conditions": "Paiement : 50% a la commande, 50% a la livraison",
    "status": "draft",
    "pdf_path": null,
    "created_at": "..."
  }
}
```

### PUT /api/devis/{devis}  *(auth)*

**Body :**
```json
{
  "status": "sent",
  "conditions": "Paiement comptant"
}
```
*Status : `draft`, `sent`, `accepted`, `refused`*

### DELETE /api/devis/{devis}  *(auth)*

**Response 204**

### GET /api/devis/{devis}/pdf  *(auth)*

**Response 200 :** Téléchargement PDF

---

## 🏁 Ordre de test recommandé

```
 1. POST  /api/register                  (→ copier le token)
 2. POST  /api/login                     (→ copier le token)
 3. PUT   /api/freelance/profile          (definir taux_horaire)
 4. POST  /api/clients                    (creer un client)
 5. POST  /api/projects                   (creer un projet)
 6. POST  /api/projects/{id}/features     (ajouter des features)
 7. POST  /api/projects/{id}/generate-estimate  (lancer IA)
 8. GET   /api/features/{id}/estimate     (verifier l'estimate)
 9. POST  /api/devis                      (generer le devis)
10. GET   /api/devis/{id}/pdf             (telecharger PDF)
```

