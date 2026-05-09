-- SportFuel bridge migration
-- Purpose: align social `user` table with canonical `utilisateurs` identities.
-- Run once on existing databases after schema deployment.

USE sportfuel;

-- 1) Ensure bridge column exists.
SET @has_utilisateur_id := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'user'
      AND COLUMN_NAME = 'utilisateur_id'
);

SET @sql_add_utilisateur_id := IF(
    @has_utilisateur_id = 0,
    'ALTER TABLE `user` ADD COLUMN utilisateur_id INT NULL AFTER id_user',
    'SELECT 1'
);
PREPARE stmt_add_utilisateur_id FROM @sql_add_utilisateur_id;
EXECUTE stmt_add_utilisateur_id;
DEALLOCATE PREPARE stmt_add_utilisateur_id;

-- 2) Ensure bridge index exists.
SET @has_bridge_index := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'user'
      AND INDEX_NAME = 'idx_user_utilisateur_id'
);

SET @sql_add_bridge_index := IF(
    @has_bridge_index = 0,
    'ALTER TABLE `user` ADD INDEX idx_user_utilisateur_id (utilisateur_id)',
    'SELECT 1'
);
PREPARE stmt_add_bridge_index FROM @sql_add_bridge_index;
EXECUTE stmt_add_bridge_index;
DEALLOCATE PREPARE stmt_add_bridge_index;

-- 3) Link existing social users to utilisateurs by email when possible.
UPDATE `user` u
JOIN utilisateurs ut ON LOWER(TRIM(u.email)) = LOWER(TRIM(ut.email))
SET u.utilisateur_id = ut.id
WHERE u.utilisateur_id IS NULL;

-- 4) Seed missing social users from utilisateurs.
--    Keep password hash from utilisateurs; use empty prenom if not available.
INSERT INTO `user` (id_user, utilisateur_id, nom, prenom, email, password, role)
SELECT
    ut.id,
    ut.id,
    ut.nom,
    '',
    ut.email,
    ut.mot_de_passe,
    ut.role
FROM utilisateurs ut
LEFT JOIN `user` u_by_bridge ON u_by_bridge.utilisateur_id = ut.id
LEFT JOIN `user` u_by_email ON LOWER(TRIM(u_by_email.email)) = LOWER(TRIM(ut.email))
WHERE u_by_bridge.id_user IS NULL
  AND u_by_email.id_user IS NULL;

-- 5) Reconcile linked social users with canonical identity fields.
UPDATE `user` u
JOIN utilisateurs ut ON u.utilisateur_id = ut.id
SET
    u.nom = COALESCE(NULLIF(ut.nom, ''), u.nom),
    u.email = COALESCE(NULLIF(ut.email, ''), u.email),
    u.password = COALESCE(NULLIF(ut.mot_de_passe, ''), u.password),
    u.role = COALESCE(NULLIF(ut.role, ''), u.role);

-- 6) Optional verification query (run manually).
-- SELECT u.id_user, u.utilisateur_id, u.email, ut.email AS utilisateur_email
-- FROM `user` u
-- LEFT JOIN utilisateurs ut ON u.utilisateur_id = ut.id
-- WHERE u.utilisateur_id IS NULL OR ut.id IS NULL;