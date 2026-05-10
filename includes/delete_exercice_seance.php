<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../Controller/training/ExerciceSeanceController.php';

header('Content-Type: application/json; charset=utf-8');

$id_exercice = $_POST['id_exercice_seance'] ?? $_GET['id_exercice_seance'] ?? $_POST['id_exercice'] ?? $_GET['id_exercice'] ?? null;

if (empty($id_exercice) || !is_numeric($id_exercice)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'ID exercice invalide'
    ]);
    exit;
}

try {
    $controller = new ExerciceSeanceController();

    ob_start();
    $controller->delete((int) $id_exercice);
    $controller_output = ob_get_clean();

    $response_code = http_response_code();
    if ($response_code >= 400) {
        echo $controller_output;
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Exercice supprimé avec succès'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
