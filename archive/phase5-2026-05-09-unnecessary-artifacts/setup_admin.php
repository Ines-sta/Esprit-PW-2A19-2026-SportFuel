<?php
require_once __DIR__ . '/controller/config.php';

try {
    // 1. Update schema
    $pdo->exec("ALTER TABLE utilisateurs ADD COLUMN IF NOT EXISTS role VARCHAR(50) DEFAULT 'Sportif'");
    $pdo->exec("ALTER TABLE utilisateurs ADD COLUMN IF NOT EXISTS statut VARCHAR(50) DEFAULT 'Actif'");

    // 2. Insert or update Admin
    $password = password_hash('admin', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = 'admin@sportfuel.tn'");
    $stmt->execute();
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ?, role = 'Admin', statut = 'Actif' WHERE email = 'admin@sportfuel.tn'");
        $stmt->execute([$password]);
        echo "Admin updated.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role, statut) VALUES ('Admin SportFuel', 'admin@sportfuel.tn', ?, 'Admin', 'Actif')");
        $stmt->execute([$password]);
        echo "Admin created.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
