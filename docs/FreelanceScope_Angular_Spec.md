# FreelanceScope — Spec Angular (Models, Pages, Formulaires)

Basé sur les 7 entités backend et le cycle fonctionnel (Setup → Auth → Clients → Projets → IA → Devis).

---

## 1. Models TypeScript (interfaces)

```typescript
// src/app/core/models/user.model.ts
export type UserRole = 'admin' | 'freelance';
export type UserStatut = 'actif' | 'inactif';

export interface User {
  id: number;
  nom: string;
  prenom: string;
  email: string;
  role: UserRole;
  telephone?: string;
  statut: UserStatut;
  taux_horaire?: number;
  created_at: string;
  updated_at: string;
}

// src/app/core/models/client.model.ts
export interface Client {
  id: number;
  user_id: number;
  nom: string;
  entreprise?: string;
  email?: string;
  telephone?: string;
  adresse?: string;
  secteur_activite?: string;
  notes?: string;
  created_at: string;
  updated_at: string;
}

// src/app/core/models/project.model.ts
export type ProjectStatut = 'brouillon' | 'en_cours' | 'termine' | 'archive';

export interface Project {
  id: number;
  client_id: number;
  titre: string;
  description?: string;
  technologies?: string;
  deadline?: string;
  budget_estime?: number;
  statut: ProjectStatut;
  date_debut?: string;
  date_fin?: string;
  created_at: string;
  updated_at: string;
  // relations optionnelles chargees selon le endpoint
  client?: Client;
  features?: ProjectFeature[];
  devis?: Devis[];
}

// src/app/core/models/project-feature.model.ts
export type Priorite = 'haute' | 'moyenne' | 'basse';
export type FeatureStatut = 'a_faire' | 'en_cours' | 'termine';

export interface ProjectFeature {
  id: number;
  project_id: number;
  nom: string;
  description?: string;
  priorite: Priorite;
  difficulte_estimee?: string;
  duree_estimee?: number;
  statut: FeatureStatut;
  ordre: number;
  created_at: string;
  updated_at: string;
  estimate?: Estimate;
}

// src/app/core/models/ai-analysis.model.ts
export type AiStatut = 'succes' | 'echec';

export interface AiAnalysis {
  id: number;
  project_id: number;
  input_text: string;
  output_json?: Record<string, unknown>;
  modele_utilise?: string;
  statut: AiStatut;
  created_at: string;
}

// src/app/core/models/estimate.model.ts
export interface Estimate {
  id: number;
  feature_id: number;
  duree_estimee_ia: number;
  difficulte?: string;
  cout_estime: number;
  elements_manquants?: string;
  questions_suggerees?: string;
  created_at: string;
}

// src/app/core/models/devis.model.ts
export type DevisStatut = 'brouillon' | 'envoye' | 'accepte' | 'refuse';

export interface Devis {
  id: number;
  project_id: number;
  numero_devis: string;
  version: number;
  statut: DevisStatut;
  montant_total: number;
  conditions?: string;
  pdf_path?: string;
  date_envoi?: string;
  date_reponse?: string;
  created_at: string;
  updated_at: string;
  project?: Project;
}
```

---

## 2. Pages / Routes

| Route | Page | Accès |
|---|---|---|
| `/login` | Connexion | public |
| `/register` | Inscription | public |
| `/forgot-password` | Demande de réinitialisation | public |
| `/reset-password/:token` | Nouveau mot de passe | public |
| `/dashboard` | Tableau de bord (projets actifs, devis en attente, revenus estimés) | freelance |
| `/profile` | Mon profil (infos + taux horaire) | freelance |
| `/clients` | Liste des clients | freelance |
| `/clients/new` | Ajouter un client | freelance |
| `/clients/:id` | Détail client (infos + historique projets) | freelance |
| `/clients/:id/edit` | Modifier un client | freelance |
| `/projects` | Liste des projets (filtrable par statut/client) | freelance |
| `/projects/new` | Créer un projet | freelance |
| `/projects/:id` | Détail projet (infos + liste features + devis) | freelance |
| `/projects/:id/edit` | Modifier un projet | freelance |
| `/projects/:id/ai-estimation` | Décrire le besoin → lancer l'IA | freelance |
| `/projects/:id/features/:featureId/edit` | Ajuster une fonctionnalité (post-IA) | freelance |
| `/projects/:id/devis/new` | Générer un devis | freelance |
| `/projects/:id/devis/:devisId` | Aperçu devis (web + bouton PDF + bouton statut) | freelance |
| `/projects/:id/devis/:devisId/edit` | Personnaliser avant envoi | freelance |
| `/users` | Liste des utilisateurs (admin) | admin |
| `/users/:id/edit` | Modifier un utilisateur (admin) | admin |

