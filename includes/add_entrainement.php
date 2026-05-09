<?php
// Handler pour ajouter un entraînement - Utilise le contrôleur MVC

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../FrontOffice/controllers/EntrainementController.php';

header('Content-Type: application/json; charset=utf-8');

// Récupérer les données POST
$data = $_POST;

// Validation des données côté serveur
$errors = [];

// Validation du titre
if (empty($data['titre'])) {
    $errors[] = 'Le type d\'entraînement est requis';
}

// Validation de la date
if (empty($data['date_entrainement'])) {
    $errors[] = 'La date est requise';
} else {
    // Vérifier que la date est au bon format
    $d = DateTime::createFromFormat('Y-m-d', $data['date_entrainement']);
    if (!$d || $d->format('Y-m-d') !== $data['date_entrainement']) {
        $errors[] = 'Format de date invalide';
    }
}

// Validation de la durée (optionnelle)
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
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $controller = new EntrainementController();
    
    // Préparer les données pour le modèle
    $entrainement_data = [
        'id_utilisateur' => $data['id_utilisateur'] ?? ($_SESSION['user_id'] ?? 1), // À adapter selon votre système d'authentification
        'titre' => $data['titre'],
        'date_entrainement' => $data['date_entrainement'],
        'duree_totale' => $data['duree_totale'] ?? null,
        'notes_globales' => $data['notes'] ?? null
    ];
    
    // Capture la réponse du contrôleur pour récupérer l'ID créé.
    ob_start();
    $controller->post($entrainement_data);
    $controller_output = ob_get_clean();

    $response_code = http_response_code();
    if ($response_code >= 400) {
        echo $controller_output;
        exit;
    }

    $controller_data = json_decode($controller_output, true);
    $created_id = $controller_data['id'] ?? null;

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'id_entrainement' => $created_id,
        'message' => 'Entraînement enregistré avec succès'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
