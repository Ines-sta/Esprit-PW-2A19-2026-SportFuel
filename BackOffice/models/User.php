<?php
require_once __DIR__ . '/Database.php';

class User {
    private $pdo;

    public function __construct() {
        $this->pdo = SocialDatabase::getConnection();
    }

    public function getAllUsers() {
        $stmt = $this->pdo->query(
            "SELECT
                u.id_user,
                u.utilisateur_id,
                COALESCE(ut.nom, u.nom) AS nom,
                COALESCE(u.prenom, '') AS prenom,
                COALESCE(ut.email, u.email) AS email,
                COALESCE(ut.role, u.role, 'Sportif') AS role,
                ut.statut AS statut
             FROM `user` u
             LEFT JOIN utilisateurs ut ON u.utilisateur_id = ut.id"
        );
        return $stmt->fetchAll();
    }

    public function getUserById($id) {
        $stmt = $this->pdo->prepare(
            "SELECT
                u.id_user,
                u.utilisateur_id,
                COALESCE(ut.nom, u.nom) AS nom,
                COALESCE(u.prenom, '') AS prenom,
                COALESCE(ut.email, u.email) AS email,
                COALESCE(ut.role, u.role, 'Sportif') AS role,
                ut.statut AS statut
             FROM `user` u
             LEFT JOIN utilisateurs ut ON u.utilisateur_id = ut.id
             WHERE u.id_user = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
?>