*(Pas de page côté "client" — accès uniquement via PDF envoyé hors app, cf. décision précédente.)*

---

## 3. Formulaires

### AuthLoginForm — `/login`
| Champ | Type | Validators |
|---|---|---|
| email | text | required, email |
| password | password | required |

### AuthRegisterForm — `/register`
| Champ | Type | Validators |
|---|---|---|
| nom | text | required |
| prenom | text | required |
| email | text | required, email |
| password | password | required, minLength(8) |
| password_confirmation | password | required, match(password) |

### AuthForgotPasswordForm — `/forgot-password`
| Champ | Type | Validators |
|---|---|---|
| email | text | required, email |

### AuthResetPasswordForm — `/reset-password/:token`
| Champ | Type | Validators |
|---|---|---|
| password | password | required, minLength(8) |
| password_confirmation | password | required, match(password) |

### ProfileForm — `/profile`
| Champ | Type | Validators |
|---|---|---|
| nom | text | required |
| prenom | text | required |
| telephone | text | — |
| taux_horaire | number | required, min(0) |

### ClientForm — `/clients/new`, `/clients/:id/edit`
| Champ | Type | Validators |
|---|---|---|
| nom | text | required |
| entreprise | text | — |
| email | text | email |
| telephone | text | — |
| adresse | text | — |
| secteur_activite | text | — |
| notes | textarea | — |

### ProjectForm — `/projects/new`, `/projects/:id/edit`
| Champ | Type | Validators |
|---|---|---|
| client_id | select (liste clients) | required |
| titre | text | required |
| description | textarea | — |
| technologies | text | — |
| deadline | date | — |
| budget_estime | number | min(0) |
| statut | select (brouillon/en_cours/termine/archive) | required (édition uniquement) |

### AIEstimationForm — `/projects/:id/ai-estimation`
| Champ | Type | Validators |
|---|---|---|
| input_text | textarea | required, minLength(20) |

*(Ce formulaire déclenche `AIController` → job en queue ; pas de champ montant/coût, l'IA ne les renseigne pas.)*

### ProjectFeatureForm — ajout manuel ou ajustement post-IA
| Champ | Type | Validators |
|---|---|---|
| nom | text | required |
| description | textarea | — |
| priorite | select (haute/moyenne/basse) | required |
| difficulte_estimee | text | — |
| duree_estimee | number | min(0) |
| statut | select (a_faire/en_cours/termine) | required (édition uniquement) |

### DevisGenerateForm — `/projects/:id/devis/new`
Aucun champ saisi — génération automatique (`montant_total` calculé). Bouton d'action uniquement.

### DevisEditForm — `/projects/:id/devis/:devisId/edit`
| Champ | Type | Validators |
|---|---|---|
| conditions | textarea | — |
| montant_total | number | min(0) |

### DevisStatusForm — mise à jour manuelle après réponse WhatsApp
| Champ | Type | Validators |
|---|---|---|
| statut | select (accepte/refuse) | required |
| date_reponse | date | required |

### UserAdminForm — `/users/:id/edit` (admin uniquement)
| Champ | Type | Validators |
|---|---|---|
| nom | text | required |
| prenom | text | required |
| email | text | required, email |
| telephone | text | — |
| role | select (admin/freelance) | required |
| statut | select (actif/inactif) | required |

---

## 4. Structure de dossiers suggérée

```
src/app/
├── core/
│   ├── models/          (interfaces ci-dessus)
│   ├── services/         (ClientService, ProjectService, AuthService, DevisService, AiService)
│   ├── guards/            (auth.guard.ts, role.guard.ts)
│   └── interceptors/      (sanctum-token.interceptor.ts)
├── features/
│   ├── auth/              (login, register, forgot/reset password)
│   ├── dashboard/
│   ├── clients/           (list, form, detail)
│   ├── projects/          (list, form, detail, features, ai-estimation)
│   ├── devis/              (generate, edit, preview)
│   └── users/              (admin only)
└── shared/
    └── components/         (ex: status-badge, priority-tag)
```
