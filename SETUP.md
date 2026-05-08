# Guide d'installation - SportFuel

Ce guide vous aidera à configurer et lancer le projet SportFuel sur votre ordinateur local.

## Pré-requis
1. **XAMPP** ou **WAMP** installé sur votre ordinateur.
2. Un navigateur Web moderne (Chrome, Edge, Firefox).

## Étapes d'installation

### 1. Préparation des fichiers
Déplacez tout le dossier du projet (`SportFuel-Module1`) dans le dossier "serveur" de votre logiciel :
* **XAMPP** : `C:\xampp\htdocs\`
* **WAMP** : `C:\wamp64\www\`

### 2. Démarrage du serveur
1. Ouvrez le panneau de contrôle de XAMPP/WAMP.
2. Démarrez les services **Apache** et **MySQL**.

### 3. Création de la base de données (PDO, sans fichier SQL)
Ne pas importer de script SQL dans phpMyAdmin : la base est créée automatiquement par PHP (PDO).

1. Ouvrez dans le navigateur :  
   `http://localhost/SportFuel-Module1/init_db.php`
2. Une fois la page affichée avec succès, la base `sportfuel`, la table `utilisateurs` et le compte administrateur par défaut sont prêts.

### 4. Configuration (si nécessaire)
Si votre utilisateur MySQL `root` a un mot de passe, modifiez **une seule fois** le fichier `controller/db_settings.php` :

```php
$DB_PASS = "votre_mot_de_passe";
```

### 5. Accès au projet
Ouvrez votre navigateur et allez à l'adresse suivante :
[http://localhost/SportFuel-Module1/view/index.html](http://localhost/SportFuel-Module1/view/index.html)

## Notes importantes
* **Session** : Accédez au site via `http://localhost/` et non en ouvrant les fichiers directement (évitez `file:///C:/...`).
* **Erreurs** : Si vous rencontrez une erreur de connexion, vérifiez que MySQL est bien démarré.
* **Sécurité** : Après installation sur un serveur réel, supprimez ou protégez `init_db.php` pour éviter qu’il soit réexécuté publiquement.
