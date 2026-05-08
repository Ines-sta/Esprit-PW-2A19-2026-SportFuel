-- ============================================
-- SportFuel — Base de données Module 4
-- Aliments + Listes de courses
-- ============================================

-- ============================================
-- Migration pour bases existantes (à exécuter une seule fois)
-- ============================================
-- ALTER TABLE course ADD COLUMN nom VARCHAR(150) NOT NULL DEFAULT 'Liste sans nom' AFTER id_utilisateur;
-- ALTER TABLE course_aliment ADD COLUMN unite VARCHAR(10) NOT NULL DEFAULT 'g' AFTER quantite;
-- ALTER TABLE aliment ADD COLUMN prix_unitaire FLOAT NOT NULL DEFAULT 0 AFTER co2_impact;
-- ALTER TABLE aliment ADD COLUMN image_url VARCHAR(500) NULL AFTER est_local;
-- ALTER TABLE course  ADD COLUMN image_url VARCHAR(500) NULL AFTER statut;

CREATE DATABASE IF NOT EXISTS sportfuel;
USE sportfuel;

-- Table: Aliment
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
);

-- Données de test: Aliments
INSERT INTO aliment (nom, categorie, kcal_portion, co2_impact, prix_unitaire, est_bio, est_local) VALUES
('Huile d''olive tunisienne', 'Huiles & Graisses', 884, 0.8, 2.8, 1, 1),
('Couscous complet', 'Céréales & Féculents', 376, 0.5, 0.6, 0, 1),
('Œufs fermiers', 'Protéines', 155, 0.3, 1.2, 1, 1),
('Harissa artisanale', 'Légumes', 45, 0.2, 0.9, 0, 1),
('Yaourt naturel', 'Produits laitiers', 59, 0.4, 0.7, 1, 0),
('Poivrons grillés', 'Légumes', 31, 0.1, 0.5, 1, 1),
('Thon de Tabarka', 'Protéines', 132, 0.6, 2.0, 0, 1),
('Dattes Deglet Nour', 'Fruits', 282, 0.2, 1.4, 1, 1);

-- Table: Course (Liste de courses)
CREATE TABLE IF NOT EXISTS course (
    id_course INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    nom VARCHAR(150) NOT NULL,
    date DATE NOT NULL,
    statut VARCHAR(50) NOT NULL DEFAULT 'Non démarrée',
    image_url VARCHAR(500) NULL
);

-- Table: CourseAliment (articles d'une liste)
-- unite : 'g', 'kg', 'ml', 'L', 'piece'
CREATE TABLE IF NOT EXISTS course_aliment (
    id_course INT NOT NULL,
    id_aliment INT NOT NULL,
    quantite FLOAT NOT NULL,
    unite VARCHAR(10) NOT NULL DEFAULT 'g',
    achete TINYINT(1) DEFAULT 0,
    PRIMARY KEY (id_course, id_aliment),
    FOREIGN KEY (id_course) REFERENCES course(id_course) ON DELETE CASCADE,
    FOREIGN KEY (id_aliment) REFERENCES aliment(id_aliment) ON DELETE CASCADE
);

-- Données de test: Courses
INSERT INTO course (id_utilisateur, nom, date, statut) VALUES
(1, 'Courses semaine marathon', '2026-04-15', 'En cours'),
(1, 'Préparation compétition',  '2026-04-18', 'Complétée'),
(2, 'Courses hebdomadaires',    '2026-04-19', 'Non démarrée');

-- Données de test: CourseAliment
INSERT INTO course_aliment (id_course, id_aliment, quantite, unite, achete) VALUES
(1, 1, 0.5, 'L',     1),
(1, 3, 12,  'piece', 0),
(1, 6, 0.5, 'kg',    1),
(1, 7, 400, 'g',     0),
(2, 2, 1,   'kg',    1),
(2, 5, 500, 'g',     1),
(2, 8, 0.5, 'kg',    1),
(3, 1, 1,   'L',     0),
(3, 4, 300, 'g',     0),
(3, 8, 1,   'kg',    0);
