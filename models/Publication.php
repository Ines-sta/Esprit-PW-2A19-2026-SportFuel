<?php
require_once __DIR__ . '/Database.php';

class Publication {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function getAllPublications() {
        $sql = "SELECT p.*, u.nom, u.prenom, u.email
                FROM publication p
                LEFT JOIN `user` u ON p.id_user = u.user_id
                ORDER BY p.date DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function updatePublication($id, $text) {
        $stmt = $this->pdo->prepare("UPDATE publication SET text = ? WHERE id_pub = ?");
        return $stmt->execute([$text, $id]);
    }

    public function deletePublication($id) {
        $stmt = $this->pdo->prepare("DELETE FROM publication WHERE id_pub = ?");
        return $stmt->execute([$id]);
    }

    public function validateText($text) {
        // Validation: not empty, min 10 chars, max 500 chars
        if (empty(trim($text))) {
            return "Le texte ne peut pas être vide.";
        }
        if (strlen($text) < 10) {
            return "Le texte doit contenir au moins 10 caractères.";
        }
        if (strlen($text) > 500) {
            return "Le texte ne peut pas dépasser 500 caractères.";
        }
        return true;
    }

    public function getPdo() {
        return $this->pdo;
    }
}
?>