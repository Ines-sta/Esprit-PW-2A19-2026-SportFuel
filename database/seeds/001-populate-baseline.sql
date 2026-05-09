-- SportFuel baseline seed data
-- Purpose: populate a usable development dataset on top of database/schema.sql
-- Notes:
-- 1) Run schema first: database/schema.sql
-- 2) This file is idempotent for core identity rows (ON DUPLICATE KEY UPDATE)

USE sportfuel;

-- Reusable bcrypt hash for demo/dev password: password
SET @seed_password := '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- ============================================================
-- Group members as canonical users
-- ============================================================
INSERT INTO utilisateurs
    (nom, email, mot_de_passe, age, poids, taille, sport_pratique, objectif, niveau, seances_semaine, role, statut)
VALUES
    ('Ines Sta', 'ines.sta@sportfuel.tn', @seed_password, 21, 58, 165, 'Course a pied', 'Endurance', 'Intermediaire', 4, 'Admin', 'Actif'),
    ('Maram Bendoulet', 'maram.bendoulet@sportfuel.tn', @seed_password, 21, 60, 168, 'Musculation', 'Prise de masse', 'Intermediaire', 5, 'Coach', 'Actif'),
    ('Yassine Bellagha', 'yassine.bellagha@sportfuel.tn', @seed_password, 22, 74, 178, 'Cyclisme', 'Maintien', 'Intermediaire', 4, 'Sportif', 'Actif'),
    ('Dhya Laabidi', 'dhya.laabidi@sportfuel.tn', @seed_password, 21, 63, 170, 'Natation', 'Perte de poids', 'Debutant', 3, 'Sportif', 'Actif'),
    ('Bayrem Hariz', 'bayrem.hariz@sportfuel.tn', @seed_password, 22, 76, 180, 'Musculation', 'Prise de masse', 'Avance', 5, 'Sportif', 'Actif')
ON DUPLICATE KEY UPDATE
    nom = VALUES(nom),
    mot_de_passe = VALUES(mot_de_passe),
    age = VALUES(age),
    poids = VALUES(poids),
    taille = VALUES(taille),
    sport_pratique = VALUES(sport_pratique),
    objectif = VALUES(objectif),
    niveau = VALUES(niveau),
    seances_semaine = VALUES(seances_semaine),
    role = VALUES(role),
    statut = VALUES(statut);

-- ============================================================
-- Bridge social users from canonical utilisateurs
-- ============================================================
INSERT INTO `user` (id_user, utilisateur_id, nom, prenom, email, password, role)
SELECT
    u.id,
    u.id,
    u.nom,
    '',
    u.email,
    u.mot_de_passe,
    u.role
FROM utilisateurs u
LEFT JOIN `user` su ON su.utilisateur_id = u.id OR su.email = u.email
WHERE su.id_user IS NULL;

UPDATE `user` su
JOIN utilisateurs u ON su.utilisateur_id = u.id OR su.email = u.email
SET
    su.utilisateur_id = u.id,
    su.nom = u.nom,
    su.email = u.email,
    su.password = u.mot_de_passe,
    su.role = u.role;

-- ============================================================
-- Minimal nutrition data
-- ============================================================
INSERT INTO aliment (nom, categorie, kcal_portion, co2_impact, prix_unitaire, est_bio, est_local)
VALUES
    ('Flocons d''avoine', 'Cereales', 117, 0.18, 2.5, 1, 0),
    ('Blanc de poulet', 'Proteines', 165, 1.80, 8.5, 0, 1),
    ('Thon en conserve', 'Proteines', 132, 0.95, 5.0, 0, 0),
    ('Riz complet', 'Glucides', 111, 0.30, 2.2, 1, 0),
    ('Banane', 'Fruits', 89, 0.08, 1.2, 0, 1),
    ('Huile d''olive', 'Lipides', 119, 0.22, 4.8, 1, 1)
ON DUPLICATE KEY UPDATE
    categorie = VALUES(categorie),
    kcal_portion = VALUES(kcal_portion),
    co2_impact = VALUES(co2_impact),
    prix_unitaire = VALUES(prix_unitaire),
    est_bio = VALUES(est_bio),
    est_local = VALUES(est_local);

INSERT INTO PlanAlimentaire
    (id_utilisateur, nom, type, kcal_cibles, semaine, date_debut, date_fin)
SELECT
    u.id,
    'Plan Performance Semaine 1',
    'maintien',
    2500,
    1,
    CURDATE(),
    DATE_ADD(CURDATE(), INTERVAL 6 DAY)
FROM utilisateurs u
WHERE u.email = 'yassine.bellagha@sportfuel.tn'
  AND NOT EXISTS (
      SELECT 1
      FROM PlanAlimentaire p
      WHERE p.id_utilisateur = u.id
        AND p.nom = 'Plan Performance Semaine 1'
  );

INSERT INTO Repas (id_plan, jour, type_repas, description, kcal)
SELECT p.id_plan, d.jour, d.type_repas, d.description, d.kcal
FROM (
    SELECT 'Lundi' AS jour, 'petit_dejeuner' AS type_repas, 'Flocons d''avoine + banane' AS description, 420 AS kcal
    UNION ALL SELECT 'Lundi', 'dejeuner', 'Riz complet + blanc de poulet', 760
    UNION ALL SELECT 'Lundi', 'diner', 'Thon + salade + huile d''olive', 620
    UNION ALL SELECT 'Mardi', 'petit_dejeuner', 'Flocons d''avoine + banane', 420
    UNION ALL SELECT 'Mardi', 'dejeuner', 'Riz complet + blanc de poulet', 760
    UNION ALL SELECT 'Mardi', 'diner', 'Thon + legumes', 580
) d
JOIN PlanAlimentaire p ON p.nom = 'Plan Performance Semaine 1'
LEFT JOIN Repas r
    ON r.id_plan = p.id_plan
   AND r.jour = d.jour
   AND r.type_repas = d.type_repas
WHERE r.id_repas IS NULL;

INSERT INTO course (id_utilisateur, nom, date, statut)
SELECT u.id, 'Courses semaine 1', CURDATE(), 'En cours'
FROM utilisateurs u
WHERE u.email = 'yassine.bellagha@sportfuel.tn'
  AND NOT EXISTS (
      SELECT 1
      FROM course c
      WHERE c.id_utilisateur = u.id
        AND c.nom = 'Courses semaine 1'
  );

INSERT INTO course_aliment (id_course, id_aliment, quantite, unite, achete)
SELECT c.id_course, a.id_aliment, q.quantite, q.unite, 0
FROM (
    SELECT 'Flocons d''avoine' AS nom, 500 AS quantite, 'g' AS unite
    UNION ALL SELECT 'Blanc de poulet', 1200, 'g'
    UNION ALL SELECT 'Riz complet', 1000, 'g'
    UNION ALL SELECT 'Banane', 8, 'piece'
    UNION ALL SELECT 'Huile d''olive', 500, 'ml'
) q
JOIN aliment a ON a.nom = q.nom
JOIN course c ON c.nom = 'Courses semaine 1'
LEFT JOIN course_aliment ca ON ca.id_course = c.id_course AND ca.id_aliment = a.id_aliment
WHERE ca.id_course IS NULL;
