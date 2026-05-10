# SportFuel

Application web de nutrition intelligente pour sportifs, developpee dans le cadre du module **Projet Technologies Web (2A)** a Esprit - Annee universitaire 2025/2026.

## Description

**SportFuel** est une plateforme web qui permet aux sportifs de gerer leur alimentation de maniere personnalisee en fonction de leur activite physique. L'application propose :

- Des **plans alimentaires** adaptes a chaque profil sportif (marathon, musculation, yoga, natation, cyclisme)
- Un **catalogue d'aliments bio et locaux** tunisiens avec suivi des calories et de l'impact CO2
- La **generation automatique de listes de courses** a partir du plan alimentaire
- Un **suivi des entrainements** avec calcul des depenses energetiques
- Un **Back Office** pour la gestion complete des utilisateurs, plans, aliments et coaches
- Un **Front Office sportif** avec dashboard personnalis�

## Table des Mati�res

- [Technologies utilis�es](#technologies-utilis�es)
- [Installation](#installation)
- [Structure du projet](#structure-du-projet)
- [Architecture MVC](#architecture-mvc)
- [Fonctionnalit�s](#fonctionnalit�s)
- [Membres du groupe](#membres-du-groupe)
- [Contributions](#contributions)
- [Licence](#licence)

## Technologies utilis�es

- **HTML5 / CSS3** � Structure et design des pages
- **PHP 8.5 (PDO)** � Logique serveur et acc�s � la base de donn�es
- **MySQL** � Base de donn�es relationnelle
- **Architecture MVC** � S�paration Mod�le / Vue / Contr�leur
- **Git & GitHub** � Gestion de versions et collaboration

## Installation

### 1. Cloner le projet

```bash
git clone https://github.com/Ines-sta/Esprit-PW-2A19-2026-SportFuel.git
cd Esprit-PW-2A19-2026-SportFuel
```

### 2. Configurer WAMP

1. T�l�chargez et installez [WampServer](https://www.wampserver.com/).
2. Copiez le dossier `Esprit-PW-2A19-2026-SportFuel` dans `C:\wamp64\www\`.
3. Lancez WampServer � l'ic�ne doit �tre **verte** (Apache + MySQL actifs).

### 3. Initialiser la base de donn�es

Ouvrez dans votre navigateur :

```
http://localhost/Esprit-PW-2A19-2026-SportFuel/init_db.php
```

La base de donn�es, toutes les tables et un compte administrateur par d�faut seront cr��s automatiquement.

> **Compte admin par d�faut :** `admin@sportfuel.tn` / `admin123`
> Supprimez `init_db.php` apr�s l'initialisation en production.

### 4. Acc�der � l'application

```
http://localhost/Esprit-PW-2A19-2026-SportFuel/
```

## Structure du projet

```
Esprit-PW-2A19-2026-SportFuel/
+-- Controller/
�   +-- core/
�   �   +-- role_context.php          # Contexte de r�le (Admin/Coach/Sportif)
�   +-- shared/
�   �   +-- db_settings.php           # Param�tres de connexion DB
�   +-- auth/
�   �   +-- AuthController.php
�   +-- training/
�   �   +-- EntrainementController.php
�   �   +-- ExerciceSeanceController.php
�   +-- nutrition/
�   �   +-- PlanAlimentaireController.php
�   �   +-- RepasController.php
�   +-- users/
�   �   +-- AdminController.php
�   �   +-- ProfilController.php
�   +-- coach/
�   +-- AdminDashboardController.php
�   +-- CoachDashboardController.php
+-- Model/
�   +-- training/
�   �   +-- Entrainement.php
�   �   +-- ExerciceSeance.php
�   +-- nutrition/
�   �   +-- Aliment.php
�   �   +-- PlanAlimentaire.php
�   �   +-- Repas.php
�   �   +-- CourseAdmin.php
�   �   +-- CourseUser.php
�   +-- users/
+-- View/
�   +-- partials/
�   �   +-- backoffice_sidebar.php
�   �   +-- frontoffice_sidebar.php
�   +-- auth/
�   �   +-- index.html                # Landing page
�   �   +-- connexion.html            # Connexion
�   �   +-- inscription.html          # Inscription
�   +-- training/
�   �   +-- admin_programs.php        # BO � Gestion des programmes
�   �   +-- admin_sessions.php        # BO � Gestion des s�ances
�   �   +-- user_planning.php         # FO � Planification sportif
�   �   +-- user_history.php          # FO � Historique sportif
�   +-- dashboard/
�   �   +-- admin.php                 # Dashboard Admin
�   +-- aliments/
�   +-- courses/
�   +-- coach/
�   +-- plans/
�   +-- users/
+-- includes/                         # Endpoints API REST (JSON)
�   +-- get_programmes.php
�   +-- get_sportifs.php
�   +-- get_coaches.php
�   +-- add_entrainement.php
�   +-- update_entrainement.php
�   +-- delete_entrainement.php
�   +-- list_entrainements.php
�   +-- add_exercice_seance.php
�   +-- update_exercice_seance.php
�   +-- delete_exercice_seance.php
�   +-- list_exercices_seance.php
+-- public/
�   +-- css/
�   �   +-- style.css
�   �   +-- entrainement.css
�   +-- js/
�   �   +-- api.js
�   �   +-- validation.js
�   +-- images/
+-- config/
�   +-- database.php                  # Classe Database (PDO)
+-- index.php                         # Routeur principal
+-- init_db.php                       # Initialisation automatique DB
+-- README.md
```

## Architecture MVC

L'application suit une architecture MVC centralis�e avec s�paration stricte des responsabilit�s :

### Mod�le (`Model/`)
Classes m�tier organis�es par domaine fonctionnel. Chaque mod�le encapsule l'acc�s PDO et la logique de donn�es de son entit�.

### Vue (`View/`)
Templates PHP/HTML organis�s par domaine. Les partials (sidebars, headers) sont r�utilis�s pour s�parer les espaces Back Office et Front Office sans duplication de code.

### Contr�leur (`Controller/`)
Orchestration des requ�tes, logique m�tier et gestion des r�les. Le fichier `core/role_context.php` centralise la d�tection du r�le session et les gardes d'acc�s.

### API (`includes/`)
Endpoints REST l�gers retournant du JSON, consomm�s en AJAX par les vues. Chaque endpoint valide les entr�es, d�l�gue au contr�leur correspondant et retourne une r�ponse normalis�e.

### Routeur (`index.php`)
Point d'entr�e unique qui dispatche vers la vue appropri�e selon les param�tres `page` et `view`, en appliquant les gardes de r�le.

## Fonctionnalit�s

| Module | Description |
|---|---|
| **Authentification** | Inscription, connexion, gestion de session et redirection par r�le |
| **Gestion des utilisateurs** | Profils sportifs, r�les (Admin / Coach / Sportif), statuts |
| **Plans alimentaires** | Cr�ation et suivi de plans nutritionnels personnalis�s par semaine |
| **Aliments & Courses** | Catalogue bio/local tunisien avec calories, impact CO2 et g�n�ration de listes de courses |
| **Entra�nements** | Programmes personnalis�s, s�ances, exercices, suivi de progression |
| **Espace coach** | Gestion des sportifs assign�s, cr�ation de programmes personnalis�s par sportif |
| **Dashboard admin** | M�triques globales, gestion des utilisateurs, assignments coach-sportifs |

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
3. **Cr�ez** une branche :
   ```bash
   git checkout -b ma-fonctionnalite
   ```
4. **Commitez** et poussez :
   ```bash
   git add .
   git commit -m "Ajout de ma fonctionnalit�"
   git push origin ma-fonctionnalite
   ```
5. **Ouvrez une Pull Request** sur GitHub

## Licence

Ce projet est r�alis� dans un cadre acad�mique � **Esprit** (�cole Sup�rieure Priv�e d'Ing�nierie et de Technologies). Il est destin� � des fins �ducatives.
