<?php
// Handler pour supprimer un entraînement - Utilise le contrôleur MVC

require_once '../config/database.php';
require_once '../FrontOffice/controllers/EntrainementController.php';

header('Content-Type: application/json; charset=utf-8');

// Récupérer l'ID depuis POST ou GET
$id_entrainement = $_POST['id_entrainement'] ?? $_GET['id_entrainement'] ?? null;

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

try {
    $controller = new EntrainementController();
    
    // Capture controller output to prevent double JSON
    ob_start();
    $controller->delete($id_entrainement);
    $controller_output = ob_get_clean();
    
    // Check if controller indicated an error (404 = not found)
    $response_code = http_response_code();
    if ($response_code >= 400) {
        echo $controller_output;
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Entraînement supprimé avec succès'
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
