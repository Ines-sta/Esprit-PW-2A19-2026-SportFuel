<?php
require_once __DIR__ . '/Database.php';

class User {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function getAllUsers() {
        $stmt = $this->pdo->query("SELECT user_id AS id_user, prenom, nom, email FROM `user`");
        return $stmt->fetchAll();
    }

    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT user_id AS id_user, prenom, nom, email FROM `user` WHERE user_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
?>