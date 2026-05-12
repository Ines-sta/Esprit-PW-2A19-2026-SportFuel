<?php
require_once __DIR__ . '/../../config.php';
try {
    $pdo->exec("ALTER TABLE utilisateurs ADD COLUMN IF NOT EXISTS face_descriptor TEXT DEFAULT NULL");
    echo "Succès : Colonne face_descriptor ajoutée ou déjà existante.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Info : La colonne existe déjà.";
    } else {
        echo "Erreur : " . $e->getMessage();
    }
}
?>
