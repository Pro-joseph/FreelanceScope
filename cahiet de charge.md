FreelanceScope — Périmètre V1 (MVP)
Projet : FreelanceScope Version : 1.1 — Palier 1 uniquement Date : Juillet 2026 Auteur : Joseph
________________________________________
1. Contexte et problématique
Les freelances rencontrent souvent des difficultés à estimer correctement le coût et la durée de leurs projets. Une mauvaise estimation entraîne des dépassements de budget, des retards et une rentabilité réduite.
Aujourd'hui, beaucoup utilisent des fichiers Excel, Notion ou des documents Word pour suivre leurs projets. Ces outils ne permettent pas de capitaliser sur l'expérience acquise ni d'obtenir une estimation intelligente basée sur les projets précédents.
Problèmes identifiés :
•	Mauvaise estimation des projets.
•	Sous-évaluation du temps de travail.
•	Difficulté à fixer un prix juste.
•	Scope creep (ajout de fonctionnalités non prévues).
•	Historique des projets dispersé.
•	Devis réalisés manuellement.
•	Difficulté à retrouver des projets similaires.
FreelanceScope vise à centraliser la gestion des clients, des projets et des devis tout en intégrant une assistance IA pour améliorer les estimations. L'estimation assistée par IA est la fonctionnalité différenciante du produit : elle n'est pas une option secondaire mais le cœur de la proposition de valeur, et doit donc être présente dès la V1.
________________________________________
2. Objectif du V1
Livrer une chaîne de valeur complète et utilisable : de la création d'un projet jusqu'à l'envoi d'un devis, avec l'IA au centre du flux d'estimation. Pas de gestion du temps, pas d'historique comparatif, pas de notifications.
________________________________________
3. Entités (7)
Entité	Rôle
User	Authentification et multi-tenant (freelance_id)
Client	Rattaché à un freelance
Project	Entité pivot
Project Feature	Unité de découpage pour l'estimation
AI Analysis	Trace brute de l'appel IA (input/output)
Estimate	Résultat structuré de l'IA, par feature
Quotation	Devis final
Propriétés
User (users) : id, nom, prénom, email, mot_de_passe (hashé), rôle (admin / freelance / client), téléphone, statut (actif / inactif), date_creation, derniere_connexion
Client (clients) : id, freelance_id, nom, entreprise, email, téléphone, adresse, secteur_activite, notes, date_creation
Project (projects) : id, client_id, freelance_id, titre, description, technologies, deadline, budget_estime, statut (brouillon / en_cours / termine / archive), date_creation, date_debut, date_fin
Project Feature (project_features) : id, project_id, nom, description, priorite (haute / moyenne / basse), difficulte_estimee, duree_estimee, statut (a_faire / en_cours / termine), ordre
difficulte_estimee et duree_estimee sur project_features = valeurs finales ajustées par le freelance (utilisées pour l'affichage et les calculs). Ce sont celles que le freelance peut modifier manuellement après la proposition IA.
AI Analysis (ai_analyses) : id, project_id, input_text, output_json, modele_utilise, statut (succes / echec), date_creation
Estimate (estimates) : id, project_id, feature_id, duree_estimee_ia, difficulte, cout_estime, elements_manquants, questions_suggerees, date_generation
duree_estimee_ia et difficulte sur estimates = valeurs brutes proposées par l'IA, conservées comme source de vérité historique (non modifiées). Si le freelance ajuste une valeur, la correction va dans project_features, pas ici — ça permet de comparer plus tard "ce que l'IA a proposé" vs "ce que le freelance a validé".
Quotation (quotations) : id, project_id, numero_devis, version, statut (brouillon / envoye / accepte / refuse), montant_total, conditions, date_creation, date_envoi, date_reponse
Pas de estimate_id sur quotations : un devis agrège toutes les estimates du projet (via project_id → project_features → estimates), pas une seule. montant_total est calculé en sommant les cout_estime de toutes les features du projet. Pas de quotation_items en V1 : le détail ligne par ligne n'existe pas, seul le total est stocké. Pas de génération PDF en V1 : le devis est consultable directement dans l'interface (vue web), pas de fichier téléchargeable.
________________________________________
4. Flux V1
Project créé → freelance décrit le besoin (input_text)
→ appel OpenAI API → stocké dans ai_analyses
→ parsing du output_json → génère/peuple project_features + estimates
→ freelance ajuste manuellement si besoin (project_features)
→ génération du Quotation → montant_total = somme des estimates du projet
→ envoi au client → acceptation / refus
________________________________________
5. User Stories V1
Administrateur
Créer un compte utilisateur
•	Saisi : nom, prénom, email, mot_de_passe, rôle, téléphone
•	Auto : id, statut = actif, date_creation
Modifier un utilisateur
•	Modifiables : nom, prénom, email, téléphone, rôle
Désactiver un compte
•	Champ : statut (actif → inactif)
Consulter la liste des utilisateurs
•	Colonnes : nom, prénom, email, rôle, statut, derniere_connexion
•	Filtres : rôle, statut
Freelance — Authentification
Créer un compte
•	Saisi : nom, prénom, email, mot_de_passe, confirmation_mot_de_passe
•	Auto : id, rôle = freelance, statut = actif, date_creation
Se connecter
•	Saisi : email, mot_de_passe
•	Mis à jour : derniere_connexion
Réinitialiser le mot de passe
•	Saisi : email → mot_de_passe modifié via token temporaire
Freelance — Tableau de bord
Consulter le dashboard
•	Widgets : projets_actifs (count), devis_en_attente (count), revenus_estimes (somme)
Freelance — Gestion des clients
Ajouter un client
•	Saisi : nom, entreprise, email, téléphone, adresse, secteur_activite, notes
•	Auto : freelance_id, date_creation
Modifier un client
•	Modifiables : nom, entreprise, email, téléphone, adresse, secteur_activite, notes
Supprimer un client
•	Contrainte : bloqué ou soft-delete si projets liés existants
Consulter l'historique des projets d'un client
•	Affiché : liste des projects (titre, statut, budget_estime, date_debut, date_fin)
Freelance — Gestion des projets
Créer un projet
•	Saisi : titre, description, client_id, technologies, deadline, budget_estime
•	Auto : id, freelance_id, statut = brouillon, date_creation
Modifier un projet
•	Modifiables : titre, description, technologies, deadline, budget_estime, statut
Suivre l'avancement
•	Champ : statut (brouillon → en_cours → termine), % calculé depuis project_features.statut
Archiver un projet terminé
•	Champ : statut = archive, date_fin auto
Freelance — Gestion des fonctionnalités
Découper un projet en fonctionnalités
•	Saisi par feature : nom, description, priorite
•	Auto : project_id, ordre, statut = a_faire
Modifier une fonctionnalité
•	Modifiables : nom, description, priorite, statut, ordre, difficulte_estimee, duree_estimee
Attribuer une priorité
•	Champ : priorite (haute / moyenne / basse)
Freelance — Estimation assistée par IA (cœur du V1)
Décrire le besoin pour générer une estimation
•	Entrée : ai_analyses.input_text
•	Sortie : ai_analyses.output_json → répercuté sur project_features + estimates
IA identifie les fonctionnalités nécessaires
•	Sortie : project_features générées (nom, description, priorite suggérée)
IA estime la durée par fonctionnalité
•	Champ : estimates.duree_estimee_ia, estimates.difficulte (valeurs brutes IA)
•	Reporté ensuite sur : project_features.duree_estimee, project_features.difficulte_estimee (valeurs ajustables)
IA détecte les éléments manquants
•	Champ : estimates.elements_manquants
IA détecte les risques de scope creep
•	Sortie : indicateur de risque rattaché à project_id
Freelance — Génération de devis
Générer un devis automatiquement
•	Généré : quotations.numero_devis, version = 1, montant_total (somme des estimates.cout_estime du projet), statut = brouillon
•	Consultation : vue web du devis (pas de PDF en V1)
Personnaliser le devis avant envoi
•	Modifiable : conditions, montant_total
Conserver les versions du devis
•	Champ : version (incrémentée à chaque modification post-envoi)
Client
Consulter un devis
•	Affiché : numero_devis, montant_total, conditions (vue web)
Accepter ou refuser un devis
•	Champ : quotations.statut (accepte / refuse), date_reponse
Consulter l'avancement du projet
•	Affiché : projects.statut, % calculé, project_features (statut)
________________________________________
6. Fonctionnalités V1
•	Authentification : Connexion, Inscription, Réinitialisation mot de passe
•	Tableau de bord : Projets actifs, Revenus estimés, Devis envoyés
•	Gestion des clients : Ajouter, Modifier, Supprimer, Historique
•	Gestion des projets : Création, Description, Technologies, Deadline, Budget, Statut
•	Gestion des fonctionnalités : Découpage en features, priorisation
•	Estimation assistée par IA : découpage, difficulté, durée, éléments manquants, questions à poser
•	Génération de devis : vue web automatique (Description, Fonctionnalités, Prix, Conditions)
Explicitement hors V1 : gestion du temps (chronomètre), historique comparatif / projets similaires, statistiques de productivité, notifications, quotation_items détaillés, génération PDF.
________________________________________
7. Exigences non fonctionnelles
•	Temps de réponse < 2s (hors appel IA, traité en queue asynchrone)
•	Laravel Sanctum, chiffrement des mots de passe, protection CSRF, validation des données
•	Responsive Desktop / Mobile, compatible Chrome / Firefox / Edge / Safari
________________________________________
8. Architecture technique
•	Backend : Laravel 13
•	Frontend : Angular
•	Base de données : MySQL
•	Authentification : Laravel Sanctum
•	IA : OpenAI API — appel isolé via service dédié (AIAnalysisService) + Job en queue
________________________________________
9. Maquettes V1
•	Connexion
•	Dashboard
•	Liste des clients / Détail client
•	Liste des projets / Détail projet
•	Assistant IA
•	Génération de devis
________________________________________
10. Cardinalités (relations clés)
Association	Cardinalités
User (freelance) — Client	(1,n) — (1,1)
Client — Project	(1,n) — (1,1)
Project — Project Feature	(1,n) — (1,1)
Project — AI Analysis	(1,n) — (1,1)
Project Feature — Estimate	(1,n) — (1,1)
Project — Quotation	(1,n) — (1,1)
Toutes les relations du V1 sont en 1,N — aucune relation N,N, donc aucune table pivot nécessaire à ce stade.
________________________________________
11. Critères d'acceptation V1
•	Les utilisateurs peuvent gérer leurs clients.
•	Les projets peuvent être créés et modifiés.
•	Les fonctionnalités peuvent être estimées via l'IA.
•	L'IA produit une estimation exploitable et traçable (ai_analyses + estimates).
•	Les devis sont générés correctement en agrégeant les estimates du projet, et consultables en ligne.
•	Le client peut consulter et accepter/refuser un devis.
•	L'application est sécurisée et responsive.

