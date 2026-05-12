<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $database = new Database();
    $pdo = $database->getPDO();

    $sql = "SELECT id, nom, email, role, statut
            FROM utilisateurs
            WHERE role = 'Coach'
            ORDER BY nom ASC";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $out = array_map(function ($row) {
        return [
            'id' => (int) $row['id'],
            'nom' => $row['nom'] ?? '',
            'email' => $row['email'] ?? '',
            'role' => $row['role'] ?? '',
            'statut' => $row['statut'] ?? ''
        ];
    }, $rows);

    echo json_encode([
        'success' => true,
        'data' => $out
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
