-- Base de données SportFuel
-- Alimentation durable & Nutrition intelligente

CREATE DATABASE IF NOT EXISTS SportFuel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE SportFuel;

-- Table PlanAlimentaire
CREATE TABLE IF NOT EXISTS PlanAlimentaire (
    id_plan INT PRIMARY KEY AUTO_INCREMENT,
    id_utilisateur INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    type ENUM('prise_de_masse', 'perte_de_poids', 'maintien', 'endurance') NOT NULL,
    kcal_cibles INT NOT NULL,
    semaine INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table Repas
CREATE TABLE IF NOT EXISTS Repas (
    id_repas INT PRIMARY KEY AUTO_INCREMENT,
    id_plan INT NOT NULL,
    jour ENUM('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche') NOT NULL,
    type_repas ENUM('petit_dejeuner','dejeuner','diner','collation') NOT NULL,
    description TEXT NOT NULL,
    kcal INT NOT NULL,
    FOREIGN KEY (id_plan) REFERENCES PlanAlimentaire(id_plan) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Données d'exemple pour PlanAlimentaire
INSERT INTO PlanAlimentaire (id_utilisateur, nom, type, kcal_cibles, semaine, date_debut, date_fin) VALUES
(1, 'Plan musculation semaine 1', 'prise_de_masse', 3200, 1, '2026-04-13', '2026-04-19'),
(2, 'Programme minceur printemps', 'perte_de_poids', 1800, 15, '2026-04-06', '2026-04-12'),
(1, 'Maintien forme été', 'maintien', 2400, 20, '2026-05-11', '2026-05-17'),
(3, 'Marathon préparation', 'endurance', 3500, 8, '2026-02-23', '2026-03-01'),
(2, 'Prise de masse avancée', 'prise_de_masse', 3800, 12, '2026-03-23', '2026-03-29'),
(3, 'Sèche compétition', 'perte_de_poids', 2000, 18, '2026-04-27', '2026-05-03');

-- Données d'exemple pour Repas
INSERT INTO Repas (id_plan, jour, type_repas, description, kcal) VALUES
(1, 'Lundi', 'petit_dejeuner', 'Flocons d\'avoine 80g, banane, whey 30g, amandes 20g', 520),
(1, 'Lundi', 'dejeuner', 'Poulet grillé 200g, riz basmati 100g, brocolis vapeur, huile d\'olive', 680),
(1, 'Lundi', 'diner', 'Saumon 180g, patate douce 150g, haricots verts, avocat 50g', 720),
(1, 'Lundi', 'collation', 'Yaourt grec 200g, fruits rouges, miel 10g', 280),
(2, 'Mardi', 'petit_dejeuner', 'Omelette 3 œufs, pain complet 40g, tomate, thé vert', 380),
(2, 'Mardi', 'dejeuner', 'Dinde 150g, quinoa 80g, courgettes grillées, citron', 450),
(2, 'Mardi', 'diner', 'Cabillaud 160g, légumes vapeur, salade verte, vinaigrette légère', 320),
(3, 'Mercredi', 'petit_dejeuner', 'Pancakes protéinés, sirop d\'érable, myrtilles', 420),
(3, 'Mercredi', 'dejeuner', 'Bœuf maigre 180g, pâtes complètes 100g, sauce tomate maison', 620),
(4, 'Jeudi', 'petit_dejeuner', 'Porridge banane-cannelle, beurre de cacahuète 20g, café', 580),
(4, 'Jeudi', 'dejeuner', 'Thon 150g, riz complet 120g, avocat, salade mixte', 680),
(4, 'Jeudi', 'collation', 'Smoothie protéiné : banane, whey, lait amande, épinards', 320),
(5, 'Vendredi', 'petit_dejeuner', 'Pain complet 60g, œufs brouillés 3, saumon fumé 40g', 480),
(5, 'Vendredi', 'dejeuner', 'Poulet curry 220g, riz basmati 120g, légumes sautés', 750);
