# FreelanceScope — Périmètre V1 (MVP)

**Projet :** FreelanceScope
**Version :** 1.3
**Date :** Juillet 2026
**Auteur :** Joseph

> Le modèle de données (entités, MCD, MLD) est documenté séparément dans `FreelanceScope_MCD_MLD.md`. Ce document couvre uniquement le contexte produit, les user stories, la stack technique et les packages.

---

## 1. Contexte et objectif

FreelanceScope est une plateforme destinée aux freelances pour piloter leurs projets clients de bout en bout : gestion des clients, découpage d'un projet en fonctionnalités, estimation assistée par IA (durée, difficulté, coût), et génération d'un devis.

**Objectif du V1 :** livrer une chaîne de valeur complète et utilisable, de la création d'un projet jusqu'à l'envoi d'un devis, avec l'IA au centre du flux d'estimation.

**Explicitement hors V1 :** gestion du temps (chronomètre), historique comparatif entre projets, statistiques de productivité, notifications automatiques, détail ligne par ligne du devis.

---

## 2. Fonctionnalités V1

- **Authentification** : Connexion, Inscription, Réinitialisation mot de passe (freelances et admins uniquement)
- **Tableau de bord** : projets actifs, revenus estimés, devis envoyés
- **Gestion des clients** : ajouter, modifier, supprimer, consulter l'historique
- **Gestion des projets** : création, description, technologies, deadline, budget, suivi de statut, archivage
- **Gestion des fonctionnalités** : découpage d'un projet en features, priorisation
- **Estimation assistée par IA** : découpage automatique, estimation de difficulté/durée, détection d'éléments manquants, questions à poser au client, détection de risques de scope creep
- **Génération de devis** : consultation en ligne et **export PDF téléchargeable**, personnalisation avant envoi, gestion des versions, envoi et suivi (accepté/refusé)

---

## 3. User Stories V1

### Administrateur

**Créer un compte utilisateur**
- Saisit les informations de base d'un utilisateur et lui attribue un rôle.

**Modifier un utilisateur**
- Peut mettre à jour les informations et le rôle d'un utilisateur existant.

**Désactiver un compte**
- Peut désactiver l'accès d'un utilisateur sans le supprimer.

**Consulter la liste des utilisateurs**
- Visualise l'ensemble des comptes, filtrables par rôle et par statut.

### Freelance — Authentification

**Créer un compte**
- S'inscrit avec ses informations personnelles et un mot de passe.

**Se connecter**
- Accède à son espace via email et mot de passe.

**Réinitialiser le mot de passe**
- Demande une réinitialisation via son email en cas d'oubli.

### Freelance — Tableau de bord

**Consulter le dashboard**
- Visualise en un coup d'œil ses projets actifs, ses devis en attente, et ses revenus estimés.

**Définir son taux horaire**
- Renseigne son taux horaire dans son profil ; ce taux sert de base au calcul automatique du coût de chaque estimation IA.

### Freelance — Gestion des clients

