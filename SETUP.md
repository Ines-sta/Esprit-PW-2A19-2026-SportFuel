# Guide d'installation - SportFuel

Ce guide correspond a la structure canonique actuelle du projet `Esprit-PW-2A19-2526-SportFuel`.

## Pre-requis
1. **WAMP** ou **XAMPP** avec Apache + MySQL.
2. PHP avec PDO MySQL active.
3. Un navigateur moderne.

## Installation rapide

### 1. Placer le projet dans le serveur local
Copiez le dossier `Esprit-PW-2A19-2526-SportFuel` dans :
* **WAMP** : `C:\wamp64\www\`
* **XAMPP** : `C:\xampp\htdocs\`

### 2. Demarrer Apache et MySQL
Lancez votre stack locale puis verifiez que les services Apache et MySQL sont actifs.

### 3. Initialiser la base
Le projet dispose d'un bootstrap PDO pour la table `utilisateurs` et le compte admin par defaut.

1. Ouvrez : `http://localhost/Esprit-PW-2A19-2526-SportFuel/init_db.php`
2. Si besoin, adaptez d'abord `Controller/db_settings.php`.
3. Pour la structure consolidee complete, utilisez aussi `database/schema.sql`.
4. Pour des donnees de demonstration (membres du groupe + nutrition de base), appliquez `database/seeds/001-populate-baseline.sql`.
5. Pour une base existante, appliquez ensuite les scripts de `database/migrations/` selon le besoin.

### 4. Points d'entree utiles
* Accueil statique : `http://localhost/Esprit-PW-2A19-2526-SportFuel/View/index.html`
* Connexion : `http://localhost/Esprit-PW-2A19-2526-SportFuel/View/connexion.html`
* Routeur plans : `http://localhost/Esprit-PW-2A19-2526-SportFuel/index.php`
* BackOffice canonique : `http://localhost/Esprit-PW-2A19-2526-SportFuel/BackOffice/index.php`

## Notes
* Evitez d'ouvrir les pages directement en `file:///...`.
* `database/schema.sql` est la source canonique de schema pendant la migration.
* Les anciens fichiers SQL racine ont ete archives dans `archive/phase7-2026-05-09-legacy-root-sql/`.
* Apres installation sur un vrai serveur, protegez ou supprimez `init_db.php`.
