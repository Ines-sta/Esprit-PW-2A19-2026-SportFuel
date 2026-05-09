# SportFuel

Application web de nutrition intelligente pour sportifs, développée dans le cadre du module **Projet Technologies Web (2A)** à Esprit — Année universitaire 2025/2026.

## Description

**SportFuel** est une plateforme web qui permet aux sportifs de gérer leur alimentation de manière personnalisée en fonction de leur activité physique. L'application propose :

- Des **plans alimentaires** adaptés à chaque profil sportif (marathon, musculation, yoga, natation, cyclisme…)
- Un **catalogue d'aliments bio et locaux** tunisiens avec suivi des calories et de l'impact CO₂
- La **génération automatique de listes de courses** à partir du plan alimentaire
- Un **suivi des entraînements** avec calcul des dépenses énergétiques
- Un **Back Office administrateur** pour la gestion complète des utilisateurs, plans, aliments et coachs
- Un **Front Office sportif** avec dashboard personnalisé

## Table des Matières

- [Technologies utilisées](#technologies-utilisées)
- [Installation](#installation)
- [Structure du projet](#structure-du-projet)
- [Fonctionnalités](#fonctionnalités)
- [Guide d'intégration du template](#guide-dintégration-du-template-pour-les-autres-modules)
- [Membres du groupe](#membres-du-groupe)
- [Contributions](#contributions)
- [Licence](#licence)

## Technologies utilisées

- **HTML5 / CSS3** — Structure et design des pages (Front Office & Back Office)
- **PHP (PDO)** — Logique serveur et accès à la base de données
- **MySQL** — Base de données relationnelle
- **Architecture MVC** — Séparation Modèle / Vue / Contrôleur
- **Git & GitHub** — Gestion de versions et collaboration

## Installation

### 1. Cloner le projet

```bash
git clone https://github.com/Ines-sta/Esprit-PW-2A19-2526-SportFuel.git
```

### 2. Configurer WAMP

1. **Installer WAMP** : Téléchargez et installez [WampServer](https://www.wampserver.com/) si ce n'est pas déjà fait.
2. **Placer le projet dans WAMP** : Copiez le dossier `Esprit-PW-2A19-2526-SportFuel` dans `C:\wamp64\www\`.
3. **Démarrer WAMP** : Lancez WampServer. L'icône dans la barre des tâches doit être **verte** (Apache + MySQL actifs).
   - Si l'icône est **orange** ou **rouge**, faites clic gauche → *Redémarrer les services*.

### 3. Base de donnees

Le projet combine maintenant :

- un bootstrap rapide via `init_db.php` pour `utilisateurs`
- un schema consolide dans `database/schema.sql`
- des migrations SQL dans `database/migrations/`

Flux recommande :

1. ajuster `Controller/db_settings.php` si besoin
2. lancer `http://localhost/Esprit-PW-2A19-2526-SportFuel/init_db.php`
3. appliquer `database/schema.sql` pour la structure complete
4. appliquer les migrations de `database/migrations/` sur une base existante si necessaire

### 4. Accéder à l'application

| Page | URL |
|---|---|
| **Landing / portail** | http://localhost/Esprit-PW-2A19-2526-SportFuel/View/index.html |
| **Connexion** | http://localhost/Esprit-PW-2A19-2526-SportFuel/View/connexion.html |
| **Routeur plans** | http://localhost/Esprit-PW-2A19-2526-SportFuel/index.php |
| **Back Office canonique** | http://localhost/Esprit-PW-2A19-2526-SportFuel/BackOffice/index.php |
| **Back Office — Aliments** | http://localhost/Esprit-PW-2A19-2526-SportFuel/BackOffice/controllers/aliment_controller.php |
| **Front Office — Catalogue** | http://localhost/Esprit-PW-2A19-2526-SportFuel/FrontOffice/controllers/aliment_controller.php |

- Option module utilisateurs: placez le projet dans `www`/`htdocs`, demarrez Apache+MySQL, puis lancez `init_db.php` si vous souhaitez initialiser la base automatiquement.

## Etat actuel de l'architecture

La migration en cours a etabli les sources canoniques suivantes :

- `BackOffice/` et `FrontOffice/` pour le runtime principal
- `index.php` pour le routage des plans et repas
- `database/schema.sql` comme source canonique de schema
- `archive/` pour les anciens fichiers preserves pendant la migration

Les dossiers racine `Controller/`, `Model/` et `View/` existent encore surtout pour compatibilite progressive sur certains flux historiques.

## Structure du projet

```
Esprit-PW-2A19-2526-SportFuel/
├── FrontOffice/
│   ├── assets/
│   │   └── css/
│   │       └── style.css
│   ├── controllers/
│   │   └── aliment_controller.php
│   ├── models/
│   │   └── Aliment.php
│   └── views/
│       ├── aliments/
│       │   └── aliments.php
│       └── courses/
│           └── courses.html
├── BackOffice/
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css
│   │   └── js/
│   │       └── validation.js
│   ├── controllers/
│   │   └── aliment_controller.php
│   ├── models/
│   │   └── Aliment.php
│   └── views/
│       ├── aliments/
│       │   └── aliments.php
│       └── courses/
│           └── courses.html
└── README.md
```

## Fonctionnalités

| Module | Description |
|---|---|
| Gestion des utilisateurs | Inscription, connexion, profils sportifs |
| Plans alimentaires | Création et suivi de plans nutritionnels personnalisés |
| Entraînements | Suivi des séances et calcul des calories brûlées |
| **Aliments & Courses** | Catalogue d'aliments bio/locaux avec catégorie, calories, impact CO₂, génération de listes de courses |
| Espace coach | Supervision des sportifs par les coachs |

## Membres du groupe

| Nom | GitHub |
|---|---|
| Ines Sta | [@ines-sta](https://github.com/ines-sta) |
| Maram Bendoulet | [@maram807](https://github.com/maram807) |
| Yassine Bellagha | [@Yassineeee](https://github.com/Yassineeee) |
| Dhya Laabidi | [@dhyaaaa](https://github.com/dhyaaaa) |
| Bayrem Hariz | [@bayremhariz](https://github.com/bayremhariz) |

## Contributions

Nous remercions tous ceux qui ont contribué à ce projet !

Si vous souhaitez contribuer, suivez les étapes ci-dessous pour faire un **fork**, créer une nouvelle branche et soumettre une **pull request** :

1. **Fork le projet** : Cliquez sur le bouton **Fork** sur la page GitHub du projet.
2. **Clonez votre fork** :
   ```bash
   git clone https://github.com/votre-utilisateur/Esprit-PW-2A19-2526-SportFuel.git
   cd Esprit-PW-2A19-2526-SportFuel
   ```
3. **Créez une branche** :
   ```bash
   git checkout -b ma-fonctionnalite
   ```
4. **Commitez vos modifications** :
   ```bash
   git add .
   git commit -m "Ajout de ma fonctionnalité"
   git push origin ma-fonctionnalite
   ```
5. **Ouvrez une Pull Request** sur GitHub.

## Guide d'intégration du template pour les autres modules

Pour intégrer le template SportFuel dans votre module, suivez ces étapes :

### Front Office

1. Créez vos pages dans `FrontOffice/views/votre-module/` :
   ```
   FrontOffice/views/votre-module/page.html
   ```

2. Utilisez ce squelette HTML :
   ```html
   <!DOCTYPE html>
   <html lang="fr">
   <head>
       <meta charset="UTF-8">
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <title>SportFuel — Votre Page</title>
       <link rel="stylesheet" href="../../assets/css/style.css">
   </head>
   <body>

   <!-- NAVBAR (copier tel quel) -->
   <nav class="navbar">
       <a href="#" class="navbar-brand">
           <div class="navbar-logo">SF</div>
           <span>Sport<em>Fuel</em></span>
       </a>
       <ul class="navbar-links">
           <li><a href="#">Dashboard</a></li>
           <li><a href="#">Mon plan</a></li>
           <li><a href="#">Entraînements</a></li>
           <li><a href="../courses/courses.html">Courses</a></li>
           <li><a href="../aliments/aliments.html">Aliments</a></li>
           <!-- Ajoutez votre lien ici avec class="active" -->
       </ul>
       <div class="navbar-user">IN</div>
   </nav>

   <!-- CONTENU -->
   <div class="main-content">
       <!-- Votre contenu ici -->
   </div>

   <div class="footer">
       &copy; 2026 SportFuel — Nutrition intelligente pour sportifs
   </div>

   </body>
   </html>
   ```

3. **Classes CSS disponibles** (pas besoin de modifier le CSS) :
   - `.stat-cards` + `.stat-card` — Cartes de statistiques en grille
   - `.card` + `.card-header` — Conteneur blanc avec ombre
   - `.food-grid` + `.food-card` — Grille de cartes produit
   - `.search-bar` — Barre de recherche + filtres
   - `.badge .badge-bio` / `.badge-local` — Étiquettes colorées
   - `.btn .btn-primary` / `.btn-success` / `.btn-danger` / `.btn-outline` — Boutons
   - `.course-list` + `.course-item` — Liste à cocher
   - `table` dans `.table-container` — Tableaux de données

### Back Office

1. Créez vos pages dans `BackOffice/views/votre-module/` :
   ```
   BackOffice/views/votre-module/page.html
   ```

2. Utilisez ce squelette HTML :
   ```html
   <!DOCTYPE html>
   <html lang="fr">
   <head>
       <meta charset="UTF-8">
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <title>SportFuel Admin — Votre Page</title>
       <link rel="stylesheet" href="../../assets/css/style.css">
   </head>
   <body>

   <!-- SIDEBAR (copier tel quel) -->
   <aside class="sidebar">
       <a href="#" class="sidebar-brand">
           <div class="sidebar-logo">SF</div>
           <span>Sport<em>Fuel</em></span>
       </a>
       <div class="sidebar-role">ADMIN</div>

       <ul class="sidebar-menu">
           <li><a href="#"><span class="icon">📊</span> Dashboard</a></li>
       </ul>
       <div class="sidebar-section">Modules</div>
       <ul class="sidebar-menu">
           <li><a href="#"><span class="icon">👥</span> Utilisateurs</a></li>
           <li><a href="#"><span class="icon">🍽️</span> Plans alimentaires</a></li>
           <li><a href="#"><span class="icon">🏋️</span> Entraînements</a></li>
           <li><a href="../aliments/aliments.html"><span class="icon">🥗</span> Aliments & courses</a></li>
           <li><a href="../courses/courses.html"><span class="icon">🛒</span> Listes de courses</a></li>
           <li><a href="#"><span class="icon">🤝</span> Espace coach</a></li>
           <!-- Ajoutez votre lien ici avec class="active" -->
       </ul>
       <div class="sidebar-section">Général</div>
       <ul class="sidebar-menu">
           <li><a href="#"><span class="icon">📈</span> Statistiques</a></li>
           <li><a href="#"><span class="icon">⚙️</span> Paramètres</a></li>
       </ul>
   </aside>

   <!-- CONTENU -->
   <div class="main-area">
       <div class="topbar">
           <h1>Votre titre de page</h1>
           <span class="date">Samedi 5 avril 2026</span>
       </div>

       <!-- Votre contenu ici -->
   </div>

   </body>
   </html>
   ```

3. **Classes CSS disponibles** (en plus de celles du FO) :
   - `.sidebar` — Menu latéral, mettre `class="active"` sur votre lien
   - `.main-area` — Zone de contenu principale (à droite du sidebar)
   - `.topbar` — En-tête avec titre + date
   - `.search-inline` — Champ de recherche compact
   - `.modal-overlay` + `.modal` — Popup modale (ajouter `.active` pour afficher)
   - `.form-group` / `.form-row` / `.form-check` — Formulaires stylisés
   - `.actions` — Groupe de boutons dans les tableaux
   - `.badge-actif` / `.badge-inactif` — Badges de statut

### Charte des couleurs

| Variable | Couleur | Usage |
|---|---|---|
| `--vert-foret` | #2d6a4f | Couleur principale, navbar, sidebar |
| `--vert-vif` | #52b788 | Accents, valeurs positives, boutons |
| `--vert-clair` | #95d5b2 | Bordures, éléments secondaires |
| `--vert-bg` | #d8f3dc (FO) / #f0fdf4 (BO) | Fond de page |
| `--orange-energie` | #f4845f | Alertes, valeurs importantes |

### Règles importantes

- **Chemin CSS** : Depuis `views/votre-module/`, le chemin est toujours `../../assets/css/style.css`
- **Liens entre pages** : Utilisez `../autre-module/page.html` pour naviguer entre modules
- **Ne pas modifier** les fichiers `style.css` existants — ajoutez vos styles spécifiques dans un fichier séparé si nécessaire
- **Respecter l'architecture MVC** : vues dans `views/`, logique dans `controllers/`, données dans `models/`

## Licence

Ce projet est réalisé dans un cadre académique à **Esprit** (École Supérieure Privée d'Ingénierie et de Technologies). Il est destiné à des fins éducatives.
