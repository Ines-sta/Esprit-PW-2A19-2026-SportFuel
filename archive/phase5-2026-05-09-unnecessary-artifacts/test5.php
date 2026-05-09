<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/models/Database.php';

try {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM user");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
