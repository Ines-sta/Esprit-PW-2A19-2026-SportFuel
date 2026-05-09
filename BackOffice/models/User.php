<?php
require_once __DIR__ . '/Database.php';

class User {
    private $pdo;

    public function __construct() {
        $this->pdo = SocialDatabase::getConnection();
    }

    public function getAllUsers() {
        $stmt = $this->pdo->query("SELECT id_user, prenom, nom, email FROM `user`");
        return $stmt->fetchAll();
    }

    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT id_user, prenom, nom, email FROM `user` WHERE id_user = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
?>