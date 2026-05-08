<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/models/Database.php';

try {
    $pdo = Database::getConnection();
    
    // Check tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(", ", $tables) . "\n";
    
    if (in_array('user', $tables)) {
        $cols = $pdo->query("DESCRIBE `user`")->fetchAll(PDO::FETCH_COLUMN);
        echo "User columns: " . implode(", ", $cols) . "\n";
    }
    
    if (in_array('publication', $tables)) {
        $cols = $pdo->query("DESCRIBE `publication`")->fetchAll(PDO::FETCH_COLUMN);
        echo "Publication columns: " . implode(", ", $cols) . "\n";
    }
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
