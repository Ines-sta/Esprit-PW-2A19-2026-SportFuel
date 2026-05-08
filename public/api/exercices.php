<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../../FrontOffice/controllers/ExerciceSeanceController.php';

$controller = new ExerciceSeanceController();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $id_entrainement = $_GET['id_entrainement'] ?? null;
            $id_exercice = $_GET['id_exercice'] ?? null;

            $controller->get($id_entrainement, $id_exercice);
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
            $id_exercice = $_GET['id_exercice_seance'] ?? $_GET['id_exercice'] ?? null;
            if (!$id_exercice) {
                http_response_code(400);
                echo json_encode(['error' => 'ID exercice requis']);
                break;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                http_response_code(400);
                echo json_encode(['error' => 'Données JSON invalides']);
                break;
            }
            $controller->put($id_exercice, $data);
            break;

        case 'DELETE':
            $id_exercice = $_GET['id_exercice_seance'] ?? $_GET['id_exercice'] ?? null;
            if (!$id_exercice) {
                http_response_code(400);
                echo json_encode(['error' => 'ID exercice requis']);
                break;
            }
            $controller->delete($id_exercice);
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