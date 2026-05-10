<?php
/**
 * SportFuel — Initialisation automatique de la base de données.
 *
 * Crée la base de données, toutes les tables et un compte administrateur par défaut.
 * Accès unique : http://localhost/Esprit-PW-2A19-2526-SportFuel/init_db.php
 *
 * Supprimez ce fichier après l'initialisation en production.
 */

require_once __DIR__ . '/Controller/shared/db_settings.php';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$DB_NAME`");

    // ── Identité ──────────────────────────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS utilisateurs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        mot_de_passe VARCHAR(255) NOT NULL,
        photo_profil_url VARCHAR(500) NULL,
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

    // Backward compatibility: ensure profile photo column exists on older databases
    try {
        $columnExistsStmt = $pdo->prepare("SHOW COLUMNS FROM utilisateurs LIKE 'photo_profil_url'");
        $columnExistsStmt->execute();
        if (!$columnExistsStmt->fetch(PDO::FETCH_ASSOC)) {
            $pdo->exec("ALTER TABLE utilisateurs ADD COLUMN photo_profil_url VARCHAR(500) NULL AFTER mot_de_passe");
        }
    } catch (Exception $e) {
        // Ignore and continue initialization flow
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS coach_sportif_assignments (
        id_assignment INT AUTO_INCREMENT PRIMARY KEY,
        id_coach INT NOT NULL,
        id_sportif INT NOT NULL,
        assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_coach_sportif (id_coach, id_sportif),
        FOREIGN KEY (id_coach) REFERENCES utilisateurs(id) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (id_sportif) REFERENCES utilisateurs(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // ── Nutrition ─────────────────────────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS aliment (
        id_aliment INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(150) NOT NULL,
        categorie VARCHAR(100) NOT NULL,
        kcal_portion FLOAT NOT NULL,
        co2_impact FLOAT NOT NULL,
        prix_unitaire FLOAT NOT NULL DEFAULT 0,
        est_bio TINYINT(1) DEFAULT 0,
        est_local TINYINT(1) DEFAULT 0,
        image_url VARCHAR(500) NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS PlanAlimentaire (
        id_plan INT AUTO_INCREMENT PRIMARY KEY,
        id_utilisateur INT NOT NULL,
        nom VARCHAR(100) NOT NULL,
        type ENUM('prise_de_masse','perte_de_poids','maintien','endurance') NOT NULL,
        kcal_cibles INT NOT NULL,
        semaine INT NOT NULL,
        date_debut DATE NOT NULL,
        date_fin DATE NOT NULL,
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE RESTRICT ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS Repas (
        id_repas INT AUTO_INCREMENT PRIMARY KEY,
        id_plan INT NOT NULL,
        jour ENUM('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche') NOT NULL,
        type_repas ENUM('petit_dejeuner','dejeuner','diner','collation') NOT NULL,
        description TEXT NOT NULL,
        kcal INT NOT NULL,
        FOREIGN KEY (id_plan) REFERENCES PlanAlimentaire(id_plan) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS repas_aliment (
        id_repas INT NOT NULL,
        id_aliment INT NOT NULL,
        quantite FLOAT NOT NULL,
        unite ENUM('g','kg','ml','L','piece') NOT NULL DEFAULT 'g',
        PRIMARY KEY (id_repas, id_aliment, unite),
        FOREIGN KEY (id_repas) REFERENCES Repas(id_repas) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (id_aliment) REFERENCES aliment(id_aliment) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS course (
        id_course INT AUTO_INCREMENT PRIMARY KEY,
        id_utilisateur INT NOT NULL,
        nom VARCHAR(150) NOT NULL,
        date DATE NOT NULL,
        statut VARCHAR(50) NOT NULL DEFAULT 'Non demarree',
        image_url VARCHAR(500) NULL,
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE RESTRICT ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS course_aliment (
        id_course INT NOT NULL,
        id_aliment INT NOT NULL,
        quantite FLOAT NOT NULL,
        unite VARCHAR(10) NOT NULL DEFAULT 'g',
        achete TINYINT(1) DEFAULT 0,
        PRIMARY KEY (id_course, id_aliment),
        FOREIGN KEY (id_course) REFERENCES course(id_course) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (id_aliment) REFERENCES aliment(id_aliment) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // ── Publications & Commentaires ───────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS publication (
        id_pub INT AUTO_INCREMENT PRIMARY KEY,
        id_utilisateur INT NOT NULL,
        text TEXT,
        priorite VARCHAR(20) NOT NULL DEFAULT 'normal',
        priority_score INT NOT NULL DEFAULT 30,
        statut VARCHAR(20) NOT NULL DEFAULT 'En attente',
        date DATETIME,
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS commentaire (
        id_cmmnt INT AUTO_INCREMENT PRIMARY KEY,
        id_pub INT,
        id_utilisateur INT NOT NULL,
        text TEXT,
        date DATETIME,
        FOREIGN KEY (id_pub) REFERENCES publication(id_pub) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // ── Entraînements ─────────────────────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS entrainements (
        id_entrainement INT AUTO_INCREMENT PRIMARY KEY,
        id_utilisateur INT NOT NULL,
        titre VARCHAR(255) NOT NULL,
        date_entrainement DATE NOT NULL,
        duree_totale INT NULL,
        notes_globales TEXT NULL,
        statut VARCHAR(30) NOT NULL DEFAULT 'En attente',
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE RESTRICT ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS exercices_seance (
        id_exercice_seance INT AUTO_INCREMENT PRIMARY KEY,
        id_entrainement INT NOT NULL,
        nom_exercice VARCHAR(255) NOT NULL,
        duree_secondes INT NOT NULL,
        series INT NULL,
        repetitions INT NULL,
        charge_kg DECIMAL(8,2) NULL,
        distance_km DECIMAL(8,2) NULL,
        ordre INT NOT NULL DEFAULT 0,
        FOREIGN KEY (id_entrainement) REFERENCES entrainements(id_entrainement) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // ── Compte Admin par défaut ───────────────────────────────────────────────
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
            body { font-family: Arial, sans-serif; background: #1a3c2e; color: white; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
            .box { background: rgba(255,255,255,0.1); padding: 40px; border-radius: 20px; text-align: center; max-width: 520px; }
            h1 { color: #52b788; }
            .ok { font-size: 40px; }
            p { line-height: 1.6; }
            .warn { font-size: 13px; color: #f4a261; margin-top: 16px; }
            a { display: inline-block; margin-top: 20px; padding: 14px 28px; background: linear-gradient(135deg, #52b788, #f4a261); color: white; text-decoration: none; border-radius: 999px; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='box'>
            <div class='ok'>✅</div>
            <h1>Base de données initialisée !</h1>
            <p>La base <strong>sportfuel</strong> et toutes les tables ont été créées.</p>
            $adminNotice
            <p class='warn'>⚠️ Supprimez <code>init_db.php</code> après l'initialisation en production.</p>
            <a href='/Esprit-PW-2A19-2526-SportFuel/'>🚀 Accéder à l'application</a>
        </div>
    </body>
    </html>
    ";

} catch (PDOException $e) {
    echo "
    <!DOCTYPE html>
    <html><head><meta charset='UTF-8'><title>Erreur</title>
    <style>body{font-family:Arial;background:#1a3c2e;color:white;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;}
    .box{background:rgba(255,0,0,0.2);padding:40px;border-radius:20px;max-width:600px;text-align:center;}</style></head>
    <body><div class='box'>
    <h1>❌ Erreur de connexion MySQL</h1>
    <p>" . htmlspecialchars($e->getMessage()) . "</p>
    <p>Vérifiez que MySQL est démarré dans WAMP.<br>
    Si vous avez un mot de passe root, modifiez <code>Controller/shared/db_settings.php</code>.</p>
    </div></body></html>
    ";
}
