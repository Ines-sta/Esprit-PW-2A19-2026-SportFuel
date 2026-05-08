<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Entrainement.php';

class EntrainementController {
    private $model;

    public function __construct() {
        $database = new Database();
        $this->model = new Entrainement($database);
    }

    // Gérer les requêtes GET
    public function get($id_utilisateur = null, $id_entrainement = null) {
        try {
            if ($id_entrainement) {
                $result = $this->model->getById($id_entrainement);
                if (!$result) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Entraînement non trouvé']);
                    return;
                }
            } else {
                $result = $this->model->getAll($id_utilisateur);
            }

            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Gérer les requêtes POST
    public function post($data) {
        try {
            $id = $this->model->create($data);
            http_response_code(201);
            echo json_encode(['id' => $id, 'message' => 'Entraînement créé avec succès']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Gérer les requêtes PUT
    public function put($id_entrainement, $data) {
        try {
            $affected = $this->model->update($id_entrainement, $data);
            if ($affected === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Entraînement non trouvé']);
                return;
            }
            echo json_encode(['message' => 'Entraînement mis à jour avec succès']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Gérer les requêtes DELETE
    public function delete($id_entrainement) {
        try {
            $affected = $this->model->delete($id_entrainement);
            if ($affected === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Entraînement non trouvé']);
                return;
            }
            echo json_encode(['message' => 'Entraînement supprimé avec succès']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

?>