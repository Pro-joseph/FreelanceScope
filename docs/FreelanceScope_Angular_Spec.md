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
  company_name: string;
  email?: string;
  phone?: string;
  projects_count?: number;
  created_at: string;
}

// src/app/core/models/project.model.ts
export interface Project {
  id: number;
  client_id: number;
  name: string;
  description?: string;
  status: string;
  features_count?: number;
  created_at: string;
  client?: Client;
  features?: ProjectFeature[];
}

// src/app/core/models/project-feature.model.ts
export type Complexity = 'simple' | 'moyen' | 'complexe';

export interface ProjectFeature {
  id: number;
  project_id: number;
  name: string;
  description?: string;
  complexity?: Complexity;
  created_at: string;
  estimate?: Estimate;
}

// src/app/core/models/ai-analysis.model.ts
export interface AiAnalysis {
  id: number;
  project_id: number;
  prompt: string;
  response?: string;
  model?: string;
  tokens_used?: number;
  created_at: string;
  updated_at: string;
}

// src/app/core/models/estimate.model.ts
export interface Estimate {
  id: number;
  feature_id: number;
  hourly_rate: number;
  total_hours: number;
  total_amount: number;
  created_at: string;
}

// src/app/core/models/devis.model.ts
export type DevisStatut = 'draft' | 'sent' | 'accepted' | 'refused';

export interface Devis {
  id: number;
  client: {
    company_name: string;
    email: string;
    phone: string;
  };
  project: {
    name: string;
    description: string;
  };
  features?: Array<{
    name: string;
    description: string;
    complexity: string;
    hourly_rate?: number;
    total_hours?: number;
    total_amount?: number;
  }>;
  total_amount: number;
  conditions?: string;
  status: DevisStatut;
  pdf_path?: string;
  created_at: string;
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
| `/dashboard` | Tableau de bord (clients_count, projects_count, devis_count) | freelance |
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
| `/admin/freelances` | Liste des freelances (admin) | admin |
| `/admin/freelances/:id/edit` | Modifier un freelance (admin) | admin |

*(Pas de page côté "client" — accès uniquement via PDF envoyé hors app.)*

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
| role | select (admin/freelance) | — |

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
| nom | text | — |
| prenom | text | — |
| email | text | email |
| telephone | text | — |
| taux_horaire | number | min(0) |

### ClientForm — `/clients/new`, `/clients/:id/edit`
| Champ | Type | Validators |
|---|---|---|
| company_name | text | required |
| email | text | email |
| phone | text | — |

### ProjectForm — `/projects/new`, `/projects/:id/edit`
| Champ | Type | Validators |
|---|---|---|
| client_id | select (liste clients) | required |
| name | text | required |
| description | textarea | — |
| status | select (draft/...) | — (édition uniquement) |

### AIEstimationForm — `/projects/:id/ai-estimation`
| Champ | Type | Validators |
|---|---|---|
| prompt | textarea | required, minLength(20) |

*(Ce formulaire déclenche `AIController` → job en queue.)*

### ProjectFeatureForm — ajout manuel ou ajustement post-IA
| Champ | Type | Validators |
|---|---|---|
| name | text | required |
| description | textarea | — |
| complexity | select (simple/moyen/complexe) | — |

### DevisGenerateForm — `/projects/:id/devis/new`
| Champ | Type | Validators |
|---|---|---|
| client_id | hidden | required |
| project_id | hidden | required |
| conditions | textarea | — |

### DevisEditForm — `/projects/:id/devis/:devisId/edit`
| Champ | Type | Validators |
|---|---|---|
| conditions | textarea | — |
| total_amount | number | min(0) |
| status | select (draft/sent/accepted/refused) | — |

### DevisStatusForm — mise à jour manuelle après réponse client
| Champ | Type | Validators |
|---|---|---|
| status | select (accepted/refused) | required |

### FreelanceAdminForm — `/admin/freelances/:id/edit` (admin uniquement)
| Champ | Type | Validators |
|---|---|---|
| nom | text | — |
| prenom | text | — |
| email | text | email |
| telephone | text | — |
| taux_horaire | number | min(0) |

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
│   └── admin/              (freelances list/edit)
└── shared/
    └── components/         (ex: status-badge, priority-tag)
```
