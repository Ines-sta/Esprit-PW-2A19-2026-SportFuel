<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../../FrontOffice/controllers/EntrainementController.php';

$controller = new EntrainementController();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Récupérer l'id_utilisateur depuis les paramètres GET ou POST
            $id_utilisateur = $_GET['id_utilisateur'] ?? null;
            $id_entrainement = $_GET['id_entrainement'] ?? null;

            if (!$id_utilisateur) {
                http_response_code(400);
                echo json_encode(['error' => 'ID utilisateur requis']);
                break;
            }

            $controller->get($id_utilisateur, $id_entrainement);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                http_response_code(400);
                echo json_encode(['error' => 'Données JSON invalides']);
                break;
            }
            $controller->post($data);
            break;

        case 'PUT':
            $id_entrainement = $_GET['id_entrainement'] ?? null;
            if (!$id_entrainement) {
                http_response_code(400);
                echo json_encode(['error' => 'ID entraînement requis']);
                break;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                http_response_code(400);
                echo json_encode(['error' => 'Données JSON invalides']);
                break;
            }
            $controller->put($id_entrainement, $data);
            break;

        case 'DELETE':
            $id_entrainement = $_GET['id_entrainement'] ?? null;
            if (!$id_entrainement) {
                http_response_code(400);
                echo json_encode(['error' => 'ID entraînement requis']);
                break;
            }
            $controller->delete($id_entrainement);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}

?>