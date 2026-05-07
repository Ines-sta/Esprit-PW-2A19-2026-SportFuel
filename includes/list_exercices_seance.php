<?php
require_once '../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$id_entrainement = $_GET['id_entrainement'] ?? null;

if (empty($id_entrainement) || !is_numeric($id_entrainement)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'ID entraînement requis'
    ]);
    exit;
}

try {
    $database = new Database();
    $pdo = $database->getPDO();

    $sql = "SELECT id_exercice_seance, id_entrainement, nom_exercice, series, repetitions, charge_kg, duree_secondes, distance_km, ordre
            FROM exercices_seance
            WHERE id_entrainement = ?
            ORDER BY ordre ASC, id_exercice_seance ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([(int) $id_entrainement]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $rows
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
