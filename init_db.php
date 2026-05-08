<?php
/**
 * Initialisation automatique de la base SportFuel via PDO (aucun import SQL manuel).
 * Ouvrez une fois : http://localhost/SportFuel-Module1/init_db.php
 * Supprimez ce fichier après l'installation en production.
 */

require_once __DIR__ . '/controller/db_settings.php';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$DB_NAME` COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$DB_NAME`");

    $pdo->exec("CREATE TABLE IF NOT EXISTS utilisateurs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        mot_de_passe VARCHAR(255) NOT NULL,
        age INT DEFAULT 0,
        poids FLOAT DEFAULT 0,
        taille FLOAT DEFAULT 0,
        sport_pratique VARCHAR(100) DEFAULT 'Aucun',
        objectif VARCHAR(100) DEFAULT 'Non défini',
        niveau VARCHAR(100) DEFAULT 'Débutant',
        seances_semaine INT DEFAULT 0,
        role VARCHAR(50) DEFAULT 'Sportif',
        statut VARCHAR(50) DEFAULT 'Actif',
        date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    try {
        $pdo->exec('ALTER TABLE utilisateurs ADD COLUMN date_inscription TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
    } catch (PDOException $e) {
        if (stripos($e->getMessage(), 'Duplicate column') === false) {
            throw $e;
        }
    }

    $adminPass = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = 'admin@sportfuel.tn'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $pdo->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role, statut, age, poids, taille)
                       VALUES ('Admin SportFuel', 'admin@sportfuel.tn', ?, 'Admin', 'Actif', 30, 75, 175)")
            ->execute([$adminPass]);
        $adminNotice = '<p>✅ Compte admin créé : <strong>admin@sportfuel.tn</strong> / <strong>admin123</strong></p>';
    } else {
        $adminNotice = '<p>ℹ️ Le compte admin existe déjà.</p>';
    }

    echo "
    <!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <title>Initialisation - SportFuel</title>
        <style>
            body { font-family: Arial, sans-serif; background: #1a3c2e; color: white; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
            .box { background: rgba(255,255,255,0.1); padding: 40px; border-radius: 20px; text-align: center; max-width: 500px; }
            h1 { color: #52b788; }
            .ok { color: #52b788; font-size: 40px; }
            a { display: inline-block; margin-top: 20px; padding: 14px 28px; background: linear-gradient(135deg, #52b788, #f4a261); color: white; text-decoration: none; border-radius: 999px; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='box'>
            <div class='ok'>✅</div>
            <h1>Base de données initialisée !</h1>
            <p>La base <strong>sportfuel</strong> et la table <strong>utilisateurs</strong> ont été créées via PDO.</p>
            $adminNotice
            <p><strong>Compte Admin :</strong><br>Email : admin@sportfuel.tn<br>Mot de passe : admin123</p>
            <a href='http://localhost/SportFuel-Module1/view/index.html'>🚀 Accéder à l'application</a>
        </div>
    </body>
    </html>
    ";

} catch (PDOException $e) {
    echo "
    <!DOCTYPE html>
    <html><head><meta charset='UTF-8'><title>Erreur</title>
    <style>body{font-family:Arial;background:#1a3c2e;color:white;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;}
    .box{background:rgba(255,0,0,0.2);padding:40px;border-radius:20px;max-width:600px;text-align:center;}</style></head>
    <body><div class='box'>
    <h1>❌ Erreur de connexion MySQL</h1>
    <p>" . htmlspecialchars($e->getMessage()) . "</p>
    <p><strong>Solutions :</strong><br>
    1. Vérifiez que MySQL est démarré dans XAMPP<br>
    2. Si vous avez un mot de passe MySQL, modifiez <code>controller/db_settings.php</code></p>
    </div></body></html>
    ";
}
