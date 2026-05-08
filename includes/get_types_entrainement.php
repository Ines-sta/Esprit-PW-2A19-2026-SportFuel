<?php
require_once '../config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $database = new Database();
    $pdo = $database->getPDO();

    $sql = "SELECT DISTINCT titre
            FROM entrainements
            WHERE titre IS NOT NULL
              AND TRIM(titre) <> ''
            ORDER BY titre ASC";

    $stmt = $pdo->query($sql);
    $types = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'types' => $types
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
