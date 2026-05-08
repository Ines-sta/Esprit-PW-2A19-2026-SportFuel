<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/models/Database.php';

try {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SHOW CREATE TABLE publication");
    print_r($stmt->fetch());
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
