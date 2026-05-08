<?php
// Handler pour mettre à jour un entraînement - Utilise le contrôleur MVC

require_once '../config/database.php';
require_once '../FrontOffice/controllers/EntrainementController.php';

header('Content-Type: application/json; charset=utf-8');

// Récupérer les données
$id_entrainement = $_POST['id_entrainement'] ?? null;
$data = $_POST;

// Validation de l'ID
if (empty($id_entrainement)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'ID d\'entraînement manquant'
    ]);
    exit;
}

if (!is_numeric($id_entrainement)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'ID invalide'
    ]);
    exit;
}

// Validation des données
$errors = [];

// Validation de la date si présente
if (!empty($data['date_entrainement'])) {
    $d = DateTime::createFromFormat('Y-m-d', $data['date_entrainement']);
    if (!$d || $d->format('Y-m-d') !== $data['date_entrainement']) {
        $errors[] = 'Format de date invalide';
    }
}

// Validation de la durée si présente
if (!empty($data['duree_totale'])) {
    if (!is_numeric($data['duree_totale']) || $data['duree_totale'] < 1) {
        $errors[] = 'La durée doit être un nombre positif';
    }
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

try {
    $controller = new EntrainementController();
    
    // Préparer les données à mettre à jour
    $update_data = [];
    if (!empty($data['titre'])) $update_data['titre'] = $data['titre'];
    if (!empty($data['date_entrainement'])) $update_data['date_entrainement'] = $data['date_entrainement'];
    if (!empty($data['duree_totale'])) $update_data['duree_totale'] = $data['duree_totale'];
    if (isset($data['notes'])) $update_data['notes_globales'] = $data['notes'];
    if (!empty($data['statut'])) $update_data['statut'] = $data['statut'];
    
    // Capture controller output to prevent double JSON
    ob_start();
    $controller->put($id_entrainement, $update_data);
    $controller_output = ob_get_clean();
    
    // Check if controller indicated an error (404 = not found)
    $response_code = http_response_code();
    if ($response_code >= 400) {
        echo $controller_output;
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Entraînement mis à jour avec succès'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
