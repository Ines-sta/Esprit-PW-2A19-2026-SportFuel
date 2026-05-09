-- SportFuel canonical schema (consolidated)
-- Source files consolidated:
-- - database.sql (aliments, courses)
-- - sportfuel.sql (plans, repas)
-- - init_db.php (utilisateurs)
-- - BackOffice/models/Database.php (social tables)

CREATE DATABASE IF NOT EXISTS sportfuel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sportfuel;

-- ============================================================
-- Core identity
-- ============================================================
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    age INT DEFAULT 0,
    poids FLOAT DEFAULT 0,
    taille FLOAT DEFAULT 0,
    sport_pratique VARCHAR(100) DEFAULT 'Aucun',
    objectif VARCHAR(100) DEFAULT 'Non defini',
    niveau VARCHAR(100) DEFAULT 'Debutant',
    seances_semaine INT DEFAULT 0,
    role VARCHAR(50) DEFAULT 'Sportif',
    statut VARCHAR(50) DEFAULT 'Actif',
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Nutrition plans
-- ============================================================
CREATE TABLE IF NOT EXISTS PlanAlimentaire (
    id_plan INT PRIMARY KEY AUTO_INCREMENT,
    id_utilisateur INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    type ENUM('prise_de_masse', 'perte_de_poids', 'maintien', 'endurance') NOT NULL,
    kcal_cibles INT NOT NULL,
    semaine INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS Repas (
    id_repas INT PRIMARY KEY AUTO_INCREMENT,
    id_plan INT NOT NULL,
    jour ENUM('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche') NOT NULL,
    type_repas ENUM('petit_dejeuner','dejeuner','diner','collation') NOT NULL,
    description TEXT NOT NULL,
    kcal INT NOT NULL,
    FOREIGN KEY (id_plan) REFERENCES PlanAlimentaire(id_plan)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Aliments and courses
-- ============================================================
CREATE TABLE IF NOT EXISTS aliment (
    id_aliment INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    categorie VARCHAR(100) NOT NULL,
    kcal_portion FLOAT NOT NULL,
    co2_impact FLOAT NOT NULL,
    prix_unitaire FLOAT NOT NULL DEFAULT 0,
    est_bio TINYINT(1) DEFAULT 0,
    est_local TINYINT(1) DEFAULT 0,
    image_url VARCHAR(500) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS course (
    id_course INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    nom VARCHAR(150) NOT NULL,
    date DATE NOT NULL,
    statut VARCHAR(50) NOT NULL DEFAULT 'Non demarree',
    image_url VARCHAR(500) NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS course_aliment (
    id_course INT NOT NULL,
    id_aliment INT NOT NULL,
    quantite FLOAT NOT NULL,
    unite VARCHAR(10) NOT NULL DEFAULT 'g',
    achete TINYINT(1) DEFAULT 0,
    PRIMARY KEY (id_course, id_aliment),
    FOREIGN KEY (id_course) REFERENCES course(id_course) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_aliment) REFERENCES aliment(id_aliment) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Social module (coach/publications)
-- ============================================================
CREATE TABLE IF NOT EXISTS user (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50),
    prenom VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role VARCHAR(50) DEFAULT 'Sportif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS publication (
    id_pub INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT,
    text TEXT,
    priorite VARCHAR(20) NOT NULL DEFAULT 'normal',
    priority_score INT NOT NULL DEFAULT 30,
    statut VARCHAR(20) NOT NULL DEFAULT 'En attente',
    date DATETIME,
    FOREIGN KEY (id_user) REFERENCES user(id_user)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS commentaire (
    id_cmmnt INT AUTO_INCREMENT PRIMARY KEY,
    id_pub INT,
    id_user INT,
    text TEXT,
    date DATETIME,
    FOREIGN KEY (id_pub) REFERENCES publication(id_pub)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_user) REFERENCES user(id_user)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NOTE:
-- Training tables (entrainements, exercices_seance) are currently managed
-- through application runtime and should be added here in a follow-up
-- migration once the final table contract is confirmed.
-- ============================================================
