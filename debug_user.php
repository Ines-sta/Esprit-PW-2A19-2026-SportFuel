<?php
require_once __DIR__ . '/controller/config.php';

$email = 'marambendoulet2005@gmail.com'; // L'email que vous utilisez

echo "<h1>Diagnostic Utilisateur : $email</h1>";

try {
    $stmt = $pdo->prepare("SELECT id, nom, email, mot_de_passe, role, statut FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $users = $stmt->fetchAll();

    if (count($users) === 0) {
        echo "<p style='color:red'>❌ AUCUN utilisateur trouvé avec cet email.</p>";
    } else {
        echo "<p style='color:green'>✅ " . count($users) . " utilisateur(s) trouvé(s).</p>";
        foreach ($users as $u) {
            echo "<hr>";
            echo "ID: " . $u['id'] . "<br>";
            echo "Nom: " . $u['nom'] . "<br>";
            echo "Email en base: " . $u['email'] . "<br>";
            echo "Hash (début): " . substr($u['mot_de_passe'], 0, 10) . "...<br>";
            echo "Longueur du hash: " . strlen($u['mot_de_passe']) . " caractères<br>";
            
            // Test manuel de hash
            $test_pass = 'votre_nouveau_password'; // Vous pouvez changer ceci pour tester
            echo "Test password_verify('123456', hash) : " . (password_verify('123456', $u['mot_de_passe']) ? '✅ OUI' : '❌ NON') . "<br>";
        }
    }
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
