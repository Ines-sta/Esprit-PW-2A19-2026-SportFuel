# SportFuel

Application web de nutrition intelligente pour sportifs, développée dans le cadre du module **Projet Technologies Web (2A)** à Esprit - Année universitaire 2025/2026.

## Description

**SportFuel** est une plateforme web qui permet aux sportifs de gérer leur alimentation de manière personnalisée en fonction de leur activité physique. L'application propose :

- Des **plans alimentaires** adaptés à chaque profil sportif (marathon, musculation, yoga, natation, cyclisme)
- Un **catalogue d'aliments bio et locaux** tunisiens avec suivi des calories et de l'impact CO2
- La **génération automatique de listes de courses** à partir du plan alimentaire
- Un **suivi des entraînements** avec calcul des dépenses énergétiques
- Un **Back Office** pour la gestion complète des utilisateurs, plans, aliments et coaches
- Un **Front Office sportif** avec dashboard personnalisé

## Table des Matières

- [Technologies utilisées](#technologies-utilisées)
- [Installation](#installation)
- [Structure du projet](#structure-du-projet)
- [Architecture MVC](#architecture-mvc)
- [Fonctionnalités](#fonctionnalités)
- [Membres du groupe](#membres-du-groupe)
- [Contributions](#contributions)
- [Licence](#licence)

## Technologies utilisées

- **HTML5 / CSS3** – Structure et design des pages
- **PHP 8.5 (PDO)** – Logique serveur et accès à la base de données
- **MySQL** – Base de données relationnelle
- **Architecture MVC** – Séparation Modèle / Vue / Contrôleur
- **Git & GitHub** – Gestion de versions et collaboration

## Installation

### 1. Cloner le projet

```bash
git clone https://github.com/Ines-sta/Esprit-PW-2A19-2026-SportFuel.git
cd Esprit-PW-2A19-2026-SportFuel
```

### 2. Configurer WAMP

1. Téléchargez et installez [WampServer](https://www.wampserver.com/).
2. Copiez le dossier `Esprit-PW-2A19-2026-SportFuel` dans `C:\wamp64\www\`.
3. Lancez WampServer – l'icône doit être **verte** (Apache + MySQL actifs).

### 3. Initialiser la base de données

Ouvrez dans votre navigateur :

```
http://localhost/Esprit-PW-2A19-2026-SportFuel/init_db.php
```

La base de données, toutes les tables et un compte administrateur par défaut seront créés automatiquement.

> **Compte admin par défaut :** `admin@sportfuel.tn` / `admin123`
> Supprimez `init_db.php` après l'initialisation en production.

### 4. Accéder à l'application

```
http://localhost/Esprit-PW-2A19-2026-SportFuel/
```

## Structure du projet

```
Esprit-PW-2A19-2026-SportFuel/
+-- Controller/
|   +-- core/
|   |   +-- role_context.php          # Contexte de rôle (Admin/Coach/Sportif)
|   +-- shared/
|   |   +-- db_settings.php           # Paramètres de connexion DB
|   +-- auth/
|   |   +-- AuthController.php
|   +-- training/
|   |   +-- EntrainementController.php
|   |   +-- ExerciceSeanceController.php
|   +-- nutrition/
|   |   +-- PlanAlimentaireController.php
|   |   +-- RepasController.php
|   +-- users/
|   |   +-- AdminController.php
|   |   +-- ProfilController.php
|   +-- coach/
|   +-- AdminDashboardController.php
|   +-- CoachDashboardController.php
+-- Model/
|   +-- training/
|   |   +-- Entrainement.php
|   |   +-- ExerciceSeance.php
|   +-- nutrition/
|   |   +-- Aliment.php
|   |   +-- PlanAlimentaire.php
|   |   +-- Repas.php
|   |   +-- CourseAdmin.php
|   |   +-- CourseUser.php
|   +-- users/
+-- View/
|   +-- partials/
|   |   +-- backoffice_sidebar.php
|   |   +-- frontoffice_sidebar.php
|   +-- auth/
|   |   +-- index.html                # Landing page
|   |   +-- connexion.html            # Connexion
|   |   +-- inscription.html          # Inscription
|   +-- training/
|   |   +-- admin_programs.php        # BO – Gestion des programmes
|   |   +-- admin_sessions.php        # BO – Gestion des séances
|   |   +-- user_planning.php         # FO – Planification sportif
|   |   +-- user_history.php          # FO – Historique sportif
|   +-- dashboard/
|   |   +-- admin.php                 # Dashboard Admin
|   +-- aliments/
|   +-- courses/
|   +-- coach/
|   +-- plans/
|   +-- users/
+-- includes/                         # Endpoints API REST (JSON)
|   +-- get_programmes.php
|   +-- get_sportifs.php
|   +-- get_coaches.php
|   +-- add_entrainement.php
|   +-- update_entrainement.php
|   +-- delete_entrainement.php
|   +-- list_entrainements.php
|   +-- add_exercice_seance.php
|   +-- update_exercice_seance.php
|   +-- delete_exercice_seance.php
|   +-- list_exercices_seance.php
+-- public/
|   +-- css/
|   |   +-- style.css
|   |   +-- entrainement.css
|   +-- js/
|   |   +-- api.js
|   |   +-- validation.js
|   +-- images/
+-- config/
|   +-- database.php                  # Classe Database (PDO)
+-- index.php                         # Routeur principal
+-- init_db.php                       # Initialisation automatique DB
+-- README.md
```

## Architecture MVC

L'application suit une architecture MVC centralisée avec séparation stricte des responsabilités :

### Modèle (`Model/`)
Classes métier organisées par domaine fonctionnel. Chaque modèle encapsule l'accès PDO et la logique de données de son entité.

### Vue (`View/`)
Templates PHP/HTML organisés par domaine. Les partials (sidebars, headers) sont réutilisés pour séparer les espaces Back Office et Front Office sans duplication de code.

### Contrôleur (`Controller/`)
Orchestration des requêtes, logique métier et gestion des rôles. Le fichier `core/role_context.php` centralise la détection du rôle session et les gardes d'accès.

### API (`includes/`)
Endpoints REST légers retournant du JSON, consommés en AJAX par les vues. Chaque endpoint valide les entrées, délègue au contrôleur correspondant et retourne une réponse normalisée.

### Routeur (`index.php`)
Point d'entrée unique qui dispatche vers la vue appropriée selon les paramètres `page` et `view`, en appliquant les gardes de rôle.

## Fonctionnalités

| Module | Description |
|---|---|
| **Authentification** | Inscription, connexion, gestion de session et redirection par rôle |
| **Gestion des utilisateurs** | Profils sportifs, rôles (Admin / Coach / Sportif), statuts |
| **Plans alimentaires** | Création et suivi de plans nutritionnels personnalisés par semaine |
| **Aliments & Courses** | Catalogue bio/local tunisien avec calories, impact CO2 et génération de listes de courses |
| **Entraînements** | Programmes personnalisés, séances, exercices, suivi de progression |
| **Espace coach** | Gestion des sportifs assignés, création de programmes personnalisés par sportif |
| **Dashboard admin** | Métriques globales, gestion des utilisateurs, assignments coach-sportifs |

## Membres du groupe

| Nom | GitHub |
|---|---|
| Ines Sta | [@ines-sta](https://github.com/ines-sta) |
| Maram Bendoulet | [@maram807](https://github.com/maram807) |
| Yassine Bellagha | [@Yassineeee](https://github.com/Yassineeee) |
| Dhya Laabidi | [@dhyaaaa](https://github.com/dhyaaaa) |
| Bayrem Hariz | [@bayremhariz](https://github.com/bayremhariz) |

## Contributions

1. **Fork** le projet sur GitHub
2. **Clonez** votre fork :
   ```bash
   git clone https://github.com/votre-utilisateur/Esprit-PW-2A19-2026-SportFuel.git
   cd Esprit-PW-2A19-2026-SportFuel
   ```
3. **Créez** une branche :
   ```bash
   git checkout -b ma-fonctionnalite
   ```
4. **Commitez** et poussez :
   ```bash
   git add .
   git commit -m "Ajout de ma fonctionnalité"
   git push origin ma-fonctionnalite
   ```
5. **Ouvrez une Pull Request** sur GitHub

## Licence

Ce projet est réalisé dans un cadre académique à **Esprit** (École Supérieure Privée d'Ingénierie et de Technologies). Il est destiné à des fins éducatives.
