
<?php
require_once __DIR__ . '/Database.php';

class Commentaire {
    private $pdo;

    public function __construct() {
        $this->pdo = SocialDatabase::getConnection();
    }

    public function getAllCommentaires() {
        $sql = "SELECT c.*, p.text as pub_text
                FROM commentaire c
                JOIN publication p ON c.id_pub = p.id_pub
                ORDER BY c.date DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function addCommentaire($id_pub, $text) {
        $stmt = $this->pdo->prepare("INSERT INTO commentaire (id_pub, text, date) VALUES (?, ?, NOW())");
        return $stmt->execute([$id_pub, $text]);
    }

    public function updateCommentaire($id, $text) {
        $stmt = $this->pdo->prepare("UPDATE commentaire SET text = ?, date = NOW() WHERE id_cmmnt = ?");
        return $stmt->execute([$text, $id]);
    }

    public function deleteCommentaire($id) {
        $stmt = $this->pdo->prepare("DELETE FROM commentaire WHERE id_cmmnt = ?");
        return $stmt->execute([$id]);
    }

    public function validateText($text) {
        // Validation: not empty, min 5 chars, max 200 chars
        if (empty(trim($text))) {
            return "Le commentaire ne peut pas être vide.";
        }
        if (strlen($text) < 5) {
            return "Le commentaire doit contenir au moins 5 caractères.";
        }
        if (strlen($text) > 200) {
            return "Le commentaire ne peut pas dépasser 200 caractères.";
        }
        return true;
    }

    public function getPdo() {
        return $this->pdo;
    }
}
?>