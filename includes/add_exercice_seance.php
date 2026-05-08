<?php
require_once '../config/database.php';
require_once '../FrontOffice/controllers/ExerciceSeanceController.php';

header('Content-Type: application/json; charset=utf-8');

$data = $_POST;
$errors = [];

if (empty($data['id_entrainement'])) {
    $errors[] = 'ID entraînement requis';
} elseif (!is_numeric($data['id_entrainement'])) {
    $errors[] = 'ID entraînement invalide';
}

if (empty($data['nom_exercice'])) {
    $errors[] = 'Nom de l\'exercice requis';
}

if (empty($data['duree_secondes']) && empty($data['duree'])) {
    $errors[] = 'Durée requise (en secondes)';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

try {
    $controller = new ExerciceSeanceController();

    $payload = [
        'id_entrainement' => (int) $data['id_entrainement'],
        'nom_exercice' => trim($data['nom_exercice']),
        'duree_secondes' => $data['duree_secondes'] ?? ($data['duree'] ?? null),
        'series' => $data['series'] ?? null,
        'repetitions' => $data['repetitions'] ?? null,
        'charge_kg' => $data['charge_kg'] ?? ($data['poids'] ?? null),
        'distance_km' => $data['distance_km'] ?? null
    ];

    ob_start();
    $controller->post($payload);
    $controller_output = ob_get_clean();

    $response_code = http_response_code();
    if ($response_code >= 400) {
        echo $controller_output;
        exit;
    }

    $controller_data = json_decode($controller_output, true);
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'id_exercice_seance' => $controller_data['id'] ?? null,
        'message' => 'Exercice ajouté avec succès'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
