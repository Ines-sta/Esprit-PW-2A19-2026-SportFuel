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
                ut.id AS id,
                ut.id AS id_user,
                ut.id AS user_id,
                ut.id AS id_utilisateur,
                ut.nom AS nom,
                '' AS prenom,
                ut.email AS email,
                ut.role AS role,
                ut.statut AS statut
             FROM utilisateurs ut"
        );
        return $stmt->fetchAll();
    }

    public function getUserById($id) {
        $stmt = $this->pdo->prepare(
            "SELECT
                ut.id AS id,
                ut.id AS id_user,
                ut.id AS user_id,
                ut.id AS id_utilisateur,
                ut.nom AS nom,
                '' AS prenom,
                ut.email AS email,
                ut.role AS role,
                ut.statut AS statut
             FROM utilisateurs ut
             WHERE ut.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
?>