**Ajouter un client**
- Enregistre une fiche client (coordonnées, entreprise, secteur d'activité, notes).

**Modifier un client**
- Met à jour les informations d'un client existant.

**Supprimer un client**
- Peut supprimer un client, sauf si des projets y sont encore rattachés.

**Consulter l'historique des projets d'un client**
- Visualise tous les projets associés à un client donné.

### Freelance — Gestion des projets

**Créer un projet**
- Décrit un nouveau projet et le rattache à un client existant.

**Modifier un projet**
- Met à jour les informations d'un projet (description, technologies, deadline, budget, statut).

**Suivre l'avancement**
- Visualise la progression du projet, calculée à partir de l'état de ses fonctionnalités.

**Archiver un projet terminé**
- Marque un projet comme terminé et l'archive.

### Freelance — Gestion des fonctionnalités

**Découper un projet en fonctionnalités**
- Ajoute manuellement des fonctionnalités à un projet avec nom, description et priorité.

**Modifier une fonctionnalité**
- Ajuste les informations, la priorité ou l'estimation d'une fonctionnalité.

**Attribuer une priorité**
- Classe chaque fonctionnalité par priorité (haute / moyenne / basse).

### Freelance — Estimation assistée par IA (cœur du V1)

**Décrire le besoin pour générer une estimation**
- Décrit le besoin en langage libre ; l'IA analyse le texte et retourne une proposition structurée.

**IA identifie les fonctionnalités nécessaires**
- L'IA propose automatiquement une liste de fonctionnalités à partir de la description du besoin.

**IA estime la durée et la difficulté par fonctionnalité**
- L'IA fournit une estimation initiale de durée et de difficulté, que le freelance peut ensuite ajuster manuellement. Le **coût n'est pas estimé par l'IA** : il est calculé automatiquement par l'application à partir de la durée estimée et du taux horaire du freelance.

**IA détecte les éléments manquants**
- L'IA signale les informations manquantes ou ambiguës dans le besoin décrit.

**IA détecte les risques de scope creep**
- L'IA alerte sur les risques de dérive de périmètre du projet.

### Freelance — Génération de devis

**Générer un devis automatiquement**
- Génère un devis à partir des estimations validées, avec un montant total calculé automatiquement.

**Personnaliser le devis avant envoi**
- Peut ajuster les conditions et le montant avant l'envoi au client.

**Télécharger le devis en PDF**
- Peut télécharger le devis au format PDF, en plus de la consultation en ligne.

**Conserver les versions du devis**
- Chaque modification post-envoi crée une nouvelle version du devis.

### Client *(accès sans création de compte, via lien sécurisé)*

**Consulter un devis**
- Consulte le devis envoyé (détails et montant), avec possibilité de le télécharger en PDF.

**Accepter ou refuser un devis**
- Répond au devis directement depuis le lien reçu.

**Consulter l'avancement du projet**
- Suit la progression du projet associé au devis.

---

## 4. Stack technique

| Composant | Choix |
|---|---|
| Backend | Laravel 13 |
| Frontend | Angular |
| Base de données | MySQL |
| Authentification | Laravel Sanctum |
| IA | OpenAI API, appel isolé via un service dédié + traitement asynchrone en queue |
| PDF | Génération à la demande avec mise en cache |
| Conteneurisation | Docker / Docker Compose |
| CI/CD | GitHub Actions |

### Exigences non fonctionnelles
- Temps de réponse < 2s (hors appel IA, traité en queue asynchrone)
- Chiffrement des mots de passe, protection CSRF, validation systématique des données
- Accès client aux devis sécurisé par lien signé à durée limitée, sans compte
- Responsive Desktop / Mobile, compatible Chrome / Firefox / Edge / Safari

---

## 5. Packages

### Backend (Composer)
- `laravel/framework` (^13) — framework principal
- `laravel/sanctum` — authentification API (freelances/admins)
- `barryvdh/laravel-dompdf` — génération du PDF du devis
- `openai-php/client` — appel à l'API OpenAI pour l'estimation IA
- `pest` + `pestphp/pest-plugin-laravel` — tests

### Frontend (npm)
- `@angular/core`, `@angular/router`, `@angular/forms`, `@angular/common` — cœur Angular
- `@angular/common/http` — appels API vers le backend Laravel
- `rxjs` — gestion des flux réactifs (déjà inclus avec Angular)
- `tailwindcss` — styling (à confirmer selon direction UI retenue)

---

## 6. Critères d'acceptation V1

- Les freelances peuvent gérer leurs clients de bout en bout.
- Les projets peuvent être créés, modifiés et suivis.
- Les fonctionnalités d'un projet peuvent être estimées via l'IA, avec ajustement manuel possible.
- Les devis sont générés correctement, consultables en ligne et téléchargeables en PDF.
- Le client peut consulter et répondre à un devis via un lien sécurisé, sans création de compte.
- L'application est sécurisée et responsive.