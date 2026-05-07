<?php
require_once '../config/database.php';
require_once '../FrontOffice/controllers/ExerciceSeanceController.php';

header('Content-Type: application/json; charset=utf-8');

$id_exercice = $_POST['id_exercice_seance'] ?? $_POST['id_exercice'] ?? null;
$data = $_POST;

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

    $update_data = [];
    if (isset($data['nom_exercice'])) $update_data['nom_exercice'] = trim($data['nom_exercice']);
    if (isset($data['duree_secondes'])) $update_data['duree_secondes'] = $data['duree_secondes'];
    if (isset($data['series'])) $update_data['series'] = $data['series'];
    if (isset($data['repetitions'])) $update_data['repetitions'] = $data['repetitions'];
    if (isset($data['charge_kg'])) $update_data['charge_kg'] = $data['charge_kg'];
    if (isset($data['distance_km'])) $update_data['distance_km'] = $data['distance_km'];

    if (empty($update_data)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Aucun champ à mettre à jour'
        ]);
        exit;
    }

    ob_start();
    $controller->put((int) $id_exercice, $update_data);
    $controller_output = ob_get_clean();

    $response_code = http_response_code();
    if ($response_code >= 400) {
        echo $controller_output;
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Exercice mis à jour avec succès'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
